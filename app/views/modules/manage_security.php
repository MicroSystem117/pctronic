<?php
$status = isset($_GET['status']) ? $_GET['status'] : null;
$alerts = [
    'security_empty' => ['type' => 'warning', 'message' => 'Completa todas las preguntas y respuestas.'],
    'security_saved' => ['type' => 'success', 'message' => 'Preguntas de seguridad actualizadas con éxito.'],
    '2fa_enabled' => ['type' => 'success', 'message' => '¡Autenticación en Dos Pasos (2FA) activada correctamente! Tu cuenta ahora está protegida.'],
    '2fa_disabled' => ['type' => 'info', 'message' => 'Autenticación en Dos Pasos (2FA) desactivada.'],
    '2fa_setup_failed' => ['type' => 'danger', 'message' => 'El código de 6 dígitos ingresado no es válido. Verifica la hora de tu teléfono e inténtalo de nuevo.'],
    '2fa_disable_wrong_pass' => ['type' => 'danger', 'message' => 'Contraseña incorrecta. No se pudo desactivar el 2FA.']
];
$sec = isset($sec) && is_array($sec) ? $sec : [];
$twoFa = isset($two_fa) && is_array($two_fa) ? $two_fa : ['totp_enabled' => 0];
$is2FaActive = !empty($twoFa['totp_enabled']);
?>
<div class="row justify-content-center">
    <div class="col-md-9 col-lg-7">
        <div class="card card-custom shadow-lg">
            <div class="card-body p-4">
                <div class="d-flex align-items-center mb-4 pb-3 border-bottom border-secondary border-opacity-25">
                    <div class="me-3 fs-2 text-primary">
                        <i class="bi bi-shield-lock-fill"></i>
                    </div>
                    <div>
                        <h3 class="text-white mb-0">Seguridad de la Cuenta</h3>
                        <p class="text-white-50 small mb-0">Gestiona la protección de tu acceso mediante 2FA y preguntas de seguridad.</p>
                    </div>
                </div>

                <?php if ($status && isset($alerts[$status])): ?>
                    <div class="alert alert-<?php echo $alerts[$status]['type']; ?> alert-dismissible fade show" role="alert">
                        <i class="bi bi-info-circle-fill me-1"></i>
                        <?php echo $alerts[$status]['message']; ?>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Cerrar"></button>
                    </div>
                <?php endif; ?>

                <ul class="nav nav-pills nav-fill mb-4" id="securityTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="tab-2fa-btn" data-bs-toggle="pill" data-bs-target="#tab-2fa" type="button" role="tab">
                            <i class="bi bi-phone me-1"></i> Autenticación 2FA
                            <?php if ($is2FaActive): ?>
                                <span class="badge bg-success ms-1">Activo</span>
                            <?php else: ?>
                                <span class="badge bg-secondary ms-1">Inactivo</span>
                            <?php endif; ?>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="tab-questions-btn" data-bs-toggle="pill" data-bs-target="#tab-questions" type="button" role="tab">
                            <i class="bi bi-question-circle me-1"></i> Preguntas Secretas
                        </button>
                    </li>
                </ul>

                <div class="tab-content" id="securityTabsContent">
                    <!-- Pestaña 1: Autenticación en Dos Pasos (2FA) -->
                    <div class="tab-pane fade show active" id="tab-2fa" role="tabpanel" aria-labelledby="tab-2fa-btn">
                        <?php if ($is2FaActive): ?>
                            <div class="card bg-success bg-opacity-10 border-success mb-4 p-4 text-center">
                                <div class="fs-1 text-success mb-2">
                                    <i class="bi bi-shield-check"></i>
                                </div>
                                <h4 class="text-white mb-1">El 2FA está ACTIVADO</h4>
                                <p class="text-white-70 small mb-0">
                                    Cada vez que inicies sesión se solicitará un código temporal de 6 dígitos desde tu app autenticadora.
                                </p>
                            </div>

                            <div class="card bg-black bg-opacity-25 border-secondary p-3">
                                <h6 class="text-danger mb-2"><i class="bi bi-exclamation-triangle"></i> Desactivar 2FA</h6>
                                <p class="text-white-50 small mb-3">
                                    Si necesitas cambiar de dispositivo o desactivar esta capa de seguridad, ingresa tu contraseña actual para confirmar.
                                </p>
                                <form action="index.php?url=auth/manage_security" method="POST">
                                    <?php echo Controller::getCsrfInput(); ?>
                                    <input type="hidden" name="action" value="disable_2fa">
                                    <div class="input-group mb-2">
                                        <input type="password" name="password" class="form-control bg-dark text-white border-secondary" placeholder="Tu contraseña actual" required>
                                        <button type="submit" class="btn btn-outline-danger">
                                            Desactivar 2FA
                                        </button>
                                    </div>
                                </form>
                            </div>
                        <?php else: ?>
                            <div class="mb-4">
                                <h5 class="text-white mb-2"><i class="bi bi-qr-code-scan me-1 text-primary"></i> Configurar Google Authenticator</h5>
                                <p class="text-white-50 small">
                                    Escanea el siguiente código QR con tu aplicación autenticadora (Google Authenticator, Microsoft Authenticator o Authy) o introduce la clave manualmente.
                                </p>
                            </div>

                            <div class="row align-items-center g-3 mb-4 bg-black bg-opacity-30 p-3 rounded border border-secondary border-opacity-50">
                                <div class="col-md-5 text-center">
                                    <?php if (!empty($qr_url)): ?>
                                        <img src="<?php echo htmlspecialchars($qr_url, ENT_QUOTES, 'UTF-8'); ?>" alt="Código QR 2FA" class="img-thumbnail p-2 bg-white" style="max-width: 170px;">
                                    <?php endif; ?>
                                </div>
                                <div class="col-md-7">
                                    <label class="form-label text-white-50 small mb-1">Clave secreta manual:</label>
                                    <div class="p-2 bg-dark rounded border border-secondary font-monospace text-warning fw-bold text-break mb-2 text-center fs-6">
                                        <?php echo htmlspecialchars($setup_secret ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                    </div>
                                    <div class="text-white-50 small">
                                        <i class="bi bi-info-circle me-1"></i> Tipo: Basado en tiempo (TOTP)
                                    </div>
                                </div>
                            </div>

                            <form action="index.php?url=auth/manage_security" method="POST">
                                <?php echo Controller::getCsrfInput(); ?>
                                <input type="hidden" name="action" value="enable_2fa">
                                <div class="mb-3">
                                    <label class="form-label text-white fw-bold">Paso 2: Ingresa el código de 6 dígitos generado</label>
                                    <input type="text" name="code" class="form-control form-control-lg bg-dark text-white border-secondary text-center fw-bold" placeholder="000000" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" required style="letter-spacing: 0.3em; font-size: 1.3rem;">
                                </div>
                                <button type="submit" class="btn btn-primary btn-lg w-100">
                                    <i class="bi bi-shield-plus me-1"></i> Activar Autenticación 2FA
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>

                    <!-- Pestaña 2: Preguntas de Seguridad -->
                    <div class="tab-pane fade" id="tab-questions" role="tabpanel" aria-labelledby="tab-questions-btn">
                        <p class="text-white-50 small mb-4">
                            Estas preguntas te permiten recuperar el acceso a tu cuenta si olvidas tu contraseña.
                        </p>
                        <form action="index.php?url=auth/manage_security" method="POST">
                            <?php echo Controller::getCsrfInput(); ?>
                            <input type="hidden" name="action" value="save_questions">
                            <div class="mb-3">
                                <label class="form-label text-white">Pregunta 1</label>
                                <input type="text" name="question1" class="form-control bg-dark text-white border-secondary" placeholder="Ej: ¿Cuál es el nombre de tu primera mascota?" value="<?php echo htmlspecialchars($sec['question1'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" required>
                                <input type="text" name="answer1" class="form-control bg-dark text-white border-secondary mt-2" placeholder="Respuesta 1" value="<?php echo htmlspecialchars($sec['answer1'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-white">Pregunta 2</label>
                                <input type="text" name="question2" class="form-control bg-dark text-white border-secondary" placeholder="Ej: ¿Ciudad donde naciste?" value="<?php echo htmlspecialchars($sec['question2'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" required>
                                <input type="text" name="answer2" class="form-control bg-dark text-white border-secondary mt-2" placeholder="Respuesta 2" value="<?php echo htmlspecialchars($sec['answer2'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-white">Pregunta 3</label>
                                <input type="text" name="question3" class="form-control bg-dark text-white border-secondary" placeholder="Ej: ¿Nombre de tu escuela primaria?" value="<?php echo htmlspecialchars($sec['question3'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" required>
                                <input type="text" name="answer3" class="form-control bg-dark text-white border-secondary mt-2" placeholder="Respuesta 3" value="<?php echo htmlspecialchars($sec['answer3'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" required>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="bi bi-save me-1"></i> Guardar preguntas de seguridad
                            </button>
                        </form>
                    </div>
                </div>

                <div class="mt-4 pt-3 border-top border-secondary border-opacity-25 text-center">
                    <a href="index.php?url=dashboard" class="text-white-50 small"><i class="bi bi-arrow-left"></i> Volver al Panel de Control</a>
                </div>
            </div>
        </div>
    </div>
</div>
