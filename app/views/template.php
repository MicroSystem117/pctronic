<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <meta name="csrf-token" content="<?php echo htmlspecialchars(Controller::getCsrfToken(), ENT_QUOTES, 'UTF-8'); ?>">
    
    <!-- PWA y Metadatos para Móvil / APK -->
    <meta name="theme-color" content="#0f172a">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="PCtronic">
    <link rel="manifest" href="<?php echo htmlspecialchars(app_url('manifest.json'), ENT_QUOTES, 'UTF-8'); ?>">
    <link rel="apple-touch-icon" href="<?php echo htmlspecialchars(app_url('assets/icon-192.png'), ENT_QUOTES, 'UTF-8'); ?>">

    <title><?php echo isset($page_title) ? $page_title : 'Starlink Control'; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.11/css/dataTables.bootstrap5.min.css">
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;600&family=Outfit:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link href="<?php echo htmlspecialchars(app_url('css/style.css?v=20261111-1'), ENT_QUOTES, 'UTF-8'); ?>" rel="stylesheet">

    <link href="<?php echo htmlspecialchars(app_url('css/template.css?v=20261111-1'), ENT_QUOTES, 'UTF-8'); ?>" rel="stylesheet">
    <style>
        body {
            background: url('<?php echo htmlspecialchars(app_url('assets/background.jpg'), ENT_QUOTES, 'UTF-8'); ?>') center center / cover no-repeat fixed !important;
        }
    </style>
</head>
<body>

<!-- Notificación Flotante Offline / Online -->
<div id="offlineNotice" class="offline-banner d-none">
    <i class="bi bi-wifi-off me-2"></i>Sin conexión a internet. Verificando señal...
</div>

