<?php

namespace App\Models;

use CodeIgniter\Model;
use App\Models\ReporteDTO;

class ServicioDashboard extends Model
{
    /**
     * Method to fetch consolidated business metrics via stored procedure
     * Controller to manage dashboard data transfer object
     */
    public function obtenerReporteConsolidado($fechaDesde, $fechaHasta)
    {
        $db = $this->db ?? \Config\Database::connect();        
        
        // Ejecutamos el procedimiento almacenado analítico de 4 Result Sets de forma atómica
        $sql = "CALL sp_obtener_reporte_comercial(?, ?)";
        $query = $db->query($sql, [$fechaDesde, $fechaHasta]);
        
        $reporteDTO = new ReporteDTO();
        
        // --- RESULT SET 1: Métricas Globales ---
        $metricasGlobales = $query->getRow();
        $reporteDTO->cantidadVentas = (int)($metricasGlobales->cantidadVentas ?? 0);
        $reporteDTO->totalIngresos  = (float)($metricasGlobales->totalIngresos ?? 0.00);

        // Inicializamos valores por defecto de contingencia ante arrays vacíos
        $reporteDTO->demografiaClientes = 'Sin Datos';
        $reporteDTO->tendenciasBusqueda = [];
        $reporteDTO->topLibros          = [];

        // Navegamos de forma nativa por los buffers de múltiples conjuntos de datos
        if (isset($db->connID) && $db->connID instanceof \mysqli) {
            
            // --- RESULT SET 2: Demografía de Clientes (Género Dominante) ---
            if ($db->connID->next_result()) {
                $result = $db->connID->store_result();
                if ($result) {
                    $row = $result->fetch_assoc();
                    $reporteDTO->demografiaClientes = $row['demografiaClientes'] ?? 'Sin Datos';
                    $result->free();
                }
            }

            // --- RESULT SET 3: Categorías de Libros más Vendidas (Top 3 Géneros) ---
            if ($db->connID->next_result()) {
                $result = $db->connID->store_result();
                if ($result) {
                    $generos = [];
                    while ($row = $result->fetch_assoc()) {
                        $generos[] = $row['generoLibro'];
                    }
                    $reporteDTO->tendenciasBusqueda = $generos;
                    $result->free();
                }
            }

            // --- RESULT SET 4: Top 3 Libros Individuales ---
            if ($db->connID->next_result()) {
                $result = $db->connID->store_result();
                if ($result) {
                    $libros = [];
                    while ($row = $result->fetch_assoc()) {
                        $libros[] = $row['titulo'];
                    }
                    $reporteDTO->topLibros = $libros;
                    $result->free();
                }
            }
            
            // Ciclo de limpieza final de seguridad exigido por el driver de mysqli
            while ($db->connID->next_result()) {
                $result = $db->connID->store_result();
                if ($result) {
                    $result->free();
                }
            }
        }
        
        return $reporteDTO;
    }
}