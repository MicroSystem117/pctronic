// Precarga de logo y variables del sistema para el reporte PDF
const appPdfLogo = new Image();
appPdfLogo.crossOrigin = 'Anonymous';
appPdfLogo.src = window.appConfig ? window.appConfig.logoUrl : "";
const currentAppUser = (window.appConfig && window.appConfig.currentUser) ? window.appConfig.currentUser : "Usuario";

let activePdfExportTable = null;
let activePdfOrientation = 'landscape';

function extractCleanCellText(td) {
    if (!td) return '';
    const clone = td.cloneNode(true);
    // Eliminar botones interactivos, iconos decorativos y scripts
    clone.querySelectorAll('.btn, button, a, .bi, script, noscript, [aria-hidden="true"]').forEach(el => el.remove());
    // Convertir saltos y bloques a saltos de línea legibles
    clone.querySelectorAll('br').forEach(br => br.replaceWith('\n'));
    clone.querySelectorAll('p, div, li').forEach(el => el.prepend('\n'));

    const text = clone.textContent || '';
    return text
        .split('\n')
        .map(line => line.trim().replace(/[ \t]{2,}/g, ' '))
        .filter(line => line.length > 0)
        .join('\n');
}

function generateTablePdf(table, orientation = null) {
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

    activePdfExportTable = table;
    const title = table.dataset.pdfTitle || 'Reporte del Sistema';

    // Encabezados y detección de columnas a omitir (ej: 'Acciones')
    const headerThs = Array.from(table.querySelectorAll('thead th'));
    const excludedIndexes = [];
    const headersData = [];

    headerThs.forEach((th, index) => {
        const text = th.textContent.trim();
        const lower = text.toLowerCase();
        if (lower === 'acciones' || lower === 'acción' || lower === 'accion' || text === '') {
            excludedIndexes.push(index);
        } else {
            headersData.push(text);
        }
    });

    // Determinar orientación si no se forzó:
    // Si la tabla contiene más de 4 columnas, usar Horizontal (Carta) para mayor holgura; si no, Vertical (Carta)
    if (!orientation) {
        if (table.dataset.pdfOrientation) {
            orientation = table.dataset.pdfOrientation;
        } else {
            orientation = headersData.length > 4 ? 'landscape' : 'portrait';
        }
    }
    activePdfOrientation = orientation;

    // Extraer filas:
    // Si la tabla usa DataTables, extraer TODOS los registros (incluso los de páginas 2, 3, etc.)
    const rowsData = [];
    if (window.jQuery && $.fn.dataTable && $.fn.dataTable.isDataTable(table)) {
        const dt = $(table).DataTable();
        const trNodes = dt.rows({ search: 'applied' }).nodes();
        Array.from(trNodes).forEach(tr => {
            const row = [];
            let colIndex = 0;
            Array.from(tr.children).forEach(td => {
                if (!excludedIndexes.includes(colIndex)) {
                    row.push(extractCleanCellText(td));
                }
                colIndex++;
            });
            if (row.length > 0) rowsData.push(row);
        });
    } else {
        table.querySelectorAll('tbody tr').forEach(tr => {
            if (tr.querySelector('.dataTables_empty')) return;
            const row = [];
            let colIndex = 0;
            Array.from(tr.children).forEach(td => {
                if (!excludedIndexes.includes(colIndex)) {
                    row.push(extractCleanCellText(td));
                }
                colIndex++;
            });
            if (row.length > 0) rowsData.push(row);
        });
    }

    // Inicializar jsPDF estrictamente en TAMAÑO CARTA (Letter)
    const pdf = new window.jspdf.jsPDF({
        orientation: orientation,
        unit: 'mm',
        format: 'letter' // 215.9 x 279.4 mm
    });

    const pageWidth = pdf.internal.pageSize.getWidth();
    const pageHeight = pdf.internal.pageSize.getHeight();

    // 1. Franja superior decorativa corporativa (Cyan / Sky)
    pdf.setFillColor(14, 165, 233); // #0ea5e9
    pdf.rect(0, 0, pageWidth, 3, 'F');

    // 2. Encabezado institucional de la página 1
    let hasLogo = false;
    if (appPdfLogo.complete && appPdfLogo.naturalWidth > 0) {
        try {
            const logoAspect = appPdfLogo.naturalWidth / appPdfLogo.naturalHeight;
            const logoH = 11;
            const logoW = logoH * logoAspect;
            pdf.addImage(appPdfLogo, 'PNG', 14, 7.5, logoW, logoH);
            hasLogo = true;
        } catch (e) {
            hasLogo = false;
        }
    }

    if (!hasLogo) {
        pdf.setFont('helvetica', 'bold');
        pdf.setFontSize(15);
        pdf.setTextColor(15, 23, 42); // #0f172a
        pdf.text('PCtronic', 14, 14);

        pdf.setFont('helvetica', 'normal');
        pdf.setFontSize(7.5);
        pdf.setTextColor(100, 116, 139); // #64748b
        pdf.text('STARLINK MANAGEMENT SYSTEM', 14, 19);
    } else {
        pdf.setFont('helvetica', 'bold');
        pdf.setFontSize(7.5);
        pdf.setTextColor(100, 116, 139); // #64748b
        pdf.text('STARLINK CONTROL & MANAGEMENT', 14, 22.5);
    }

    // Tarjeta de metadatos del reporte a la derecha
    const metaX = pageWidth - 14;
    pdf.setFont('helvetica', 'bold');
    pdf.setFontSize(13);
    pdf.setTextColor(15, 23, 42);
    pdf.text(title, metaX, 12.5, { align: 'right' });

    pdf.setFont('helvetica', 'normal');
    pdf.setFontSize(7.8);
    pdf.setTextColor(71, 85, 105); // #475569

    const dateStr = new Date().toLocaleString('es-VE', {
        year: 'numeric', month: '2-digit', day: '2-digit',
        hour: '2-digit', minute: '2-digit', hour12: true
    });

    pdf.text('Fecha: ' + dateStr + '  |  Generado por: ' + currentAppUser, metaX, 17.5, { align: 'right' });
    pdf.text('Total registros: ' + rowsData.length + '  |  Formato: Tamaño Carta (' + (orientation === 'landscape' ? 'Horizontal' : 'Vertical') + ')', metaX, 22, { align: 'right' });

    // Línea divisoria elegante
    pdf.setDrawColor(226, 232, 240); // #e2e8f0
    pdf.setLineWidth(0.35);
    pdf.line(14, 26, pageWidth - 14, 26);

    // Detección automática de alineación por columna
    const columnStyles = {};
    headersData.forEach((h, idx) => {
        const hLow = h.toLowerCase();
        if (hLow.includes('monto') || hLow.includes('precio') || hLow.includes('deuda') || hLow.includes('valor')) {
            columnStyles[idx] = { halign: 'right' };
        } else if (hLow.includes('fecha') || hLow.includes('vence') || hLow.includes('moneda') || hLow.includes('estado') || hLow.includes('kit') || hLow.includes('serial')) {
            columnStyles[idx] = { halign: 'center' };
        }
    });

    // 3. Renderizado de tabla con jspdf-autotable
    pdf.autoTable({
        head: [headersData],
        body: rowsData,
        startY: 30,
        margin: { top: 16, right: 14, bottom: 16, left: 14 },
        theme: 'striped',
        styles: {
            font: 'helvetica',
            fontSize: 7.5,
            textColor: [30, 41, 59], // #1e293b
            lineColor: [226, 232, 240], // #e2e8f0
            lineWidth: 0.15,
            cellPadding: { top: 2.2, right: 2, bottom: 2.2, left: 2 },
            valign: 'middle',
            overflow: 'linebreak'
        },
        headStyles: {
            fillColor: [15, 23, 42], // Fondo Slate Navy elegante #0f172a
            textColor: [255, 255, 255],
            fontSize: 8,
            fontStyle: 'bold',
            valign: 'middle',
            cellPadding: { top: 2.8, right: 2, bottom: 2.8, left: 2 }
        },
        alternateRowStyles: {
            fillColor: [248, 250, 252] // #f8fafc
        },
        columnStyles: columnStyles,
        didDrawPage: function(data) {
            const pageCount = pdf.internal.getNumberOfPages();
            const currentPage = data.pageNumber;
            const pWidth = pdf.internal.pageSize.getWidth();
            const pHeight = pdf.internal.pageSize.getHeight();

            pdf.saveGraphicsState();

            // Franja superior decorativa en todas las páginas
            pdf.setFillColor(14, 165, 233);
            pdf.rect(0, 0, pWidth, 2.5, 'F');

            // Pie de página profesional
            pdf.setDrawColor(226, 232, 240);
            pdf.setLineWidth(0.3);
            pdf.line(14, pHeight - 11, pWidth - 14, pHeight - 11);

            pdf.setFont('helvetica', 'normal');
            pdf.setFontSize(7.5);
            pdf.setTextColor(148, 163, 184); // #94a3b8
            pdf.text('PCtronic Starlink • Reporte Oficial del Sistema • Tamaño Carta', 14, pHeight - 6);
            pdf.text('Página ' + currentPage + ' de ' + pageCount, pWidth - 14, pHeight - 6, { align: 'right' });

            pdf.restoreGraphicsState();
        }
    });

    const filename = title.toLowerCase().replace(/[^a-z0-9]+/gi, '-') + '-carta.pdf';
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
    const waLink = document.getElementById('pdfWhatsAppLink');
    if (waLink) waLink.classList.add('d-none');
    previewTitle.innerHTML = '<i class="bi bi-file-earmark-pdf-fill text-danger me-2"></i>' + title + ' <span class="badge bg-secondary ms-2" style="font-size: 0.70rem;"><i class="bi bi-file-earmark-text me-1"></i>Carta (' + (orientation === 'landscape' ? 'Horizontal' : 'Vertical') + ')</span>';

    // Reflejar la orientación activa en los botones del modal
    const btnLandscape = document.getElementById('btnPdfOrientationLandscape');
    const btnPortrait = document.getElementById('btnPdfOrientationPortrait');
    if (btnLandscape && btnPortrait) {
        if (orientation === 'landscape') {
            btnLandscape.classList.add('active', 'btn-info', 'text-dark');
            btnLandscape.classList.remove('btn-outline-info');
            btnPortrait.classList.remove('active', 'btn-info', 'text-dark');
            btnPortrait.classList.add('btn-outline-info');
        } else {
            btnPortrait.classList.add('active', 'btn-info', 'text-dark');
            btnPortrait.classList.remove('btn-outline-info');
            btnLandscape.classList.remove('active', 'btn-info', 'text-dark');
            btnLandscape.classList.add('btn-outline-info');
        }
    }

    bootstrap.Modal.getOrCreateInstance(document.getElementById('pdfPreviewModal')).show();
}

