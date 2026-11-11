<div class="row justify-content-center mt-4">
    <div class="col-md-5 col-lg-4">
        <div class="card card-custom p-4 shadow-lg text-white" style="border: 1px solid rgba(56, 189, 248, 0.25);">
            <div class="text-center mb-4">
                <img src="<?php echo htmlspecialchars(app_url('assets/Logo.png'), ENT_QUOTES, 'UTF-8'); ?>" alt="Starlink Control Manager" class="img-fluid mb-2" style="max-height: 75px; object-fit: contain;">
                <h4 class="mb-1 text-white fw-bold">Iniciar Sesión</h4>
                <div class="small text-white-50">Ingresa tus credenciales autorizadas</div>
            </div>
            <form action="index.php?url=login" method="POST">
                <?php echo Controller::getCsrfInput(); ?>
                <div class="mb-3">
                    <label class="form-label text-white-50 small fw-semibold">Cédula de Identidad</label>
                    <div class="input-group">
                        <span class="input-group-text bg-dark text-info border-secondary"><i class="bi bi-person-vcard"></i></span>
                        <input type="text" name="ci" class="form-control bg-dark text-white border-secondary" placeholder="Ej: 12345678" inputmode="numeric" pattern="[0-9]+" oninput="this.value = this.value.replace(/[^0-9]/g, '')" title="Solo se permiten números" required autofocus autocomplete="username">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label text-white-50 small fw-semibold">Contraseña</label>
                    <div class="input-group">
                        <span class="input-group-text bg-dark text-info border-secondary"><i class="bi bi-shield-lock"></i></span>
                        <input id="loginSimplePassword" type="password" name="password" class="form-control bg-dark text-white border-secondary" placeholder="Tu contraseña" required autocomplete="current-password">
                        <button class="btn btn-outline-secondary" type="button" onclick="const p = document.getElementById('loginSimplePassword'); p.type = p.type === 'password' ? 'text' : 'password';"><i class="bi bi-eye"></i></button>
                    </div>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <a href="index.php?url=auth/forgot_password" class="text-info text-decoration-none small">
                        <i class="bi bi-key me-1"></i>¿Olvidaste tu contraseña?
                    </a>
                </div>
                <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">
                    <i class="bi bi-box-arrow-in-right me-2"></i>Ingresar
                </button>
            </form>
            <div class="mt-4 text-center text-white-50 small">
                ¿Aún no tienes cuenta? <a href="index.php?url=register" class="text-info text-decoration-none fw-semibold">Regístrate aquí</a>
            </div>
        </div>
    </div>
</div>
