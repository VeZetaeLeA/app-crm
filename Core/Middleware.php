<?php
namespace Core;

/**
 * Middleware Interface
 */
interface Middleware
{
    /**
     * Handle the incoming request
     * 
     * @param array $params Optional parameters for the middleware
     * @return bool|void Return false to stop execution, void to continue
     */
    public function handle($params = []);
}
