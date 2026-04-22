<div class='container'>
    <section class="mt-5">
        <h2 class="m-5 bg-primary b-5 text-center p-5 text-white">Catálogo de Libros</h2>
        
        <div class="container">
            <a href="<?php echo base_url('buy/showCart')?>" class="btn btn-outline-primary mb-3">Mirar Carrito</a>
            
            <div class="card mb-4 shadow-sm">
                <div class="card-body">
                    <form action="<?= base_url('/') ?>" method="GET" class="row g-3 align-items-end">
                        
                        <div class="col-md-3">
                            <label for="titulo" class="form-label">Título del libro</label>
                            <input type="text" class="form-control" id="titulo" name="titulo" placeholder="Ej: Harry Potter" value="<?= isset($_GET['titulo']) ? esc($_GET['titulo']) : '' ?>">
                        </div>
                        
                        <div class="col-md-2">
                            <label for="idGenero" class="form-label">Género</label>
                            <select id="idGenero" name="idGenero" class="form-select">
                                <option value="">Todos</option>
                                <?php if(isset($generos)): ?>
                                    <?php foreach($generos as $genero): ?>
                                        <option value="<?= $genero['idGenero'] ?>" <?= (isset($_GET['idGenero']) && $_GET['idGenero'] == $genero['idGenero']) ? 'selected' : '' ?>>
                                            <?= $genero['nombre'] ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label for="idAutor" class="form-label">Autor</label>
                            <select id="idAutor" name="idAutor" class="form-select">
                                <option value="">Todos</option>
                                <?php if(isset($autores)): ?>
                                    <?php foreach($autores as $autor): ?>
                                        <option value="<?= $autor['idAutor'] ?>" <?= (isset($_GET['idAutor']) && $_GET['idAutor'] == $autor['idAutor']) ? 'selected' : '' ?>>
                                            <?= $autor['nombre'] ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                        
                        <div class="col-md-3">
                            <label class="form-label">Rango de Precio ($)</label>
                            <div class="input-group">
                                <input type="number" step="0.01" class="form-control" placeholder="Mín" name="precioMin" value="<?= isset($_GET['precioMin']) ? esc($_GET['precioMin']) : '' ?>">
                                <span class="input-group-text">-</span>
                                <input type="number" step="0.01" class="form-control" placeholder="Máx" name="precioMax" value="<?= isset($_GET['precioMax']) ? esc($_GET['precioMax']) : '' ?>">
                            </div>
                        </div>
                        
                        <div class="col-md-2 d-flex gap-2">
                            <button type="submit" class="btn btn-primary w-50">Buscar</button>
                            <a href="<?= base_url('/') ?>" class="btn btn-secondary w-50">Limpiar</a>
                        </div>
                        
                    </form>
                </div>
            </div>

            <?php if (isset($error_validacion) && $error_validacion !== null): ?>
                <div class="alert alert-danger text-center fw-bold shadow-sm mb-4" role="alert">
                    ⚠️ <?= esc($error_validacion) ?>
                </div>
            <?php endif; ?>

            <div class="row mt-3">
                <?php if (!empty($productos)): ?>
                    <?php foreach ($productos as $libro): ?>
                        <div class="col-3 mt-2">
                            <div class="card text-center h-100">
                                <div class="card-header fw-bold">
                                    <?php echo $libro['Título'] ?? $libro['titulo'] ?>
                                </div>
                                <div class="card-body d-flex flex-column">
                                    
                                    <h5 class="card-title text-dark">
                                        <?php echo $libro['nombres_autores'] ?? $libro['Autores'] ?? 'Autor Desconocido' ?>
                                    </h5>
                                    
                                    <p class="card-text text-muted"><?php echo $libro['Género'] ?? $libro['nombre_genero'] ?? '' ?></p>

                                    <?php 
                                        // Obtenemos el nombre del archivo de la imagen desde la BD
                                        $nombreArchivo = $libro['img'] ?? ''; 

                                        // Calculamos la ruta final de la imagen
                                        if (!empty($nombreArchivo)) {
                                            $rutaImagen = base_url('public/imagenes/' . $nombreArchivo);
                                        } else {
                                            $rutaImagen = base_url('public/imagenes/default.png');
                                        }
                                    ?>

                                    <img src="<?= $rutaImagen ?>" class="card-img-top mb-3" alt="Portada" style="height: 300px; object-fit: cover; width: 100%;">

                                    <p class="card-text mt-auto mb-3 fs-4 rounded bg-primary text-white p-2">
                                        <?php echo '$' . ($libro['Precio'] ?? $libro['precio']) ?>
                                    </p>
                                </div>
                                <div class="card-footer text-body-secondary">
                                    <form action="<?= base_url('cart/add/' . ($libro['id'] ?? $libro['idLibro'])) ?>" method="post">
                                        <button type="submit" class="btn btn-success w-100">Agregar al Carrito</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <?php if (!isset($error_validacion) || $error_validacion === null): ?>
                        <div class="col-12 text-center mt-5">
                            <h4 class="text-muted">No se encontraron resultados para su búsqueda.</h4>
                            <a href="<?= base_url('/') ?>" class="btn btn-outline-secondary mt-3">Limpiar búsqueda</a>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </section>
</div>