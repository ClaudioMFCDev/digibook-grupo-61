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

        // Check chronological integrity: desde <= hasta AND hasta <= today
        return ($inicio <= $fin) && ($fin <= $hoy);
    }
}