<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>DigiBook - Panel de Control</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container my-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Dashboard Control Comercial</h1>
        <a href="<?= base_url('logout') ?>" class="btn btn-outline-danger">Salir</a>
    </div>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('info')): ?>
        <div class="alert alert-warning"><?= session()->getFlashdata('info') ?></div>
    <?php endif; ?>

    <div class="card p-4 mb-4 shadow-sm">
        <form action="<?= base_url('dashboard') ?>" method="POST" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label font-weight-bold">Fecha Desde:</label>
                <input type="date" name="fechaDesde" value="<?= $desde ?>" class="form-control" required>
            </div>
            <div class="col-md-4">
                <label class="form-label font-weight-bold">Fecha Hasta:</label>
                <input type="date" name="fechaHasta" value="<?= $hasta ?>" class="form-control" required>
            </div>
            <div class="col-md-4">
                <button type="submit" class="btn btn-primary w-100">Filtrar Dashboard</button>
            </div>
        </form>
    </div>

    <div class="row text-center mb-4">
        <div class="col-md-6">
            <div class="card bg-success text-white p-4 shadow-sm">
                <h3>Ingresos Totales</h3>
                <p class="display-6 m-0">$<?= number_format($reporte->totalIngresos, 2) ?></p>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card bg-info text-white p-4 shadow-sm">
                <h3>Volumen de Ventas</h3>
                <p class="display-6 m-0"><?= $reporte->cantidadVentas ?> órdenes</p>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6 mb-4">
            <div class="card p-4 shadow-sm h-100">
                <h4 class="border-bottom pb-2">Top 3 Libros Más Vendidos</h4>
                <?php if (empty($reporte->topLibros)): ?>
                    <p class="text-muted mt-3">Sin registros comerciales en este período.</p>
                <?php else: ?>
                    <ul class="list-group list-group-flush mt-2">
                        <?php foreach ($reporte->topLibros as $index => $libro): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <strong>#<?= $index + 1 ?> <?= esc($libro->titulo) ?></strong>
                                <span class="badge bg-primary rounded-pill"><?= $libro->copias_vendidas ?> copias</span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>

        <div class="col-md-6 mb-4">
            <div class="card p-4 shadow-sm h-100">
                <h4 class="border-bottom pb-2">Distribución por Sexo de Compradores</h4>
                <?php if (empty($reporte->demografia)): ?>
                    <p class="text-muted mt-3">Sin datos demográficos registrados.</p>
                <?php else: ?>
                    <table class="table table-striped mt-2">
                        <thead>
                            <tr>
                                <th>Sexo</th>
                                <th>Cantidad de Compras</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($reporte->demografia as $row): ?>
                                <tr>
                                    <td><strong><?= $row->sexo === 'M' ? 'Masculino' : ($row->sexo === 'F' ? 'Femenino' : 'Otro') ?></strong></td>
                                    <td><?= $row->total ?> transacciones</td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

</body>
</html>