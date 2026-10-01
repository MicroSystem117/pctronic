<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title : 'Starlink Control'; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.11/css/dataTables.bootstrap5.min.css">
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;600&family=Outfit:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link href="<?php echo htmlspecialchars(app_url('css/style.css?v=20261001-1'), ENT_QUOTES, 'UTF-8'); ?>" rel="stylesheet">

    <style>
        /* ==========================================================================
           ESTRUCTURA DE LA PLANTILLA - MODO OSCURO GLOBAL
           ========================================================================== */
        body {
            background:
                url('<?php echo htmlspecialchars(app_url('assets/background.jpg'), ENT_QUOTES, 'UTF-8'); ?>') center center / cover no-repeat fixed !important;
            color: #ffffff !important;
        }

        /* ==========================================================================
           SWEETALERT2 GLOBAL: SIEMPRE AL FRENTE DE TODO (Z-INDEX MÁXIMO)
           ========================================================================== */
        .swal2-container {
            z-index: 999999 !important;
            pointer-events: none;
        }
        .swal2-container.swal2-backdrop-show,
        .swal2-container.swal2-noanimation {
            pointer-events: auto;
        }
        .swal2-popup {
            pointer-events: auto;
        }
        .swal2-popup.swal-custom-dark {
            background: #161b22 !important;
            border: 1px solid rgba(255, 255, 255, 0.15) !important;
            color: #ffffff !important;
            border-radius: 16px !important;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.7) !important;
            z-index: 1000000 !important;
        }
        .swal-custom-dark .swal2-title {
            color: #ffffff !important;
        }
        .swal-custom-dark .swal2-html-container {
            color: #cbd5e1 !important;
        }
        .swal2-toast.swal-custom-toast {
            background: #1e293b !important;
            color: #ffffff !important;
            border: 1px solid rgba(255, 255, 255, 0.15) !important;
            border-radius: 12px !important;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5) !important;
            z-index: 1000000 !important;
            pointer-events: auto !important;
        }
        .swal-custom-toast .swal2-title {
            color: #ffffff !important;
            font-size: 0.95rem !important;
        }
        
        .sidebar {
            position: fixed !important;
            top: 0;
            left: 0;
            bottom: 0;
            width: 300px;
            max-width: 85vw;
            z-index: 1050;
            transform: translateX(-105%);
            transition: transform 0.25s ease;
            background: transparent !important;
        }

        .sidebar.sidebar-open {
            transform: translateX(0);
        }

        .sidebar .sidebar-panel {
            height: 100vh;
            border-radius: 0 18px 18px 0;
        }

        .sidebar-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.35);
            backdrop-filter: blur(2px);
            z-index: 1040;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.2s ease;
        }

        .sidebar-overlay.show {
            opacity: 1;
            pointer-events: auto;
        }

        .main-content {
            min-height: 100vh;
        }

        .sidebar-panel.collapsed {
            padding: 1rem 0.5rem !important;
        }

        .sidebar-panel.collapsed .sidebar-brand {
            justify-content: center;
        }

        .sidebar-logo {
            filter: drop-shadow(0 6px 12px rgba(0, 0, 0, 0.35));
        }

        .auth-brand {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: min(100%, 420px);
            min-height: 150px;
            padding: 1rem;
            border-radius: 18px;
            background: rgba(8, 12, 20, 0.32);
            box-shadow: 0 10px 28px rgba(0, 0, 0, 0.35);
        }

        .auth-brand img {
            max-width: 100%;
            max-height: 110px;
            object-fit: contain;
            filter: drop-shadow(0 8px 18px rgba(0, 0, 0, 0.4));
        }

        .sidebar-panel.collapsed .sidebar-toggle {
            margin-left: auto;
            margin-right: auto;
        }

        .card-custom {
            background-color: #161b22 !important;
            border: 1px solid #30363d !important;
        }

        .auth-card {
            background: linear-gradient(180deg, rgba(22, 27, 34, 0.96), rgba(12, 15, 22, 0.96)) !important;
            border: 1px solid rgba(255, 255, 255, 0.12) !important;
            border-radius: 22px !important;
            box-shadow: 0 18px 45px rgba(0, 0, 0, 0.45) !important;
            backdrop-filter: blur(10px);
        }

        .auth-card .card-body {
            padding: 1.75rem !important;
            border-radius: 22px;
            background:
                linear-gradient(180deg, rgba(255, 255, 255, 0.03), rgba(255, 255, 255, 0.01)),
                rgba(10, 12, 18, 0.82);
        }

        .auth-card .form-control,
        .auth-card .input-group .btn,
        .auth-card .nav-tabs .nav-link {
            border-radius: 14px;
        }

        .auth-card .form-control {
            background: rgba(8, 12, 18, 0.85) !important;
            border: 1px solid rgba(255, 255, 255, 0.14) !important;
            box-shadow: inset 0 1px 2px rgba(0, 0, 0, 0.2);
        }

        .auth-card .form-control:focus {
            border-color: rgba(13, 110, 253, 0.85) !important;
            box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25) !important;
        }

        .auth-card .input-group .btn {
            border-left: 0;
            background: rgba(255, 255, 255, 0.06);
            color: #fff;
        }

        .auth-card .nav-tabs {
            border-bottom: 1px solid rgba(255, 255, 255, 0.12);
        }

        .auth-card .nav-tabs .nav-link {
            color: #d8e0ec;
            background: transparent;
            border: 1px solid transparent;
            font-weight: 600;
            transition: all 0.2s ease;
        }

        .auth-card .nav-tabs .nav-link:hover {
            color: #ffffff;
            background: rgba(255, 255, 255, 0.04);
        }

        .auth-card .nav-tabs .nav-link.active {
            color: #ffffff;
            background: linear-gradient(180deg, #0d6efd, #0a57d5);
            border-color: #0d6efd #0d6efd transparent;
            box-shadow: 0 6px 16px rgba(13, 110, 253, 0.35);
        }

        .auth-card .btn-primary,
        .auth-card .btn-success {
            border-radius: 14px;
            font-weight: 700;
            letter-spacing: 0.3px;
            box-shadow: 0 10px 22px rgba(0, 0, 0, 0.25);
        }

        /* ==========================================================================
           APLICACIÓN MASIVA DE BLANCO ABSOLUTO (SELECTORES DE ALTA ESPECIFICIDAD)
           ========================================================================== */

        /* Romper grises en textos secundarios, alertas vacías y pies de página */
        p, small, span, td, th, label, .text-muted, .text-white-50, .text-light-50 {
            color: #ffffff !important;
        }

        /* Forzar blanco en todo el árbol de navegación del Sidebar */
        .sidebar a, 
        .sidebar span, 
        .sidebar i, 
        .nav-link {
            color: #ffffff !important;
        }

        /* Mantener azul el módulo seleccionado de la barra lateral */
        .nav-pills .nav-link.active, 
        .nav-pills .nav-link.active * {
            background-color: #0d6efd !important;
            color: #ffffff !important;
        }

        /* Efecto de selección sutil al pasar el mouse */
        .nav-link:hover {
            background-color: rgba(255, 255, 255, 0.1) !important;
            color: #ffffff !important;
        }

        /* ==========================================================================
           ESTILOS ULTRA-ESPECÍFICOS PARA CONTENEDORES MODALES Y FORMULARIOS
           ========================================================================== */

        /* Garantizar visibilidad de títulos, etiquetas y avisos dentro del modal */
        .modal,
        .modal-dialog,
        .modal-content,
        .modal-header,
        .modal-title,
        .modal-body,
        .modal-body label,
        .modal-body .form-label,
        .modal-body .form-text,
        .modal-body small {
            color: #ffffff !important;
        }

        /* Configuración de las casillas de entrada (inputs) de texto */
        .form-control, 
        .form-select, 
        input, 
        select, 
        textarea {
            color: #ffffff !important;
            background-color: rgba(255, 255, 255, 0.08) !important;
            border: 1px solid #30363d !important;
        }

        /* Asegurar que el texto que escribe el usuario se renderice en blanco puro */
        .form-control:focus, 
        input:focus, 
        select:focus {
            color: #ffffff !important;
            background-color: rgba(255, 255, 255, 0.12) !important;
        }

        /* Color claro y nítido para los placeholders de ejemplo */
        .form-control::placeholder, 
        input::placeholder {
            color: #cbd5e1 !important;
            opacity: 1 !important;
        }

        /* ==========================================================================
   CORRECCIÓN PARA CAJAS DE SELECCIÓN (SELECT / OPTION)
   ========================================================================== */

/* Forzar que las opciones desplegables tengan fondo claro y letra negra */
select option,
.form-select option,
.modal-content select option {
    color: #212529 !important;
    background-color: #ffffff !important;
}

/* Por si acaso el navegador Chromium toma el focus del select con letras oscuras */
select:focus option {
    color: #212529 !important;
    background-color: #ffffff !important;
}

        /* Botón de cerrar (X) del modal en blanco */
        .btn-close {
            filter: invert(1) !important;
        }

        /* ==========================================================================
           EXCEPCIONES OBLIGATORIAS (ALERTAS ROJAS Y FILAS CLARAS)
           ========================================================================== */

        /* Rescatar el color rojo de los asteriscos obligatorios */
        .text-danger, 
        .text-danger *, 
        span[style*="color: red"], 
        span[style*="color:red"] {
            color: #ff4d4d !important;
        }

        /* Mantener texto oscuro únicamente dentro de las cabeceras claras de las tablas */
        .table-light, 
        .table-light th, 
        .table-light td, 
        .table-light * {
            color: #212529 !important;
        }

        /* ==========================================================================
           TABLAS MODERNAS Y RESPONSIVE
           ========================================================================== */
        .table {
            --bs-table-bg: transparent !important;
            --bs-table-striped-bg: rgba(255, 255, 255, 0.02) !important;
            --bs-table-hover-bg: rgba(14, 165, 233, 0.08) !important;
            border-color: rgba(255, 255, 255, 0.06) !important;
        }

        .table thead th {
            background: rgba(15, 23, 42, 0.95) !important;
            color: #94a3b8 !important;
            font-family: 'Outfit', sans-serif !important;
            font-weight: 600 !important;
            text-transform: uppercase !important;
            font-size: 0.74rem !important;
            letter-spacing: 0.06em !important;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1) !important;
            padding: 0.85rem 0.9rem !important;
            white-space: nowrap !important;
            vertical-align: middle;
        }

        .table tbody td {
            padding: 0.85rem 0.9rem !important;
            vertical-align: middle !important;
            border-bottom: 1px solid rgba(255, 255, 255, 0.04) !important;
            color: #e2e8f0 !important;
        }

        .table-responsive {
            position: relative;
            border-radius: 14px;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            scrollbar-width: thin;
            scrollbar-color: rgba(14, 165, 233, 0.4) rgba(15, 23, 42, 0.6);
        }

        .table-responsive::-webkit-scrollbar {
            height: 6px;
        }
        .table-responsive::-webkit-scrollbar-track {
            background: rgba(15, 23, 42, 0.6);
            border-radius: 999px;
        }
        .table-responsive::-webkit-scrollbar-thumb {
            background: linear-gradient(90deg, #0ea5e9, #2563eb);
            border-radius: 999px;
        }

        /* Indicador móvil para sugerir scroll */
        .mobile-table-hint {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            font-size: 0.76rem;
            font-weight: 500;
            color: #94a3b8;
            background: rgba(14, 165, 233, 0.08);
            border: 1px solid rgba(14, 165, 233, 0.22);
            border-radius: 20px;
            padding: 0.35rem 0.75rem;
            margin-bottom: 0.65rem;
        }

        /* DataTables controles oscuros y estilizados */
        .dataTables_wrapper .dataTables_filter input {
            background: rgba(15, 23, 42, 0.85) !important;
            border: 1px solid rgba(255, 255, 255, 0.12) !important;
            border-radius: 10px !important;
            color: #ffffff !important;
            padding: 0.35rem 0.75rem !important;
            font-size: 0.85rem !important;
        }
        .dataTables_wrapper .dataTables_filter input:focus {
            border-color: #0ea5e9 !important;
            box-shadow: 0 0 0 3px rgba(14, 165, 233, 0.25) !important;
            outline: none !important;
        }
        .dataTables_wrapper .dataTables_length select {
            background-color: #1e293b !important;
            border: 1px solid rgba(255, 255, 255, 0.12) !important;
            border-radius: 8px !important;
            color: #ffffff !important;
            padding: 0.3rem 0.5rem !important;
        }
        .dataTables_wrapper .dataTables_info {
            color: #94a3b8 !important;
            font-size: 0.82rem !important;
            padding-top: 0.75rem !important;
        }
        .dataTables_wrapper .dataTables_paginate {
            padding-top: 0.75rem !important;
        }
        .dataTables_wrapper .dataTables_paginate .pagination {
            gap: 4px;
        }
        .dataTables_wrapper .dataTables_paginate .page-item .page-link {
            background: rgba(15, 23, 42, 0.6) !important;
            border: 1px solid rgba(255, 255, 255, 0.08) !important;
            color: #94a3b8 !important;
            border-radius: 8px !important;
            padding: 0.3rem 0.65rem !important;
            font-size: 0.82rem !important;
        }
        .dataTables_wrapper .dataTables_paginate .page-item.active .page-link {
            background: linear-gradient(135deg, #0ea5e9, #2563eb) !important;
            border-color: transparent !important;
            color: #ffffff !important;
            font-weight: 600;
            box-shadow: 0 2px 10px rgba(14, 165, 233, 0.4);
        }

        /* ==========================================================================
           REGLAS RESPONSIVE MÓVIL (TELÉFONOS)
           ========================================================================== */
        @media (max-width: 767.98px) {
            body {
                font-size: 0.92rem;
            }

            .main-content {
                padding: 0.75rem 0.5rem !important;
            }

            .card-custom {
                padding: 1rem 0.75rem !important;
                border-radius: 16px !important;
            }

            /* Los botones en celdas de tabla NO se expanden al 100% */
            .table .btn,
            .table .btn-sm,
            .table td .btn,
            .table td a.btn,
            .table td button.btn {
                width: auto !important;
                min-width: 32px !important;
                height: 32px !important;
                display: inline-flex !important;
                align-items: center !important;
                justify-content: center !important;
                padding: 0 0.55rem !important;
                margin: 1px !important;
                white-space: nowrap !important;
                border-radius: 8px !important;
            }

            .table td:last-child {
                white-space: nowrap !important;
                min-width: 90px;
            }

            /* Columna fija (Sticky) en pantallas pequeñas para no perder el contexto */
            .table-sticky-col th:first-child,
            .table-sticky-col td:first-child {
                position: sticky !important;
                left: 0 !important;
                z-index: 3 !important;
                background-color: #0f172a !important;
                box-shadow: 3px 0 8px rgba(0, 0, 0, 0.5) !important;
                border-right: 1px solid rgba(255, 255, 255, 0.1) !important;
            }
            .table-sticky-col thead th:first-child {
                z-index: 4 !important;
                background-color: #0f172a !important;
            }

            /* Botones principales de cabecera de página sí se adaptan */
            .page-heading-actions {
                flex-direction: column !important;
                align-items: stretch !important;
                gap: 0.65rem !important;
            }
            .page-heading-actions .btn {
                width: 100% !important;
            }
        }
    </style>
</head>
<body>

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
                    'error' => ['type' => 'danger', 'message' => 'Ocurrió un error durante la operación.']
                ];
            ?>

            <script>
                window.appAlerts = <?php echo json_encode($alerts, JSON_UNESCAPED_UNICODE); ?>;
                window.initialAppStatus = <?php echo json_encode($status); ?>;
            </script>

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

<div class="offcanvas offcanvas-start bg-dark text-white" tabindex="-1" id="mobileSidebar" aria-labelledby="mobileSidebarLabel">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="mobileSidebarLabel">Menú</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Cerrar"></button>
    </div>
    <div class="offcanvas-body p-0">
        <?php include "layout/sidebar.php"; ?>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.11/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.11/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jspdf@2.5.1/dist/jspdf.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jspdf-autotable@3.8.2/dist/jspdf.plugin.autotable.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function exportTableToPdf(table) {
    if (!window.jspdf || typeof window.jspdf.jsPDF !== 'function') {
        Swal.fire({
            icon: 'error',
            title: 'Error de exportación',
            text: 'No se pudo cargar el módulo de exportación PDF.',
            customClass: { popup: 'swal-custom-dark' },
            confirmButtonColor: '#0d6efd'
        });
        return;
    }

    const pdf = new window.jspdf.jsPDF({ orientation: 'landscape' });
    const title = table.dataset.pdfTitle || 'Reporte';
    const exportTable = table.cloneNode(true);
    const headerCells = Array.from(exportTable.querySelectorAll('thead th'));
    const excludedIndexes = headerCells.reduce((indexes, cell, index) => {
        if (cell.textContent.trim().toLowerCase() === 'acciones') {
            indexes.push(index);
        }
        return indexes;
    }, []);

    exportTable.querySelectorAll('tr').forEach(row => {
        Array.from(row.children).reverse().forEach((cell, reverseIndex) => {
            const index = row.children.length - 1 - reverseIndex;
            if (excludedIndexes.includes(index)) {
                cell.remove();
            }
        });
    });

    pdf.setFontSize(16);
    pdf.text(title, 14, 15);
    pdf.setFontSize(9);
    pdf.text('Generado: ' + new Date().toLocaleString('es-VE'), 14, 22);
    pdf.autoTable({
        html: exportTable,
        startY: 28,
        theme: 'grid',
        styles: { fontSize: 8, cellPadding: 2, overflow: 'linebreak' },
        headStyles: { fillColor: [13, 110, 253], textColor: [255, 255, 255] },
        alternateRowStyles: { fillColor: [242, 246, 252] }
    });
    const filename = title.toLowerCase().replace(/[^a-z0-9]+/gi, '-') + '.pdf';
    const pdfUrl = URL.createObjectURL(pdf.output('blob'));
    const previewFrame = document.getElementById('pdfPreviewFrame');
    const downloadLink = document.getElementById('pdfDownloadLink');
    const previewTitle = document.getElementById('pdfPreviewTitle');

    if (window.pdfPreviewUrl) {
        URL.revokeObjectURL(window.pdfPreviewUrl);
    }

    window.pdfPreviewUrl = pdfUrl;
    previewFrame.src = pdfUrl;
    downloadLink.href = pdfUrl;
    downloadLink.download = filename;
    previewTitle.textContent = 'Vista previa: ' + title;
    bootstrap.Modal.getOrCreateInstance(document.getElementById('pdfPreviewModal')).show();
}

const pdfPreviewModal = document.createElement('div');
pdfPreviewModal.className = 'modal fade';
pdfPreviewModal.id = 'pdfPreviewModal';
pdfPreviewModal.tabIndex = -1;
pdfPreviewModal.innerHTML = '<div class="modal-dialog modal-xl modal-dialog-centered"><div class="modal-content bg-dark text-white border-secondary" style="height: 90vh"><div class="modal-header border-secondary"><h5 class="modal-title" id="pdfPreviewTitle">Vista previa PDF</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button></div><div class="modal-body p-0"><iframe id="pdfPreviewFrame" title="Vista previa del PDF" style="width: 100%; height: 100%; border: 0; background: #525659"></iframe></div><div class="modal-footer border-secondary"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cerrar</button><a id="pdfDownloadLink" class="btn btn-primary" download><i class="bi bi-download me-1"></i> Descargar PDF</a></div></div></div>';
document.body.appendChild(pdfPreviewModal);

function initializePdfExportables() {
document.querySelectorAll('.pdf-exportable').forEach(function (table) {
    const wrapper = table.closest('.table-responsive') || table.parentElement;
    if (!wrapper || wrapper.querySelector('.pdf-export-button')) {
        return;
    }

    const toolbar = document.createElement('div');
    toolbar.className = 'd-flex justify-content-end mb-2';
    toolbar.innerHTML = '<button type="button" class="btn btn-sm btn-outline-light pdf-export-button"><i class="bi bi-file-earmark-pdf me-1"></i> Exportar PDF</button>';
    wrapper.parentNode.insertBefore(toolbar, wrapper);
    toolbar.querySelector('button').addEventListener('click', function () {
        exportTableToPdf(table);
    });
});
}

function initializeDataTables() {
    $('.datatable').each(function () {
        const table = $(this).DataTable({
            paging: true,
            searching: true,
            ordering: true,
            info: true,
            lengthChange: true,
            pageLength: 10,
            responsive: false,
            scrollX: true,
            autoWidth: false,
            columnDefs: [{ targets: -1, orderable: false }],
            language: {
                decimal: '',
                emptyTable: 'No hay datos disponibles en la tabla',
                info: 'Mostrando _START_ a _END_ de _TOTAL_ registros',
                infoEmpty: 'Mostrando 0 a 0 de 0 registros',
                infoFiltered: '(filtrado de _MAX_ registros totales)',
                lengthMenu: 'Mostrar _MENU_ registros',
                loadingRecords: 'Cargando...',
                processing: 'Procesando...',
                search: 'Buscar:',
                zeroRecords: 'No se encontraron registros coincidentes',
                paginate: {
                    first: 'Primero',
                    last: 'Último',
                    next: 'Siguiente',
                    previous: 'Anterior'
                }
            }
        });

        const searchAccountsInput = document.getElementById('search_accounts');
        if (searchAccountsInput) {
            searchAccountsInput.addEventListener('input', function () {
                table.search(this.value.trim()).draw();
            });
        }

        const searchClientsInput = document.getElementById('search_clients');
        if (searchClientsInput) {
            searchClientsInput.addEventListener('input', function () {
                table.search(this.value.trim()).draw();
            });
        }

        const searchAntenasInput = document.getElementById('search_antenas');
        const searchAntenasType = document.getElementById('search_antenas_type');
        if (searchAntenasInput && searchAntenasType) {
            const columnMap = {
                all: null,
                serial: 0,
                nickname: 1,
                kit: 2,
                cliente: 3,
                cuenta: 6
            };

            const applyAntenaFilter = function () {
                const query = searchAntenasInput.value.trim();
                const type = searchAntenasType.value;
                table.columns().search('');

                if (!query) {
                    table.search('').draw();
                    return;
                }

                if (type === 'all') {
                    table.search(query).draw();
                    return;
                }

                const columnIndex = columnMap[type];
                if (columnIndex !== null && columnIndex !== undefined) {
                    table.column(columnIndex).search(query);
                } else {
                    table.search(query);
                }

                table.draw();
            };

            searchAntenasInput.addEventListener('input', applyAntenaFilter);
            searchAntenasType.addEventListener('change', applyAntenaFilter);
        }
    });
}

window.showSweetAlert = function(type, message, isModal = false) {
    if (typeof Swal === 'undefined') return;
    const iconMap = {
        'success': 'success',
        'danger': 'error',
        'error': 'error',
        'warning': 'warning',
        'info': 'info'
    };
    const icon = iconMap[type] || 'info';

    if (isModal || icon === 'error') {
        Swal.fire({
            icon: icon,
            title: icon === 'error' ? 'Atención' : (icon === 'success' ? '¡Éxito!' : 'Aviso'),
            text: message,
            confirmButtonColor: '#0d6efd',
            confirmButtonText: 'Entendido',
            customClass: { popup: 'swal-custom-dark' }
        });
    } else {
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3500,
            timerProgressBar: true,
            customClass: { popup: 'swal-custom-toast' },
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer);
                toast.addEventListener('mouseleave', Swal.resumeTimer);
            }
        });
        Toast.fire({
            icon: icon,
            title: message
        });
    }
};

