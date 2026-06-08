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
        return view('Contenido/login');    
    }

    /**
     * Controller to user login and session management
     */
    public function autenticar()
    {
        $session = session();
        $db = \Config\Database::connect();
        
        // Grab credentials from POST request
        $email = $this->request->getPost('email');
        $contrasena = $this->request->getPost('contrasena');

        // Query the database to find the user by credentials
        $usuario = $db->table('usuario')
            ->where('email', $email)
            ->where('contrasenia', $contrasena)
            ->get()
            ->getRowArray();

        if ($usuario) {
            // Set session global variables based on database entity attributes
            $sessionData = [
                'dni'          => $usuario['dni'],
                'nombre'       => $usuario['nombre'],
                'email'        => $usuario['email'],
                'idTipoUsuario'=> (int)$usuario['idTipoUsuario'], // 1 = Admin, 2 = Cliente
                'isLoggedIn'   => true
            ];
            
            $session->set($sessionData);

            // Redirect to dashboard if successful and user is Admin
            if ((int)$usuario['idTipoUsuario'] === 1) {
                return redirect()->to(base_url('dashboard'));
            }
            
            // Redirect to home if user is a standard customer
            return redirect()->to(base_url('/'));
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
        return redirect()->to(base_url('/'));
    }
}