<div class="container mt-4">
    
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="display-5 fw-bold text-dark"><?= esc($titulo); ?></h1>
            <p class="text-muted">Panel de monitoreo de rendimiento y métricas consolidadas de ventas.</p>
        </div>
    </div>

    <div class="position-fixed top-0 end-0 p-3 mt-5" style="z-index: 9999;">
        
        <?php if (!empty($alertaError)): ?>
            <div id="alerta-comercial" class="alert alert-danger alert-dismissible fade show shadow-lg border-start border-danger border-4" role="alert">
                <div class="d-flex align-items-center">
                    <i class="bi bi-exclamation-triangle-fill me-2 fs-4 text-danger"></i>
                    <div>
                        <strong>Freno de Seguridad:</strong><br>
                        <?= esc($alertaError); ?>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if (!empty($alertaInfo)): ?>
            <div id="alerta-comercial" class="alert alert-info alert-dismissible fade show shadow-lg border-start border-info border-4" role="alert">
                <div class="d-flex align-items-center">
                    <i class="bi bi-info-circle-fill me-2 fs-4 text-info"></i>
                    <div>
                        <strong>Aviso del Sistema:</strong><br>
                        <?= esc($alertaInfo); ?>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-body bg-light">
            <form action="<?= base_url('dashboard'); ?>" method="POST" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label for="fechaDesde" class="form-label fw-semibold text-secondary">Fecha Desde</label>
                    <input type="date" class="form-control" id="fechaDesde" name="fechaDesde" 
                        value="<?= isset($desde) ? esc($desde) : ''; ?>">
                </div>
                <div class="col-md-4">
                    <label for="fechaHasta" class="form-label fw-semibold text-secondary">Fecha Hasta</label>
                    <input type="date" class="form-control" id="fechaHasta" name="fechaHasta" 
                        value="<?= isset($hasta) ? esc($hasta) : ''; ?>">
                </div>
                <div class="col-md-4 d-grid">
                    <button type="submit" class="btn btn-primary fw-bold">
                        <i class="bi bi-filter-left me-1"></i> Filtrar Dashboard
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-6 mb-3">
            <div class="card border-start border-success border-4 shadow-sm h-100">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-uppercase text-muted small fw-bold">Total Ingresos</h6>
                        <h2 class="text-success fw-bold m-0">$<?= number_format($reporte->totalIngresos, 2, ',', '.'); ?></h2>
                    </div>
                    <div class="bg-success bg-opacity-10 p-3 rounded">
                        <i class="bi bi-cash-coin text-success fs-3"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 mb-3">
            <div class="card border-start border-primary border-4 shadow-sm h-100">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-uppercase text-muted small fw-bold">Cantidad de Ventas</h6>
                        <h2 class="text-primary fw-bold m-0"><?= esc($reporte->cantidadVentas); ?> ordenes</h2>
                    </div>
                    <div class="bg-primary bg-opacity-10 p-3 rounded">
                        <i class="bi bi-cart-check text-primary fs-3"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white py-3 border-0">
                    <h5 class="m-0 fw-bold text-dark"><i class="bi bi-trophy text-warning me-2"></i>Top 3 Libros Más Vendidos</h5>
                </div>
                <div class="card-body p-0">
                    <?php if (!empty($reporte->topLibros)): ?>
                        <ul class="list-group list-group-flush">
                            <?php foreach ($reporte->topLibros as $index => $libro): ?>
                                <li class="list-group-item d-flex align-items-center py-3">
                                    <span class="badge bg-dark rounded-circle me-3"><?= $index + 1; ?></span>
                                    <span class="fw-semibold text-secondary"><?= esc($libro); ?></span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <div class="p-4 text-center text-muted">
                            <i class="bi bi-journal-x d-block fs-2 mb-2"></i>
                            No se registran unidades vendidas en este intervalo.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-md-6 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white py-3 border-0">
                    <h5 class="m-0 fw-bold text-dark"><i class="bi bi-graph-up-arrow text-info me-2"></i>Perfil y Tendencias de Consumo</h5>
                </div>
                <div class="card-body">
                    <div class="mb-4">
                        <h6 class="text-muted fw-bold small text-uppercase">Segmento Dominante de Clientes:</h6>
                        <div class="p-3 bg-light rounded border-start border-info border-3 mt-2">
                            <span class="fw-semibold text-dark"><i class="bi bi-people me-2 text-info"></i><?= esc($reporte->demografiaClientes); ?></span>
                        </div>
                    </div>
                    
                    <div>
                        <h6 class="text-muted fw-bold small text-uppercase mb-2">Categorías Más Buscadas del Período:</h6>
                        <div class="d-flex flex-wrap gap-2 pt-1">
                            <?php foreach ($reporte->tendenciasBusqueda as $tendencia): ?>
                                <span class="badge bg-info bg-opacity-10 text-info px-3 py-2 border border-info border-opacity-25 rounded-pill fw-semibold">
                                    <i class="bi bi-tag-fill me-1"></i><?= esc($tendencia); ?>
                                </span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Buscar la alerta comercial por su ID único
    const alerta = document.getElementById('alerta-comercial');
    
    if (alerta) {
        // CONFIGURACIÓN: 8000 milisegundos = 8 segundos visible en pantalla
        setTimeout(function() {
            // Aplicar transición suave de CSS nativo (0.8 segundos de desvanecimiento)
            alerta.style.transition = "opacity 0.8s ease";
            alerta.style.opacity = "0";
            
            // Eliminar por completo el elemento del HTML cuando termine de desvanecerse
            setTimeout(function() {
                alerta.remove();
            }, 800); 
        }, 8000); // <-- Cambiá este número si querés más o menos tiempo
    }
});
</script>