<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Models\ServicioDashboard;

class ProbarDashboard extends BaseCommand
{
    protected $group       = 'Demo Examen';
    protected $name        = 'dashboard:probar'; // Trigger with: php spark dashboard:probar
    protected $description = 'Ejecución de Casos de Prueba Unitarios sobre el método aislado.';

    public function run(array $params)
    {
        // Instanciamos ÚNICAMENTE el servicio para probarlo de forma aislada
        $servicio = new ServicioDashboard();

        CLI::clearScreen();
        CLI::write("==========================================================", 'cyan');
        CLI::write("   BANCO DE PRUEBAS UNITARIAS: SERVICIODASHBOARD (CLI)", 'cyan');
        CLI::write("==========================================================", 'cyan');
        CLI::write("Auditoría directa de 'obtenerReporteConsolidado()' sin filtros previos.");
        CLI::write("Ingrese 'x' para finalizar el script.\n");

        while (true) {
            CLI::write("----------------------------------------------------------", 'white');
            
            // 1. Entrada de parámetros crudos
            $inputDesde = CLI::prompt("Ingrese fechaDesde AAAA-MM-DD (Escriba 'null' para forzar NULL)");
            if (strtolower($inputDesde) === 'x') break;

            $inputHasta = CLI::prompt("Ingrese fechaHasta AAAA-MM-DD (Escriba 'null' para forzar NULL)");
            if (strtolower($inputHasta) === 'x') break;

            // Interceptor: Convertimos la entrada al tipo de dato primitivo real de PHP
            if (strtolower($inputDesde) === 'null') {
                $fechaDesde = null;
            } elseif (is_numeric($inputDesde)) {
                $fechaDesde = (int)$inputDesde; // Forces explicit integer type
            } else {
                $fechaDesde = $inputDesde;
            }

            if (strtolower($inputHasta) === 'null') {
                $fechaHasta = null;
            } elseif (is_numeric($inputHasta)) {
                $fechaHasta = (int)$inputHasta; // Forces explicit integer type
            } else {
                $fechaHasta = $inputHasta;
            }

            CLI::write("\n[EJECUTANDO] Invocando al método aislado en el laboratorio...", 'yellow');
            CLI::write("Parámetros enviados: fechaDesde = (" . gettype($fechaDesde) . ") " . ($fechaDesde ?? 'NULL') . " | fechaHasta = (" . gettype($fechaHasta) . ") " . ($fechaHasta ?? 'NULL'), 'white');

            // 2. Bloque de aislamiento y captura
            try {
                // Invocación directa sin pasar por controladores ni estrategias
                $reporteDTO = $servicio->obtenerReporteConsolidado($fechaDesde, $fechaHasta);

                // Si no explota, analizamos qué tipo de ÉXITO devolvió
                if ($reporteDTO->cantidadVentas === 0 && $reporteDTO->totalIngresos == 0.00) {
                    
                    // 🟡 ÉXITO TÉCNICO CON DTO VACÍO (CAMINO DE CEROS)
                    CLI::write("\n📊 [RESULTADO ESPERADO]: Éxito Técnico con DTO Vacío", 'yellow');
                    CLI::write("Aclaración: El método no falló, pero la consulta SQL (BETWEEN) dio 0 registros.", 'white');
                    CLI::write("-> Objeto devuelto: ReporteDTO { cantidadVentas: 0, totalIngresos: 0.00, demografiaClientes: 'Sin Datos' }");
                
                } else {
                    
                    // 🟢 ÉXITO (SUCCESS) CON DATOS POBLADOS
                    CLI::write("\n🟢 [RESULTADO ESPERADO]: Éxito (Success) con Datos Poblados", 'green');
                    CLI::write("Aclaración: El método procesó las fechas y recuperó información real.", 'white');
                    CLI::write("-> Objeto devuelto: ReporteDTO { cantidadVentas: " . $reporteDTO->cantidadVentas . ", totalIngresos: $" . $reporteDTO->totalIngresos . ", demografia: '" . $reporteDTO->demografiaClientes . "' }");
                }

            } catch (\TypeError $e) {
                
                // 🔴 FALLO POR TIPO DE DATO (PHP ERRORS)
                CLI::write("\n❌ [RESULTADO ESPERADO]: Fallo (TypeError)", 'red');
                CLI::write("Aclaración: El núcleo de PHP rechazó los tipos de datos enviados.", 'white');
                CLI::write("Mensaje Literal: " . $e->getMessage(), 'red');

            } catch (\Exception $e) {
                
                // 🔴 FALLO POR BASE DE DATOS o EXCEPCIÓN DE SISTEMA
                CLI::write("\n❌ [RESULTADO ESPERADO]: Fallo (DatabaseException / Error)", 'red');
                CLI::write("Aclaración: El método colapsó internamente al intentar armar las consultas con estos datos.", 'white');
                CLI::write("Excepción Capturada: " . get_class($e), 'red');
                CLI::write("Mensaje Literal: " . $e->getMessage(), 'red');
            }
            
            CLI::write("\n");
        }

        CLI::write("\nPruebas finalizadas de forma segura.", 'cyan');
    }
}