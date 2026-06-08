<?php

namespace App\Models;

// Objeto de Transferencia de Datos (DTO) para consolidar las métricas comerciales 
class ReporteDTO
{
    public $totalIngresos;
    public $cantidadVentas;
    public $topLibros;          // Un arreglo con los nombres de los libros más vendidos
    public $tendenciasBusqueda;  // Un arreglo con los géneros de libros más buscados
    public $demografiaClientes;  // Un string o arreglo descriptivo del grupo de edad dominante

    /**
     * Constructor para inicializar el contenedor de transporte de datos analíticos.
     * Controller to manage dashboard data transfer object
     */
    public function __construct(
        $cantidadVentas = 0, 
        $totalIngresos = 0.00, 
        $demografiaClientes = 'Sin Datos', 
        $topLibros = [],
        $tendenciasBusqueda = [] // <-- Reemplazamos 'propiedadCinco' por tu variable real
    )
    {
        $this->totalIngresos = (float)$totalIngresos;
        $this->cantidadVentas = (int)$cantidadVentas;
        $this->topLibros = $topLibros;
        $this->tendenciasBusqueda = $tendenciasBusqueda; // <-- Ahora sí existe y no va a tirar error
        $this->demografiaClientes = $demografiaClientes;
    }
}