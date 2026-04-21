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

    public function index()
    {
        $prodModel = new ArticuloModel();
        $generoModel = new GeneroModel();

        // Capturamos los filtros que vienen por GET desde el formulario
        $titulo    = $this->request->getGet('titulo') ?: null;
        $idGenero  = $this->request->getGet('idGenero') ?: null;
        $precioMin = $this->request->getGet('precioMin') ?: null;
        $precioMax = $this->request->getGet('precioMax') ?: null;

        // Verificamos si hay una búsqueda activa
        if ($titulo || $idGenero || $precioMin || $precioMax) {
            // Si hay filtros, usamos el método que llama al Procedimiento Almacenado
            $resultado = $prodModel->getArticulosFiltrados($titulo, $idGenero, $precioMin, $precioMax);
        } else {
            $consultaOriginal = $prodModel->getArticulos();
            $resultado = $consultaOriginal['resultado'];
        }

        // Obtenemos todos los géneros para el select de la búsqueda
        $generos = $generoModel->findAll();

        // Preparamos los datos para las vistas
        $data['titulo'] = 'Catálogo de Libros';
        $data['productos'] = $resultado;
        $data['generos'] = $generos;

        // Renderizamos el contenido
        echo view('plantillas/head', $data);
        echo view('plantillas/navbar');
        // Pasamos tanto los productos como los géneros a la vista principal
        echo view('Contenido/index', $data); 
        echo view('plantillas/footer');
    }
}