<?php

namespace App\Libraries;

interface ValidadorStrategy 
{
    public function validar(array $datos): bool;
}