<div class="container-fluid">
    <div class="row g-0">
        <?php if (empty($hideLayout)): ?>
            <div class="sidebar d-none d-md-block" id="desktopSidebar">
                <?php include "layout/sidebar.php"; ?>
            </div>
            <div class="sidebar-overlay" id="sidebarOverlay" aria-hidden="true"></div>
        <?php endif; ?>

        <div id="mainContent" class="col-12 p-3 p-md-4 d-flex flex-column main-content">
            <?php
                $status = isset($_GET['status']) ? $_GET['status'] : null;
                $alerts = [
                    'success' => ['type' => 'success', 'message' => 'Operación realizada con éxito.'],
                    'updated' => ['type' => 'success', 'message' => 'Actualización realizada correctamente.'],
                    'deleted' => ['type' => 'success', 'message' => 'Eliminado exitosamente.'],
                    'empty' => ['type' => 'warning', 'message' => 'Faltan datos obligatorios. Completa los campos requeridos.'],
                    'access_denied' => ['type' => 'danger', 'message' => 'Acceso denegado. No tienes permisos para realizar esta acción.'],
                    'account_country_required' => ['type' => 'warning', 'message' => 'La cuenta administrativa seleccionada no tiene un país de origen configurado.'],
                    'invalid_pay' => ['type' => 'warning', 'message' => 'Día de pago inválido. Debe ser un número entre 1 y 31.'],
                    'invalid_email' => ['type' => 'warning', 'message' => 'El email no es válido.'],
                    'account_exists' => ['type' => 'warning', 'message' => 'Esta cuenta ya existe.'],
                    'email_exists' => ['type' => 'warning', 'message' => 'Este email ya está registrado en otra cuenta.'],
                    'user_created' => ['type' => 'success', 'message' => 'Usuario creado correctamente.'],
                    'user_updated' => ['type' => 'success', 'message' => 'Usuario actualizado correctamente.'],
                    'user_deleted' => ['type' => 'success', 'message' => 'Usuario eliminado correctamente.'],
                    'user_delete_denied' => ['type' => 'warning', 'message' => 'No puedes eliminar el usuario de la sesión actual.'],
                    'user_ci_exists' => ['type' => 'warning', 'message' => 'La cédula ya está registrada en otro usuario.'],
                    'user_empty' => ['type' => 'warning', 'message' => 'Completa los campos obligatorios del usuario.'],
                    'antenna_exists' => ['type' => 'warning', 'message' => 'Esta antena ya existe (serial duplicado).'],
                    'payment_success' => ['type' => 'success', 'message' => 'Pago registrado correctamente.'],
                    'payment_pending' => ['type' => 'info', 'message' => 'Pago cargado y enviado a revisión.'],
                    'payment_reviewed' => ['type' => 'success', 'message' => 'Estado del pago actualizado correctamente.'],
                    'payment_receipt_invalid' => ['type' => 'warning', 'message' => 'El comprobante no es válido. Usa una imagen o PDF de máximo 5 MB.'],
                    'payment_deleted' => ['type' => 'success', 'message' => 'Pago eliminado correctamente.'],
                    'backup_success' => ['type' => 'success', 'message' => 'Respaldo creado correctamente.'],
                    'backup_error' => ['type' => 'danger', 'message' => 'No se pudo crear el respaldo.'],
                    'restore_success' => ['type' => 'success', 'message' => 'Base de datos restaurada correctamente.'],
                    'restore_error' => ['type' => 'danger', 'message' => 'No se pudo restaurar el respaldo.'],
                    'restore_invalid_file' => ['type' => 'warning', 'message' => 'Archivo inválido. Carga un archivo SQL válido.'],
                    'export_success' => ['type' => 'success', 'message' => 'Exportación CSV creada correctamente.'],
                    'export_error' => ['type' => 'danger', 'message' => 'No se pudo crear la exportación CSV.'],
                    'delete_success' => ['type' => 'success', 'message' => 'Respaldo eliminado correctamente.'],
                    'delete_error' => ['type' => 'danger', 'message' => 'No se pudo eliminar el respaldo.'],
                    'login_success' => ['type' => 'success', 'message' => 'Inicio de sesión exitoso.'],
                    'login_failed' => ['type' => 'danger', 'message' => 'Usuario o contraseña incorrectos.'],
                    'login_empty' => ['type' => 'warning', 'message' => 'Completa los datos de inicio de sesión.'],
                    'logout_success' => ['type' => 'success', 'message' => 'Has cerrado sesión correctamente.'],
                    'register_success' => ['type' => 'success', 'message' => 'Registro completado. Ya puedes iniciar sesión.'],
                    'register_error' => ['type' => 'danger', 'message' => 'No se pudo completar el registro.'],
                    'register_empty' => ['type' => 'warning', 'message' => 'Completa todos los campos del registro.'],
                    'password_mismatch' => ['type' => 'warning', 'message' => 'Las contraseñas no coinciden.'],
                    'ci_exists' => ['type' => 'warning', 'message' => 'La cédula ya está registrada.'],
                    'no_sec_questions' => ['type' => 'danger', 'message' => 'No tienes preguntas de seguridad registradas. Configura al menos 3 preguntas desde tu panel de usuario o contacta al administrador.'],
                    'password_reset_success' => ['type' => 'success', 'message' => 'Contraseña restablecida correctamente. Ya puedes iniciar sesión.'],
                    'security_saved' => ['type' => 'success', 'message' => 'Preguntas de seguridad guardadas correctamente.'],
                    'security_empty' => ['type' => 'warning', 'message' => 'Completa todas las preguntas y respuestas de seguridad.'],
                    'session_questions' => ['type' => 'info', 'message' => 'Detectamos otra sesión activa. Responde tus preguntas de seguridad para continuar.'],
                    'session_questions_wrong' => ['type' => 'danger', 'message' => 'Las respuestas de seguridad no son correctas.'],
                    'session_questions_invalid' => ['type' => 'warning', 'message' => 'La verificación de seguridad expiró o está incompleta.'],
                    'session_questions_unavailable' => ['type' => 'danger', 'message' => 'No tienes preguntas de seguridad configuradas para autorizar otra sesión.'],
                    'session_revoked' => ['type' => 'danger', 'message' => 'Esta sesión fue cerrada porque se abrió otra sesión para el mismo usuario.'],
                    'forgot_empty' => ['type' => 'warning', 'message' => 'Ingresa tu cédula de identidad.'],
                    'forgot_user_not_found' => ['type' => 'danger', 'message' => 'No se encontró un usuario con esa cédula.'],
                    'answers_empty' => ['type' => 'warning', 'message' => 'Completa todas las respuestas de seguridad.'],
                    'wrong_answers' => ['type' => 'danger', 'message' => 'Alguna respuesta es incorrecta. Intenta de nuevo.'],
                    'reset_empty' => ['type' => 'warning', 'message' => 'Completa los campos de la nueva contraseña.'],
                    'exemption_saved' => ['type' => 'success', 'message' => 'Deuda previa exonerada correctamente.'],
                    'exemption_cleared' => ['type' => 'info', 'message' => 'Exoneración eliminada. Se restableció el cálculo original de deuda.'],
                    'exemption_error' => ['type' => 'danger', 'message' => 'No se pudo aplicar la exoneración de deuda.'],
                    'logs_cleared_old' => ['type' => 'success', 'message' => 'Registros antiguos eliminados correctamente.'],
                    'logs_cleared_all' => ['type' => 'success', 'message' => 'Historial de registros vaciado completamente.'],
                    'error' => ['type' => 'danger', 'message' => 'Ocurrió un error durante la operación.']
                ];
            ?>

            <div id="globalAlertContainer" style="display: none;"></div>

            <?php if (empty($hideLayout)): ?>
                <?php include "layout/header.php"; ?>
            <?php endif; ?>

            <main class="flex-grow-1">
                <?php 
                    if (isset($content) && file_exists($content)) {
                        include $content; 
                    } else {
                        echo "<p class='text-danger'>Error: El contenido de la vista no se pudo cargar.</p>";
                    }
                ?>
            </main>

            <hr class="border-secondary my-3">
            
            <?php include "layout/footer.php"; ?>
        </div>
    </div>
