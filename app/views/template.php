<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title : 'Starlink Control'; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    
    <link href="http://localhost/starlink-control/public/css/style.css" rel="stylesheet">

    <style>
        /* ==========================================================================
           ESTRUCTURA DE LA PLANTILLA - MODO OSCURO GLOBAL
           ========================================================================== */
        body {
            background-color: #0f111a !important;
            color: #ffffff !important;
        }
        
        .sidebar {
            background-color: #161b22 !important;
            min-height: 100vh;
            border-right: 1px solid #30363d;
            transition: width 0.2s ease, padding 0.2s ease;
        }

        .main-content {
            min-height: 100vh;
        }

        .sidebar.collapsed-sidebar {
            width: 80px !important;
            max-width: 80px !important;
            flex: 0 0 80px !important;
        }

        .sidebar.sidebar-hidden {
            display: none !important;
        }

        .main-content.full-width-content {
            width: 100% !important;
            max-width: 100% !important;
            flex: 0 0 100% !important;
        }

        .sidebar-panel.collapsed {
            padding: 1rem 0.5rem !important;
        }

        .sidebar-panel.collapsed .sidebar-brand {
            justify-content: center;
        }

        .sidebar-panel.collapsed .sidebar-toggle {
            margin-left: auto;
            margin-right: auto;
        }

        .card-custom {
            background-color: #161b22 !important;
            border: 1px solid #30363d !important;
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

        @media (max-width: 767.98px) {
            body {
                font-size: 0.95rem;
            }

            .main-content {
                padding: 1rem !important;
            }

            .card-custom {
                padding: 1rem !important;
            }

            .table-responsive {
                font-size: 0.9rem;
            }

            .btn {
                width: 100%;
                margin-bottom: 0.35rem;
            }

            .d-flex.justify-content-between,
            .d-flex.justify-content-end {
                flex-direction: column;
                align-items: flex-start !important;
            }
        }
    </style>
</head>
<body>

<div class="container-fluid">
    <div class="row g-0">
        <?php if (empty($hideLayout)): ?>
            <div class="col-12 col-md-3 col-lg-2 p-0 sidebar d-none d-md-block" id="desktopSidebar">
                <?php include "layout/sidebar.php"; ?>
            </div>
        <?php endif; ?>

        <div id="mainContent" class="<?php echo empty($hideLayout) ? 'col-12 col-md-9 col-lg-10' : 'col-12'; ?> p-3 p-md-4 d-flex flex-column main-content">
                <?php if (empty($hideLayout)): ?>
                    <?php include "layout/header.php"; ?>
                <?php endif; ?>
            <?php
                $status = isset($_GET['status']) ? $_GET['status'] : null;
                $alerts = [
                    'success' => ['type' => 'success', 'message' => 'Operación realizada con éxito.'],
                    'updated' => ['type' => 'success', 'message' => 'Actualización realizada correctamente.'],
                    'deleted' => ['type' => 'success', 'message' => 'Eliminado exitosamente.'],
                    'empty' => ['type' => 'warning', 'message' => 'Faltan datos obligatorios. Completa los campos requeridos.'],
                    'invalid_pay' => ['type' => 'warning', 'message' => 'Día de pago inválido. Debe ser un número entre 1 y 31.'],
                    'invalid_email' => ['type' => 'warning', 'message' => 'El email no es válido.'],
                    'account_exists' => ['type' => 'warning', 'message' => 'Esta cuenta ya existe.'],
                    'email_exists' => ['type' => 'warning', 'message' => 'Este email ya está registrado en otra cuenta.'],
                    'antenna_exists' => ['type' => 'warning', 'message' => 'Esta antena ya existe (serial duplicado).'],
                    'payment_success' => ['type' => 'success', 'message' => 'Pago registrado correctamente.'],
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
                    'error' => ['type' => 'danger', 'message' => 'Ocurrió un error durante la operación.']
                ];
            ?>

            <?php if (empty($hideLayout)): ?>
                <?php if ($status && isset($alerts[$status])): ?>
                    <div class="alert alert-<?php echo $alerts[$status]['type']; ?> alert-dismissible fade show" role="alert">
                        <?php echo $alerts[$status]['message']; ?>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Cerrar"></button>
                    </div>
                <?php endif; ?>
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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>