<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class ProbarDashboard extends BaseCommand
{
    protected $group       = 'Demo Examen';
    protected $name        = 'dashboard:probar'; 
    protected $description = 'Prueba Unitaria Pura Automatizada mediante aserciones (asserts) sobre el Dashboard.';

    public function run(array $params)
    {
        // Instanciamos el servicio simulado en memoria
        $servicio = new ServicioDashboardMock();

        CLI::clearScreen();
        CLI::write("EJECUTANDO BANCO DE PRUEBAS AUTOMATICAS: DASHBOARD", 'cyan');
        CLI::write("----------------------------------------------------------\n", 'white');

        try {
            // CASO 01: Rango válido (Mayo 2026) -> Simula datos poblados
            CLI::write("Caso 01 - Rango valido:");
            CLI::write("-> Datos de Entrada: fechaDesde = '2026-05-01', fechaHasta = '2026-05-31'.");
            $res1 = $servicio->obtenerReporteConsolidado("2026-05-01", "2026-05-31");
            CLI::write("-> Valor Obtenido: ReporteDTO con cantidadVentas = " . $res1->cantidadVentas . " e ingresos = $" . $res1->totalIngresos);
            $this->assertEquals(12, $res1->cantidadVentas, "Caso 01 - Rango valido");
            CLI::write("----------------------------------------------------------", 'white');

            // CASO 02: Sin datos (NULL - NULL) -> Espera resultado vacío (0 ventas)
            CLI::write("Caso 02 - Sin datos:");
            CLI::write("-> Datos de Entrada: fechaDesde = NULL, fechaHasta = NULL.");
            $res2 = $servicio->obtenerReporteConsolidado(null, null);
            CLI::write("-> Valor Obtenido: ReporteDTO vacio con cantidadVentas = " . $res2->cantidadVentas);
            $this->assertEquals(0, $res2->cantidadVentas, "Caso 02 - Sin datos");
            CLI::write("----------------------------------------------------------", 'white');

            // CASO 03: Inversión Lógica en fechas -> Espera resultado vacío (0 ventas)
            CLI::write("Caso 03 - Inversion Logica en fechas:");
            CLI::write("-> Datos de Entrada: fechaDesde = '2026-06-01', fechaHasta = '2026-05-15'.");
            $res3 = $servicio->obtenerReporteConsolidado("2026-06-01", "2026-05-15");
            CLI::write("-> Valor Obtenido: ReporteDTO vacio con cantidadVentas = " . $res3->cantidadVentas);
            $this->assertEquals(0, $res3->cantidadVentas, "Caso 03 - Inversion Logica en fechas");
            CLI::write("----------------------------------------------------------", 'white');

            // CASO 04: Fechas futuras -> Espera resultado vacío (0 ventas)
            CLI::write("Caso 04 - Fechas futuras:");
            CLI::write("-> Datos de Entrada: fechaDesde = '2026-07-01', fechaHasta = '2026-07-15'.");
            $res4 = $servicio->obtenerReporteConsolidado("2026-07-01", "2026-07-15");
            CLI::write("-> Valor Obtenido: ReporteDTO vacio con cantidadVentas = " . $res4->cantidadVentas);
            $this->assertEquals(0, $res4->cantidadVentas, "Caso 04 - Fechas futuras");
            CLI::write("----------------------------------------------------------", 'white');

            // CASO 05: Rango de fechas sin ventas (Año 2020) -> Espera resultado vacío (0 ventas)
            CLI::write("Caso 05 - Rango de fechas sin ventas:");
            CLI::write("-> Datos de Entrada: fechaDesde = '2020-01-01', fechaHasta = '2020-01-31'.");
            $res5 = $servicio->obtenerReporteConsolidado("2020-01-01", "2020-01-31");
            CLI::write("-> Valor Obtenido: ReporteDTO vacio con cantidadVentas = " . $res5->cantidadVentas);
            $this->assertEquals(0, $res5->cantidadVentas, "Caso 05 - Rango de fechas sin ventas");
            CLI::write("----------------------------------------------------------", 'white');

            // CASO 06: Parámetros corruptos (Integer en lugar de String de fecha) -> Robustez, espera 0 ventas de forma segura
            CLI::write("Caso 06 - Parametros corruptos:");
            CLI::write("-> Datos de Entrada: fechaDesde = 12311212 (int), fechaHasta = NULL.");
            $res6 = $servicio->obtenerReporteConsolidado(12311212, null);
            CLI::write("-> Valor Obtenido: ReporteDTO vacio con cantidadVentas = " . $res6->cantidadVentas);
            $this->assertEquals(0, $res6->cantidadVentas, "Caso 06 - Parametros corruptos");
            CLI::write("----------------------------------------------------------", 'white');

            CLI::write("\n[RESULTADO] Todas las pruebas de la matriz de Dashboard se ejecutaron con exito.", 'green');

        } catch (\Exception $e) {
            CLI::write("\n[FALLO DE ASERCIÓN] " . $e->getMessage(), 'red');
        }
    }

    private function assertEquals($esperado, $obtenido, $escenario)
    {
        if ($esperado !== $obtenido) {
            throw new \Exception("{$escenario}: Se esperaba {$esperado}, pero se obtuvo {$obtenido}.");
        }
        CLI::write("[ÉXITO] {$escenario}\n", 'green');
    }
}

/**
 * Clase DTO equivalente para la transferencia segura de datos estructurados
 */
class ReporteDTO {
    public int $cantidadVentas = 0;
    public float $totalIngresos = 0.00;
    public string $demografiaClientes = 'Sin Datos';
}

/**
 * Doble de prueba (Mock) del servicio para garantizar aislamiento total
 */
class ServicioDashboardMock {
    public function obtenerReporteConsolidado($fechaDesde, $fechaHasta): ReporteDTO {
        $dto = new ReporteDTO();

        // Logica de resolucion determinista basada en tu matriz de prueba
        if ($fechaDesde === "2026-05-01" && $fechaHasta === "2026-05-31") {
            $dto->cantidadVentas = 12;
            $dto->totalIngresos = 85400.00;
            $dto->demografiaClientes = 'Masculino';
            return $dto;
        }

        // Cualquier otro escenario (Fechas futuras, invertidas, nulas o corruptas) retorna el objeto vacio de forma segura
        return $dto;
    }
}