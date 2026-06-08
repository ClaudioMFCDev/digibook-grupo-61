<nav class="navbar navbar-expand-lg bg-dark">
  <div class="container-fluid">
    <a class="navbar-brand text-light" href="#">Digibook</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
  <div class="collapse navbar-collapse" id="navbarSupportedContent">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0 ">
        <li class="nav-item">
          <a class="nav-link active text-light" aria-current="page" href="<?php echo base_url('/');?>">Home</a>
        </li>
        <?php if (session()->get('isLoggedIn') && (int)session()->get('idTipoUsuario') === 1): ?>
          <li class="nav-item">
            <a class="nav-link text-light fw-bold" href="<?= base_url('dashboard'); ?>">
              <i class="bi bi-graph-up me-1 text-light"></i> Dashboard Comercial
            </a>
          </li>
        <?php endif; ?>

        <div class="d-flex align-items-center gap-3">
          <?php if (session()->get('isLoggedIn')): ?>
            <span class="text-light small">Hola, <strong><?= esc(session()->get('nombre')); ?></strong></span>
            <a href="<?= base_url('auth/logout'); ?>" class="btn btn-sm btn-outline-danger fw-bold">Salir</a>
          <?php else: ?>
            <a href="<?= base_url('login'); ?>" class="btn btn-sm btn-light fw-bold">Ingresar</a>
          <?php endif; ?>
        </div>
      </ul>
    </div>
  </div>
</nav>