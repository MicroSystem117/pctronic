<?php
$status = isset($_GET['status']) ? $_GET['status'] : null;
$alerts = [
    'forgot_empty' => ['type' => 'warning', 'message' => 'Ingresa tu cédula de identidad.'],
    'forgot_user_not_found' => ['type' => 'danger', 'message' => 'No se encontró un usuario con esa cédula.'],
    'no_sec_questions' => ['type' => 'danger', 'message' => 'No tienes preguntas de seguridad registradas. Contacta al administrador para restablecer tu contraseña.']
];
?>
<div class="auth-container d-flex justify-content-center align-items-center min-vh-100 py-5">
    <div class="auth-card card card-custom shadow-lg" style="max-width: 520px; width: 100%;">
        <div class="card-body px-4 py-5">
            <div class="text-center mb-4">
                <div class="auth-logo rounded-circle bg-primary d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                    <i class="bi bi-star-fill text-white fs-2"></i>
                </div>
                <h3 class="text-white mb-1">Starlink Control</h3>
                <p class="text-white-50 mb-0">Recuperar acceso a tu cuenta</p>
            </div>

            <?php if ($status && isset($alerts[$status])): ?>
                <div class="alert alert-<?php echo $alerts[$status]['type']; ?> alert-dismissible fade show" role="alert">
                    <?php echo $alerts[$status]['message']; ?>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Cerrar"></button>
                </div>
            <?php endif; ?>

            <form action="index.php?url=auth/forgot_password" method="POST">
                <div class="mb-3">
                    <label class="form-label text-white">Cédula de Identidad</label>
                    <input type="text" name="ci" class="form-control bg-dark text-white border-secondary" placeholder="Ej: 12345678" required autofocus>
                </div>
                <button type="submit" class="btn btn-primary w-100">Continuar</button>
            </form>

            <div class="text-center mt-3">
                <a href="index.php?url=login" class="text-white-50"><i class="bi bi-arrow-left"></i> Volver al inicio de sesión</a>
            </div>
        </div>
    </div>
</div>
