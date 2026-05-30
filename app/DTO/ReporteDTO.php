<?php

namespace App\DTO;

class ReporteDTO 
{
    public float $totalIngresos = 0.0;
    public int $cantidadVentas = 0;
    public array $topLibros = [];
    public array $demografia = [];

    /**
     * Entity constructor to bind processed raw database metrics
     */
    public function __construct(array $data) 
    {
        $this->totalIngresos = (float)($data['totales']->total_ingresos ?? 0.0);
        $this->cantidadVentas = (int)($data['totales']->cantidad_ventas ?? 0);
        $this->topLibros = $data['top_libros'] ?? [];
        $this->demografia = $data['demografia'] ?? [];
    }
}