<?php

namespace App\Controllers;

use App\Models\ArticuloModel;
use App\Models\GeneroModel;

class Home extends BaseController
{
    private $roles = null;

    public function __construct()
    {
        $session = \Config\Services::session();
    }

    private function validarParametros($precioMin, $precioMax)
    {
        // 2.1.1. S: Detecta incongruencia en los datos
        if ($precioMin !== null && $precioMax !== null && $precioMin > $precioMax) {
            return "Error de validación: El precio mínimo no puede ser mayor al precio máximo.";
        }
        
        // Retorna null si no hay errores
        return null; 
    }

    public function index()
    {
        $prodModel = new ArticuloModel();
        $generoModel = new GeneroModel();

        // Capturamos los filtros que vienen por GET desde el formulario
        // Convertimos  '' en null
        $titulo = $this->request->getGet('titulo');
        $titulo = ($titulo !== '' && $titulo !== null) ? trim($titulo) : null;

        $idGenero = $this->request->getGet('idGenero');
        $idGenero = ($idGenero !== '' && $idGenero !== null) ? $idGenero : null;

        $idAutor = $this->request->getGet('idAutor');
        $idAutor = ($idAutor !== '' && $idAutor !== null) ? $idAutor : null;

        $precioMin = $this->request->getGet('precioMin');
        $precioMin = ($precioMin !== '' && $precioMin !== null) ? (float)$precioMin : null;

        $precioMax = $this->request->getGet('precioMax');
        $precioMax = ($precioMax !== '' && $precioMax !== null) ? (float)$precioMax : null;




        // Llamada explícita al método de validación
        $error = $this->validarParametros($precioMin, $precioMax);
        $data['error_validacion'] = $error;

        if ($error) {
            // Si hay error, frenamos la ejecución y devolvemos un arreglo vacío
            $resultado = [];
        } else {
            // Si pasa la validación, ejecuta la consulta
            $resultado = $prodModel->getArticulosFiltrados($titulo, $idGenero, $idAutor, $precioMin, $precioMax);
        }

        // Obtenemos la lista de géneros para armar el menú desplegable (select) en la vista
        //$generos = $generoModel->findAll();
        $data['generos'] = $generoModel->findAll();

        // Preparamos el arreglo de datos que le vamos a enviar a las vistas
        $data['autores'] = \Config\Database::connect()->table('autor')->get()->getResultArray(); 

        $data['titulo'] = 'Catálogo de Libros';
        $data['productos'] = $resultado;

        // Renderizamos el contenido
        echo view('plantillas/head', $data);
        echo view('plantillas/navbar');
        // Pasamos tanto los productos como los géneros a la vista principal
        echo view('Contenido/index', $data); 
        echo view('plantillas/footer');
    }
}