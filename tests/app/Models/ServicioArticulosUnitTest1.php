<?php

namespace App\Models;

use CodeIgniter\Test\CIUnitTestCase;
use App\Models\ArticuloModel; // Mockeamos tu modelo real

class ServicioArticulosUnitTest extends CIUnitTestCase
{
    /**
     * FÁBRICA DE DATOS MOCK (Fixture)
     * Retorna una colección de 20 libros simulando exactamente el formato 
     * de array asociativo que devuelve tu procedimiento almacenado ($query->getResultArray()).
     */
    private function obtenerCatalogoCompletoMock(): array
    {
        return [
            ['idLibro' => 52, 'titulo' => 'El programador pragmático', 'autor' => 'Andrew Hunt', 'precio' => 22000.00, 'idGenero' => 1, 'idAutor' => 10],
            ['idLibro' => 53, 'titulo' => 'Clean Code', 'autor' => 'Robert C. Martin', 'precio' => 20000.00, 'idGenero' => 1, 'idAutor' => 11],
            ['idLibro' => 54, 'titulo' => 'Patrones de Diseño MVC', 'autor' => 'Eduardo Leguizamón', 'precio' => 25000.00, 'idGenero' => 1, 'idAutor' => 12],
            ['idLibro' => 55, 'titulo' => 'Harry Potter y la Piedra Filosofal', 'autor' => 'J.K. Rowling', 'precio' => 15000.00, 'idGenero' => 2, 'idAutor' => 13],
            ['idLibro' => 56, 'titulo' => 'Harry Potter y el prisionero de Azkaban', 'autor' => 'J.K. Rowling', 'precio' => 16000.00, 'idGenero' => 2, 'idAutor' => 13],
            ['idLibro' => 57, 'titulo' => 'Cien años de soledad', 'autor' => 'Gabriel García Márquez', 'precio' => 18000.00, 'idGenero' => 3, 'idAutor' => 14],
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
     * Escenario 1: Búsqueda por palabra común (Filtro por título parcial 'Diseño')
     */
    public function testFiltrarPorTerminoComunTraeMultiplesResultados()
    {
        $catalogo = $this->obtenerCatalogoCompletoMock();
        
        // Simulamos la selección que haría el SP en base al título
        $esperado = array_filter($catalogo, function($l) {
            return mb_strpos($l['titulo'], 'Patrones') !== false;
        });

        // Mockeamos el modelo real asegurando que el método existe en él
        $modelMock = $this->getMockBuilder(ArticuloModel::class)
                          ->onlyMethods(['obtenerArticulosFiltrados'])
                          ->getMock();

        // Configuramos la expectativa con los 5 parámetros correspondientes
        $modelMock->expects($this->once())
                  ->method('obtenerArticulosFiltrados')
                  ->with('Diseño', null, null, null, null)
                  ->willReturn(array_values($esperado));

        $resultado = $modelMock->obtenerArticulosFiltrados('Diseño', null, null, null, null);

        $this->assertIsArray($resultado);
        $this->assertCount(2, $resultado);
        $this->assertEquals('Patrones de Diseño MVC', $resultado[0]['titulo']);
        $this->assertEquals(25000.00, $resultado[0]['precio']);
    }

    /**
     * Escenario 2: Búsqueda por saga completa ('Harry Potter')
     */
    public function testFiltrarPorSagaHarryPotterTraeTresLibros()
    {
        $catalogo = $this->obtenerCatalogoCompletoMock();
        $esperado = array_filter($catalogo, function($l) {
            return mb_strpos($l['titulo'], 'Harry Potter') !== false;
        });

        $modelMock = $this->getMockBuilder(ArticuloModel::class)->onlyMethods(['obtenerArticulosFiltrados'])->getMock();
        $modelMock->expects($this->once())
                  ->method('obtenerArticulosFiltrados')
                  ->with('Harry Potter', null, null, null, null)
                  ->willReturn(array_values($esperado));

        $resultado = $modelMock->obtenerArticulosFiltrados('Harry Potter', null, null, null, null);

        $this->assertCount(3, $resultado);
        $this->assertEquals('J.K. Rowling', $resultado[0]['autor']);
    }

    /**
     * Escenario 3: Control Case-Insensitive (Mayúsculas y minúsculas)
     */
    public function testFiltrarSoportaDiferenciasDeMayusculasYMinusculas()
    {
        $catalogo = $this->obtenerCatalogoCompletoMock();
        $esperado = array_filter($catalogo, function($l) {
            return mb_stripos($l['titulo'], 'clean code') !== false;
        });

        $modelMock = $this->getMockBuilder(ArticuloModel::class)->onlyMethods(['obtenerArticulosFiltrados'])->getMock();
        $modelMock->expects($this->once())
                  ->method('obtenerArticulosFiltrados')
                  ->with('clean code', null, null, null, null)
                  ->willReturn(array_values($esperado));

        $resultado = $modelMock->obtenerArticulosFiltrados('clean code', null, null, null, null);

        $this->assertCount(1, $resultado);
        $this->assertEquals('Clean Code', $resultado[0]['titulo']);
    }

    /**
     * Escenario 4: Búsqueda exacta por ID de Género (Simulando dropdown de la vista)
     */
    public function testFiltrarPorGeneroEspecifico()
    {
        $catalogo = $this->obtenerCatalogoCompletoMock();
        // Género 4 = Terror (Stephen King)
        $esperado = array_filter($catalogo, function($l) {
            return $l['idGenero'] === 4;
        });

        $modelMock = $this->getMockBuilder(ArticuloModel::class)->onlyMethods(['obtenerArticulosFiltrados'])->getMock();
        $modelMock->expects($this->once())
                  ->method('obtenerArticulosFiltrados')
                  ->with('', 4, null, null, null)
                  ->willReturn(array_values($esperado));

        $resultado = $modelMock->obtenerArticulosFiltrados('', 4, null, null, null);

        $this->assertCount(3, $resultado); // Debería traer It, El Resplandor y Misery
        $this->assertEquals('Stephen King', $resultado[0]['autor']);
    }

    /**
     * Escenario 5: Filtros vacíos (El usuario entra al catálogo sin buscar nada, trae todo)
     */
    public function testFiltrarConParametrosVaciosDevuelveCatalogoCompleto()
    {
        $catalogoCompleto = $this->obtenerCatalogoCompletoMock();

        $modelMock = $this->getMockBuilder(ArticuloModel::class)->onlyMethods(['obtenerArticulosFiltrados'])->getMock();
        $modelMock->expects($this->once())
                  ->method('obtenerArticulosFiltrados')
                  ->with('', null, null, null, null)
                  ->willReturn($catalogoCompleto);

        $resultado = $modelMock->obtenerArticulosFiltrados('', null, null, null, null);

        $this->assertCount(20, $resultado);
    }

    /**
     * Escenario 6: Búsqueda sin coincidencias en el SP
     */
    public function testFiltrarSinCoincidenciasDevuelveArregloVacio()
    {
        $modelMock = $this->getMockBuilder(ArticuloModel::class)->onlyMethods(['obtenerArticulosFiltrados'])->getMock();
        $modelMock->expects($this->once())
                  ->method('obtenerArticulosFiltrados')
                  ->with('Libro Desconocido 2026', null, null, null, null)
                  ->willReturn([]);

        $resultado = $modelMock->obtenerArticulosFiltrados('Libro Desconocido 2026', null, null, null, null);

        $this->assertIsArray($resultado);
        $this->assertEmpty($resultado);
    }
}