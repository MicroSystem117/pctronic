<?php
$status = isset($_GET['status']) ? $_GET['status'] : null;
$alerts = [
    '2fa_invalid' => ['type' => 'danger', 'message' => 'Código de autenticación incorrecto o expirado. Intenta de nuevo.']
];
?>
<div class="auth-container d-flex justify-content-center align-items-center min-vh-100 py-5">
    <div class="auth-card card card-custom shadow-lg" style="max-width: 480px; width: 100%;">
        <div class="card-body px-4 py-5">
            <div class="text-center mb-4">
                <div class="auth-brand mb-3">
                    <img src="<?php echo htmlspecialchars(app_url('assets/Logo.png'), ENT_QUOTES, 'UTF-8'); ?>" alt="Starlink Control Manager" class="img-fluid" style="max-height: 50px;">
                </div>
                <div class="fs-1 text-primary mb-2">
                    <i class="bi bi-shield-lock-fill"></i>
                </div>
                <h4 class="text-white mb-1">Verificación en Dos Pasos</h4>
                <p class="text-white-50 small mb-0">Ingresa el código de 6 dígitos generado por tu aplicación de autenticación (Google Authenticator, Authy, etc.).</p>
            </div>

            <?php if ($status && isset($alerts[$status])): ?>
                <div class="alert alert-<?php echo $alerts[$status]['type']; ?> alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-1"></i>
                    <?php echo $alerts[$status]['message']; ?>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Cerrar"></button>
                </div>
            <?php endif; ?>

            <form action="index.php?url=auth/verify_2fa" method="POST">
                <?php echo Controller::getCsrfInput(); ?>
                <div class="mb-4">
                    <label class="form-label text-white small">Código de Seguridad (6 dígitos)</label>
                    <input type="text" name="code" class="form-control form-control-lg text-center bg-dark text-white border-secondary fw-bold letter-spacing-2" placeholder="000000" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" autofocus required autocomplete="one-time-code" style="letter-spacing: 0.5em; font-size: 1.5rem;">
                </div>
                <button type="submit" class="btn btn-primary btn-lg w-100">
                    <i class="bi bi-check-circle me-1"></i> Verificar y Acceder
                </button>
            </form>

            <div class="text-center mt-4 pt-2 border-top border-secondary border-opacity-25">
                <a href="index.php?url=logout" class="text-white-50 small"><i class="bi bi-box-arrow-left"></i> Cancelar e iniciar con otra cuenta</a>
            </div>
        </div>
    </div>
</div>

