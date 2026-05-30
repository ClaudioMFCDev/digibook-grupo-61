<?php

namespace App\Libraries;

class ValidarParametros implements ValidadorStrategy 
{
    public function validar(array $datos): bool 
    {
        $min = isset($datos['precioMin']) ? (float)$datos['precioMin'] : 0;
        $max = isset($datos['precioMax']) ? (float)$datos['precioMax'] : 0;

        // Check logical price range boundaries
        if ($max > 0 && $min > $max) {
            return false;
        }
        return true;
    }
}