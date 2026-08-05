<?php
$status = isset($_GET['status']) ? $_GET['status'] : null;
$alerts = [
    'reset_empty' => ['type' => 'warning', 'message' => 'Completa los campos de la nueva contraseña.'],
    'password_mismatch' => ['type' => 'warning', 'message' => 'Las contraseñas no coinciden.']
];
?>
<div class="auth-container d-flex justify-content-center align-items-center min-vh-100 py-5">
    <div class="auth-card card card-custom shadow-lg" style="max-width: 520px; width: 100%;">
        <div class="card-body px-4 py-5">
            <div class="text-center mb-4">
                <div class="auth-brand mb-3">
                    <img src="http://localhost/starlink-control/public/assets/Logo.png" alt="PCtronic" class="img-fluid">
                </div>
                <p class="text-white-50 mb-0">Establece una nueva contraseña</p>
            </div>

            <?php if ($status && isset($alerts[$status])): ?>
                <div class="alert alert-<?php echo $alerts[$status]['type']; ?> alert-dismissible fade show" role="alert">
                    <?php echo $alerts[$status]['message']; ?>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Cerrar"></button>
                </div>
            <?php endif; ?>

            <form action="index.php?url=auth/reset_password" method="POST">
                <div class="mb-3">
                    <label class="form-label text-white">Nueva contraseña</label>
                    <input type="password" name="password" class="form-control bg-dark text-white border-secondary" placeholder="Ingresa tu nueva contraseña" required>
                </div>
                <div class="mb-3">
                    <label class="form-label text-white">Confirmar contraseña</label>
                    <input type="password" name="confirm_password" class="form-control bg-dark text-white border-secondary" placeholder="Repite tu nueva contraseña" required>
                </div>
                <button type="submit" class="btn btn-success w-100">Guardar nueva contraseña</button>
            </form>

            <div class="text-center mt-3">
                <a href="index.php?url=login" class="text-white-50"><i class="bi bi-arrow-left"></i> Cancelar</a>
            </div>
        </div>
    </div>
</div>
