<?php

namespace App\Controllers;

use CodeIgniter\Entity\Cast\StringCast;
use CodeIgniter\HTTP\Request;
use CodeIgniter\Session\Session;

use App\Models\ArticuloModel;
use ci4shoppingcart\Libraries\Cart;
use PhpParser\Node\Stmt\TryCatch;

class Carrito extends BaseController{
    private $cart;
    private $articulo;
    
    /**
     * Constructor de la clase ProductController
     */
    public function __construct()
    {
        //llamada al constructor de la superclase
        parent::__construct(); 
        $this->cart = new Cart(); 
        $this->articulo = new ArticuloModel(); 
    }

    public function showCart(){
        $cart_data = $this->getCart();
        return view('plantillas/head') .
               view('plantillas/navbar') .
               view('contenido/VistaCarrito',['cart' => $cart_data]) .
               view('plantillas/footer');
    }

    /**
     * Permite obtener los datos guardados en el carrito
     */
    public function getCart(){
        return $this->cart->contents();
    }

    public function getTotal(){
        return $this->cart->total();
    }

    public function destruirCart(){
        $this->cart->destroy();
    }

    private function buscarArticulo($idProd){
        try {
            $articulo = $this->articulo->getArticuloPorId(intval($idProd));
            return $articulo;
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    /**
     * Auxiliar para insertar en la librería del carrito
     */
    private function agregarCarrito($datosArticulo){
        try {
            $this->cart->insert($datosArticulo);
        } catch (\Throwable $th) {
            return redirect()->back()->withInput()->with('cart_errors', 'Error al agregar el producto al artículo');
        }
    }

    /**
     * Permite agregar un nuevo producto al carrito
     */
    public function controlAgregar($idProd){
        
        $idProd = intval($idProd);
        $productoExiste = false;
        
        // Controla que el producto ya no esté en el carrito
        foreach ($this->getCart() as $item) {
            if (intval($item['id']) === $idProd) {
                $productoExiste = true;
                break;
            }
        }
        
        if ($productoExiste){
            return redirect()->back()->withInput()->with('cart_errors', 'El producto ya está en el carrito');
        } 
        
        // Ejecutamos la búsqueda mediante el procedimiento almacenado
        $articulo = $this->buscarArticulo($idProd); 
        $datosArticulo = null;

        // CASO A: El procedimiento almacenado trajo al libro con éxito (tiene autor)
        if (!empty($articulo['resultado']) && isset($articulo['resultado'][0])) {
            $datosArticulo = [
                'id'        => $idProd, 
                'qty'       => 1, 
                'price'     => $articulo['resultado'][0]['Precio'], 
                'name'      => $articulo['resultado'][0]['Título'], 
                'genre'     => $articulo['resultado'][0]['Género'], 
                'editorial' => $articulo['resultado'][0]['Editorial'], 
                'author'    => (!empty($articulo['resultado'][0]['Autores'])) ? $articulo['resultado'][0]['Autores'] : 'Autor Desconocido',
            ]; 
        } 
        // CASO B: El procedimiento volvió vacío por culpa del INNER JOIN (libro sin autor)
        else {
            // Controller fallback query to fetch database records directly
            $db = \Config\Database::connect();
            $builder = $db->table('articulo');
            $libroBase = $builder->getWhere(['idLibro' => $idProd])->getRowArray();

            // Si el registro existe en la tabla base, armamos los datos esenciales para la compra
            if ($libroBase) {
                $datosArticulo = [
                    'id'        => $idProd, 
                    'qty'       => 1, 
                    'price'     => $libroBase['precio'], 
                    'name'      => $libroBase['titulo'], 
                    'genre'     => 'General', 
                    'editorial' => 'Particular',
                    'author'    => 'Autor Desconocido',
                ];
            }
        }

        // REDIRECCIÓN Y AGREGADO: Centralizado al final para evitar pantallas blancas
        if ($datosArticulo !== null) {
            $this->agregarCarrito($datosArticulo);
            return redirect()->to(base_url('buy/showCart'));
        } else {
            return redirect()->back()->with('error', 'El artículo seleccionado no se encuentra disponible.');
        }
    }

    /**
     * Permite eliminar un elemento del carrito según su id
     */
    public function eliminarArticulo($idProd){
        $encontrado = false;
        foreach ($this->getCart() as $rowid => $item) {
            if ($item['id'] == $idProd) {
                $this->cart->remove($rowid);
                $encontrado = true;
                break;
            }
        }
        if ($this->request->isAJAX()) {
            if ($encontrado) {
                 return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Producto eliminado del carrito.'
                ]);
            } else {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Producto no encontrado en el carrito.'
                ]);
            }
        }
    }

    /**
     * Método que permite la eliminación total del carrito
     */
    public function vaciarCarrito()
    {
        $this->cart->destroy();
        return redirect()->to(base_url('buy/showCart'));
    }
}