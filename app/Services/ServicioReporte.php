<?php

namespace App\Services;

use App\DTO\ReporteDTO;
use CodeIgniter\Database\Config;

class ServicioReporte 
{
    protected $db;

    public function __construct() 
    {
        $this->db = Config::connect();
    }

    /**
     * Gathers and aggregates raw financial data from MySQL database
     */
    public function getReporteConsolidado(string $desde, string $hasta): ReporteDTO 
    {
        // Metric 1: Financial Totals (SUM & COUNT)
        $totalesQuery = $this->db->query("
            SELECT SUM(montoTotal) as total_ingresos, COUNT(idCompra) as cantidad_ventas 
            FROM compra 
            WHERE fecha BETWEEN ? AND ?
        ", [$desde, $hasta]);
        
        $totales = $totalesQuery->getRow();

        // Metric 2: Demographics by Sex (JOIN Compra + Usuario)
        $demoQuery = $this->db->query("
            SELECT u.sexo, COUNT(c.idCompra) as total 
            FROM compra c
            INNER JOIN usuario u ON c.dni = u.dni
            WHERE c.fecha BETWEEN ? AND ?
            GROUP BY u.sexo
        ", [$desde, $hasta]);
        
        $demografia = $demoQuery->getResult();

        // Metric 3: Top 3 Best Sellers (JOIN DetalleCompra + Articulo)
        $topQuery = $this->db->query("
            SELECT a.titulo, SUM(dc.cantidad) as copias_vendidas
            FROM detallecompra dc
            INNER JOIN articulo a ON dc.idArticulo = a.idArticulo
            INNER JOIN compra c ON dc.idCompra = c.idCompra
            WHERE c.fecha BETWEEN ? AND ?
            GROUP BY a.idArticulo
            ORDER BY copias_vendidas DESC
            LIMIT 3
        ", [$desde, $hasta]);
        
        $topLibros = $topQuery->getResult();

        // Pack data into the DTO container
        return new ReporteDTO([
            'totales'    => $totales,
            'demografia' => $demografia,
            'top_libros' => $topLibros
        ]);
    }
}