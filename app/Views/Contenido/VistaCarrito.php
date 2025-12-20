<?php if (session()->getFlashdata('mensaje')): ?>
    <div class="alert alert-<?= session()->getFlashdata('tipo') ?> alert-dismissible fade show" role="alert">
        <?= session()->getFlashdata('mensaje') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<?php if (count($cart) <= 0) {; ?>
    <p class="display-1 p-3 bg-danger m-5">
        Aún no ha agregado productos al carrito.
    </p>

    <div class="d-flex justify-content-between mx-2">

        <a 
            href="<?= base_url('cart/delete') ?>" 
            class="btn btn-danger" 
            onclick="return confirm('¿Estás seguro de que deseas eliminar todos los productos del carrito?');"
        >
            Vaciar Carrito
        </a>

        <a href="<?= base_url('/') ?>" class="btn btn-secondary">Seguir Comprando</a>
        <a href="<?= base_url('buy/registrar') ?>" class="btn btn-success">Finalizar Compra</a>    

    </div>
<?php } else {; ?>

    <table class="table table-striped table-hover text-center mt-3">
        <thead class="table-dark ">
            <tr>
                <th>Título</th>
                <th>Precio</th>
                <th>Autor</th>
                <th>Editorial</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($cart as $id => $item): ?>
                <tr>
                    <td><?= esc($item['name']) ?></td>
                    <td>$<?= number_format($item['price'], 2) ?></td>
                    <td><?= $item['author'] ?></td>
                    <td><?= $item['editorial'] ?></td>
                    <td>
                        <a class="btn-eliminar btn btn-sm btn-danger" data-id="<?= $item['id'] ?>">Eliminar</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>


        </tfoot>
    </table>

    <div class="d-flex justify-content-between mx-2">

        <a 
            href="<?= base_url('cart/delete') ?>" 
            class="btn btn-danger" 
            onclick="return confirm('¿Estás seguro de que deseas eliminar todos los productos del carrito?');"
        >
            Vaciar Carrito
        </a>
        <a href="<?= base_url('/') ?>" class="btn btn-secondary">Seguir Comprando</a>
        <a href="<?= base_url('buy/registrar') ?>" class="btn btn-success">Finalizar Compra</a>    

    </div>
<?php } ?>