function exportTableToPdf(table) {
    generateTablePdf(table);
}

// Inicialización de eventos para alternar orientación e imprimir en el modal
document.addEventListener('DOMContentLoaded', function () {
    const btnLandscape = document.getElementById('btnPdfOrientationLandscape');
    const btnPortrait = document.getElementById('btnPdfOrientationPortrait');
    const btnPrint = document.getElementById('btnPrintPdf');

    if (btnLandscape) {
        btnLandscape.addEventListener('click', function () {
            if (activePdfExportTable && activePdfOrientation !== 'landscape') {
                generateTablePdf(activePdfExportTable, 'landscape');
            }
        });
    }

    if (btnPortrait) {
        btnPortrait.addEventListener('click', function () {
            if (activePdfExportTable && activePdfOrientation !== 'portrait') {
                generateTablePdf(activePdfExportTable, 'portrait');
            }
        });
    }

    if (btnPrint) {
        btnPrint.addEventListener('click', function () {
            const frame = document.getElementById('pdfPreviewFrame');
            if (frame && frame.contentWindow) {
                frame.contentWindow.focus();
                frame.contentWindow.print();
            }
        });
    }
});

function initializePdfExportables() {
    document.querySelectorAll('.pdf-exportable').forEach(function (table) {
        const wrapper = table.closest('.table-responsive') || table.parentElement;
        if (!wrapper) return;

        // Evitar duplicar botones de exportación si ya existe en el contenedor
        if (wrapper.previousElementSibling && wrapper.previousElementSibling.classList.contains('pdf-export-toolbar')) {
            return;
        }
        if (wrapper.querySelector('.pdf-export-button')) {
            return;
        }

        const toolbar = document.createElement('div');
        toolbar.className = 'd-flex justify-content-end mb-2 pdf-export-toolbar';
        toolbar.innerHTML = '<button type="button" class="btn btn-sm btn-outline-light pdf-export-button"><i class="bi bi-file-earmark-pdf me-1 text-danger"></i> Exportar PDF (Carta)</button>';
        wrapper.parentNode.insertBefore(toolbar, wrapper);
        toolbar.querySelector('button').addEventListener('click', function () {
            exportTableToPdf(table);
        });
    });
}

