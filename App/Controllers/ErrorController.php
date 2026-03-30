<?php
namespace App\Controllers;

use Core\Controller;

class ErrorController extends Controller
{
    public function index()
    {
        http_response_code(404);
        $this->viewLayout('public/errors/404', 'error', [
            'title' => '404 - Ruta No Encontrada | ' . \Core\Config::get('business.company_name')
        ]);
    }
}
