<?php
namespace App\Controllers;

use CodeIgniter\Controller;
use App\Models\CompraModel;
use ci4shoppingcart\Libraries\Cart;

class Compra extends Controller
{
    protected $cart;
    protected $compraModel;

    public function __construct()
    {
        $this->cart = new Cart();
        $this->compraModel = new CompraModel();
    }

    /**
     * MÉTODO controlarCarritoVacio
     */
    private function controlarCarritoVacio()
    {
        $contenido = $this->cart->contents();
        return empty($contenido);
    }

    /**
     * MÉTODO destruirCart
     * Encapsula la lógica de la librería externa
     */
    private function destruirCart()
    {
        // Aquí llamamos a la librería real
        $this->cart->destroy();
    }

    // ****************************************** CONTROLARCOMPRA *************************************
    public function controlarCompra()
    {
        // Controlar vacío
        if ($this->controlarCarritoVacio()) {
            // Guardamos el mensaje de error en la memoria temporal
            session()->setFlashdata('mensaje', 'El carrito está vacío.');
            session()->setFlashdata('tipo', 'danger');

            // Redireccionamos automáticamente a la vista del carrito
            return redirect()->to(base_url('cart'));
        }

        // Datos para la compra
        $dni = 32837262; 
        $fecha = date('Y-m-d'); 

        $total = $this->cart->total();

        // Control sobre el calculo del total
        if ($total <= 0) {
            // Preparamos el mensaje para el usuario (UX)
            session()->setFlashdata('mensaje', 'Error al calcular el total de la compra, inténtelo nuevamente más tarde.');
            session()->setFlashdata('tipo', 'danger'); // Color rojo de alerta

            // Redirigimos al usuario para que vea el mensaje
            return redirect()->to(base_url('cart'));
        }

        // Preparar JSON
        $items = [];
        foreach ($this->cart->contents() as $item) {
            $items[] = [
                'id'     => intval($item['id']),
                'qty'    => intval($item['qty']),
                'precio' => floatval($item['price'])
            ];
        }

        $jsonDetalles = json_encode($items);

        // Intentar guardar
        try {
            $this->compraModel->registrarCompra($total, $fecha, $dni, $jsonDetalles);

            // --- ÉXITO ---
        
            $this->destruirCart(); 

            return "
            <div style='text-align:center; margin-top:50px; font-family:sans-serif;'>
                <h1 style='color:green;'>¡Compra Exitosa!</h1>
                <p>La transacción ha sido registrada en la base de datos.</p>
                <p>El carrito se ha vaciado correctamente usando <code>destruirCart()</code>.</p>
                <br>
                <a href='".base_url('cart')."' style='padding:10px 20px; background:#007bff; color:white; text-decoration:none; border-radius:5px;'>Volver al Inicio</a>
            </div>";

        } catch (\Throwable $th) {
            // --- ERROR ---
            return "
            <div style='text-align:center; margin-top:50px; font-family:sans-serif;'>
                <h1 style='color:red;'>Error en la compra</h1>
                <p>Detalle técnico: " . $th->getMessage() . "</p>
                <a href='".base_url('cart')."'>Volver al Carrito</a>
            </div>";
        }
    }
}