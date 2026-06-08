<?php

namespace App\Models;

use CodeIgniter\Test\CIUnitTestCase;
use App\Models\ServicioDashboard;
use App\Models\ReporteDTO;

/**
 * METODO A PROBAR: obtenerReporteConsolidado($fechaDesde, $fechaHasta)
 * * DESCRIPCIÓN: Suite de pruebas unitarias puras bajo la técnica de Caja Negra.
 * Evalúa el algoritmo REAL de Claudio inyectando respuestas simuladas (Mocks) 
 * directo en el conector de base de datos ($db) para aislar la infraestructura física.
 */
class ServicioDashboardUnitTest extends CIUnitTestCase
{
    private function consoleLog(string $m): void
    {
        fwrite(STDOUT, $m . "\n");
    }

    /**
     * Helper para construir la infraestructura de simulación de base de datos (Inyección de dependencias)
     */
    private function prepararServicioConDbMock($filaFake, $proximosResultados = false)
    {
        // 1. Clonamos el objeto de resultados (Query)
        $queryMock = $this->getMockBuilder(\CodeIgniter\Database\MySQLi\Result::class)
                          ->disableOriginalConstructor()
                          ->getMock();
        $queryMock->method('getRow')->willReturn($filaFake);

        // 2. Clonamos el gestor de conexión principal
        $dbMock = $this->getMockBuilder(\CodeIgniter\Database\MySQLi\Connection::class)
                       ->disableOriginalConstructor()
                       ->getMock();
        $dbMock->method('query')->willReturn($queryMock);
        
        // 3. Clonamos el driver nativo de hilos de PHP (mysqli) para el bucle while de limpieza
        $mysqliMock = $this->getMockBuilder(\mysqli::class)
                           ->disableOriginalConstructor()
                           ->onlyMethods(['next_result'])
                           ->getMock();
        $mysqliMock->method('next_result')->willReturn($proximosResultados);
        $dbMock->connID = $mysqliMock;

        // 4. Instanciamos el servicio real e inyectamos el simulador por Reflection
        $servicioReal = new ServicioDashboard();
        $reflection = new \ReflectionClass($servicioReal);
        $dbProperty = $reflection->getProperty('db');
        $dbProperty->setAccessible(true);
        $dbProperty->setValue($servicioReal, $dbMock);

        return $servicioReal;
    }

    /**
     * ESCENARIO 1: Evaluación normal (Camino Feliz)
     */
    public function testEscenario01_ReporteConsolidadoNormal()
    {
        $this->consoleLog("\n=================================================================================");
        $this->consoleLog("🧪 CASO 1: Camino Feliz - Período válido");
        $this->consoleLog("👉 ENTRADAS: '2026-05-01' al '2026-05-31'");

        $filaFake = (object)['cantidadVentas' => 45, 'totalIngresos' => 385000.00];
        $servicio = $this->prepararServicioConDbMock($filaFake);

        $resultado = $servicio->obtenerReporteConsolidado('2026-05-01', '2026-05-31');

        $this->consoleLog("📥 SIMULACIÓN: SP devuelve filas con registros comerciales.");
        $this->consoleLog("   • Resultado Mapeado: {$resultado->cantidadVentas} ventas | \${$resultado->totalIngresos}");

        $this->assertInstanceOf(ReporteDTO::class, $resultado);
        $this->assertEquals(45, $resultado->cantidadVentas);
        $this->assertEquals(385000.00, $resultado->totalIngresos);
    }

    /**
     * ESCENARIO 2: Valores nulos o vacíos (Carga Inicial)
     */
    public function testEscenario02_FechasVaciasPorDefecto()
    {
        $this->consoleLog("\n---------------------------------------------------------------------------------");
        $this->consoleLog("🧪 CASO 2: Carga inicial del sistema (Parámetros vacíos)");
        $this->consoleLog("👉 ENTRADAS: '' y ''");

        // Si entran vacíos, simulamos que el SP responde con ceros por contingencia
        $filaFake = (object)['cantidadVentas' => 0, 'totalIngresos' => 0.00];
        $servicio = $this->prepararServicioConDbMock($filaFake);

        $resultado = $servicio->obtenerReporteConsolidado('', '');

        $this->consoleLog("📥 SIMULACIÓN: El sistema responde con un objeto DTO estructural limpio.");
        $this->consoleLog("   • Resultado Mapeado: {$resultado->cantidadVentas} ventas | \${$resultado->totalIngresos}");

        $this->assertInstanceOf(ReporteDTO::class, $resultado);
        $this->assertEquals(0, $resultado->cantidadVentas);
    }

