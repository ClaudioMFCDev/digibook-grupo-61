<div class="login-container" style="max-width: 400px; margin: 50px auto; padding: 20px; border: 1px solid #ccc;">
    <h2>Iniciar Sesión - DigiBook</h2>
    
    <?php if (session()->getFlashdata('error')): ?>
        <p style="color: red;"><?= session()->getFlashdata('error') ?></p>
    <?php endif; ?>

    <form action="<?= base_url('login/autenticar') ?>" method="POST">
        <div style="margin-bottom: 15px;">
            <label for="email">Correo Electrónico:</label>
            <input type="email" name="email" id="email" required style="width: 100%; padding: 8px;">
        </div>
        
        <div style="margin-bottom: 15px;">
            <label for="contrasena">Contraseña:</label>
            <input type="password" name="contrasena" id="contrasena" required style="width: 100%; padding: 8px;">
        </div>
        
        <button type="submit" style="padding: 10px 15px; background-color: #007bff; color: white; border: none; cursor: pointer;">
            Ingresar
        </button>
    </form>
</div>