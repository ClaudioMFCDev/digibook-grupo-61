<?php

namespace App\Controllers;

use App\Models\ServicioDashboard;
use App\Libraries\ValidarPeriodo; 

class Dashboard extends BaseController 
{
    protected $servicioDashboard;

    public function __construct() 
    {
        // Constructor para inicializar el modelo orquestador del tablero
        $this->servicioDashboard = new ServicioDashboard();
    }

    public function validarPeriodo($fechaDesde, $fechaHasta){

        $datosAValidar = ['desde' => $fechaDesde, 'hasta' => $fechaHasta];
        // Instanciamos la estrategia concreta del patrón
        $validador = new ValidarPeriodo();

        return $validador->validar($datosAValidar);
    }

    public function index() 
    {
        // 1. Captura de parámetros enviados desde el formulario por POST
        $fechaDesde = $this->request->getPost('fechaDesde');
        $fechaHasta = $this->request->getPost('fechaHasta');
        
        $alertaError = null;
        $alertaInfo  = null;

        // Control de interfaz por si el usuario envía campos vacíos
        if ($this->request->getMethod() === 'post' && (empty($fechaDesde) || empty($fechaHasta))) {
            $alertaError = 'Se necesitan ambas fechas para realizar la consulta.';
            $fechaDesde = date('Y-m-d', strtotime('-30 days'));
            $fechaHasta = date('Y-m-d');
            $reporteDTO = session()->get('reporte_defecto');
            
        // Carga inicial legítima del sistema (Petición GET ordinaria sin filtros)
        } elseif (empty($fechaDesde) || empty($fechaHasta)) {
            $fechaDesde = date('Y-m-d', strtotime('-30 days'));
            $fechaHasta = date('Y-m-d');
        }

        // IMPLEMENTACIÓN DEL PATRÓN STRATEGY
        if ($alertaError === null) {

            // DELEGACIÓN: El controlador no calcula marcas de tiempo, solo le pregunta a la estrategia
            if (!$this->validarPeriodo($fechaDesde, $fechaHasta)) {
                
                // Mensaje unificado para los casos fallidos de fechas invertidas o futuras
                $alertaError = 'Error: El rango de fechas seleccionado es incoherente o superior a la fecha actual. Operación frenada.';
                
                // Contingencia segura: Forzamos la restauración de los datos por defecto desde la sesión
                $fechaDesde = date('Y-m-d', strtotime('-30 days'));
                $fechaHasta = date('Y-m-d');
                $reporteDTO = session()->get('reporte_defecto');
                
            } else {
                
                // CAMINO FELIZ Y GUARDADO DE DATOS POR DEFECTO EN SESIÓN
                // Ejecución del método real del servicio de datos
                $reporteDTO = $this->servicioDashboard->obtenerReporteConsolidado($fechaDesde, $fechaHasta);

                // Si es la primera vez que entra a la app, congelamos este reporte analítico en la sesión
                if (!session()->has('reporte_defecto')) {
                    session()->set('reporte_defecto', $reporteDTO);
                }

                // Curso Alternativo 2: El rango temporal es correcto pero la base de datos está vacía
                if ($reporteDTO->cantidadVentas == 0) {
                    $alertaInfo = 'Advertencia: El período seleccionado no registra movimientos comerciales. Se restauraron los datos por defecto.';
                    $reporteDTO = session()->get('reporte_defecto');
                }
            }
        }

        // Respaldo de seguridad absoluta por si falló alguna asignación en memoria
        if (!isset($reporteDTO) || $reporteDTO === null) {
            $reporteDTO = session()->get('reporte_defecto') ?? $this->servicioDashboard->obtenerReporteConsolidado($fechaDesde, $fechaHasta);
        }

        // 4. Empaquetado final de variables controladas para la interfaz de usuario
        $data = [
            'titulo'      => 'Dashboard Analítico Comercial',
            'reporte'     => $reporteDTO,
            'desde'       => $fechaDesde,
            'hasta'       => $fechaHasta,
            'alertaError' => $alertaError, 
            'alertaInfo'  => $alertaInfo   
        ];

        // Retornamos las vistas del sistema concatenadas
        return view('plantillas/head', $data)
             . view('plantillas/navbar')
             . view('Dashboard/index', $data)
             . view('plantillas/footer');
    }
}