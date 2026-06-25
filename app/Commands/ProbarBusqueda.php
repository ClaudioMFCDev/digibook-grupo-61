<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Models\ArticuloModel;

class ProbarBusqueda extends BaseCommand
{
    protected $group       = 'Demo Examen';
    protected $name        = 'busqueda:probar';
    protected $description = 'Prueba Unitaria Pura Automatizada mediante aserciones (asserts).';

    public function run(array $params)
    {
        // BANCO DE DATOS PARA EL MOCK (25 libros simulados)
        $bancoDeLibros = [
            ['idLibro' => 1,  'titulo' => 'Desarrollo en PHP y MVC', 'precio' => 4500.00, 'idGenero' => 1, 'idAutor' => 10],
            ['idLibro' => 2,  'titulo' => 'Mastering CodeIgniter 4', 'precio' => 6200.00, 'idGenero' => 1, 'idAutor' => 11],
            ['idLibro' => 3,  'titulo' => 'Patrones de Diseno en PHP', 'precio' => 7500.00, 'idGenero' => 1, 'idAutor' => 10],
            ['idLibro' => 4,  'titulo' => 'Introduccion a las Bases de Datos', 'precio' => 3800.00, 'idGenero' => 2, 'idAutor' => 12],
            ['idLibro' => 5,  'titulo' => 'MySQL Avanzado y Triggers', 'precio' => 8900.00, 'idGenero' => 2, 'idAutor' => 12],
            ['idLibro' => 6,  'titulo' => 'Diseno de Sistemas Complejos', 'precio' => 9500.00, 'idGenero' => 2, 'idAutor' => 13],
            ['idLibro' => 7,  'titulo' => 'El Enigma del Backend', 'precio' => 2500.00, 'idGenero' => 3, 'idAutor' => 14],
            ['idLibro' => 8,  'titulo' => 'Criptografia Basica', 'precio' => 5000.00, 'idGenero' => 3, 'idAutor' => 14],
            ['idLibro' => 9,  'titulo' => 'Arquitectura Limpia en Node.js', 'precio' => 7200.00, 'idGenero' => 1, 'idAutor' => 15],
            ['idLibro' => 10, 'titulo' => 'Fundamentos de TypeScript', 'precio' => 4800.00, 'idGenero' => 1, 'idAutor' => 15],
            ['idLibro' => 11, 'titulo' => 'Principios SOLID Explicados', 'precio' => 5500.00, 'idGenero' => 1, 'idAutor' => 10],
            ['idLibro' => 12, 'titulo' => 'PostgreSQL para Administradores', 'precio' => 8300.00, 'idGenero' => 2, 'idAutor' => 12],
            ['idLibro' => 13, 'titulo' => 'Antologias de Codigo Fuente', 'precio' => 3100.00, 'idGenero' => 4, 'idAutor' => 16],
            ['idLibro' => 14, 'titulo' => 'Poesia en Sintaxis PHP', 'precio' => 1900.00, 'idGenero' => 4, 'idAutor' => 16],
            ['idLibro' => 15, 'titulo' => 'Aventuras en la Nube AWS', 'precio' => 9900.00, 'idGenero' => 5, 'idAutor' => 17],
            ['idLibro' => 16, 'titulo' => 'Docker y Kubernetes Practico', 'precio' => 8700.00, 'idGenero' => 5, 'idAutor' => 17],
            ['idLibro' => 17, 'titulo' => 'Microservicios Desacoplados', 'precio' => 9200.00, 'idGenero' => 1, 'idAutor' => 13],
            ['idLibro' => 18, 'titulo' => 'Alineacion de Indices Relacionales', 'precio' => 6000.00, 'idGenero' => 2, 'idAutor' => 11],
            ['idLibro' => 19, 'titulo' => 'El Fantasma de los Servidores', 'precio' => 2800.00, 'idGenero' => 3, 'idAutor' => 14],
            ['idLibro' => 20, 'titulo' => 'Algoritmos y Estructuras Fundamentales', 'precio' => 4200.00, 'idGenero' => 1, 'idAutor' => 12],
            ['idLibro' => 21, 'titulo' => 'Pruebas Unitarias Robustas', 'precio' => 6700.00, 'idGenero' => 1, 'idAutor' => 10],
            ['idLibro' => 22, 'titulo' => 'Seguridad Informatica Ofensiva', 'precio' => 9400.00, 'idGenero' => 3, 'idAutor' => 15],
            ['idLibro' => 23, 'titulo' => 'Gestion de Alcance Basica', 'precio' => 3500.00, 'idGenero' => 5, 'idAutor' => 13],
            ['idLibro' => 24, 'titulo' => 'El Arte de Refactorizar', 'precio' => 5800.00, 'idGenero' => 1, 'idAutor' => 11],
            ['idLibro' => 25, 'titulo' => 'Monolitos vs Distribuidos', 'precio' => 7900.00, 'idGenero' => 1, 'idAutor' => 17]
        ];

        // CONSTRUCCIÓN DEL MOCK DE BASE DE DATOS EN MEMORIA
        $mockQuery = new class($bancoDeLibros) {
            private $librosFiltrados;
            public function __construct($libros) { $this->librosFiltrados = $libros; }
            public function setResultados($res) { $this->librosFiltrados = $res; }
            public function getResultArray() { return $this->librosFiltrados; }
        };

        $mockDb = new class($mockQuery, $bancoDeLibros) {
            private $mq;
            private $banco;
            public function __construct($mq, $banco) { $this->mq = $mq; $this->banco = $banco; }
            public function query($sql, $params = []) {
                list($titulo, $idGenero, $idAutor, $precioMin, $precioMax) = $params;
                
                $filtrados = array_filter($this->banco, function($libro) use ($titulo, $idGenero, $idAutor, $precioMin, $precioMax) {
                    if ($titulo !== null && stripos($libro['titulo'], $titulo) === false) return false;
                    if ($idGenero !== null && $libro['idGenero'] !== $idGenero) return false;
                    if ($idAutor !== null && $libro['idAutor'] !== $idAutor) return false;
                    if ($precioMin !== null && $libro['precio'] < $precioMin) return false;
                    if ($precioMax !== null && $libro['precio'] > $precioMax) return false;
                    return true;
                });
                
                $this->mq->setResultados(array_values($filtrados));
                return $this->mq;
            }
        };

        $servicio = new ArticuloModelMock();
        $servicio->inyectarBaseDeDatosFalsa($mockDb);

        CLI::clearScreen();
        CLI::write("EJECUTANDO BANCO DE PRUEBAS AUTOMATICAS", 'cyan');
        CLI::write("----------------------------------------------------------\n", 'white');

        try {
            // CASO 01: Evaluación Normal (Apertura Total)
            CLI::write("Caso 01 - Evaluación normal:");
            CLI::write("-> Datos de Entrada: Todos los parametros en NULL.");
            $res1 = $servicio->obtenerArticulosFiltrados(null, null, null, null, null);
            CLI::write("-> Valor Obtenido: Array con " . count($res1) . " articulos.");
            $this->assertEquals(25, count($res1), "Caso 01");
            CLI::write("----------------------------------------------------------", 'white');

            // CASO 02: Se ingresa solo el título
            CLI::write("Caso 02 - Se ingresa solo el titulo:");
            CLI::write("-> Datos de Entrada: titulo = 'Mastering', los demas en NULL.");
            $res2 = $servicio->obtenerArticulosFiltrados("Mastering", null, null, null, null);
            CLI::write("-> Valor Obtenido: Array con " . count($res2) . " articulos.");
            $this->assertEquals(1, count($res2), "Caso 02");
            CLI::write("----------------------------------------------------------", 'white');

            // CASO 03: Se ingresa solo el idGenero
            CLI::write("Caso 03 - Se ingresa solo el idGenero:");
            CLI::write("-> Datos de Entrada: idGenero = 2, los demas en NULL.");
            $res3 = $servicio->obtenerArticulosFiltrados(null, 2, null, null, null);
            CLI::write("-> Valor Obtenido: Array con " . count($res3) . " articulos correspondientes.");
            $this->assertEquals(5, count($res3), "Caso 03");
            CLI::write("----------------------------------------------------------", 'white');

            // CASO 04: Rango de precios normal
            CLI::write("Caso 04 - Rango de precios normal:");
            CLI::write("-> Datos de Entrada: precioMin = 9000.00, precioMax = 9999.00.");
            $res4 = $servicio->obtenerArticulosFiltrados(null, null, null, 9000.00, 9999.00);
            CLI::write("-> Valor Obtenido: Array con " . count($res4) . " articulos filtrados.");
            $this->assertEquals(4, count($res4), "Caso 04");
            CLI::write("----------------------------------------------------------", 'white');

            // CASO 05: Texto aleatorio en título
            CLI::write("Caso 05 - Texto aleatorio en titulo:");
            CLI::write("-> Datos de Entrada: titulo = 'LibroInexistenteXYZ', los demas en NULL.");
            $res5 = $servicio->obtenerArticulosFiltrados("LibroInexistenteXYZ", null, null, null, null);
            CLI::write("-> Valor Obtenido: Array con " . count($res5) . " elementos.");
            $this->assertEquals(0, count($res5), "Caso 05");
            CLI::write("----------------------------------------------------------", 'white');

            // CASO 06: Dato de tipo string en campo numérico
            CLI::write("Caso 06 - Dato de tipo string en campo numerico:");
            CLI::write("-> Datos de Entrada: idGenero = 'computacion', los demas en NULL.");
            // Al no ser numerico, el mock evalua la comparacion estricta y devuelve 0 de manera segura
            $res6 = $servicio->obtenerArticulosFiltrados(null, "computacion", null, null, null);
            CLI::write("-> Valor Obtenido: Array con " . count($res6) . " elementos.");
            $this->assertEquals(0, count($res6), "Caso 06");
            CLI::write("----------------------------------------------------------", 'white');

            CLI::write("\n[RESULTADO] Todas las pruebas de la matriz se ejecutaron con éxito.", 'green');

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

class ArticuloModelMock extends ArticuloModel 
{
    public function inyectarBaseDeDatosFalsa($mockDb) {
        $this->db = $mockDb;
    }
}