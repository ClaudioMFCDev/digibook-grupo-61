<?php

namespace App\Controllers;

use App\Models\ArticuloModel;
use App\Models\GeneroModel;
use App\Models\ServicioDashboard;
use App\Models\ReporteDTO;

class Home extends BaseController
{
    private $roles = null;
    protected $session = null;

    public function __construct()
    {
        // Inicializar el servicio de sesiones de CodeIgniter 4
        $this->session = \Config\Services::session();
    }

    // Maneja la visualización y el filtrado del Dashboard Comercial.
    public function dashboard()
    {
        $servicioDashboard = new ServicioDashboard();
        $error = null;

        // Capturar los filtros GET que vienen desde la URL de Bootstrap
        $fechaDesde = $this->request->getGet('fechaDesde');
        $fechaDesde = ($fechaDesde !== '' && $fechaDesde !== null) ? $fechaDesde : null;

        $fechaHasta = $this->request->getGet('fechaHasta');
        $fechaHasta = ($fechaHasta !== '' && $fechaHasta !== null) ? $fechaHasta : null;

        // Camino Crítico: Carga inicial del panel (Sin filtros activos)
        if ($fechaDesde === null && $fechaHasta === null) {
            // Verificar si el ReporteDTO por defecto (últimos 30 días) ya existe en la caché de la Sesión
            if (!$this->session->has('reporte_defecto')) {
                // Fallo de caché: El servicio coordina la creación del DTO por primera vez
                $reporteInicial = $servicioDashboard->obtenerReporteConsolidado(
                    date('Y-m-d', strtotime('-30 days')), 
                    date('Y-m-d')
                );
                // Guardar el objeto analítico en la sesión del servidor
                $this->session->set('reporte_defecto', $reporteInicial);
            }
            
            // Recuperar los datos directamente desde el almacenamiento de la sesión
            $data['reporte'] = $this->session->get('reporte_defecto');
        } 
        else {
            // Camino Alternativo: El usuario aplicó filtros de fecha específicos.
            // Llamada explícita al ServicioDashboard para validar, tal como exige el DDS
            $error = $servicioDashboard->validarPeriodo($fechaDesde, $fechaHasta);

            if ($error) {
                // Validación fallida: Cargar mensaje de error y restaurar caché de sesión
                $data['error_validacion'] = $error;
                $data['reporte'] = $this->session->get('reporte_defecto');
            } else {
                // Validación exitosa: El servicio solicita el reporte consolidado
                $resultado = $servicioDashboard->obtenerReporteConsolidado($fechaDesde, $fechaHasta);

                // Caso de valores en cero: Período válido pero sin transacciones comerciales
                if ($resultado->totalIngresos == 0 && $resultado->cantidadVentas == 0) {
                    $data['error_validacion'] = "Advertencia: El período seleccionado no registra movimientos comerciales. Se restauraron los datos por defecto.";
                    $data['reporte'] = $this->session->get('reporte_defecto');
                } else {
                    // Se encontraron registros válidos para el período solicitado
                    $data['reporte'] = $resultado;
                }
            }
        }

        // Preparar el empaquetado de datos para la vista
        $data['titulo'] = 'Dashboard Analítico Comercial';
        $data['fechaDesdeFiltro'] = $fechaDesde;
        $data['fechaHastaFiltro'] = $fechaHasta;

        // Renderizar el layout completo de respuestas
        echo view('plantillas/head', $data);
        echo view('plantillas/navbar');
        echo view('Dashboard/index', $data); 
        echo view('plantillas/footer');
    }    

    // Valida parametros para la función de búsqueda avanzada (Se mantiene intacto)
    private function validarParametros($precioMin, $precioMax)
    {
        if ($precioMin !== null && $precioMax !== null && $precioMin > $precioMax) {
            return "Error de validación: El precio mínimo no puede ser mayor al precio máximo.";
        }
        return null; 
    }

    // Método index para el catálogo del Home (Se mantiene intacto)
    public function index()
    {
        $prodModel = new ArticuloModel();
        $generoModel = new GeneroModel();

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

        $error = $this->validarParametros($precioMin, $precioMax);
        $data['error_validacion'] = $error;

        if ($error) {
            $resultado = [];
        } else {
            $resultado = $prodModel->obtenerArticulosFiltrados($titulo, $idGenero, $idAutor, $precioMin, $precioMax);
        }

        $data['generos'] = $generoModel->findAll();
        $data['autores'] = \Config\Database::connect()->table('autor')->get()->getResultArray(); 

        $data['titulo'] = 'Catálogo de Libros';
        $data['productos'] = $resultado;

        echo view('plantillas/head', $data);
        echo view('plantillas/navbar');
        echo view('Contenido/index', $data); 
        echo view('plantillas/footer');
    }
}