// Garantizar que SweetAlert2 reciba el foco sin conflicto con modales de Bootstrap
document.addEventListener('focusin', function (e) {
    if (e.target && e.target.closest && e.target.closest('.swal2-container')) {
        e.stopImmediatePropagation();
    }
}, true);

window.checkUrlStatusAndAlert = function(url = window.location.href) {
    try {
        const u = new URL(url, window.location.origin);
        const status = u.searchParams.get('status');
        if (status && window.appAlerts && window.appAlerts[status]) {
            const item = window.appAlerts[status];
            window.showSweetAlert(item.type, item.message);
            u.searchParams.delete('status');
            window.history.replaceState({}, '', u.toString());
        }
    } catch (e) {
        console.error(e);
    }
};

$(document).ready(function () {
    initializePdfExportables();
    initializeDataTables();
    if (window.initialAppStatus && window.appAlerts && window.appAlerts[window.initialAppStatus]) {
        const item = window.appAlerts[window.initialAppStatus];
        window.showSweetAlert(item.type, item.message);
        try {
            const u = new URL(window.location.href);
            u.searchParams.delete('status');
            window.history.replaceState({}, '', u.toString());
        } catch(e) {}
    }
});

function replaceMainContent(documentResponse, responseUrl) {
    const nextMain = documentResponse.querySelector('main');
    const currentMain = document.querySelector('main');
    if (!nextMain || !currentMain) {
        window.location.href = responseUrl;
        return;
    }

    document.querySelectorAll('.modal.show').forEach(modal => {
        const modalInstance = bootstrap.Modal.getInstance(modal);
        if (modalInstance) modalInstance.hide();
    });
    document.querySelectorAll('.modal-backdrop').forEach(backdrop => backdrop.remove());
    document.body.classList.remove('modal-open');
    document.body.style.removeProperty('padding-right');

    const currentAlerts = document.getElementById('globalAlertContainer');
    const nextAlerts = documentResponse.getElementById('globalAlertContainer');
    if (currentAlerts && nextAlerts) {
        currentAlerts.innerHTML = nextAlerts.innerHTML;
    }

    currentMain.innerHTML = nextMain.innerHTML;
    document.title = documentResponse.title;
    window.history.pushState({}, '', responseUrl);

    currentMain.querySelectorAll('script').forEach(oldScript => {
        const newScript = document.createElement('script');
        Array.from(oldScript.attributes).forEach(attribute => newScript.setAttribute(attribute.name, attribute.value));
        newScript.textContent = oldScript.textContent;
        oldScript.replaceWith(newScript);
    });

    document.dispatchEvent(new Event('app:content-ready'));
    initializePdfExportables();
    initializeDataTables();
    window.checkUrlStatusAndAlert(responseUrl);
}

