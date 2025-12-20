<?php

namespace App\Controllers;

use CodeIgniter\Entity\Cast\StringCast;
use CodeIgniter\HTTP\Request;
use CodeIgniter\Session\Session;
use App\Models\ArticuloModel;
use App\Models\AutorModel;
use App\Models\EditorialModel;
use App\Models\GeneroModel;

class Articulo extends BaseController{
    private $genero;
    private $editorial;
    private $autor;
    private $articulo;
    /**
     * Constructor de la clase ProductController
     */
    public function __construct()
    {
        $data = [
            'generos'     => [],
            'editoriales' => [],
            'autores'     => []
        ];

    }

    public function show_product_form(){
        
        // Inicializamos vacíos
        // Esto asegura que la vista tenga variables aunque la BD falle
        $generos = [];
        $autores = [];
        $editoriales = [];

        // Intentamos llenar los datos
        try {
            // Instanciamos AQUÍ ADENTRO para proteger la conexión
            $generoModel = new GeneroModel();
            $autorModel = new AutorModel();
            $editorialModel = new EditorialModel();       
            
            $generos = $generoModel->getGeneros();
            $autores = $autorModel->getAutores();
            $editoriales = $editorialModel->getEditoriales();

        } catch (\Throwable $th) {
            // Si falla la BD, no hacemos nada
            // El error real ya viene en la sesión desde validarDatos().
            // Aquí solo evitamos que la página explote al intentar cargar los combos.
        }

        // Cargamos la vista, ahora es seguro
        return view('plantillas/head') .
               view('plantillas/navbar') . 
               view('contenido/VistaAgregarArticulo', [
                   'generos' => $generos,
                   'editoriales' => $editoriales,
                   'autores' => $autores
               ]) .
               view('plantillas/footer');
    }    

// ****************************************** METODO VALIDARDATOS *********************************************
    public function validarDatos()
    {
        // Validar inputs con tus reglas manuales (más claro para la defensa)
        $reglas = [
            'nombre_libro'    => 'required|min_length[3]|max_length[255]|is_unique[articulo.titulo]',
            'precio_libro'    => 'required|numeric|greater_than[0]',
            'editorial_libro' => 'required|is_natural_no_zero',
            'autor_libro'     => 'required|is_natural_no_zero',
            'genero_libro'    => 'required|is_natural_no_zero',
            'sinopsis_libro'  => 'required',
            'paginas_libro'   => 'required|is_natural_no_zero',
            'fecha_libro'     => 'required'
        ];

        if (! $this->validate($reglas)) {
            // Camino Alternativo 1: Error de validación
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        // Procesar la Imagen
        $archivoImagen = $this->request->getFile('imagen');
        $nombreImagen = null; 

        if ($archivoImagen && $archivoImagen->isValid() && !$archivoImagen->hasMoved()) {
            $nombreImagen = $archivoImagen->getRandomName();
            $archivoImagen->move('public/imagenes', $nombreImagen);
        }

        // Preparar datos (Nota: Eliminé la segunda validación redundante 'newBook')
        $datos = [
            'titulo'       => $this->request->getPost('nombre_libro'),
            'precio'       => floatval($this->request->getPost('precio_libro')),
            'editorial_id' => intval($this->request->getPost('editorial_libro')),
            'sinopsis'     => $this->request->getPost('sinopsis_libro'),
            'paginas'      => intval($this->request->getPost('paginas_libro')),
            'autor_id'     => intval($this->request->getPost('autor_libro')),
            'genero_id'    => intval($this->request->getPost('genero_libro')),
            'img'          => $nombreImagen,
            'fecha_publicacion' => $this->request->getPost('fecha_libro') // Pasamos directo, el modelo formatea
        ];

        // Inserción Segura
        try {
            // Creamos el modelo DENTRO de la protección
            $articuloModel = new ArticuloModel();

            // LLAMAMOS AL MODELO
            $resultado = $articuloModel->insertaArticulo($datos);
            
            if ($resultado['resultado'] == 0) {
                // Error lógico de BD (ej: título duplicado)
                return redirect()->back()
                                ->withInput()
                                ->with('mensaje_error', 'Error BD: ' . $resultado['msj_error']);
            }

            // Camino Feliz: Éxito
            session()->setFlashdata('mensaje', '¡Libro creado con éxito!');
            return redirect()->to(base_url('products'));

        } catch (\Throwable $th) {
            // Error Crítico (BD apagada)
            $mensajeError = "No se pudo guardar el libro. " . $th->getMessage();
            return redirect()->back()->withInput()->with('mensaje_error', $mensajeError);
        }

        // cargo la vista aunque la conexion a la db falle
        return view('plantillas/head') .
        view('plantillas/navbar') .
        view('contenido/VistaAgregarArticulo',['generos' => $generos,'editoriales'=>$editoriales,'autores'=>$autores]) .
        view('plantillas/footer');

    }

}  