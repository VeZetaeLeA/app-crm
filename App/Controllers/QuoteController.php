<?php
namespace App\Controllers;

use Core\Controller;
use Core\Auth;

class QuoteController extends Controller
{
    /**
     * Confirmation page after budget/service request
     */
    public function received()
    {
        $this->viewLayout('public/quote/received', 'public', [
            'title' => 'Solicitud Recibida | ' . \Core\Config::get('business.company_name')
        ]);
    }
}
