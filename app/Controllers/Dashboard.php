<?php

namespace App\Controllers;

use App\Services\ServicioReporte;
use App\Libraries\ValidarPeriodo;

class Dashboard extends BaseController 
{
    protected $servicioReporte;

    public function __construct() 
    {
        $this->servicioReporte = new ServicioReporte();
    }

    /**
     * Core handler to analyze filters and stream data metrics
     */
    public function index() 
    {
        // 1. Setup fallback dates (last 30 days)
        $fechaDesde = $this->request->getPost('fechaDesde') ?? date('Y-m-d', strtotime('-30 days'));
        $fechaHasta = $this->request->getPost('fechaHasta') ?? date('Y-m-d');

        $datosAValidar = ['desde' => $fechaDesde, 'hasta' => $fechaHasta];

        // 2. Instantiate and execute the Strategy
        $validador = new ValidarPeriodo();
        
        if (!$validador->validar($datosAValidar)) {
            // Alternative Course 1: Validation failed
            session()->setFlashdata('error', 'El rango de fechas seleccionado es incoherente.');
            // Fallback to default safe date parameters
            $fechaDesde = date('Y-m-d', strtotime('-30 days'));
            $fechaHasta = date('Y-m-d');
        }

        // 3. Request data payload through service layer
        $reporteDTO = $this->servicioReporte->getReporteConsolidated($fechaDesde, $fechaHasta);

        // Alternative Course 2: Valid check but empty row payload returned
        if ($reporteDTO->cantidadVentas === 0) {
            session()->setFlashdata('info', 'No se registraron movimientos comerciales en el período seleccionado.');
        }

        // 4. Stream response variables to interface layer
        return view('dashboard_comercial', [
            'reporte' => $reporteDTO,
            'desde'   => $fechaDesde,
            'hasta'   => $fechaHasta
        ]);
    }
}