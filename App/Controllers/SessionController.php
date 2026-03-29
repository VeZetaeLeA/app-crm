<?php
namespace App\Controllers;

use Core\Session;

class SessionController
{
    /**
     * Endpoint para mantener la sesión viva (Heartbeat)
     */
    public function heartbeat()
    {
        header('Content-Type: application/json');

        // Simplemente respondemos éxito. 
        // El simple hecho de hacer la petición actualiza la cookie de sesión en el servidor.
        echo json_encode(['status' => 'alive', 'timestamp' => time()]);
        exit;
    }
}
