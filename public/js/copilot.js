/**
 * Copiloto Starlink • Asistente y Guía Operativa sin IA
 * Proporciona tours interactivos en vivo, spotlights sobre elementos de la interfaz
 * y búsqueda rápida de procedimientos basada en el Manual de Usuario.
 */

(function() {
    'use strict';

    // Definición de tours interactivos guiados
    const COPILOT_TOURS = {
        command_palette: {
            title: 'Atajos y Búsqueda Global (Ctrl + K)',
            requiredUrl: null, // Disponible en cualquier módulo
            steps: [
                {
                    target: '#btnOpenCommandPalette',
                    title: 'Buscador Global y Acceso Rápido',
                    text: 'Desde cualquier pantalla puedes presionar <strong>Ctrl + K</strong> o hacer clic en este botón para buscar antenas, clientes o módulos sin usar el menú.',
                    position: 'bottom'
                },
                {
                    target: '#commandPaletteModal',
                    actionBefore: function() {
                        const modalEl = document.getElementById('commandPaletteModal');
                        if (modalEl && typeof bootstrap !== 'undefined') {
                            const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
                            modal.show();
                        }
                    },
                    title: 'Paleta de Comandos Activa',
                    text: 'Escribe cualquier término (ej: "pagos", "antena", "cliente") y navega con las flechas <strong>↑ / ↓</strong>. Presiona <strong>ENTER</strong> para seleccionar o <strong>ESC</strong> para salir.',
                    position: 'center'
                }
            ]
        },
        payment: {
            title: 'Registro de Cobranza y Pagos',
            requiredUrl: 'payments',
            steps: [
                {
                    target: '#btnOpenPaymentModal, button[data-bs-target="#modalPayment"], a[href*="payments"]',
                    title: '1. Iniciar Registro de Pago',
                    text: 'Haz clic en el botón de registrar pago. Puedes registrar pagos para una antena individual o seleccionar múltiples antenas a la vez.',
                    position: 'bottom'
                },
                {
                    target: '#search_payments, #paymentsTable_filter input, .dataTables_filter input',
                    title: '2. Historial y Conciliación',
                    text: 'En esta tabla puedes buscar pagos por cliente, número de referencia o fecha para verificar si ya fue conciliado o está pendiente de aprobación.',
                    position: 'bottom'
                },
                {
                    target: '.btn-outline-light[title*="Recibo"], a[href*="payments/receipt"]',
                    title: '3. Recibo Oficial con Código QR',
                    text: 'Cada pago aprobado dispone de un botón para generar el comprobante en tamaño carta con <strong>código QR verificable</strong> para el cliente.',
                    position: 'left'
                }
            ]
        },
        suspension: {
            title: 'Corte Técnico y Reactivación de Servicio',
            requiredUrl: 'antenas',
            steps: [
                {
                    target: '#search_antenas, #antenasTable_filter input',
                    title: '1. Localizar la Antena',
                    text: 'Escribe el serial, kit, cliente o alias en este buscador para ubicar rápidamente la antena del cliente.',
                    position: 'bottom'
                },
                {
                    target: '.badge.bg-success, .badge.bg-danger, .badge.bg-warning, .badge.bg-secondary',
                    title: '2. Verificar Estado de Solvencia',
                    text: 'Observa la insignia: <strong class="text-success">Solvente</strong>, <strong class="text-warning">Por Vencer</strong>, <strong class="text-danger">Vencido</strong> o <strong class="text-secondary">Suspendido</strong>.',
                    position: 'bottom'
                },
                {
                    target: 'button[title*="Suspender"], button[title*="Reactivar"], .btn-outline-secondary, .btn-outline-warning',
                    title: '3. Interruptor de Corte / Reactivación',
                    text: 'Haz clic en este botón para registrar el <strong>corte técnico</strong> (suspensión operativa). Si el cliente ya pagó, el mismo botón sirve para <strong>reactivar</strong> el servicio.',
                    position: 'left'
                },
                {
                    target: '.btn-whatsapp, a[title*="WhatsApp"]',
                    title: '4. Recordatorio por WhatsApp',
                    text: 'Presiona este botón para redactar un mensaje automático de cobro con los datos del serial y saldo adeudado del cliente.',
                    position: 'left'
                }
            ]
        },
        client_statement: {
            title: 'Ficha y Estado de Cuenta de Cliente',
            requiredUrl: 'clients',
            steps: [
                {
                    target: '#search_clients, #clientsTable_filter input',
                    title: '1. Buscar Cliente',
                    text: 'Introduce el nombre, apellido o número de cédula en este filtro para encontrar el expediente del cliente.',
                    position: 'bottom'
                },
                {
                    target: '.btn-client-starlinks, button[data-bs-target="#modalClientStarlinks"]',
                    title: '2. Antenas Vinculadas',
                    text: 'Este botón despliega todas las antenas que pertenecen al cliente con su respectivo plan mensual y estado.',
                    position: 'left'
                },
                {
                    target: 'button[title*="Estado de cuenta"], .btn-outline-primary',
                    title: '3. Generar Estado de Cuenta en PDF',
                    text: 'Genera un reporte consolidado en tamaño carta con el balance total, mensualidades pagadas y meses pendientes de todas sus antenas.',
                    position: 'left'
                }
            ]
        },
        calendar: {
            title: 'Calendario y Fechas de Vencimiento',
            requiredUrl: 'dashboard',
            steps: [
                {
                    target: '#btnCalViewGrid, #btnCalViewList',
                    title: '1. Selector de Vista (Grilla vs Lista)',
                    text: 'Puedes alternar entre la vista de calendario mensual tradicional o la <strong>vista de agenda/lista</strong> especialmente optimizada para celulares.',
                    position: 'bottom'
                },
                {
                    target: '#calendarGridView, .calendar-table',
                    title: '2. Días con Cortes y Cobros',
                    text: 'Las casillas con fondo azul u obsidian resaltan los días del mes donde vencen planes de antenas. Haz clic en cualquier día para ver la lista detallada.',
                    position: 'top'
                },
                {
                    target: '#chartCollectionTrend, #chartPlansDistribution',
                    title: '3. Telemetría y Recaudación Mensual',
                    text: 'Estos gráficos interactivos muestran la tendencia de cobranza del mes y la proporción de clientes según su plan contratado.',
                    position: 'top'
                }
            ]
        }
    };

    // Objeto principal del Copiloto
    const StarlinkCopilot = {
        currentTour: null,
        currentStepIndex: 0,
        activeElement: null,

        init: function() {
            this.bindEvents();
            this.updateContextInfo();
            this.checkAutoTour();
        },

        // Enlazar eventos de la interfaz
        bindEvents: function() {
            const self = this;

            // Botones de inicio de tour
            document.querySelectorAll('.copilot-tour-btn').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    const tourKey = this.getAttribute('data-tour');
                    self.startTour(tourKey);
                });
            });

            // Buscador interno del Copiloto
            const searchInput = document.getElementById('copilotSearchInput');
            const clearBtn = document.getElementById('copilotSearchClearBtn');

            if (searchInput) {
                searchInput.addEventListener('input', function() {
                    self.filterFaqTopics(this.value.trim().toLowerCase());
                });
            }

            if (clearBtn && searchInput) {
                clearBtn.addEventListener('click', function() {
                    searchInput.value = '';
                    self.filterFaqTopics('');
                    searchInput.focus();
                });
            }

            // Botones del Tooltip del Tour
            const tourNextBtn = document.getElementById('copilotTourNextBtn');
            const tourPrevBtn = document.getElementById('copilotTourPrevBtn');
            const tourCloseBtn = document.getElementById('copilotTourCloseBtn');
            const tourExitBtn = document.getElementById('copilotTourExitBtn');

            if (tourNextBtn) {
                tourNextBtn.addEventListener('click', function() {
                    self.nextStep();
                });
            }

            if (tourPrevBtn) {
                tourPrevBtn.addEventListener('click', function() {
                    self.prevStep();
                });
            }

            if (tourCloseBtn) {
                tourCloseBtn.addEventListener('click', function() {
                    self.endTour();
                });
            }

            if (tourExitBtn) {
                tourExitBtn.addEventListener('click', function() {
                    self.endTour();
                });
            }

            // Atajos de teclado durante el Tour
            document.addEventListener('keydown', function(e) {
                if (!self.currentTour) return;

                if (e.key === 'Escape') {
                    self.endTour();
                } else if (e.key === 'ArrowRight' && e.target.tagName !== 'INPUT') {
                    self.nextStep();
                } else if (e.key === 'ArrowLeft' && e.target.tagName !== 'INPUT') {
                    self.prevStep();
                }
            });

            // Reposicionar en resize de ventana
            window.addEventListener('resize', function() {
                if (self.currentTour && self.activeElement) {
                    self.positionTooltip(self.activeElement);
                }
            });
        },

        // Detección contextual de la pantalla actual
        updateContextInfo: function() {
            const urlParams = new URLSearchParams(window.location.search);
            const currentUrl = (urlParams.get('url') || 'dashboard').replace(/\/$/, '');

            const titleEl = document.getElementById('copilotContextTitle');
            const descEl = document.getElementById('copilotContextDesc');
            const actionsEl = document.getElementById('copilotContextActions');
            const badgeEl = document.getElementById('copilotCurrentModuleBadge');

            if (!titleEl || !descEl || !actionsEl) return;

            if (badgeEl) badgeEl.textContent = currentUrl;

            let title = 'Panel de Control Principal';
            let desc = 'Visualiza los indicadores financieros del mes, el calendario de cortes y los pagos recientes.';
            let actionsHtml = `
                <button type="button" class="btn btn-outline-info btn-sm py-1 px-2 small" onclick="StarlinkCopilot.startTour('calendar')">
                    <i class="bi bi-play-circle me-1"></i>Tour de Calendario
                </button>
                <button type="button" class="btn btn-outline-light btn-sm py-1 px-2 small" onclick="StarlinkCopilot.startTour('command_palette')">
                    <i class="bi bi-keyboard me-1"></i>Atajos Ctrl+K
                </button>
            `;

            if (currentUrl === 'antenas') {
                title = 'Gestión y Monitoreo de Antenas';
                desc = 'Consulta el estado de solvencia o mora de cada terminal, aplica cortes técnicos preventivos y envía recordatorios de WhatsApp.';
                actionsHtml = `
                    <button type="button" class="btn btn-outline-warning btn-sm py-1 px-2 small" onclick="StarlinkCopilot.startTour('suspension')">
                        <i class="bi bi-play-circle me-1"></i>Tour Corte y Reactivación
                    </button>
                `;
            } else if (currentUrl === 'payments') {
                title = 'Cobranzas y Validación de Pagos';
                desc = 'Registra pagos simples o múltiples, concilia transferencias bancarias y genera recibos oficiales en tamaño carta con código QR.';
                actionsHtml = `
                    <button type="button" class="btn btn-outline-success btn-sm py-1 px-2 small" onclick="StarlinkCopilot.startTour('payment')">
                        <i class="bi bi-play-circle me-1"></i>Tour Registro de Pago
                    </button>
                `;
            } else if (currentUrl === 'clients') {
                title = 'Directorio y Ficha de Clientes';
                desc = 'Administra los titulares de servicio, visualiza las antenas asignadas a cada cliente y exporta sus estados de cuenta consolidados.';
                actionsHtml = `
                    <button type="button" class="btn btn-outline-info btn-sm py-1 px-2 small" onclick="StarlinkCopilot.startTour('client_statement')">
                        <i class="bi bi-play-circle me-1"></i>Tour Estado de Cuenta
                    </button>
                `;
            } else if (currentUrl === 'database') {
                title = 'Mantenimiento y Respaldo SQL';
                desc = 'Genera copias de seguridad descargables de toda la base de datos para resguardar la información ante cualquier contingencia.';
                actionsHtml = `
                    <a href="MANUAL_DE_USUARIO.md" class="btn btn-outline-info btn-sm py-1 px-2 small" target="_blank">
                        <i class="bi bi-shield-check me-1"></i>Ver Guía de Backups
                    </a>
                `;
            } else if (currentUrl === 'logs') {
                title = 'Auditoría y Trazabilidad';
                desc = 'Historial inmutable de eventos del sistema: cortes técnicos, aprobaciones de pago, inicios de sesión y cambios de configuración.';
                actionsHtml = '';
            } else if (currentUrl === 'manual') {
                title = 'Manual de Operaciones Completo';
                desc = 'Estás en la biblioteca técnica con los 10 capítulos del manual oficial de Starlink Control Manager.';
                actionsHtml = `
                    <button type="button" class="btn btn-outline-light btn-sm py-1 px-2 small" onclick="window.print()">
                        <i class="bi bi-printer me-1"></i>Imprimir Guía
                    </button>
                `;
            }

            titleEl.textContent = title;
            descEl.innerHTML = desc;
            actionsEl.innerHTML = actionsHtml;
        },

        // Iniciar un tour interactivo
        startTour: function(tourKey) {
            const tour = COPILOT_TOURS[tourKey];
            if (!tour) return;

            const urlParams = new URLSearchParams(window.location.search);
            const currentUrl = (urlParams.get('url') || 'dashboard').replace(/\/$/, '');

            // Si el tour requiere otra pantalla, preguntar al usuario y redirigir
            if (tour.requiredUrl && tour.requiredUrl !== currentUrl) {
                const targetUrl = 'index.php?url=' + tour.requiredUrl;
                const modalDrawer = bootstrap.Offcanvas.getInstance(document.getElementById('copilotDrawer'));
                if (modalDrawer) modalDrawer.hide();

                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: 'Navegar al Módulo',
                        text: 'El tour "' + tour.title + '" requiere estar en la pantalla de ' + tour.requiredUrl.toUpperCase() + '. ¿Deseas ir ahora?',
                        icon: 'info',
                        showCancelButton: true,
                        confirmButtonText: '<i class="bi bi-arrow-right-circle me-1"></i>Ir al Módulo',
                        cancelButtonText: 'Cancelar',
                        background: '#0f172a',
                        color: '#ffffff',
                        confirmButtonColor: '#0284c7',
                        cancelButtonColor: '#475569'
                    }).then(function(result) {
                        if (result.isConfirmed) {
                            sessionStorage.setItem('starlink_auto_tour', tourKey);
                            window.location.href = targetUrl;
                        }
                    });
                } else {
                    if (confirm('Este tour requiere ir al módulo ' + tour.requiredUrl + '. ¿Deseas continuar?')) {
                        sessionStorage.setItem('starlink_auto_tour', tourKey);
                        window.location.href = targetUrl;
                    }
                }
                return;
            }

            // Cerrar el drawer lateral para que el usuario vea la pantalla completa
            const drawerEl = document.getElementById('copilotDrawer');
            if (drawerEl && typeof bootstrap !== 'undefined') {
                const offcanvasInstance = bootstrap.Offcanvas.getInstance(drawerEl);
                if (offcanvasInstance) offcanvasInstance.hide();
            }

            this.currentTour = tour;
            this.currentStepIndex = 0;
            this.showStep(0);
        },

        // Mostrar un paso específico
        showStep: function(index) {
            if (!this.currentTour || !this.currentTour.steps[index]) {
                this.endTour();
                return;
            }

            this.currentStepIndex = index;
            const step = this.currentTour.steps[index];
            const totalSteps = this.currentTour.steps.length;

            // Ejecutar acción previa si está definida
            if (typeof step.actionBefore === 'function') {
                step.actionBefore();
            }

            // Limpiar highlight anterior
            if (this.activeElement) {
                this.activeElement.classList.remove('copilot-spotlight-active');
            }

            // Buscar elemento objetivo
            let targetEl = null;
            if (typeof step.target === 'string') {
                const selectors = step.target.split(',');
                for (let i = 0; i < selectors.length; i++) {
                    const found = document.querySelector(selectors[i].trim());
                    if (found && found.offsetParent !== null) { // visible
                        targetEl = found;
                        break;
                    }
                }
                // Si ninguno visible, tomar el primero existente
                if (!targetEl) {
                    targetEl = document.querySelector(step.target);
                }
            } else if (step.target instanceof HTMLElement) {
                targetEl = step.target;
            }

            const tooltipEl = document.getElementById('copilotTourTooltip');
            const badgeEl = document.getElementById('copilotTourBadge');
            const titleEl = document.getElementById('copilotTourTitle');
            const textEl = document.getElementById('copilotTourText');
            const prevBtn = document.getElementById('copilotTourPrevBtn');
            const nextBtn = document.getElementById('copilotTourNextBtn');

            if (!tooltipEl) return;

            badgeEl.textContent = 'Paso ' + (index + 1) + ' de ' + totalSteps;
            titleEl.textContent = step.title;
            textEl.innerHTML = step.text;

            // Botón anterior
            if (index === 0) {
                prevBtn.style.display = 'none';
            } else {
                prevBtn.style.display = 'inline-block';
            }

            // Botón siguiente / finalizar
            if (index === totalSteps - 1) {
                nextBtn.innerHTML = '<i class="bi bi-check-lg me-1"></i>Entendido';
                nextBtn.className = 'btn btn-sm btn-success';
            } else {
                nextBtn.innerHTML = 'Siguiente<i class="bi bi-arrow-right ms-1"></i>';
                nextBtn.className = 'btn btn-sm btn-primary';
            }

            tooltipEl.classList.remove('d-none');

            if (targetEl) {
                this.activeElement = targetEl;
                targetEl.classList.add('copilot-spotlight-active');
                targetEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
                this.positionTooltip(targetEl, step.position);
            } else {
                // Posicionar flotante centrado si no hay elemento
                this.positionTooltipCenter();
            }
        },

        // Posicionar el Tooltip flotante anclado al elemento
        positionTooltip: function(targetEl, preferredPos) {
            const tooltipEl = document.getElementById('copilotTourTooltip');
            if (!tooltipEl || !targetEl) return;

            const rect = targetEl.getBoundingClientRect();
            const tooltipRect = tooltipEl.getBoundingClientRect();
            const viewportWidth = window.innerWidth;
            const viewportHeight = window.innerHeight;

            // En móviles (< 576px), posicionar abajo en el centro
            if (viewportWidth < 576) {
                tooltipEl.style.position = 'fixed';
                tooltipEl.style.bottom = '80px';
                tooltipEl.style.left = '16px';
                tooltipEl.style.right = '16px';
                tooltipEl.style.top = 'auto';
                tooltipEl.style.width = 'auto';
                tooltipEl.style.transform = 'none';
                return;
            }

            tooltipEl.style.position = 'fixed';
            tooltipEl.style.width = '360px';
            tooltipEl.style.maxWidth = '90vw';
            tooltipEl.style.transform = 'none';

            let top = 0;
            let left = 0;

            const pos = preferredPos || (rect.bottom + tooltipRect.height + 20 < viewportHeight ? 'bottom' : 'top');

            if (pos === 'bottom') {
                top = rect.bottom + 14;
                left = rect.left + (rect.width / 2) - 180;
            } else if (pos === 'top') {
                top = rect.top - tooltipRect.height - 14;
                left = rect.left + (rect.width / 2) - 180;
            } else if (pos === 'left') {
                top = rect.top + (rect.height / 2) - (tooltipRect.height / 2);
                left = rect.left - 374;
            } else {
                top = rect.bottom + 14;
                left = rect.left;
            }

            // Asegurar que no se salga de la pantalla
            if (left < 16) left = 16;
            if (left + 360 > viewportWidth - 16) left = viewportWidth - 376;
            if (top < 16) top = 16;
            if (top + tooltipRect.height > viewportHeight - 16) top = viewportHeight - tooltipRect.height - 16;

            tooltipEl.style.top = Math.round(top) + 'px';
            tooltipEl.style.left = Math.round(left) + 'px';
            tooltipEl.style.right = 'auto';
            tooltipEl.style.bottom = 'auto';
        },

        // Posicionar Tooltip al centro si no hay elemento
        positionTooltipCenter: function() {
            const tooltipEl = document.getElementById('copilotTourTooltip');
            if (!tooltipEl) return;

            tooltipEl.style.position = 'fixed';
            tooltipEl.style.top = '50%';
            tooltipEl.style.left = '50%';
            tooltipEl.style.transform = 'translate(-50%, -50%)';
            tooltipEl.style.width = '380px';
            tooltipEl.style.maxWidth = '90vw';
            tooltipEl.style.bottom = 'auto';
            tooltipEl.style.right = 'auto';
        },

        // Siguiente paso
        nextStep: function() {
            if (!this.currentTour) return;
            if (this.currentStepIndex < this.currentTour.steps.length - 1) {
                this.showStep(this.currentStepIndex + 1);
            } else {
                this.endTour(true);
            }
        },

        // Paso anterior
        prevStep: function() {
            if (!this.currentTour) return;
            if (this.currentStepIndex > 0) {
                this.showStep(this.currentStepIndex - 1);
            }
        },

        // Finalizar Tour
        endTour: function(completed) {
            if (this.activeElement) {
                this.activeElement.classList.remove('copilot-spotlight-active');
                this.activeElement = null;
            }

            const tooltipEl = document.getElementById('copilotTourTooltip');
            if (tooltipEl) {
                tooltipEl.classList.add('d-none');
            }

            this.currentTour = null;
            this.currentStepIndex = 0;

            if (completed && typeof Swal !== 'undefined') {
                const Toast = Swal.mixin({
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3500,
                    timerProgressBar: true,
                    background: '#0f172a',
                    color: '#ffffff'
                });
                Toast.fire({
                    icon: 'success',
                    title: '¡Tour completado con éxito!'
                });
            }
        },

        // Verificar si venimos de una redirección para iniciar un tour automáticamente
        checkAutoTour: function() {
            const autoTourKey = sessionStorage.getItem('starlink_auto_tour');
            if (autoTourKey && COPILOT_TOURS[autoTourKey]) {
                sessionStorage.removeItem('starlink_auto_tour');
                const self = this;
                setTimeout(function() {
                    self.startTour(autoTourKey);
                }, 400);
            }
        },

        // Filtro de preguntas frecuentes del manual
        filterFaqTopics: function(query) {
            const faqItems = document.querySelectorAll('.copilot-faq-item');
            if (!faqItems.length) return;

            faqItems.forEach(function(item) {
                const keywords = item.getAttribute('data-keywords') || '';
                const text = item.textContent.toLowerCase();
                const collapseEl = item.querySelector('.accordion-collapse');

                if (!query || keywords.includes(query) || text.includes(query)) {
                    item.style.display = '';
                    if (query && query.length >= 2 && collapseEl && typeof bootstrap !== 'undefined') {
                        const collapseInstance = bootstrap.Collapse.getOrCreateInstance(collapseEl, { toggle: false });
                        collapseInstance.show();
                    }
                } else {
                    item.style.display = 'none';
                    if (collapseEl && typeof bootstrap !== 'undefined') {
                        const collapseInstance = bootstrap.Collapse.getInstance(collapseEl);
                        if (collapseInstance) collapseInstance.hide();
                    }
                }
            });
        }
    };

    // Exponer globalmente
    window.StarlinkCopilot = StarlinkCopilot;

    // Inicializar al cargar el DOM
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            StarlinkCopilot.init();
        });
    } else {
        StarlinkCopilot.init();
    }
})();