function loadContentWithoutReload(url, options = {}) {
    const requestOptions = Object.assign({
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    }, options);

    return fetch(url, requestOptions).then(response => {
        if (!response.ok) throw new Error('No se pudo actualizar el contenido.');
        if (response.url.includes('url=login')) {
            window.location.href = response.url;
            return null;
        }
        return response.text().then(html => ({ html, url: response.url }));
    }).then(result => {
        if (!result) return;
        const parsedDocument = new DOMParser().parseFromString(result.html, 'text/html');
        replaceMainContent(parsedDocument, result.url);
    });
}

// Interceptor universal de confirmaciones con SweetAlert2
document.addEventListener('click', function (event) {
    const targetLink = event.target.closest('a[onclick*="confirm"], button[onclick*="confirm"], .btn-confirm-action, [data-confirm-text]');
    if (!targetLink) return;

    if (targetLink.dataset.swalApproved === 'true') {
        targetLink.dataset.swalApproved = 'false';
        return;
    }

    event.preventDefault();
    event.stopPropagation();
    event.stopImmediatePropagation();

    const title = targetLink.dataset.confirmTitle || '¿Estás seguro?';
    let text = targetLink.dataset.confirmText;
    if (!text && targetLink.getAttribute('onclick')) {
        const match = targetLink.getAttribute('onclick').match(/confirm\s*\(\s*['"]([^'"]+)['"]\s*\)/);
        if (match && match[1]) text = match[1];
    }
    if (!text) text = 'Esta acción no se puede deshacer.';

    const confirmBtn = targetLink.dataset.confirmBtn || 'Sí, continuar';
    const isDanger = !targetLink.classList.contains('btn-warning') && !targetLink.classList.contains('btn-info');

    Swal.fire({
        title: title,
        text: text,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: isDanger ? '#dc3545' : '#eab308',
        cancelButtonColor: '#6c757d',
        confirmButtonText: confirmBtn,
        cancelButtonText: 'Cancelar',
        customClass: { popup: 'swal-custom-dark' }
    }).then((result) => {
        if (result.isConfirmed) {
            targetLink.dataset.swalApproved = 'true';
            if (targetLink.tagName.toLowerCase() === 'a' && targetLink.href) {
                if (targetLink.href.includes('index.php?url=') && targetLink.href.includes('action=')) {
                    loadContentWithoutReload(targetLink.href).catch(err => {
                        window.showSweetAlert('danger', err.message, true);
                    });
                } else {
                    window.location.href = targetLink.href;
                }
            } else if (targetLink.form) {
                targetLink.form.submit();
            } else {
                targetLink.click();
            }
        }
    });
}, true);

