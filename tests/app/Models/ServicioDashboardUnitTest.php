<?php

namespace App\Models;

use CodeIgniter\Test\CIUnitTestCase;
use App\Models\ServicioDashboard;
use App\Models\ReporteDTO;

/**
 * METODO A PROBAR: obtenerReporteConsolidado($fechaDesde, $fechaHasta)
 * * DESCRIPCIÓN: Suite de pruebas unitarias de Caja Negra con simulación Multi-Query.
 * Evalúa el algoritmo REAL de Claudio asegurando que el mapeo completo de su ReporteDTO 
 * (Métricas, Demografía, Tendencias y Top 3 de Libros) sea procesado correctamente.
 */
class ServicioDashboardUnitTest extends CIUnitTestCase
{
    private function consoleLog(string $m): void
    {
        fwrite(STDOUT, $m . "\n");
    }

    /**
     * Helper avanzado para construir la secuencia de respuestas de la base de datos (Multi-Query Mocking)
     */
    private function prepararServicioConDbMock($filaGlobal, $filaDemografia, $arrayTendencias, $arrayTopLibros)
    {
        // 1. Mockeamos los distintos objetos de resultados (Query Results)
        $qGlobal = $this->getMockBuilder(\CodeIgniter\Database\MySQLi\Result::class)->disableOriginalConstructor()->getMock();
        $qGlobal->method('getRow')->willReturn($filaGlobal);

        $qDemografia = $this->getMockBuilder(\CodeIgniter\Database\MySQLi\Result::class)->disableOriginalConstructor()->getMock();
        $qDemografia->method('getRow')->willReturn($filaDemografia);

        $qTendencias = $this->getMockBuilder(\CodeIgniter\Database\MySQLi\Result::class)->disableOriginalConstructor()->getMock();
        $qTendencias->method('getResultArray')->willReturn($arrayTendencias);

        $qTopLibros = $this->getMockBuilder(\CodeIgniter\Database\MySQLi\Result::class)->disableOriginalConstructor()->getMock();
        $qTopLibros->method('getResultArray')->willReturn($arrayTopLibros);

        // 2. Mockeamos el gestor de conexión principal
        $dbMock = $this->getMockBuilder(\CodeIgniter\Database\MySQLi\Connection::class)
                       ->disableOriginalConstructor()
                       ->getMock();

        // CONFLICTO RESUELTO: Programamos el método query() para que devuelva los 4 resultados en estricto orden secuencial
        $dbMock->method('query')->willReturnOnConsecutiveCalls($qGlobal, $qDemografia, $qTendencias, $qTopLibros);
        
        // 3. Mockeamos el driver nativo de hilos de PHP (mysqli) para el bucle de limpieza
        $mysqliMock = $this->getMockBuilder(\mysqli::class)->disableOriginalConstructor()->onlyMethods(['next_result'])->getMock();
        $mysqliMock->method('next_result')->willReturn(false);
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
        $this->consoleLog("🧪 CASO 1: Camino Feliz - Período válido con DTO Completo");
        $this->consoleLog("👉 ENTRADAS: '2026-05-01' al '2026-05-31'");

        // Definimos los datos simulados correlativos para simular toda tu DB
        $filaGlobal = (object)['cantidadVentas' => 45, 'totalIngresos' => 385000.00];
        $filaDemo   = (object)['demografia' => 'Masculino'];
        $tendencias = [['genero_libro' => 'Informática'], ['genero_libro' => 'Terror']];
        $topLibros  = [['titulo' => 'Clean Code'], ['titulo' => 'Patrones de Diseño MVC']];

        $servicio = $this->prepararServicioConDbMock($filaGlobal, $filaDemo, $tendencias, $topLibros);
        $resultado = $servicio->obtenerReporteConsolidado('2026-05-01', '2026-05-31');

        $this->consoleLog("📥 SIMULACIÓN: Extracción analítica completa simulada en memoria.");
        $this->consoleLog("   • Ventas: {$resultado->cantidadVentas} | Ingresos: \${$resultado->totalIngresos}");
        $this->consoleLog("   • Perfil Cliente: '{$resultado->demografiaClientes}'");
        $this->consoleLog("   • Categoría Estrella: '{$resultado->tendenciasBusqueda[0]}' | Libro Líder: '{$resultado->topLibros[0]}'");

        // Validamos el DTO al 100% de sus atributos reales
        $this->assertInstanceOf(ReporteDTO::class, $resultado);
        $this->assertEquals(45, $resultado->cantidadVentas);
        $this->assertEquals(385000.00, $resultado->totalIngresos);
        $this->assertEquals('Masculino', $resultado->demografiaClientes);
        $this->assertEquals('Informática', $resultado->tendenciasBusqueda[0]);
        $this->assertEquals('Clean Code', $resultado->topLibros[0]);
    }

