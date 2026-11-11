<?php
$activeTab = isset($activeTab) ? $activeTab : 'login';
$status = isset($_GET['status']) ? $_GET['status'] : null;
$requiresSessionQuestions = $status === 'session_questions';
$alerts = [
    'login_empty' => ['type' => 'warning', 'message' => 'Completa los datos de inicio de sesión.'],
    'login_failed' => ['type' => 'danger', 'message' => 'Cédula o contraseña incorrectas.'],
    'session_questions' => ['type' => 'info', 'message' => 'Detectamos otra sesión activa. Responde tus preguntas de seguridad para continuar.'],
    'session_questions_wrong' => ['type' => 'danger', 'message' => 'Las respuestas de seguridad no son correctas.'],
    'session_questions_invalid' => ['type' => 'warning', 'message' => 'La verificación de seguridad expiró o está incompleta.'],
    'session_questions_unavailable' => ['type' => 'danger', 'message' => 'No tienes preguntas de seguridad configuradas para autorizar otra sesión.'],
    'session_revoked' => ['type' => 'danger', 'message' => 'Esta sesión fue cerrada porque se abrió otra sesión para el mismo usuario.'],
    'register_success' => ['type' => 'success', 'message' => 'Registro completado con éxito. Ya puedes iniciar sesión.'],
    'register_error' => ['type' => 'danger', 'message' => 'No se pudo completar el registro.'],
    'register_empty' => ['type' => 'warning', 'message' => 'Completa todos los campos obligatorios del registro.'],
    'password_mismatch' => ['type' => 'warning', 'message' => 'Las contraseñas no coinciden.'],
    'ci_exists' => ['type' => 'warning', 'message' => 'Esta cédula ya se encuentra registrada en el sistema.'],
    'no_sec_questions' => ['type' => 'danger', 'message' => 'No tienes preguntas de seguridad registradas. Contacta al administrador.'],
    'password_reset_success' => ['type' => 'success', 'message' => 'Contraseña restablecida correctamente. Ya puedes iniciar sesión.']
];
?>
<style>
.auth-container {
    min-height: 100vh;
    background: radial-gradient(circle at 50% 20%, rgba(14, 165, 233, 0.08) 0%, transparent 60%),
                radial-gradient(circle at 80% 80%, rgba(30, 58, 138, 0.12) 0%, transparent 50%),
                var(--st-bg, #030712);
}

.auth-card {
    background: linear-gradient(180deg, rgba(15, 23, 42, 0.95) 0%, rgba(11, 19, 41, 0.98) 100%) !important;
    border: 1px solid rgba(56, 189, 248, 0.25) !important;
    border-radius: 1.25rem !important;
    box-shadow: 0 25px 60px -15px rgba(0, 0, 0, 0.9), 0 0 35px rgba(14, 165, 233, 0.12) !important;
    backdrop-filter: blur(12px);
    overflow: hidden;
}

.auth-card .nav-tabs {
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    background: rgba(3, 7, 18, 0.5);
    border-radius: 0.75rem;
    padding: 4px;
}

.auth-card .nav-tabs .nav-link {
    color: rgba(255, 255, 255, 0.65);
    background-color: transparent;
    border: none;
    border-radius: 0.5rem;
    font-weight: 600;
    font-size: 0.9rem;
    padding: 0.6rem 1rem;
    transition: all 0.2s ease;
}

.auth-card .nav-tabs .nav-link:hover {
    color: #38bdf8;
    background: rgba(56, 189, 248, 0.05);
}

.auth-card .nav-tabs .nav-link.active {
    color: #ffffff;
    background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%);
    box-shadow: 0 4px 12px rgba(2, 132, 199, 0.35);
}

.auth-input-group .input-group-text {
    background-color: rgba(15, 23, 42, 0.9);
    border-color: rgba(255, 255, 255, 0.15);
    color: #38bdf8;
}