</div>

<?php if (empty($hideLayout)): ?>
    <?php include "layout/bottom_nav.php"; ?>
<?php endif; ?>

<div class="offcanvas offcanvas-start bg-dark text-white" tabindex="-1" id="mobileSidebar" aria-labelledby="mobileSidebarLabel">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="mobileSidebarLabel">Menú</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Cerrar"></button>
    </div>
    <div class="offcanvas-body p-0">
        <?php include "layout/sidebar.php"; ?>
    </div>
</div>

<!-- Modal Vista Previa de PDF (Tamaño Carta) -->
<div class="modal fade" id="pdfPreviewModal" tabindex="-1" aria-labelledby="pdfPreviewTitle" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content bg-dark text-white border-secondary shadow-lg pdf-preview-content">
            <div class="modal-header border-secondary d-flex justify-content-between align-items-center py-2 px-3">
                <div class="d-flex align-items-center gap-2">
                    <h5 class="modal-title fs-6 mb-0 d-flex align-items-center" id="pdfPreviewTitle">
                        <i class="bi bi-file-earmark-pdf-fill text-danger me-2"></i>Vista previa PDF
                    </h5>
                    <span class="badge bg-primary text-white d-none d-sm-inline-flex align-items-center" style="font-size: 0.70rem;">
                        <i class="bi bi-file-earmark-ruled me-1"></i>Tamaño Carta
                    </span>
                </div>
                <div class="d-flex align-items-center gap-1 gap-sm-2">
                    <div class="btn-group btn-group-sm" role="group" aria-label="Orientación Carta">
                        <button type="button" class="btn btn-sm btn-outline-info active" id="btnPdfOrientationLandscape" title="Orientación Horizontal (Carta)">
                            <i class="bi bi-layout-sidebar-reverse me-1"></i><span class="d-none d-sm-inline">Horizontal</span>
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-info" id="btnPdfOrientationPortrait" title="Orientación Vertical (Carta)">
                            <i class="bi bi-file-earmark me-1"></i><span class="d-none d-sm-inline">Vertical</span>
                        </button>
                    </div>
                    <button type="button" class="btn-close btn-close-white ms-1 ms-sm-2" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
            </div>
            <div class="modal-body p-0 position-relative" style="background: #334155;">
                <iframe id="pdfPreviewFrame" title="Vista previa del PDF" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; border: 0; background: #334155;"></iframe>
            </div>
            <div class="modal-footer border-secondary d-flex justify-content-between align-items-center flex-wrap gap-2 py-2 px-3">
                <div class="text-white-50 small d-none d-md-block">
                    <i class="bi bi-info-circle me-1"></i>Optimizado en <strong>Tamaño Carta (8.5 × 11 pulg / 215.9 × 279.4 mm)</strong>
                </div>
                <div class="d-flex gap-2 flex-wrap ms-auto">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <a id="pdfWhatsAppLink" class="btn btn-sm btn-success d-none" target="_blank" rel="noopener" title="Compartir por WhatsApp">
                        <i class="bi bi-whatsapp me-1"></i>Enviar WhatsApp
                    </a>
                    <button type="button" class="btn btn-sm btn-outline-light" id="btnPrintPdf" title="Imprimir documento">
                        <i class="bi bi-printer me-1"></i>Imprimir
                    </button>
                    <a id="pdfDownloadLink" class="btn btn-sm btn-primary" download>
                        <i class="bi bi-download me-1"></i>Descargar PDF
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.11/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.11/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jspdf@2.5.1/dist/jspdf.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jspdf-autotable@3.8.2/dist/jspdf.plugin.autotable.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
window.appConfig = {
    logoUrl: <?php echo json_encode(app_url('assets/Logo.png'), JSON_UNESCAPED_SLASHES); ?>,
    currentUser: <?php echo json_encode($_SESSION['user'] ?? 'Usuario', JSON_UNESCAPED_UNICODE); ?>,
    csrfToken: <?php echo json_encode(Controller::getCsrfToken()); ?>,
    alerts: <?php echo json_encode($alerts ?? [], JSON_UNESCAPED_UNICODE); ?>,
    initialStatus: <?php echo json_encode($status ?? null); ?>
};
window.csrfToken = window.appConfig.csrfToken;
window.appAlerts = window.appConfig.alerts;
window.initialAppStatus = window.appConfig.initialStatus;
</script>
<script src="<?php echo htmlspecialchars(app_url('js/app.js?v=20261111-1'), ENT_QUOTES, 'UTF-8'); ?>"></script>
</body>
</html>