    /**
     * ESCENARIO 3: Valor límite erróneo - Inversión lógica (Desde > Hasta)
     */
    public function testEscenario03_InversionFronterasDeTiempo()
    {
        $this->consoleLog("\n---------------------------------------------------------------------------------");
        $this->consoleLog("🧪 CASO 3: Valor límite erróneo - Inversión lógica (Desde > Hasta)");
        $this->consoleLog("👉 ENTRADAS: '2026-06-30' al '2026-06-01'");

        // Al estar invertidas, el SP no macheará registros en el BETWEEN y arrojará nulos/ceros
        $filaFake = (object)['cantidadVentas' => 0, 'totalIngresos' => 0.00];
        $servicio = $this->prepararServicioConDbMock($filaFake);

        $resultado = $servicio->obtenerReporteConsolidado('2026-06-30', '2026-06-01');

        $this->consoleLog("📥 SIMULACIÓN: El rango cronológico incoherente no produce mach en la consulta SQL.");
        $this->assertLessThanOrEqual(0, $resultado->cantidadVentas);
        $this->assertInstanceOf(ReporteDTO::class, $resultado);
    }

    /**
     * ESCENARIO 4: Consulta en período de fechas futuras
     */
    public function testEscenario04_RangoFechasFuturas()
    {
        $this->consoleLog("\n---------------------------------------------------------------------------------");
        $this->consoleLog("🧪 CASO 4: Intento de consulta con fechas futuras");
        $this->consoleLog("👉 ENTRADAS: '2028-01-01' al '2028-01-31'");

        // En el futuro no existen datos de compras físicos cargados
        $filaFake = (object)['cantidadVentas' => 0, 'totalIngresos' => 0.00];
        $servicio = $this->prepararServicioConDbMock($filaFake);

        $resultado = $servicio->obtenerReporteConsolidado('2028-01-01', '2028-01-31');

        $this->consoleLog("📥 SIMULACIÓN: El motor retorna vacío por inexistencia cronológica de transacciones.");
        $this->assertEquals(0.00, $resultado->totalIngresos);
        $this->assertInstanceOf(ReporteDTO::class, $resultado);
    }

    /**
     * ESCENARIO 5: Rango válido pero sin transacciones comerciales
     */
    public function testEscenario05_RangoValidoSinTransacciones()
    {
        $this->consoleLog("\n---------------------------------------------------------------------------------");
        $this->consoleLog("🧪 CASO 5: Rango temporal correcto, pero sin transacciones históricas");
        $this->consoleLog("👉 ENTRADAS: '2025-01-01' al '2025-01-31'");

        // Período válido pero el SP devuelve ceros porque nadie compró nada en ese mes
        $filaFake = (object)['cantidadVentas' => 0, 'totalIngresos' => 0.00];
        $servicio = $this->prepararServicioConDbMock($filaFake);

        $resultado = $servicio->obtenerReporteConsolidado('2025-01-01', '2025-01-31');

        $this->consoleLog("📥 SIMULACIÓN: Período histórico desierto. Retorna ceros para gatillar la alerta en controlador.");
        $this->assertEquals(0, $resultado->cantidadVentas);
        $this->assertInstanceOf(ReporteDTO::class, $resultado);
    }

    /**
     * ESCENARIO 6: Tipo de dato incorrecto (Entero enviado en vez de String Date)
     */
    public function testEscenario06_TipoDeDatoIncorrecto()
    {
        $this->consoleLog("\n---------------------------------------------------------------------------------");
        $this->consoleLog("🧪 CASO 6: Tipo de dato incorrecto (Parámetro Integer en vez de String Date)");
        $this->consoleLog("👉 ENTRADAS: 20260501 (Entero) y '2026-05-31'");

        // El algoritmo real recibe el entero. Evaluamos que la capa de abstracción no explote.
        $filaFake = (object)['cantidadVentas' => 0, 'totalIngresos' => 0.00];
        $servicio = $this->prepararServicioConDbMock($filaFake);

        // Pasamos un número entero crudo en la primera posición
        $resultado = $servicio->obtenerReporteConsolidado(20260501, '2026-05-31');

        $this->consoleLog("📥 SIMULACIÓN: El algoritmo procesa la variable numérica convirtiéndola/tratándola de forma segura.");
        $this->assertInstanceOf(ReporteDTO::class, $resultado);
        $this->consoleLog("=================================================================================\n");
    }
}