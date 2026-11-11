/**
 * Jeremy • Motor de Copiloto y Guía Interactiva (Microservicio / SDK Frontend)
 * Totalmente desacoplado, modular y reutilizable en cualquier aplicación web.
 */

(function() {
    'use strict';

    const Jeremy = {
        name: 'Jeremy',
        manifest: null,
        currentTour: null,
        currentStepIndex: 0,
        activeElement: null,
        isInitialized: false,

        /**
         * Inicializar a Jeremy con opciones o manifiesto
         * @param {Object} options { manifestUrl, manifestData, currentRoute }
         */
        init: async function(options = {}) {
            if (this.isInitialized) return;

            this.options = Object.assign({
                name: 'Jeremy',
                manifestUrl: 'public/jeremy/jeremy-manifest.json',
                manifestData: null,
                currentRoute: null
            }, options);

            this.name = this.options.name || 'Jeremy';

            try {
                if (this.options.manifestData) {
                    this.manifest = this.options.manifestData;
                } else {
                    const response = await fetch(this.options.manifestUrl);
                    if (!response.ok) throw new Error('Error al cargar manifiesto de Jeremy');
                    this.manifest = await response.json();
                }
            } catch (err) {
                console.warn('[Jeremy] No se pudo cargar el manifiesto externo. Usando fallback local:', err);
                this.manifest = this.getDefaultManifest();
            }

            this.ensureDomElements();
            this.bindEvents();
            this.updateContextInfo();
            this.renderToursList();
            this.renderFaqList();
            this.checkAutoTour();

            this.isInitialized = true;
        },

        // Manifiesto de respaldo en caso de desconexión
        getDefaultManifest: function() {
            return {
                appName: "Starlink Control",
                copilotName: "Jeremy",
                contexts: {},
                tours: {},
                faqs: []
            };
        },

        // Resolver la ruta actual de la aplicación
        getCurrentRoute: function() {
            if (typeof this.options.currentRoute === 'function') {
                return this.options.currentRoute();
            }
            if (typeof this.options.currentRoute === 'string') {
                return this.options.currentRoute;
            }
            const urlParams = new URLSearchParams(window.location.search);
            return (urlParams.get('url') || 'dashboard').replace(/\/$/, '');
        },

        // Inyectar el botón y el drawer si no existen en el HTML
        ensureDomElements: function() {
            // 1. Botón Flotante
            if (!document.getElementById('jeremyFloatingBtn')) {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.id = 'jeremyFloatingBtn';
                btn.className = 'jeremy-floating-btn';
                btn.setAttribute('aria-label', `Abrir copiloto ${this.name}`);
                btn.setAttribute('title', `${this.name} • Copiloto y Guía Interactiva`);
                btn.innerHTML = `
                    <span class="jeremy-radar-dot"></span>
                    <i class="bi bi-robot jeremy-icon"></i>
                    <span class="jeremy-label d-none d-sm-inline">${this.name}</span>
                    <span class="badge bg-info text-dark ms-1 d-none d-md-inline" style="font-size: 0.65rem; font-weight: 700;">COPILOTO</span>
                `;
                document.body.appendChild(btn);
            }

            // 2. Offcanvas / Panel Lateral de Jeremy
            if (!document.getElementById('jeremyDrawer')) {
                const drawer = document.createElement('div');
                drawer.id = 'jeremyDrawer';
                drawer.className = 'offcanvas offcanvas-end jeremy-drawer';
                drawer.tabIndex = -1;
                drawer.setAttribute('aria-labelledby', 'jeremyDrawerLabel');

                const appName = this.manifest.appName || 'Aplicación';
                const manualUrl = this.manifest.manualUrl || '#';

                drawer.innerHTML = `
                    <div class="offcanvas-header border-bottom border-secondary border-opacity-25 py-3 px-3 px-sm-4" style="background: #0d1524;">
                        <div class="d-flex align-items-center gap-2">
                            <div class="jeremy-avatar-badge">
                                <i class="bi bi-robot text-info fs-4"></i>
                            </div>
                            <div>
                                <h5 class="offcanvas-title fs-6 fw-bold mb-0 text-white d-flex align-items-center gap-2" id="jeremyDrawerLabel">
                                    ${this.name}
                                    <span class="badge bg-primary text-white border border-info border-opacity-50" style="font-size: 0.65rem;">Copiloto Oficial</span>
                                </h5>
                                <small class="text-white-50" style="font-size: 0.75rem;">${appName} • Asistente sin IA</small>
                            </div>
                        </div>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Cerrar"></button>
                    </div>

                    <div class="offcanvas-body p-3 p-sm-4 custom-scrollbar d-flex flex-column gap-3">
                        <!-- Buscador -->
                        <div class="jeremy-search-box">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-dark text-info border-secondary"><i class="bi bi-search"></i></span>
                                <input type="text" id="jeremySearchInput" class="form-control bg-dark text-white border-secondary" placeholder="¿Qué necesitas hacer en la app? (ej. 'corte', 'pago', 'recibo')..." autocomplete="off">
                                <button class="btn btn-outline-secondary" type="button" id="jeremySearchClearBtn"><i class="bi bi-x-lg"></i></button>
                            </div>
                        </div>

                        <!-- Tarjeta Contextual -->
                        <div class="card p-3 jeremy-context-card" id="jeremyContextCard">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span class="badge bg-dark text-info border border-info border-opacity-25" style="font-size: 0.70rem;">
                                    <i class="bi bi-geo-alt-fill me-1"></i>PANTALLA ACTUAL
                                </span>
                                <span class="text-white-50 small tech-mono" id="jeremyCurrentRouteBadge">...</span>
                            </div>
                            <h6 class="text-white fw-bold mb-1" id="jeremyContextTitle">Cargando guía...</h6>
                            <p class="text-white-50 small mb-2" id="jeremyContextDesc" style="font-size: 0.80rem; line-height: 1.35;"></p>
                            <div class="d-flex gap-2 flex-wrap" id="jeremyContextActions"></div>
                        </div>

                        <!-- Tours Guiados -->
                        <div class="jeremy-section" id="jeremyToursSection">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <h6 class="text-uppercase text-white-50 small fw-bold mb-0 tech-mono">
                                    <i class="bi bi-play-circle text-info me-1"></i>Tours Guiados en Pantalla
                                </h6>
                                <span class="badge bg-secondary text-white-50" style="font-size: 0.65rem;">Interactivo</span>
                            </div>
                            <p class="text-white-50 small mb-2" style="font-size: 0.78rem;">
                                ${this.name} iluminará los botones en pantalla y te enseñará los procedimientos paso a paso.
                            </p>
                            <div class="list-group list-group-flush gap-2" id="jeremyToursList"></div>
                        </div>

                        <!-- FAQs del Manual -->
                        <div class="jeremy-section" id="jeremyFaqSection">
                            <h6 class="text-uppercase text-white-50 small fw-bold mb-2 tech-mono">
                                <i class="bi bi-question-circle text-info me-1"></i>Preguntas Frecuentes
                            </h6>
                            <div class="accordion accordion-flush" id="jeremyFaqAccordion"></div>
                        </div>

                        <!-- Enlace al Manual Completo -->
                        <div class="mt-auto pt-3 border-top border-secondary border-opacity-25">
                            <a href="${manualUrl}" class="btn btn-outline-primary btn-sm w-100 d-flex align-items-center justify-content-center gap-2">
                                <i class="bi bi-book-half"></i>
                                <span>Abrir Manual Completo de Operaciones</span>
                            </a>
                            <div class="text-center text-white-50 mt-2" style="font-size: 0.70rem;">
                                ${this.name} • Copiloto Microservicio v${this.manifest.version || '1.0'}
                            </div>
                        </div>
                    </div>
                `;
                document.body.appendChild(drawer);
            }

            // 3. Tarjeta Tooltip del Tour
            if (!document.getElementById('jeremyTourTooltip')) {
                const tooltip = document.createElement('div');
                tooltip.id = 'jeremyTourTooltip';
                tooltip.className = 'jeremy-tour-card d-none';
                tooltip.setAttribute('role', 'dialog');
                tooltip.setAttribute('aria-modal', 'true');
                tooltip.innerHTML = `
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="badge bg-primary text-white" id="jeremyTourBadge" style="font-size: 0.68rem;">Paso 1 de 4</span>
                        <button type="button" class="btn-close btn-close-white btn-sm" id="jeremyTourCloseBtn" aria-label="Cerrar tour"></button>
                    </div>
                    <h6 class="text-white fw-bold mb-1" id="jeremyTourTitle">Paso del Tour</h6>
                    <p class="text-white-50 small mb-3" id="jeremyTourText" style="font-size: 0.82rem; line-height: 1.4;"></p>
                    <div class="d-flex align-items-center justify-content-between gap-2">
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="jeremyTourPrevBtn">
                            <i class="bi bi-arrow-left me-1"></i>Anterior
                        </button>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-sm btn-link text-white-50 text-decoration-none" id="jeremyTourExitBtn">
                                Salir
                            </button>
                            <button type="button" class="btn btn-sm btn-primary" id="jeremyTourNextBtn">
                                Siguiente<i class="bi bi-arrow-right ms-1"></i>
                            </button>
                        </div>
                    </div>
                `;
                document.body.appendChild(tooltip);
            }
        },

        // Enlazar eventos de interacción
        bindEvents: function() {
            const self = this;

            // Abrir Drawer al hacer clic en el botón flotante
            const btn = document.getElementById('jeremyFloatingBtn');
            if (btn) {
                btn.addEventListener('click', function() {
                    self.open();
                });
            }

            // Buscador interno de Jeremy
            const searchInput = document.getElementById('jeremySearchInput');
            const clearBtn = document.getElementById('jeremySearchClearBtn');

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

            // Botones del Tooltip
            const tourNextBtn = document.getElementById('jeremyTourNextBtn');
            const tourPrevBtn = document.getElementById('jeremyTourPrevBtn');
            const tourCloseBtn = document.getElementById('jeremyTourCloseBtn');
            const tourExitBtn = document.getElementById('jeremyTourExitBtn');

            if (tourNextBtn) tourNextBtn.addEventListener('click', () => self.nextStep());
            if (tourPrevBtn) tourPrevBtn.addEventListener('click', () => self.prevStep());
            if (tourCloseBtn) tourCloseBtn.addEventListener('click', () => self.endTour());
            if (tourExitBtn) tourExitBtn.addEventListener('click', () => self.endTour());

            // Navegación con teclado durante el Tour
            document.addEventListener('keydown', function(e) {
                if (!self.currentTour) return;
                if (e.key === 'Escape') self.endTour();
                else if (e.key === 'ArrowRight' && e.target.tagName !== 'INPUT') self.nextStep();
                else if (e.key === 'ArrowLeft' && e.target.tagName !== 'INPUT') self.prevStep();
            });

            // Reposicionar en resize
            window.addEventListener('resize', function() {
                if (self.currentTour && self.activeElement) {
                    self.positionTooltip(self.activeElement);
                }
            });
        },

        // Renderizar la guía contextual de la pantalla actual
        updateContextInfo: function() {
            const currentRoute = this.getCurrentRoute();
            const badgeEl = document.getElementById('jeremyCurrentRouteBadge');
            const titleEl = document.getElementById('jeremyContextTitle');
            const descEl = document.getElementById('jeremyContextDesc');
            const actionsEl = document.getElementById('jeremyContextActions');

            if (badgeEl) badgeEl.textContent = currentRoute;
            if (!titleEl || !descEl || !actionsEl) return;

            const context = this.manifest.contexts ? this.manifest.contexts[currentRoute] : null;

            if (context) {
                titleEl.textContent = context.title;
                descEl.innerHTML = context.desc;
                
                let actionsHtml = '';
                if (Array.isArray(context.actions)) {
                    context.actions.forEach(act => {
                        if (act.tour) {
                            actionsHtml += `
                                <button type="button" class="btn ${act.btnClass || 'btn-outline-info'} btn-sm py-1 px-2 small" onclick="Jeremy.startTour('${act.tour}')">
                                    <i class="bi ${act.icon || 'bi-play-circle'} me-1"></i>${act.label}
                                </button>
                            `;
                        } else if (act.url) {
                            actionsHtml += `
                                <a href="${act.url}" target="${act.target || '_self'}" class="btn ${act.btnClass || 'btn-outline-light'} btn-sm py-1 px-2 small">
                                    <i class="bi ${act.icon || 'bi-link'} me-1"></i>${act.label}
                                </a>
                            `;
                        } else if (act.action === 'print') {
                            actionsHtml += `
                                <button type="button" class="btn ${act.btnClass || 'btn-outline-light'} btn-sm py-1 px-2 small" onclick="window.print()">
                                    <i class="bi ${act.icon || 'bi-printer'} me-1"></i>${act.label}
                                </button>
                            `;
                        }
                    });
                }
                actionsEl.innerHTML = actionsHtml;
            } else {
                titleEl.textContent = 'Módulo ' + currentRoute.toUpperCase();
                descEl.innerHTML = `Hola, soy <strong>${this.name}</strong>. Consulta mis tours interactivos o pregúntame cualquier duda sobre el manual.`;
                actionsEl.innerHTML = '';
            }
        },

        // Renderizar listado de tours
        renderToursList: function() {
            const listEl = document.getElementById('jeremyToursList');
            if (!listEl || !this.manifest.tours) return;

            let html = '';
            for (const [key, tour] of Object.entries(this.manifest.tours)) {
                html += `
                    <button type="button" class="list-group-item list-group-item-action bg-dark text-white border border-secondary border-opacity-25 rounded-3 p-2.5 d-flex align-items-center justify-content-between jeremy-tour-btn" onclick="Jeremy.startTour('${key}')">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge ${tour.iconBg || 'bg-info'} bg-opacity-25 p-2 rounded-circle">
                                <i class="bi ${tour.icon || 'bi-play-circle'} fs-6"></i>
                            </span>
                            <div>
                                <div class="fw-semibold small">${tour.title}</div>
                                <div class="text-white-50" style="font-size: 0.72rem;">${tour.desc || ''}</div>
                            </div>
                        </div>
                        <i class="bi bi-chevron-right text-white-50 small"></i>
                    </button>
                `;
            }
            listEl.innerHTML = html;
        },

        // Renderizar preguntas frecuentes del manual
        renderFaqList: function() {
            const accordionEl = document.getElementById('jeremyFaqAccordion');
            if (!accordionEl || !Array.isArray(this.manifest.faqs)) return;

            let html = '';
            this.manifest.faqs.forEach((faq, index) => {
                const faqId = `jeremyFaq_${index}`;
                html += `
                    <div class="accordion-item bg-dark border-secondary border-opacity-25 rounded-3 mb-2 overflow-hidden jeremy-faq-item" data-keywords="${faq.keywords || ''}">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed bg-dark text-white py-2 px-3 small" type="button" data-bs-toggle="collapse" data-bs-target="#${faqId}">
                                ${faq.question}
                            </button>
                        </h2>
                        <div id="${faqId}" class="accordion-collapse collapse" data-bs-parent="#jeremyFaqAccordion">
                            <div class="accordion-body text-white-50 py-2 px-3 small border-top border-secondary border-opacity-25" style="font-size: 0.80rem;">
                                ${faq.answer}
                            </div>
                        </div>
                    </div>
                `;
            });
            accordionEl.innerHTML = html;
        },

        // Abrir Drawer de Jeremy
        open: function() {
            const drawerEl = document.getElementById('jeremyDrawer');
            if (drawerEl && typeof bootstrap !== 'undefined') {
                const inst = bootstrap.Offcanvas.getOrCreateInstance(drawerEl);
                inst.show();
            }
        },

        // Cerrar Drawer de Jeremy
        close: function() {
            const drawerEl = document.getElementById('jeremyDrawer');
            if (drawerEl && typeof bootstrap !== 'undefined') {
                const inst = bootstrap.Offcanvas.getInstance(drawerEl);
                if (inst) inst.hide();
            }
        },

        // Iniciar un tour interactivo
        startTour: function(tourKey) {
            const tour = this.manifest.tours ? this.manifest.tours[tourKey] : null;
            if (!tour) return;

            const currentRoute = this.getCurrentRoute();

            // Si el tour requiere otra pantalla, preguntar y redirigir
            if (tour.requiredUrl && tour.requiredUrl !== currentRoute) {
                const targetUrl = 'index.php?url=' + tour.requiredUrl;
                this.close();

                const proceedMsg = `El tour "${tour.title}" se realiza en la pantalla de ${tour.requiredUrl.toUpperCase()}. ¿Deseas que te lleve allí ahora?`;

                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: `Ir con ${this.name}`,
                        text: proceedMsg,
                        icon: 'info',
                        showCancelButton: true,
                        confirmButtonText: '<i class="bi bi-arrow-right-circle me-1"></i>Ir a la pantalla',
                        cancelButtonText: 'Cancelar',
                        background: '#0f172a',
                        color: '#ffffff',
                        confirmButtonColor: '#0284c7',
                        cancelButtonColor: '#475569'
                    }).then(result => {
                        if (result.isConfirmed) {
                            sessionStorage.setItem('jeremy_auto_tour', tourKey);
                            window.location.href = targetUrl;
                        }
                    });
                } else if (confirm(proceedMsg)) {
                    sessionStorage.setItem('jeremy_auto_tour', tourKey);
                    window.location.href = targetUrl;
                }
                return;
            }

            this.close();
            this.currentTour = tour;
            this.currentStepIndex = 0;
            this.showStep(0);
        },

        // Mostrar un paso del tour
        showStep: function(index) {
            if (!this.currentTour || !this.currentTour.steps[index]) {
                this.endTour();
                return;
            }

            this.currentStepIndex = index;
            const step = this.currentTour.steps[index];
            const totalSteps = this.currentTour.steps.length;

            // Manejador de acción previa especial (ej: abrir modal)
            if (step.actionBefore === 'openCommandPalette') {
                const modalEl = document.getElementById('commandPaletteModal');
                if (modalEl && typeof bootstrap !== 'undefined') {
                    bootstrap.Modal.getOrCreateInstance(modalEl).show();
                }
            } else if (typeof step.actionBefore === 'function') {
                step.actionBefore();
            }

            // Limpiar highlight anterior
            if (this.activeElement) {
                this.activeElement.classList.remove('jeremy-spotlight-active');
            }

            // Buscar elemento en el DOM
            let targetEl = null;
            if (typeof step.target === 'string') {
                const selectors = step.target.split(',');
                for (let i = 0; i < selectors.length; i++) {
                    const found = document.querySelector(selectors[i].trim());
                    if (found && found.offsetParent !== null) {
                        targetEl = found;
                        break;
                    }
                }
                if (!targetEl) targetEl = document.querySelector(step.target);
            }

            const tooltipEl = document.getElementById('jeremyTourTooltip');
            const badgeEl = document.getElementById('jeremyTourBadge');
            const titleEl = document.getElementById('jeremyTourTitle');
            const textEl = document.getElementById('jeremyTourText');
            const prevBtn = document.getElementById('jeremyTourPrevBtn');
            const nextBtn = document.getElementById('jeremyTourNextBtn');

            if (!tooltipEl) return;

            badgeEl.textContent = `Paso ${index + 1} de ${totalSteps}`;
            titleEl.textContent = step.title;
            textEl.innerHTML = step.text;

            prevBtn.style.display = index === 0 ? 'none' : 'inline-block';

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
                targetEl.classList.add('jeremy-spotlight-active');
                targetEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
                this.positionTooltip(targetEl, step.position);
            } else {
                this.positionTooltipCenter();
            }
        },

        // Posicionar el Tooltip flotante anclado al elemento
        positionTooltip: function(targetEl, preferredPos) {
            const tooltipEl = document.getElementById('jeremyTourTooltip');
            if (!tooltipEl || !targetEl) return;

            const rect = targetEl.getBoundingClientRect();
            const tooltipRect = tooltipEl.getBoundingClientRect();
            const viewportWidth = window.innerWidth;
            const viewportHeight = window.innerHeight;

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
            const tooltipEl = document.getElementById('jeremyTourTooltip');
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

        // Avanzar paso
        nextStep: function() {
            if (!this.currentTour) return;
            if (this.currentStepIndex < this.currentTour.steps.length - 1) {
                this.showStep(this.currentStepIndex + 1);
            } else {
                this.endTour(true);
            }
        },

        // Retroceder paso
        prevStep: function() {
            if (!this.currentTour) return;
            if (this.currentStepIndex > 0) {
                this.showStep(this.currentStepIndex - 1);
            }
        },

        // Terminar Tour
        endTour: function(completed) {
            if (this.activeElement) {
                this.activeElement.classList.remove('jeremy-spotlight-active');
                this.activeElement = null;
            }

            const tooltipEl = document.getElementById('jeremyTourTooltip');
            if (tooltipEl) tooltipEl.classList.add('d-none');

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
                    title: `¡Tour completado con ${this.name}!`
                });
            }
        },

        // Auto-reanudar tour tras redirección
        checkAutoTour: function() {
            const autoTourKey = sessionStorage.getItem('jeremy_auto_tour') || sessionStorage.getItem('starlink_auto_tour');
            if (autoTourKey) {
                sessionStorage.removeItem('jeremy_auto_tour');
                sessionStorage.removeItem('starlink_auto_tour');
                setTimeout(() => {
                    this.startTour(autoTourKey);
                }, 400);
            }
        },

        // Filtro de búsqueda en FAQs
        filterFaqTopics: function(query) {
            const faqItems = document.querySelectorAll('.jeremy-faq-item');
            if (!faqItems.length) return;

            faqItems.forEach(item => {
                const keywords = item.getAttribute('data-keywords') || '';
                const text = item.textContent.toLowerCase();
                const collapseEl = item.querySelector('.accordion-collapse');

                if (!query || keywords.includes(query) || text.includes(query)) {
                    item.style.display = '';
                    if (query && query.length >= 2 && collapseEl && typeof bootstrap !== 'undefined') {
                        bootstrap.Collapse.getOrCreateInstance(collapseEl, { toggle: false }).show();
                    }
                } else {
                    item.style.display = 'none';
                    if (collapseEl && typeof bootstrap !== 'undefined') {
                        const inst = bootstrap.Collapse.getInstance(collapseEl);
                        if (inst) inst.hide();
                    }
                }
            });
        }
    };

    // Exponer globalmente
    window.Jeremy = Jeremy;
    window.StarlinkCopilot = Jeremy; // Compatibilidad retroactiva

    // Inicialización automática
    const currentScript = document.currentScript;
    const scriptManifest = currentScript ? currentScript.getAttribute('data-manifest') : null;
    const resolvedManifestUrl = scriptManifest || (window.appConfig && window.appConfig.manifestUrl) || 'jeremy/jeremy-manifest.json';

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {
            Jeremy.init({
                manifestUrl: resolvedManifestUrl
            });
        });
    } else {
        Jeremy.init({
            manifestUrl: resolvedManifestUrl
        });
    }
})();
