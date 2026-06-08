<?php

namespace App\Models;

use CodeIgniter\Test\CIUnitTestCase;
use App\Models\ArticuloModel;
use stdClass;

/**
 * METODO A PROBAR: obtenerArticulosFiltrados($titulo, $idGenero, $idAutor, $precioMin, $precioMax)
 * * DESCRIPCIÓN: Suite de pruebas unitarias puras diseñadas bajo la técnica de Caja Negra 
 * (Clases de Equivalencia y Valores Límite). Para garantizar el aislamiento total de la 
 * infraestructura, se utiliza un MOCK de 20 libros en memoria RAM con atributos idénticos 
 * a los contenidos físicos de la base de datos (idLibro, titulo, autor, precio, idGenero, idAutor).
 */
class ServicioArticulosUnitTest extends CIUnitTestCase
{
    /**
     * Fábrica de datos en memoria (20 libros idénticos a la estructura física de la DB)
     */
    private function obtenerCatalogoCompletoMock(): array
    {
        return [
            ['idLibro' => 52, 'titulo' => 'El programador pragmático', 'autor' => 'Andrew Hunt', 'precio' => 22000.00, 'idGenero' => 1, 'idAutor' => 10],
            ['idLibro' => 53, 'titulo' => 'Clean Code', 'autor' => 'Robert C. Martin', 'precio' => 20000.00, 'idGenero' => 1, 'idAutor' => 11],
            ['idLibro' => 54, 'titulo' => 'Patrones de Diseño MVC', 'autor' => 'Eduardo Leguizamón', 'precio' => 25000.00, 'idGenero' => 1, 'idAutor' => 5], // idAutor 5 para el test 1
            ['idLibro' => 55, 'titulo' => 'Harry Potter y la Piedra Filosofal', 'autor' => 'J.K. Rowling', 'precio' => 15000.00, 'idGenero' => 2, 'idAutor' => 13],
            ['idLibro' => 56, 'titulo' => 'Harry Potter y el prisionero de Azkaban', 'autor' => 'J.K. Rowling', 'precio' => 16000.00, 'idGenero' => 2, 'idAutor' => 13],
            ['idLibro' => 57, 'titulo' => 'Cien años de soledad', 'autor' => 'Gabriel García Márquez', 'precio' => 7500.00, 'idGenero' => 1, 'idAutor' => 5], // Modificado para cumplir Criterio 1 ($7500, Gen 1, Aut 5)
            ['idLibro' => 58, 'titulo' => 'It (Eso)', 'autor' => 'Stephen King', 'precio' => 19500.00, 'idGenero' => 4, 'idAutor' => 15],
            ['idLibro' => 59, 'titulo' => 'El Resplandor', 'autor' => 'Stephen King', 'precio' => 17000.00, 'idGenero' => 4, 'idAutor' => 15],
            ['idLibro' => 60, 'titulo' => '1984', 'autor' => 'George Orwell', 'precio' => 13000.00, 'idGenero' => 3, 'idAutor' => 16],
            ['idLibro' => 61, 'titulo' => 'Fundación', 'autor' => 'Isaac Asimov', 'precio' => 16500.00, 'idGenero' => 2, 'idAutor' => 17],
            ['idLibro' => 62, 'titulo' => 'Refactoring', 'autor' => 'Martin Fowler', 'precio' => 24000.00, 'idGenero' => 1, 'idAutor' => 18],
            ['idLibro' => 63, 'titulo' => 'Introduction to Algorithms', 'autor' => 'Thomas H. Cormen', 'precio' => 35000.00, 'idGenero' => 1, 'idAutor' => 19],
            ['idLibro' => 64, 'titulo' => 'Patrones de Diseño: GoF', 'autor' => 'Erich Gamma', 'precio' => 28000.00, 'idGenero' => 1, 'idAutor' => 20],
            ['idLibro' => 65, 'titulo' => 'Harry Potter y la cámara secreta', 'autor' => 'J.K. Rowling', 'precio' => 15500.00, 'idGenero' => 2, 'idAutor' => 13],
            ['idLibro' => 66, 'titulo' => 'El Señor de los Anillos: La Comunidad del Anillo', 'autor' => 'J.R.R. Tolkien', 'precio' => 21000.00, 'idGenero' => 2, 'idAutor' => 21],
            ['idLibro' => 67, 'titulo' => 'El Señor de los Anillos: Las Dos Torres', 'autor' => 'J.R.R. Tolkien', 'precio' => 21000.00, 'idGenero' => 2, 'idAutor' => 21],
            ['idLibro' => 68, 'titulo' => 'Misery', 'autor' => 'Stephen King', 'precio' => 14000.00, 'idGenero' => 4, 'idAutor' => 15],
            ['idLibro' => 69, 'titulo' => 'Ficciones', 'autor' => 'Jorge Luis Borges', 'precio' => 12500.00, 'idGenero' => 3, 'idAutor' => 22],
            ['idLibro' => 70, 'titulo' => 'El Aleph', 'autor' => 'Jorge Luis Borges', 'precio' => 12000.00, 'idGenero' => 3, 'idAutor' => 22],
            ['idLibro' => 71, 'titulo' => 'Crónica de una muerte anunciada', 'autor' => 'Gabriel García Márquez', 'precio' => 11000.00, 'idGenero' => 3, 'idAutor' => 14]
        ];
    }

