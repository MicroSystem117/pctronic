<?php
$activeTab = isset($activeTab) ? $activeTab : 'login';
$status = isset($_GET['status']) ? $_GET['status'] : null;
$requiresSessionQuestions = $status === 'session_questions';
$alerts = [
    'login_empty' => ['type' => 'warning', 'message' => 'Completa los datos de inicio de sesión.'],
    'login_failed' => ['type' => 'danger', 'message' => 'Usuario o contraseña incorrectos.'],
    'session_questions' => ['type' => 'info', 'message' => 'Detectamos otra sesión activa. Responde tus preguntas de seguridad para continuar.'],
    'session_questions_wrong' => ['type' => 'danger', 'message' => 'Las respuestas de seguridad no son correctas.'],
    'session_questions_invalid' => ['type' => 'warning', 'message' => 'La verificación de seguridad expiró o está incompleta.'],
    'session_questions_unavailable' => ['type' => 'danger', 'message' => 'No tienes preguntas de seguridad configuradas para autorizar otra sesión.'],
    'session_revoked' => ['type' => 'danger', 'message' => 'Esta sesión fue cerrada porque se abrió otra sesión para el mismo usuario.'],
    'register_success' => ['type' => 'success', 'message' => 'Registro completado. Ya puedes iniciar sesión.'],
    'register_error' => ['type' => 'danger', 'message' => 'No se pudo completar el registro.'],
    'register_empty' => ['type' => 'warning', 'message' => 'Completa todos los campos del registro.'],
    'password_mismatch' => ['type' => 'warning', 'message' => 'Las contraseñas no coinciden.'],
    'ci_exists' => ['type' => 'warning', 'message' => 'La cédula ya está registrada.'],
    'no_sec_questions' => ['type' => 'danger', 'message' => 'No tienes preguntas de seguridad registradas. Configura al menos 3 preguntas desde tu panel de usuario o contacta al administrador.'],
    'password_reset_success' => ['type' => 'success', 'message' => 'Contraseña restablecida correctamente. Ya puedes iniciar sesión.']
];
?>
<style>
.auth-card .nav-tabs .nav-link {
    color: #ffffff;
    background-color: transparent;
    border: 1px solid transparent;
}
.auth-card .nav-tabs .nav-link.active {
    color: #ffffff;
    background-color: #0d6efd;
    border-color: #0d6efd #0d6efd #212529;
}
.auth-card .nav-tabs .nav-link.active:hover,
.auth-card .nav-tabs .nav-link.active:focus {
    color: #ffffff;
}
</style>
<div class="auth-container d-flex justify-content-center align-items-center min-vh-100 py-5">
    <div class="auth-card card card-custom shadow-lg" style="max-width: 520px; width: 100%;">
        <div class="card-body px-4 py-5">
            <div class="text-center mb-4">
                <div class="auth-brand mb-3">
                    <img src="<?php echo htmlspecialchars(app_url('assets/Logo.png'), ENT_QUOTES, 'UTF-8'); ?>" alt="PCtronic" class="img-fluid">
                </div>
                <p class="text-white-50 mb-0">Sistema de gestión técnica</p>
            </div>

            <?php if ($status && isset($alerts[$status])): ?>
                <div class="alert alert-<?php echo $alerts[$status]['type']; ?> alert-dismissible fade show" role="alert">
                    <?php echo $alerts[$status]['message']; ?>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Cerrar"></button>
                </div>
            <?php endif; ?>

            <ul class="nav nav-tabs nav-justified mb-4" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link <?php echo $activeTab === 'login' ? 'active' : ''; ?>" id="login-tab" data-bs-toggle="tab" data-bs-target="#login" type="button" role="tab" aria-controls="login" aria-selected="<?php echo $activeTab === 'login' ? 'true' : 'false'; ?>">Iniciar Sesión</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link <?php echo $activeTab === 'register' ? 'active' : ''; ?>" id="register-tab" data-bs-toggle="tab" data-bs-target="#register" type="button" role="tab" aria-controls="register" aria-selected="<?php echo $activeTab === 'register' ? 'true' : 'false'; ?>">Registrarse</button>
                </li>
            </ul>

            <div class="tab-content">
                <div class="tab-pane fade <?php echo $activeTab === 'login' ? 'show active' : ''; ?>" id="login" role="tabpanel" aria-labelledby="login-tab">
                    <form action="index.php?url=login" method="POST">
                        <?php if ($requiresSessionQuestions): ?>
                            <input type="hidden" name="security_check" value="1">
                            <?php for ($questionNumber = 1; $questionNumber <= 3; $questionNumber++): ?>
                                <div class="mb-3">
                                    <label class="form-label text-white"><?php echo htmlspecialchars($questions['question' . $questionNumber] ?? 'Pregunta de seguridad', ENT_QUOTES); ?></label>
                                    <input type="text" name="answer<?php echo $questionNumber; ?>" class="form-control bg-dark text-white border-secondary" required>
                                </div>
                            <?php endfor; ?>
                            <button type="submit" class="btn btn-primary w-100">Verificar y continuar</button>
                        <?php else: ?>
                            <div class="mb-3">
                                <label class="form-label text-white">Cédula de Identidad</label>
                                <input type="text" name="ci" class="form-control bg-dark text-white border-secondary" placeholder="Ej: 12345678" inputmode="numeric" pattern="[0-9]+" oninput="this.value = this.value.replace(/[^0-9]/g, '')" title="Solo se permiten números" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-white">Contraseña</label>
                                <div class="input-group">
                                    <input id="loginPassword" type="password" name="password" class="form-control bg-dark text-white border-secondary" placeholder="Ingresa tu contraseña" required>
                                    <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('loginPassword')"><i class="bi bi-eye"></i></button>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Iniciar Sesión</button>
                        <?php endif; ?>
                        <div class="text-center mt-3">
                            <a href="index.php?url=auth/forgot_password" class="text-white-50 small"><i class="bi bi-key"></i> Olvidé mi contraseña</a>
                        </div>
                    </form>
                </div>
                <div class="tab-pane fade <?php echo $activeTab === 'register' ? 'show active' : ''; ?>" id="register" role="tabpanel" aria-labelledby="register-tab">
                    <form action="index.php?url=register" method="POST">
                        <div class="row g-3">
                            <div class="col-6">
                                <label class="form-label text-white">Nombre</label>
                                <input type="text" name="name" class="form-control bg-dark text-white border-secondary" pattern="[A-Za-zÁÉÍÓÚáéíóúÑñÜüÀ-ÿ' -]+" oninput="this.value = this.value.replace(/[0-9]/g, '')" title="No se permiten números" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label text-white">Apellido</label>
                                <input type="text" name="surname" class="form-control bg-dark text-white border-secondary" pattern="[A-Za-zÁÉÍÓÚáéíóúÑñÜüÀ-ÿ' -]+" oninput="this.value = this.value.replace(/[0-9]/g, '')" title="No se permiten números" required>
                            </div>
                        </div>
                        <div class="mb-3 mt-3">
                            <label class="form-label text-white">Cédula de Identidad</label>
                            <input type="text" name="ci" class="form-control bg-dark text-white border-secondary" placeholder="Ej: 12345678" inputmode="numeric" pattern="[0-9]+" oninput="this.value = this.value.replace(/[^0-9]/g, '')" title="Solo se permiten números" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-white">Fecha de Nacimiento</label>
                            <input type="date" name="birth" class="form-control bg-dark text-white border-secondary">
                        </div>
                        <div class="row g-3">
                            <div class="col-6">
                                <label class="form-label text-white">Contraseña</label>
                                <input type="password" name="password" class="form-control bg-dark text-white border-secondary" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label text-white">Confirmar contraseña</label>
                                <input type="password" name="confirm_password" class="form-control bg-dark text-white border-secondary" required>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-success w-100 mt-4">Crear cuenta</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function togglePassword(fieldId) {
    const field = document.getElementById(fieldId);
    if (!field) return;
    field.type = field.type === 'password' ? 'text' : 'password';
}
</script>
