<?php

namespace App\Models;

use CodeIgniter\Test\CIUnitTestCase;
use App\Models\ServicioDashboard;
use App\Models\ReporteDTO;

/**
 * METODO A PROBAR: obtenerReporteConsolidado($fechaDesde, $fechaHasta)
 * * DESCRIPCIÓN: Test Unitario Puro con Mock de Capa de Datos (Database Mocking).
 * Aquí probamos el algoritmo REAL de Claudio. No anulamos su método. 
 * Lo que hacemos es interceptar la conexión de CodeIgniter ($db) para que, en vez de ir a MySQL,
 * devuelva filas simuladas en memoria RAM. Testeamos si el método procesa y mapea correctamente.
 */
class ServicioDashboardUnitTest extends CIUnitTestCase
{
    private function consoleLog(string $m): void
    {
        fwrite(STDOUT, $m . "\n");
    }

    /**
     * ESCENARIO 1: Probar que el algoritmo REAL procese y mapee bien los datos analíticos
     */
    public function testEscenario01_ProcesamientoDeReporteReal()
    {
        $this->consoleLog("\n=================================================================================");
        $this->consoleLog("🧪 TEST UNITARIO LEGÍTIMO: obtenerReporteConsolidado()");
        $this->consoleLog("📋 OBJETIVO: Evaluar el algoritmo REAL de Claudio usando una Base de Datos Mockeada");
        $this->consoleLog("👉 ENTRADAS EVALUADAS: '2026-05-01' al '2026-05-31'");

        // 1. Creamos las filas simuladas que se supone que el SP devolvería en la realidad
        $filaGlobalFake = (object)[
            'cantidadVentas' => 45,
            'totalIngresos'  => 385000.00
        ];

        // 2. Mockeamos el objeto Query de CodeIgniter para que devuelva nuestra fila simulada
        $queryMock = $this->getMockBuilder(\CodeIgniter\Database\MySQLi\Result::class)
                          ->disableOriginalConstructor()
                          ->getMock();
                          
        // Cuando tu método real llame a $query->getRow(), le va a retornar la fila analítica fake
        $queryMock->method('getRow')->willReturn($filaGlobalFake);

        // 3. Mockeamos la Conexión de Base de Datos ($db) de CodeIgniter
        $dbMock = $this->getMockBuilder(\CodeIgniter\Database\MySQLi\Connection::class)
                       ->disableOriginalConstructor()
                       ->getMock();
                       
        // Cuando tu método intente hacer $db->query("CALL..."), va a recibir nuestro QueryMock en vez de ir a XAMPP
        $dbMock->method('query')->willReturn($queryMock);
        
        // Simulamos la propiedad connID (el driver mysqli de PHP) para los buffers del SP
        $dbMock->connID = (object)[
            'next_result'  => function() { return false; }, // Corta el bucle de hilos para no trabarse en memoria
            'store_result' => function() { return true; }
        ];

        // 4. Instanciamos tu servicio REAL
        $servicioReal = new ServicioDashboard();

        // 5. ¡MAGIA ARQUITECTÓNICA!: Usamos PHP Reflection para meterle el conector falso al servicio sin alterar tu código
        $reflection = new \ReflectionClass($servicioReal);
        $dbProperty = $reflection->getProperty('db');
        $dbProperty->setAccessible(true);
        $dbProperty->setValue($servicioReal, $dbMock); // Reemplazamos la DB real por nuestro Mock de datos

        // 6. EJECUTAMOS TU MÉTODO REAL
        // Tu código va a correr de verdad, pero consumiendo los datos fake que preparamos arriba
        $resultado = $servicioReal->obtenerReporteConsolidado('2026-05-01', '2026-05-31');

        $this->consoleLog("📥 VERIFICANDO MAPEOS DEL ALGORITMO:");
        $this->consoleLog("   • ¿Tu código procesó las Ventas?: {$resultado->cantidadVentas} órdenes");
        $this->consoleLog("   • ¿Tu código procesó los Ingresos?: \${$resultado->totalIngresos}");
        $this->consoleLog("=================================================================================\n");

        // 7. ASEVERACIONES: Comprobamos si tu lógica interna mapeó todo dentro del DTO correctamente
        $this->assertInstanceOf(ReporteDTO::class, $resultado);
        $this->assertEquals(45, $resultado->cantidadVentas); // Comprueba que tu casting (int) funcionó
        $this->assertEquals(385000.00, $resultado->totalIngresos); // Comprueba que tu casting (float) funcionó
    }
}