function initializeDataTables() {
    if (window.jQuery && $.fn && $.fn.dataTable) {
        $.fn.dataTable.ext.errMode = function (settings, helpPage, message) {
            console.warn('DataTables warning:', message, helpPage);
        };
    }

    $('.datatable').each(function () {
        if ($.fn.DataTable.isDataTable(this)) {
            $(this).DataTable().destroy();
        }

        // Si la tabla contiene una fila única con colspan (mensaje vacío de PHP),
        // se vacía el tbody para que DataTables aplique su mensaje nativo sin error tn/18
        const $tbody = $(this).find('tbody');
        if ($tbody.find('tr td[colspan]').length > 0 && $tbody.find('tr').length === 1) {
            $tbody.empty();
        }

        const table = $(this).DataTable({
            paging: true,
            searching: true,
            ordering: true,
            order: [],
            info: true,
            lengthChange: true,
            pageLength: 10,
            responsive: false,
            scrollX: false,
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
                cliente: 0,
                nickname: 1,
                kit: 2,
                serial: 3,
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

    if (isModal) {
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
            position: 'bottom-end',
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
    const defaultHeaders = {
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': window.csrfToken || ''
    };
    const requestOptions = Object.assign({}, options);
    requestOptions.headers = Object.assign(defaultHeaders, options.headers || {});

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

// Configurar token CSRF en jQuery si está presente
if (window.jQuery) {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': window.csrfToken || ''
        }
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
                const urlObj = new URL(targetLink.href, window.location.href);
                const action = urlObj.searchParams.get('action');
                const isStateChangingAction = ['delete', 'approve', 'reject', 'restore_existing'].includes(action);

                if (isStateChangingAction) {
                    const postData = new FormData();
                    urlObj.searchParams.forEach((val, key) => {
                        if (key !== 'url') {
                            postData.append(key, val);
                        }
                    });
                    postData.append('csrf_token', window.csrfToken || '');

                    const targetUrl = urlObj.pathname + (urlObj.searchParams.has('url') ? '?url=' + urlObj.searchParams.get('url') : '');

                    loadContentWithoutReload(targetUrl, {
                        method: 'POST',
                        body: postData
                    }).catch(err => {
                        window.showSweetAlert('danger', err.message || 'Error al procesar la acción.', true);
                    });
                } else if (targetLink.href.includes('index.php?url=') && targetLink.href.includes('action=')) {
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

// Interceptor de confirmación y protección CSRF en formularios
document.addEventListener('submit', function (event) {
    const form = event.target;
    if (!(form instanceof HTMLFormElement)) return;

    // Inyectar automáticamente token CSRF en formularios POST si falta
    if (form.method && form.method.toUpperCase() === 'POST') {
        if (!form.querySelector('input[name="csrf_token"]')) {
            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = 'csrf_token';
            csrfInput.value = window.csrfToken || '';
            form.appendChild(csrfInput);
        }
    }

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

// ==========================================================================
// PWA: SERVICE WORKER REGISTRATION & OFFLINE MONITOR
// ==========================================================================
if ('serviceWorker' in navigator) {
    window.addEventListener('load', function() {
        navigator.serviceWorker.register('sw.js').then(function(reg) {
            console.log('Service Worker registrado correctamente (PWA lista):', reg.scope);
        }).catch(function(err) {
            console.warn('Error al registrar Service Worker:', err);
        });
    });
}

// Monitor de Conexión a Internet (Online / Offline)
function updateOnlineStatus() {
    let banner = document.getElementById('offlineNotice');
    if (!banner) {
        banner = document.createElement('div');
        banner.id = 'offlineNotice';
        banner.className = 'offline-banner d-none';
        document.body.prepend(banner);
    }

    if (!navigator.onLine) {
        banner.className = 'offline-banner';
        banner.innerHTML = '<i class="bi bi-wifi-off me-2"></i>Sin conexión a internet. Verificando señal...';
    } else {
        if (!banner.classList.contains('d-none')) {
            banner.className = 'offline-banner online';
            banner.innerHTML = '<i class="bi bi-wifi me-2"></i>Conexión restablecida con éxito.';
            setTimeout(() => {
                banner.className = 'offline-banner d-none';
            }, 3000);
        }
    }
}

window.addEventListener('online', updateOnlineStatus);
window.addEventListener('offline', updateOnlineStatus);

document.dispatchEvent(new Event('app:content-ready'));