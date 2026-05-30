<?php

namespace App\Libraries;

interface ValidadorStrategy 
{
    /**
     * Unique contract to execute validations across the app
     */
    public function validar(array $datos): bool;
}