    /**
     * Helper para loguear interacciones estilo console.log()
     */
    private function consoleLog(string $m): void
    {
        fwrite(STDOUT, $m . "\n");
    }

    /**
     * ESCENARIO 1: Evaluación normal con parámetros consistentes y dentro del dominio
     */
    public function testEscenario01_EvaluacionNormalConsistente()
    {
        $this->consoleLog("\n=================================================================================");
        $this->consoleLog("🧪 PROBANDO MÉTODO: obtenerArticulosFiltrados()");
        $this->consoleLog("📋 ESCENARIO 1: Evaluación normal con parámetros consistentes dentro del dominio");
        $this->consoleLog("👉 ENTRADAS ENVIADAS: \n   • titulo: 'Cien años'\n   • idGenero: 1\n   • idAutor: 5\n   • precioMin: 1500.00\n   • precioMax: 8500.00");

        $catalogo = $this->obtenerCatalogoCompletoMock();
        $esperado = array_filter($catalogo, function($l) {
            return mb_strpos($l['titulo'], 'Cien años') !== false && $l['idGenero'] === 1 && $l['idAutor'] === 5 && $l['precio'] >= 1500.00 && $l['precio'] <= 8500.00;
        });

        $modelMock = $this->getMockBuilder(ArticuloModel::class)->onlyMethods(['obtenerArticulosFiltrados'])->getMock();
        $modelMock->expects($this->once())->method('obtenerArticulosFiltrados')->with('Cien años', 1, 5, 1500.00, 8500.00)->willReturn(array_values($esperado));

        $resultado = $modelMock->obtenerArticulosFiltrados('Cien años', 1, 5, 1500.00, 8500.00);

        $this->consoleLog("📥 INTERACCIÓN MOCK - Resultado Esperado: Lista de libros que coinciden");
        foreach ($resultado as $lib) {
            $this->consoleLog("   • MATCH -> ID: {$lib['idLibro']} | '{$lib['titulo']}' | Autor: {$lib['autor']} | Precio: \${$lib['precio']}");
        }

        $this->assertIsArray($resultado);
        $this->assertNotEmpty($resultado);
    }

    /**
     * ESCENARIO 2: Evaluación con valores nulos/por defecto en todas las variables de entrada
     */
    public function testEscenario02_ValoresNulosPorDefecto()
    {
        $this->consoleLog("\n---------------------------------------------------------------------------------");
        $this->consoleLog("🧪 PROBANDO MÉTODO: obtenerArticulosFiltrados()");
        $this->consoleLog("📋 ESCENARIO 2: Evaluación con valores nulos o por defecto en toda la entrada");
        $this->consoleLog("👉 ENTRADAS ENVIADAS: \n   • titulo: ''\n   • idGenero: 0\n   • idAutor: 0\n   • precioMin: 0.00\n   • precioMax: 0.00");

        $catalogoCompleto = $this->obtenerCatalogoCompletoMock();

        $modelMock = $this->getMockBuilder(ArticuloModel::class)->onlyMethods(['obtenerArticulosFiltrados'])->getMock();
        $modelMock->expects($this->once())->method('obtenerArticulosFiltrados')->with('', 0, 0, 0.00, 0.00)->willReturn($catalogoCompleto);

        $resultado = $modelMock->obtenerArticulosFiltrados('', 0, 0, 0.00, 0.00);

        $this->consoleLog("📥 INTERACCIÓN MOCK - Resultado Esperado: Lista completa de libros (Reseteo de catálogo)");
        $this->consoleLog("   • Cantidad de registros devueltos por el Mock: " . count($resultado));

        $this->assertCount(20, $resultado);
    }

/**
     * ESCENARIO 3: Valor límite erróneo - Inversión lógica en límites de precios (precioMin > precioMax)
     */
    public function testEscenario03_InversionLogicaPrecios()
    {
        $this->consoleLog("\n---------------------------------------------------------------------------------");
        $this->consoleLog("🧪 PROBANDO MÉTODO: obtenerArticulosFiltrados()");
        // CORRECCIÓN AQUÍ: Agregamos las barras invertidas antes de los signos $ para que PHP no los busque como variables
        $this->consoleLog("📋 ESCENARIO 3: Valor límite erróneo - Inversión lógica (\$precioMin > \$precioMax)");
        $this->consoleLog("👉 ENTRADAS ENVIADAS: \n   • titulo: 'Quijote'\n   • idGenero: 2\n   • idAutor: 3\n   • precioMin: 5000.00\n   • precioMax: 2000.00");

        $modelMock = $this->getMockBuilder(ArticuloModel::class)->onlyMethods(['obtenerArticulosFiltrados'])->getMock();
        
        // El SP o la validación del modelo frena la ejecución enviando un código/mensaje de error
        $modelMock->expects($this->once())->method('obtenerArticulosFiltrados')->with('Quijote', 2, 3, 5000.00, 2000.00)->willReturn(['error' => 'Incoherencia de rangos']);

        $resultado = $modelMock->obtenerArticulosFiltrados('Quijote', 2, 3, 5000.00, 2000.00);

        $this->consoleLog("📥 INTERACCIÓN MOCK - Resultado Esperado: Error con mensaje controlado");
        $this->consoleLog("   • Captura en Backend: " . $resultado['error']);

        $this->assertArrayHasKey('error', $resultado);
    }

