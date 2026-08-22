<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title : 'Starlink Control'; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.11/css/dataTables.bootstrap5.min.css">
    
    <link href="http://localhost/starlink-control/public/css/style.css?v=20260819-4" rel="stylesheet">

    <style>
        /* ==========================================================================
           ESTRUCTURA DE LA PLANTILLA - MODO OSCURO GLOBAL
           ========================================================================== */
        body {
            background:
                url('http://localhost/starlink-control/public/assets/background.jpg') center center / cover no-repeat fixed !important;
            color: #ffffff !important;
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
<script>
function exportTableToPdf(table) {
    if (!window.jspdf || typeof window.jspdf.jsPDF !== 'function') {
        alert('No se pudo cargar el módulo de exportación PDF.');
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

$(document).ready(function () {
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
});
</script>
</body>
</html>