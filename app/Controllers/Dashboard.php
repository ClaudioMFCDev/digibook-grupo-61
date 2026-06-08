<?php

namespace App\Controllers;

use App\Models\ServicioDashboard;
use App\Libraries\ValidarPeriodo;

class Dashboard extends BaseController 
{
    protected $servicioDashboard;

    public function __construct() 
    {
        $this->servicioDashboard = new ServicioDashboard();
    }

    /**
     * Controlador principal para analizar filtros y enviar métricas
     * Controller to handle commercial dashboard visualization and filtering
     */
    public function index() 
    {
        // 1. Detectar parámetros por POST
        $fechaDesde = $this->request->getPost('fechaDesde');
        $fechaHasta = $this->request->getPost('fechaHasta');
        
        $hoy = date('Y-m-d');

        // Variables locales para controlar las alertas por pantalla de forma limpia
        $alertaError = null;
        $alertaInfo  = null;

        // NUEVA EVALUACIÓN: Si el usuario envió el formulario pero dejó una o ambas fechas vacías
        if ($this->request->getMethod() === 'post' && (empty($fechaDesde) || empty($fechaHasta))) {
            
            $alertaError = 'Se necesitan ambas fechas para realizar la consulta.';
            
            // Forzar rango por defecto de contingencia
            $fechaDesde = date('Y-m-d', strtotime('-30 days'));
            $fechaHasta = date('Y-m-d');
            $reporteDTO = session()->get('reporte_defecto');

        // Si es la carga inicial legítima de la página (petición GET ordinaria)
        } elseif (empty($fechaDesde) || empty($fechaHasta)) {
            
            $fechaDesde = date('Y-m-d', strtotime('-30 days'));
            $fechaHasta = date('Y-m-d');
            
        }

        // Si no saltó el error de campos vacíos arriba, continuamos con las validaciones de negocio
        if ($alertaError === null) {
            
            $datosAValidar = ['desde' => $fechaDesde, 'hasta' => $fechaHasta];
            $validador = new ValidarPeriodo();
            $esFutura = ($fechaDesde > $hoy || $fechaHasta > $hoy);
            
            // EVALUACIÓN 1: Verificar si el usuario intentó viajar al futuro
            if ($esFutura) {
                $alertaError = 'Error: El sistema detectó fechas superiores a la actual. Operación frenada.';
                
                $fechaDesde = date('Y-m-d', strtotime('-30 days'));
                $fechaHasta = date('Y-m-d');
                $reporteDTO = session()->get('reporte_defecto');
                
            // EVALUACIÓN 2: Verificar si las fechas están invertidas
            } elseif (!$validador->validar($datosAValidar)) {
                $alertaError = 'Error: El rango de fechas seleccionado es incoherente (la fecha desde no puede ser mayor a la fecha hasta).';
                
                $fechaDesde = date('Y-m-d', strtotime('-30 days'));
                $fechaHasta = date('Y-m-d');
                $reporteDTO = session()->get('reporte_defecto');
                
            // EVALUACIÓN 3: Si todo está perfecto, se procesa el flujo normal de negocio
            } else {
                $reporteDTO = $this->servicioDashboard->obtenerReporteConsolidado($fechaDesde, $fechaHasta);

                if (!session()->has('reporte_defecto')) {
                    session()->set('reporte_defecto', $reporteDTO);
                }

                // Camino Alternativo 2: Período pasado válido pero sin transacciones
                if ($reporteDTO->cantidadVentas == 0) {
                    $alertaInfo = 'Advertencia: El período seleccionado no registra movimientos comerciales. Se restauraron los datos por defecto.';
                    $reporteDTO = session()->get('reporte_defecto');
                }
            }
        }

        // Si por algún flujo colateral quedó el objeto DTO sin instanciar, traemos el de defecto por seguridad
        if (!isset($reporteDTO) || $reporteDTO === null) {
            $reporteDTO = session()->get('reporte_defecto') ?? $this->servicioDashboard->obtenerReporteConsolidado($fechaDesde, $fechaHasta);
        }

        // 4. Enviar variables de respuesta controladas a la capa de interfaz
        $data = [
            'titulo'      => 'Dashboard Analítico Comercial',
            'reporte'     => $reporteDTO,
            'desde'       => $fechaDesde,
            'hasta'       => $fechaHasta,
            'alertaError' => $alertaError, 
            'alertaInfo'  => $alertaInfo   
        ];

        // Retornamos las vistas unificadas
        $htmlCompleto = view('plantillas/head', $data)
                      . view('plantillas/navbar')
                      . view('Dashboard/index', $data)
                      . view('plantillas/footer');

        return $htmlCompleto;
    }
}