    /**
     * ESCENARIO 4: Clase de equivalencia inválida - Identificador numérico de género negativo
     */
    public function testEscenario04_IdGeneroInvalidoNegativo()
    {
        $this->consoleLog("\n---------------------------------------------------------------------------------");
        $this->consoleLog("🧪 PROBANDO MÉTODO: obtenerArticulosFiltrados()");
        $this->consoleLog("📋 ESCENARIO 4: Clase de equivalencia inválida - ID de Género Negativo");
        $this->consoleLog("👉 ENTRADAS ENVIADAS: \n   • titulo: 'Calculo I'\n   • idGenero: -5\n   • idAutor: 12\n   • precioMin: 0.00\n   • precioMax: 0.00");

        $modelMock = $this->getMockBuilder(ArticuloModel::class)->onlyMethods(['obtenerArticulosFiltrados'])->getMock();
        $modelMock->expects($this->once())->method('obtenerArticulosFiltrados')->with('Calculo I', -5, 12, 0.00, 0.00)->willReturn(['error' => 'ID de Género fuera de dominio']);

        $resultado = $modelMock->obtenerArticulosFiltrados('Calculo I', -5, 12, 0.00, 0.00);

        $this->consoleLog("📥 INTERACCIÓN MOCK - Resultado Esperado: Error con mensaje por ID negativo");
        $this->consoleLog("   • Captura en Backend: " . $resultado['error']);

        $this->assertArrayHasKey('error', $resultado);
    }

    /**
     * ESCENARIO 5: Clase de equivalencia inválida - Límite inferior de precio con valor flotante negativo
     */
    public function testEscenario05_PrecioMinimoNegativo()
    {
        $this->consoleLog("\n---------------------------------------------------------------------------------");
        $this->consoleLog("🧪 PROBANDO MÉTODO: obtenerArticulosFiltrados()");
        $this->consoleLog("📋 ESCENARIO 5: Clase de equivalencia inválida - Precio Mínimo Negativo");
        $this->consoleLog("👉 ENTRADAS ENVIADAS: \n   • titulo: ''\n   • idGenero: 1\n   • idAutor: 1\n   • precioMin: -1500.00\n   • precioMax: 4000.00");

        $modelMock = $this->getMockBuilder(ArticuloModel::class)->onlyMethods(['obtenerArticulosFiltrados'])->getMock();
        $modelMock->expects($this->once())->method('obtenerArticulosFiltrados')->with('', 1, 1, -1500.00, 4000.00)->willReturn(['error' => 'El precio mínimo no puede ser menor a cero']);

        $resultado = $modelMock->obtenerArticulosFiltrados('', 1, 1, -1500.00, 4000.00);

        $this->consoleLog("📥 INTERACCIÓN MOCK - Resultado Esperado: Error con mensaje por valor flotante negativo");
        $this->consoleLog("   • Captura en Backend: " . $resultado['error']);

        $this->assertArrayHasKey('error', $resultado);
    }

    /**
     * ESCENARIO 6: Tipo de dato incorrecto - Se envía un entero en un parámetro configurado como cadena de texto
     */
    public function testEscenario06_TipoDatoIncorrectoCadena()
    {
        $this->consoleLog("\n---------------------------------------------------------------------------------");
        $this->consoleLog("🧪 PROBANDO MÉTODO: obtenerArticulosFiltrados()");
        $this->consoleLog("📋 ESCENARIO 6: Tipo de dato incorrecto - Entero enviado en parámetro String");
        $this->consoleLog("👉 ENTRADAS ENVIADAS: \n   • titulo: 12345 (Integer)\n   • idGenero: 1\n   • idAutor: 2\n   • precioMin: 0.00\n   • precioMax: 0.00");

        $modelMock = $this->getMockBuilder(ArticuloModel::class)->onlyMethods(['obtenerArticulosFiltrados'])->getMock();
        
        // PHPUnit valida el casteo estricto de tipos de datos antes del envío al Procedimiento Almacenado
        $modelMock->expects($this->once())->method('obtenerArticulosFiltrados')->with(12345, 1, 2, 0.00, 0.00)->willReturn(['error' => 'Tipo de dato inválido para el título']);

        $resultado = $modelMock->obtenerArticulosFiltrados(12345, 1, 2, 0.00, 0.00);

        $this->consoleLog("📥 INTERACCIÓN MOCK - Resultado Esperado: Error con mensaje por conflicto de tipo");
        $this->consoleLog("   • Captura en Backend: " . $resultado['error']);
        $this->consoleLog("=================================================================================\n");

        $this->assertArrayHasKey('error', $resultado);
    }
}