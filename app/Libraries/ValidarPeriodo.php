<?php

namespace App\Libraries;

class ValidarPeriodo implements ValidadorStrategy 
{
    public function validar(array $datos): bool 
    {
        if (empty($datos['desde']) || empty($datos['hasta'])) {
            return false;
        }

        $inicio = strtotime($datos['desde']);
        $fin = strtotime($datos['hasta']);
        $hoy = time();

        // relacion de rango y fechas futuras
        return ($inicio <= $fin) && ($fin <= $hoy);
    }
}