    /**
     * ESCENARIO 2: Valores nulos o vacíos (Carga Inicial)
     */
    public function testEscenario02_FechasVaciasPorDefecto()
    {
        $this->consoleLog("\n---------------------------------------------------------------------------------");
        $this->consoleLog("🧪 CASO 2: Carga inicial del sistema (Parámetros vacíos)");
        $this->consoleLog("👉 ENTRADAS: '' y ''");

        $servicio = $this->prepararServicioConDbMock(
            (object)['cantidadVentas' => 0, 'totalIngresos' => 0.00],
            (object)['demografia' => 'Sin Datos'],
            [],
            []
        );

        $resultado = $servicio->obtenerReporteConsolidado('', '');

        $this->consoleLog("   • Resultado Mapeado: Ceros estructurales y arrays limpios generados.");
        $this->assertEquals(0, $resultado->cantidadVentas);
        $this->assertEmpty($resultado->topLibros);
    }

    /**
     * ESCENARIO 3: Valor límite erróneo - Inversión lógica (Desde > Hasta)
     */
    public function testEscenario03_InversionFronterasDeTiempo()
    {
        $this->consoleLog("\n---------------------------------------------------------------------------------");
        $this->consoleLog("🧪 CASO 3: Valor límite erróneo - Inversión lógica (Desde > Hasta)");
        $this->consoleLog("👉 ENTRADAS: '2026-06-30' al '2026-06-01'");

        $servicio = $this->prepararServicioConDbMock((object)['cantidadVentas' => 0, 'totalIngresos' => 0.00], null, [], []);
        $resultado = $servicio->obtenerReporteConsolidado('2026-06-30', '2026-06-01');

        $this->assertLessThanOrEqual(0, $resultado->cantidadVentas);
    }

    /**
     * ESCENARIO 4: Consulta en período de fechas futuras
     */
    public function testEscenario04_RangoFechasFuturas()
    {
        $this->consoleLog("\n---------------------------------------------------------------------------------");
        $this->consoleLog("🧪 CASO 4: Intento de consulta con fechas futuras");
        $this->consoleLog("👉 ENTRADAS: '2028-01-01' al '2028-01-31'");

        $servicio = $this->prepararServicioConDbMock((object)['cantidadVentas' => 0, 'totalIngresos' => 0.00], null, [], []);
        $resultado = $servicio->obtenerReporteConsolidado('2028-01-01', '2028-01-31');

        $this->assertEquals(0.00, $resultado->totalIngresos);
    }

    /**
     * ESCENARIO 5: Rango válido pero sin transacciones comerciales
     */
    public function testEscenario05_RangoValidoSinTransacciones()
    {
        $this->consoleLog("\n---------------------------------------------------------------------------------");
        $this->consoleLog("🧪 CASO 5: Rango temporal correcto, pero sin transacciones históricas");
        $this->consoleLog("👉 ENTRADAS: '2025-01-01' al '2025-01-31'");

        $servicio = $this->prepararServicioConDbMock((object)['cantidadVentas' => 0, 'totalIngresos' => 0.00], null, [], []);
        $resultado = $servicio->obtenerReporteConsolidado('2025-01-01', '2025-01-31');

        $this->assertEquals(0, $resultado->cantidadVentas);
    }

    /**
     * ESCENARIO 6: Tipo de dato incorrecto (Entero enviado en vez de String Date)
     */
    public function testEscenario06_TipoDeDatoIncorrecto()
    {
        $this->consoleLog("\n---------------------------------------------------------------------------------");
        $this->consoleLog("🧪 CASO 6: Tipo de dato incorrecto (Parámetro Integer en vez de String Date)");
        $this->consoleLog("👉 ENTRADAS: 20260501 (Entero) y '2026-05-31'");

        $servicio = $this->prepararServicioConDbMock(
            (object)['cantidadVentas' => 0, 'totalIngresos' => 0.00],
            (object)['demografia' => 'Desconocido'],
            [],
            []
        );

        $resultado = $servicio->obtenerReporteConsolidado(20260501, '2026-05-31');

        $this->assertInstanceOf(ReporteDTO::class, $resultado);
        $this->consoleLog("=================================================================================\n");
    }
}