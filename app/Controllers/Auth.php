<?php

namespace App\Controllers;

use CodeIgniter\Controller;

class Auth extends BaseController
{
    /**
     * Render the login view
     */
    public function index()
    {
        return view('login');
    }

    /**
     * Controller to user login and session management
     */
    public function autenticar()
    {
        $session = session();
        
        // Grab credentials from POST request
        $email = $this->request->getPost('email');
        $contrasena = $this->request->getPost('contrasena');

        // Hardcoded credentials for testing/defense purposes
        $usuarioHardcodeado = [
            'dni'        => '40123456',
            'nombre'     => 'Administrador Claudio',
            'email'      => 'admin@digibook.com',
            'contrasena' => 'admin123',
            'sexo'       => 'M',
            'rol'        => 'admin'
        ];

        // Basic credentials matching logic
        if ($email === $usuarioHardcodeado['email'] && $contrasena === $usuarioHardcodeado['contrasena']) {
            
            // Set session global variables
            $sessionData = [
                'dni'      => $usuarioHardcodeado['dni'],
                'nombre'   => $usuarioHardcodeado['nombre'],
                'email'    => $usuarioHardcodeado['email'],
                'rol'      => $usuarioHardcodeado['rol'],
                'isLoggedIn' => true
            ];
            
            $session->set($sessionData);

            // Redirect to dashboard if successful
            return redirect()->to(base_url('dashboard'));
        } else {
            // Set temporary flash message and redirect back
            $session->setFlashdata('error', 'Credenciales inválidas. Intente nuevamente.');
            return redirect()->to(base_url('login'));
        }
    }

    /**
     * Destroy user session
     */
    public function logout()
    {
        session()->destroy();
        return redirect()->to(base_url('login'));
    }
}