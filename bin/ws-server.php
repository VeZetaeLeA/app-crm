<?php
require __DIR__ . '/../vendor/autoload.php';

use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use Core\SocketHandler;
use React\EventLoop\Factory;
use React\Socket\Server as ReactSocket;
use Clue\React\Redis\Factory as RedisFactory;

define('BASE_PATH', realpath(__DIR__ . '/..'));

// Load .env manual if needed or rely on putenv
if (file_exists(BASE_PATH . '/.env')) {
    $lines = file(BASE_PATH . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        list($name, $value) = explode('=', $line, 2);
        putenv(trim($name) . "=" . trim($value));
    }
}

$loop = Factory::create();
$socketHandler = new SocketHandler();

// WebSocket Server
$webSock = new ReactSocket('0.0.0.0:8080', $loop);
$server = new IoServer(
    new HttpServer(
        new WsServer(
            $socketHandler
        )
    ),
    $webSock,
    $loop
);

// Redis Subscriber (Bridge to bridge HTTP -> WebSocket)
$redisFactory = new RedisFactory($loop);
$redisHost = getenv('REDIS_HOST') ?: '127.0.0.1';
$redisPort = getenv('REDIS_PORT') ?: '6379';

$redisFactory->createClient("redis://{$redisHost}:{$redisPort}")->then(function ($client) use ($socketHandler) {
    echo "Connected to Redis Subscriber\n";
    
    $client->subscribe('Vezetaelea-realtime');
    
    $client->on('message', function ($channel, $payload) use ($socketHandler) {
        echo "Redis Message received: {$payload}\n";
        $data = json_decode($payload, true);
        
        if (isset($data['target_user'])) {
            $socketHandler->sendToUser($data['target_user'], $payload);
        } else {
            $socketHandler->broadcast($payload);
        }
    });
}, function (\Exception $e) {
    echo "Could not connect to Redis: {$e->getMessage()}\n";
});

echo "WebSocket Server started on port 8080\n";
$loop->run();