// Interceptor de confirmación en formularios
document.addEventListener('submit', function (event) {
    const form = event.target;
    if (!(form instanceof HTMLFormElement)) return;

    if (form.dataset.swalApproved === 'true') {
        form.dataset.swalApproved = 'false';
        return;
    }

    const onsubmitAttr = form.getAttribute('onsubmit') || '';
    if (onsubmitAttr.includes('confirm') || form.dataset.confirmText) {
        event.preventDefault();
        event.stopPropagation();
        event.stopImmediatePropagation();

        let text = form.dataset.confirmText;
        if (!text && onsubmitAttr) {
            const match = onsubmitAttr.match(/confirm\s*\(\s*['"]([^'"]+)['"]\s*\)/);
            if (match && match[1]) text = match[1];
        }
        if (!text) text = '¿Confirmar operación?';

        Swal.fire({
            title: '¿Confirmar acción?',
            text: text,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#0d6efd',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, continuar',
            cancelButtonText: 'Cancelar',
            customClass: { popup: 'swal-custom-dark' }
        }).then((result) => {
            if (result.isConfirmed) {
                form.dataset.swalApproved = 'true';
                form.submit();
            }
        });
        return;
    }

    if (event.defaultPrevented || form.dataset.ajaxDisabled === 'true') return;
    if (form.method.toLowerCase() !== 'post') return;
    if (form.action.includes('url=login') || form.action.includes('url=register') || form.action.includes('url=auth')) return;

    event.preventDefault();
    const submitButton = form.querySelector('button[type="submit"]');
    if (submitButton) submitButton.disabled = true;
    loadContentWithoutReload(form.action, { method: 'POST', body: new FormData(form) })
        .catch(error => window.showSweetAlert('danger', error.message, true))
        .finally(() => { if (submitButton) submitButton.disabled = false; });
});

document.addEventListener('click', function (event) {
    if (event.defaultPrevented) return;
    const link = event.target.closest('a[href]');
    if (!link || link.target === '_blank' || link.hasAttribute('download') || link.dataset.bsToggle || link.href.includes('#')) return;
    if (!link.href.includes('index.php?url=') || !link.href.includes('action=')) return;
    if (link.classList.contains('payment-action') || link.classList.contains('payment-delete-action')) return;
    if (link.getAttribute('onclick')?.includes('confirm') || link.dataset.confirmText) return;

    event.preventDefault();
    loadContentWithoutReload(link.href).catch(error => window.showSweetAlert('danger', error.message, true));
});

document.dispatchEvent(new Event('app:content-ready'));
</script>
</body>
</html>