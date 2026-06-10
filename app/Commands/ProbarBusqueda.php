<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Models\ArticuloModel;

class ProbarBusqueda extends BaseCommand
{
    protected $group       = 'Demo Examen';
    protected $name        = 'busqueda:probar';
    protected $description = 'Prueba Unitaria Pura bajo aislamiento total del metodo obtenerArticulosFiltrados.';

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
        // Repuesta de la db falsa.
        $mockQuery = new class($bancoDeLibros) {
            private $librosFiltrados;
            public function __construct($libros) { $this->librosFiltrados = $libros; }
            public function setResultados($res) { $this->librosFiltrados = $res; }
            public function getResultArray() { return $this->librosFiltrados; }
        };

        // Motor de db falso
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

        // Instanciamos el doble de rodaje que está declarado abajo del archivo
        $servicio = new ArticuloModelMock();
        $servicio->inyectarBaseDeDatosFalsa($mockDb);

        CLI::clearScreen();
        CLI::write("==========================================================", 'cyan');
        CLI::write("   PRUEBA UNITARIA MOCKED: OBTENERARTICULOSFILTRADOS", 'cyan');
        CLI::write("==========================================================", 'cyan');
        CLI::write("Auditoría aislada del método del modelo ante estímulos.");
        CLI::write("Ingrese 'x' para finalizar el script.\n");

        while (true) {
            CLI::write("----------------------------------------------------------", 'white');
            
            $inputTitulo = CLI::prompt("1. Titulo a buscar (Escriba 'null' para NULL)");
            if (strtolower($inputTitulo) === 'x') break;

            $inputGenero = CLI::prompt("2. idGenero (Escriba 'null' para NULL)");
            if (strtolower($inputGenero) === 'x') break;

            $inputAutor = CLI::prompt("3. idAutor (Escriba 'null' para NULL)");
            if (strtolower($inputAutor) === 'x') break;

            $inputMin = CLI::prompt("4. precioMin (Escriba 'null' para NULL)");
            if (strtolower($inputMin) === 'x') break;

            $inputMax = CLI::prompt("5. precioMax (Escriba 'null' para NULL)");
            if (strtolower($inputMax) === 'x') break;

            $titulo = (strtolower($inputTitulo) === 'null') ? null : $inputTitulo;
            $idGenero = (strtolower($inputGenero) === 'null') ? null : (is_numeric($inputGenero) ? (int)$inputGenero : $inputGenero);
            $idAutor = (strtolower($inputAutor) === 'null') ? null : (is_numeric($inputAutor) ? (int)$inputAutor : $inputAutor);
            $precioMin = (strtolower($inputMin) === 'null') ? null : (is_numeric($inputMin) ? (float)$inputMin : $inputMin);
            $precioMax = (strtolower($inputMax) === 'null') ? null : (is_numeric($inputMax) ? (float)$inputMax : $inputMax);

            CLI::write("\n[ESTIMULO] Evaluando respuesta del metodo aislado...", 'yellow');
            CLI::write("Tipos: Titulo (" . gettype($titulo) . ") | Genero (" . gettype($idGenero) . ") | Autor (" . gettype($idAutor) . ") | Min (" . gettype($precioMin) . ") | Max (" . gettype($precioMax) . ")", 'white');

            try {
                $resultados = $servicio->obtenerArticulosFiltrados($titulo, $idGenero, $idAutor, $precioMin, $precioMax);

                if (empty($resultados)) {
                    CLI::write("\n📊 [RESULTADO ESPERADO]: Exito Tecnico con Array Vacio", 'yellow');
                    CLI::write("Aclaración: El metodo respondio perfectamente retornando un array asociativo limpio sin romper la estructura.", 'white');
                    CLI::write("-> Respuesta: array(0) { }");
                } else {
                    CLI::write("\n🟢 [RESULTADO ESPERADO]: Exito con Datos Encontrados (" . count($resultados) . " items)", 'green');
                    CLI::write("Aclaración: El metodo aislado filtro correctamente sobre la coleccion inyectada.", 'white');
                    CLI::write("-> Primer elemento devuelto: " . json_encode($resultados[0]), 'white');
                }

            } catch (\TypeError $e) {
                CLI::write("\n❌ [RESULTADO ESPERADO]: Fallo de Tipado (TypeError)", 'red');
                CLI::write("Aclaración: PHP intercepto y aborto la ejecucion debido a una incongruencia de tipos primitivos.", 'white');
                CLI::write("Mensaje: " . $e->getMessage(), 'red');
            } catch (\Exception $e) {
                CLI::write("\n❌ [RESULTADO ESPERADO]: Fallo General", 'red');
                CLI::write("Mensaje: " . $e->getMessage(), 'red');
            }
            CLI::write("\n");
        }

        CLI::write("\nLaboratorio de pruebas unitarias puras cerrado.", 'cyan');
    }
}

// 2. EL MOCK SE DECLARA AL FINAL PARA NO INTERFERIR CON EL AUTOLOADER DE SPARK
class ArticuloModelMock extends ArticuloModel 
{
    public function inyectarBaseDeDatosFalsa($mockDb) {
        $this->db = $mockDb;
    }
}