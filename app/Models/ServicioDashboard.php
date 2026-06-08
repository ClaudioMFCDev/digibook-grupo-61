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
        $db = \Config\Database::connect();
        
        // Ejecutamos el procedimiento almacenado analítico de 4 Result Sets
        $sql = "CALL sp_obtener_reporte_comercial(?, ?)";
        $query = $db->query($sql, [$fechaDesde, $fechaHasta]);
        
        $reporteDTO = new ReporteDTO();
        
        // --- RESULT SET 1: Métricas Globales ---
        $metricasGlobales = $query->getRow();
        $reporteDTO->cantidadVentas = (int)($metricasGlobales->cantidadVentas ?? 0);
        $reporteDTO->totalIngresos  = (float)($metricasGlobales->totalIngresos ?? 0.00);
        
        // --- RESULT SET 2: Demografía de Clientes (Masculino/Femenino) ---
        // Usamos el método auxiliar seguro para evitar bloqueos de hilos de mysqli
        $reporteDTO->demografiaClientes = $this->obtenerDemografiaAuxiliar($db, $fechaDesde, $fechaHasta);

        // --- RESULT SET 3: Categorías de Libros más Vendidas (TENDENCIAS) ---
        // Sincronizamos con el método auxiliar que calcula el top de géneros literarios
        $reporteDTO->tendenciasBusqueda = $this->obtenerTendenciasAuxiliar($db, $fechaDesde, $fechaHasta);

        // --- RESULT SET 4: Top 3 Libros Individuales ---
        $reporteDTO->topLibros = $this->obtenerTopLibrosAuxiliar($db, $fechaDesde, $fechaHasta);
        
        // Liberar hilos de mysqli pendientes para que el driver no se tilde en la siguiente recarga
        while ($db->connID->next_result()) {
            $result = $db->connID->store_result();
            if ($result) {
                $result->free();
            }
        }
        
        return $reporteDTO;
    }

    /**
     * Auxiliar method to fetch dominant gender demographic data directly from user table
     */
    private function obtenerDemografiaAuxiliar($db, $desde, $hasta) {
        // CORRECCIÓN: Leemos u.genero directamente sin hacer JOIN con otra tabla
        $sql = "SELECT u.genero AS demografia 
                FROM compra c 
                JOIN usuario u ON c.dni = u.dni 
                WHERE c.fecha BETWEEN ? AND ? 
                GROUP BY u.genero 
                ORDER BY COUNT(c.idCompra) DESC LIMIT 1";
                
        $q = $db->query($sql, [$desde, $hasta])->getRow();
        return $q->demografia ?? 'Sin Datos';
    }

    /**
     * Auxiliar method to fetch Top 3 best selling books using exact physical columns
     */
    private function obtenerTopLibrosAuxiliar($db, $desde, $hasta) {
        // CORRECCIÓN: Cambiamos dc.idArticulo y a.idArticulo por idLibro
        $sql = "SELECT a.titulo 
                FROM detallescompra dc 
                JOIN compra c ON dc.idCompra = c.idCompra 
                JOIN articulo a ON dc.idLibro = a.idLibro 
                WHERE c.fecha BETWEEN ? AND ? 
                GROUP BY a.idLibro, a.titulo 
                ORDER BY COUNT(dc.idDetalle) DESC LIMIT 3";
                
        $res = $db->query($sql, [$desde, $hasta])->getResultArray();
        return array_column($res, 'titulo');
    }

    /**
     * [NUEVO MÉTODO] Trae los GÉNEROS LITERARIOS más comprados (Terror, Informática, etc.)
     * Devuelve un arreglo para que machee con tu propiedad $tendenciasBusqueda
     */
    private function obtenerTendenciasAuxiliar($db, $desde, $hasta) {
        $sql = "SELECT g.nombre AS genero_libro
                FROM compra c 
                JOIN detallescompra dc ON c.idCompra = dc.idCompra
                JOIN articulo a ON dc.idLibro = a.idLibro 
                JOIN genero g ON a.idGenero = g.idGenero
                WHERE c.fecha BETWEEN ? AND ? 
                GROUP BY g.idGenero, g.nombre 
                ORDER BY COUNT(dc.idDetalle) DESC LIMIT 3"; // Traemos un Top 3 de géneros
                
        $res = $db->query($sql, [$desde, $hasta])->getResultArray();
        return array_column($res, 'genero_libro'); // Retorna un array plano de strings
    }
}