.auth-input-group .form-control {
    background-color: rgba(3, 7, 18, 0.7) !important;
    border-color: rgba(255, 255, 255, 0.15) !important;
    color: #ffffff !important;
}

.auth-input-group .form-control:focus {
    border-color: #38bdf8 !important;
    box-shadow: 0 0 0 0.25rem rgba(56, 189, 248, 0.2) !important;
}

.auth-input-group .btn-eye-toggle {
    border-color: rgba(255, 255, 255, 0.15);
    background-color: rgba(15, 23, 42, 0.9);
    color: rgba(255, 255, 255, 0.65);
}

.auth-input-group .btn-eye-toggle:hover {
    color: #38bdf8;
    background-color: rgba(30, 41, 59, 1);
}

.badge-telemetry {
    background: rgba(14, 165, 233, 0.1);
    border: 1px solid rgba(56, 189, 248, 0.3);
    box-shadow: 0 0 15px rgba(14, 165, 233, 0.15);
}

.auth-logo-img {
    max-height: 85px;
    width: auto;
    object-fit: contain;
    filter: drop-shadow(0 6px 16px rgba(0, 0, 0, 0.6));
    transition: transform 0.25s ease;
}

.auth-logo-img:hover {
    transform: scale(1.02);
}
</style>

<div class="auth-container d-flex justify-content-center align-items-center py-5 px-3">
    <div class="auth-card card shadow-lg" style="max-width: 480px; width: 100%;">
        <div class="card-body p-4 p-md-5">
            <!-- Header con Logo Starlink y Telemetría -->
            <div class="text-center mb-4">
                <div class="auth-brand mb-2">
                    <img src="<?php echo htmlspecialchars(app_url('assets/Logo.png'), ENT_QUOTES, 'UTF-8'); ?>" alt="Starlink Control Manager" class="img-fluid auth-logo-img">
                </div>
                <div class="d-inline-flex align-items-center gap-2 px-3 py-1 rounded-pill badge-telemetry">
                    <span class="status-pulse-online"></span>
                    <span class="text-info small fw-bold tech-mono" style="font-size: 0.72rem; letter-spacing: 0.5px;">CONEXIÓN SEGURA • SSL 256-BIT</span>
                </div>
            </div>

            <!-- Selector de Pestañas Iniciar Sesión / Registrarse -->
            <ul class="nav nav-tabs nav-justified mb-4" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link <?php echo $activeTab === 'login' ? 'active' : ''; ?>" id="login-tab" data-bs-toggle="tab" data-bs-target="#login" type="button" role="tab" aria-controls="login" aria-selected="<?php echo $activeTab === 'login' ? 'true' : 'false'; ?>">
                        <i class="bi bi-box-arrow-in-right me-1"></i>Iniciar Sesión
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link <?php echo $activeTab === 'register' ? 'active' : ''; ?>" id="register-tab" data-bs-toggle="tab" data-bs-target="#register" type="button" role="tab" aria-controls="register" aria-selected="<?php echo $activeTab === 'register' ? 'true' : 'false'; ?>">
                        <i class="bi bi-person-plus me-1"></i>Registrarse
                    </button>
                </li>
            </ul>

            <div class="tab-content">
                <!-- ================= TAB: INICIAR SESIÓN ================= -->
                <div class="tab-pane fade <?php echo $activeTab === 'login' ? 'show active' : ''; ?>" id="login" role="tabpanel" aria-labelledby="login-tab">
                    <form id="loginForm" action="index.php?url=login" method="POST">
                        <?php echo Controller::getCsrfInput(); ?>

                        <?php if ($requiresSessionQuestions): ?>
                            <!-- Flujo de Verificación de Segunda Sesión Activa -->
                            <div class="alert alert-info border-info border-opacity-50 py-2 small mb-3">
                                <i class="bi bi-info-circle-fill me-1"></i> Detectamos otra sesión activa. Responde tus preguntas de seguridad para autorizar el acceso:
                            </div>
                            <input type="hidden" name="security_check" value="1">
                            <?php for ($questionNumber = 1; $questionNumber <= 3; $questionNumber++): ?>
                                <div class="mb-3">
                                    <label class="form-label text-white small fw-semibold"><?php echo htmlspecialchars($questions['question' . $questionNumber] ?? 'Pregunta de seguridad ' . $questionNumber, ENT_QUOTES); ?></label>
                                    <input type="text" name="answer<?php echo $questionNumber; ?>" class="form-control bg-dark text-white border-secondary" placeholder="Tu respuesta..." required autocomplete="off">
                                </div>
                            <?php endfor; ?>
                            <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">
                                <i class="bi bi-shield-check me-1"></i>Verificar y continuar
                            </button>
                        <?php else: ?>
                            <!-- Formulario Normal de Login -->
                            <div class="mb-3">
                                <label class="form-label text-white small fw-semibold">Cédula de Identidad</label>
                                <div class="input-group auth-input-group">
                                    <span class="input-group-text"><i class="bi bi-person-vcard"></i></span>
                                    <input type="text" id="loginCi" name="ci" class="form-control" placeholder="Ej: 12345678" inputmode="numeric" pattern="[0-9]+" oninput="this.value = this.value.replace(/[^0-9]/g, '')" title="Solo se permiten números" required autofocus autocomplete="username">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label text-white small fw-semibold">Contraseña</label>
                                <div class="input-group auth-input-group">
                                    <span class="input-group-text"><i class="bi bi-shield-lock"></i></span>
                                    <input id="loginPassword" type="password" name="password" class="form-control" placeholder="Ingresa tu contraseña" required autocomplete="current-password">
                                    <button class="btn btn-eye-toggle" type="button" onclick="togglePasswordVisibility('loginPassword', this)" title="Mostrar/Ocultar contraseña">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </div>
                            </div>

                            <!-- Opciones: Recordar Cédula y Recuperar Contraseña -->
                            <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
                                <div class="form-check">
                                    <input class="form-check-input bg-dark border-secondary" type="checkbox" id="rememberCi">
                                    <label class="form-check-label text-white-50 small" for="rememberCi">
                                        Recordar mi cédula
                                    </label>
                                </div>
                                <a href="index.php?url=auth/forgot_password" class="text-info text-decoration-none small">
                                    <i class="bi bi-key me-1"></i>¿Olvidaste tu clave?
                                </a>
                            </div>

                            <button type="submit" id="btnLoginSubmit" class="btn btn-primary w-100 py-2 fw-semibold fs-6 shadow-sm">
                                <i class="bi bi-box-arrow-in-right me-2"></i>Ingresar al Sistema
                            </button>
                        <?php endif; ?>
                    </form>
                </div>

                <!-- ================= TAB: REGISTRARSE ================= -->
                <div class="tab-pane fade <?php echo $activeTab === 'register' ? 'show active' : ''; ?>" id="register" role="tabpanel" aria-labelledby="register-tab">
                    <form id="registerForm" action="index.php?url=register" method="POST">
                        <?php echo Controller::getCsrfInput(); ?>
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label text-white small fw-semibold">Nombre</label>
                                <div class="input-group auth-input-group">
                                    <input type="text" name="name" class="form-control" placeholder="Nombre" pattern="[A-Za-zÁÉÍÓÚáéíóúÑñÜüÀ-ÿ' -]+" oninput="this.value = this.value.replace(/[0-9]/g, '')" title="No se permiten números" required>
                                </div>
                            </div>
                            <div class="col-6">
                                <label class="form-label text-white small fw-semibold">Apellido</label>
                                <div class="input-group auth-input-group">
                                    <input type="text" name="surname" class="form-control" placeholder="Apellido" pattern="[A-Za-zÁÉÍÓÚáéíóúÑñÜüÀ-ÿ' -]+" oninput="this.value = this.value.replace(/[0-9]/g, '')" title="No se permiten números" required>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-white small fw-semibold">Cédula de Identidad</label>
                            <div class="input-group auth-input-group">
                                <span class="input-group-text"><i class="bi bi-person-vcard"></i></span>
                                <input type="text" name="ci" class="form-control" placeholder="Ej: 12345678" inputmode="numeric" pattern="[0-9]+" oninput="this.value = this.value.replace(/[^0-9]/g, '')" title="Solo se permiten números" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-white small fw-semibold">Fecha de Nacimiento</label>
                            <div class="input-group auth-input-group">
                                <span class="input-group-text"><i class="bi bi-calendar3"></i></span>
                                <input type="date" name="birth" class="form-control">
                            </div>
                        </div>

                        <div class="row g-2 mb-4">
                            <div class="col-6">
                                <label class="form-label text-white small fw-semibold">Contraseña</label>
                                <div class="input-group auth-input-group">
                                    <input id="regPassword" type="password" name="password" class="form-control" placeholder="Mínimo 6 caracteres" required autocomplete="new-password">
                                    <button class="btn btn-eye-toggle" type="button" onclick="togglePasswordVisibility('regPassword', this)">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="col-6">
                                <label class="form-label text-white small fw-semibold">Confirmar</label>
                                <div class="input-group auth-input-group">
                                    <input id="regConfirmPassword" type="password" name="confirm_password" class="form-control" placeholder="Repite contraseña" required autocomplete="new-password">
                                    <button class="btn btn-eye-toggle" type="button" onclick="togglePasswordVisibility('regConfirmPassword', this)">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <button type="submit" id="btnRegisterSubmit" class="btn btn-success w-100 py-2 fw-semibold fs-6 shadow-sm">
                            <i class="bi bi-person-check-fill me-2"></i>Crear Cuenta
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// ==========================================================================
// UTILIDADES INTERACTIVAS DE LOGIN & REGISTER
// ==========================================================================
function togglePasswordVisibility(fieldId, btnEl) {
    const field = document.getElementById(fieldId);
    if (!field) return;
    const isPassword = field.type === 'password';
    field.type = isPassword ? 'text' : 'password';
    if (btnEl) {
        const icon = btnEl.querySelector('i');
        if (icon) {
            icon.className = isPassword ? 'bi bi-eye-slash text-info' : 'bi bi-eye';
        }
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const ciInput = document.getElementById('loginCi');
    const rememberCheckbox = document.getElementById('rememberCi');
    const loginForm = document.getElementById('loginForm');
    const btnLoginSubmit = document.getElementById('btnLoginSubmit');

    // Recuperar cédula guardada en localStorage
    if (ciInput && rememberCheckbox) {
        const savedCi = localStorage.getItem('starlink_saved_ci');
        if (savedCi) {
            ciInput.value = savedCi;
            rememberCheckbox.checked = true;
            const pwdInput = document.getElementById('loginPassword');
            if (pwdInput) {
                pwdInput.focus();
            }
        }
    }

    // Guardar / Borrar cédula al enviar formulario
    if (loginForm) {
        loginForm.addEventListener('submit', function() {
            if (rememberCheckbox && ciInput) {
                if (rememberCheckbox.checked && ciInput.value.trim() !== '') {
                    localStorage.setItem('starlink_saved_ci', ciInput.value.trim());
                } else {
                    localStorage.removeItem('starlink_saved_ci');
                }
            }

            if (btnLoginSubmit && !btnLoginSubmit.disabled) {
                btnLoginSubmit.disabled = true;
                btnLoginSubmit.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Autenticando...';
            }
        });
    }

    const registerForm = document.getElementById('registerForm');
    const btnRegisterSubmit = document.getElementById('btnRegisterSubmit');
    if (registerForm && btnRegisterSubmit) {
        registerForm.addEventListener('submit', function() {
            if (!btnRegisterSubmit.disabled) {
                btnRegisterSubmit.disabled = true;
                btnRegisterSubmit.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Registrando...';
            }
        });
    }
});
</script>
