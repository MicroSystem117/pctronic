<?php
// Copiloto Starlink (Asistente y Guía Operativa sin IA)
$userRole = $_SESSION['user_role'] ?? 'Expectador';
$isAdmin = $userRole === 'Administrador';
$isModerator = $userRole === 'Moderador';
$currentUrl = isset($_GET['url']) ? rtrim($_GET['url'], '/') : 'dashboard';
if (empty($currentUrl)) {
    $currentUrl = 'dashboard';
}
?>

<!-- Botón Flotante del Copiloto Starlink -->
<button type="button" 
        id="btnOpenCopilot" 
        class="copilot-floating-btn" 
        data-bs-toggle="offcanvas" 
        data-bs-target="#copilotDrawer" 
        aria-controls="copilotDrawer" 
        aria-label="Abrir Copiloto Starlink" 
        title="Copiloto Starlink: Asistente y Guía Interactiva">
    <span class="copilot-btn-glow"></span>
    <span class="copilot-radar-dot"></span>
    <i class="bi bi-compass-fill copilot-icon"></i>
    <span class="copilot-label d-none d-sm-inline">Copiloto</span>
    <span class="badge bg-info text-dark ms-1 d-none d-md-inline" style="font-size: 0.65rem; font-weight: 700;">GUÍA</span>
</button>

<!-- Panel Lateral Deslizable del Copiloto (Offcanvas) -->
<div class="offcanvas offcanvas-end text-white border-start border-secondary" 
     tabindex="-1" 
     id="copilotDrawer" 
     aria-labelledby="copilotDrawerLabel" 
     style="background: #090e17; width: 420px; max-width: 90vw; z-index: 1055;">
    
    <!-- Encabezado del Copiloto -->
    <div class="offcanvas-header border-bottom border-secondary border-opacity-25 py-3 px-3 px-sm-4" style="background: #0d1524;">
        <div class="d-flex align-items-center gap-2">
            <div class="copilot-avatar-badge">
                <i class="bi bi-compass-fill text-info fs-4"></i>
            </div>
            <div>
                <h5 class="offcanvas-title fs-6 fw-bold mb-0 text-white d-flex align-items-center gap-2" id="copilotDrawerLabel">
                    Copiloto Starlink
                    <span class="badge bg-primary text-white border border-info border-opacity-50" style="font-size: 0.65rem;">Manual Oficial</span>
                </h5>
                <small class="text-white-50" style="font-size: 0.75rem;">Guía de operaciones y tours en vivo</small>
            </div>
        </div>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Cerrar"></button>
    </div>

    <!-- Cuerpo del Copiloto -->
    <div class="offcanvas-body p-3 p-sm-4 custom-scrollbar d-flex flex-column gap-3">

        <!-- 1. Buscador Rápido de Procedimientos -->
        <div class="copilot-search-box">
            <div class="input-group input-group-sm">
                <span class="input-group-text bg-dark text-info border-secondary"><i class="bi bi-search"></i></span>
                <input type="text" 
                       id="copilotSearchInput" 
                       class="form-control bg-dark text-white border-secondary" 
                       placeholder="¿Qué necesitas hacer? (ej. 'corte', 'pago', 'recibo', 'qr', 'mora')..."
                       autocomplete="off">
                <button class="btn btn-outline-secondary" type="button" id="copilotSearchClearBtn">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
        </div>

        <!-- 2. Tarjeta Contextual: "¿Dónde estás?" -->
        <div class="card card-custom border-primary border-opacity-50 p-3 copilot-context-card" id="copilotContextCard">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="badge bg-dark text-info border border-info border-opacity-25" style="font-size: 0.70rem;">
                    <i class="bi bi-geo-alt-fill me-1"></i>PANTALLA ACTUAL
                </span>
                <span class="text-white-50 small tech-mono" id="copilotCurrentModuleBadge"><?php echo htmlspecialchars($currentUrl); ?></span>
            </div>
            <h6 class="text-white fw-bold mb-1" id="copilotContextTitle">Cargando guía contextual...</h6>
            <p class="text-white-50 small mb-2" id="copilotContextDesc" style="font-size: 0.80rem; line-height: 1.35;"></p>
            <div class="d-flex gap-2 flex-wrap" id="copilotContextActions">
                <!-- Inyectado dinámicamente con botones de acción rápida -->
            </div>
        </div>

        <!-- 3. Sección: Tours Guiados Paso a Paso (Interactivo en Vivo) -->
        <div class="copilot-section" id="copilotToursSection">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <h6 class="text-uppercase text-white-50 small fw-bold mb-0 tech-mono">
                    <i class="bi bi-play-circle text-info me-1"></i>Tours Guiados en Pantalla
                </h6>
                <span class="badge bg-secondary text-white-50" style="font-size: 0.65rem;">Interactivo</span>
            </div>
            <p class="text-white-50 small mb-2" style="font-size: 0.78rem;">
                El copiloto iluminará los botones y campos en pantalla explicándote el paso a paso según el manual.
            </p>

            <div class="list-group list-group-flush gap-2">
                <!-- Tour 1: Cobranzas y Pagos -->
                <button type="button" class="list-group-item list-group-item-action bg-dark text-white border border-secondary border-opacity-25 rounded-3 p-2.5 d-flex align-items-center justify-content-between copilot-tour-btn" data-tour="payment">
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-success bg-opacity-25 text-success p-2 rounded-circle">
                            <i class="bi bi-cash-stack fs-6"></i>
                        </span>
                        <div>
                            <div class="fw-semibold small">Registrar Cobranza o Pago Rápido</div>
                            <div class="text-white-50" style="font-size: 0.72rem;">Selección de antena, monto, meses y capture</div>
                        </div>
                    </div>
                    <i class="bi bi-chevron-right text-white-50 small"></i>
                </button>

                <!-- Tour 2: Corte Técnico -->
                <button type="button" class="list-group-item list-group-item-action bg-dark text-white border border-secondary border-opacity-25 rounded-3 p-2.5 d-flex align-items-center justify-content-between copilot-tour-btn" data-tour="suspension">
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-warning bg-opacity-25 text-warning p-2 rounded-circle">
                            <i class="bi bi-slash-circle fs-6"></i>
                        </span>
                        <div>
                            <div class="fw-semibold small">Corte Técnico y Reactivación</div>
                            <div class="text-white-50" style="font-size: 0.72rem;">Suspender servicio y habilitarlo tras pago</div>
                        </div>
                    </div>
                    <i class="bi bi-chevron-right text-white-50 small"></i>
                </button>

                <!-- Tour 3: Estado de Cuenta -->
                <button type="button" class="list-group-item list-group-item-action bg-dark text-white border border-secondary border-opacity-25 rounded-3 p-2.5 d-flex align-items-center justify-content-between copilot-tour-btn" data-tour="client_statement">
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-primary bg-opacity-25 text-info p-2 rounded-circle">
                            <i class="bi bi-person-lines-fill fs-6"></i>
                        </span>
                        <div>
                            <div class="fw-semibold small">Ficha y Estado de Cuenta de Cliente</div>
                            <div class="text-white-50" style="font-size: 0.72rem;">Ver antenas asociadas y generar PDF</div>
                        </div>
                    </div>
                    <i class="bi bi-chevron-right text-white-50 small"></i>
                </button>

                <!-- Tour 4: Atajos Ctrl+K -->
                <button type="button" class="list-group-item list-group-item-action bg-dark text-white border border-secondary border-opacity-25 rounded-3 p-2.5 d-flex align-items-center justify-content-between copilot-tour-btn" data-tour="command_palette">
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-info bg-opacity-25 text-info p-2 rounded-circle">
                            <i class="bi bi-keyboard fs-6"></i>
                        </span>
                        <div>
                            <div class="fw-semibold small">Atajos y Paleta Global (Ctrl + K)</div>
                            <div class="text-white-50" style="font-size: 0.72rem;">Navegación ultrarrápida sin usar el ratón</div>
                        </div>
                    </div>
                    <i class="bi bi-chevron-right text-white-50 small"></i>
                </button>

                <!-- Tour 5: Calendario y Alertas -->
                <button type="button" class="list-group-item list-group-item-action bg-dark text-white border border-secondary border-opacity-25 rounded-3 p-2.5 d-flex align-items-center justify-content-between copilot-tour-btn" data-tour="calendar">
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-danger bg-opacity-25 text-danger p-2 rounded-circle">
                            <i class="bi bi-calendar3 fs-6"></i>
                        </span>
                        <div>
                            <div class="fw-semibold small">Calendario y Alertas de Vencimiento</div>
                            <div class="text-white-50" style="font-size: 0.72rem;">Días de corte, moras y vista agenda</div>
                        </div>
                    </div>
                    <i class="bi bi-chevron-right text-white-50 small"></i>
                </button>
            </div>
        </div>

        <!-- 4. Sección: Base de Conocimiento Rápida (FAQs del Manual) -->
        <div class="copilot-section" id="copilotFaqSection">
            <h6 class="text-uppercase text-white-50 small fw-bold mb-2 tech-mono">
                <i class="bi bi-question-circle text-info me-1"></i>Preguntas Frecuentes del Manual
            </h6>
            
            <div class="accordion accordion-flush copilot-accordion" id="copilotFaqAccordion">
                
                <!-- FAQ 1: Estados -->
                <div class="accordion-item bg-dark border-secondary border-opacity-25 rounded-3 mb-2 overflow-hidden copilot-faq-item" data-keywords="estados mora corte suspendido solvente por vencer fecha vencimiento dias">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed bg-dark text-white py-2 px-3 small" type="button" data-bs-toggle="collapse" data-bs-target="#copilotFaq1">
                            ¿Qué significan los estados de solvencia y mora?
                        </button>
                    </h2>
                    <div id="copilotFaq1" class="accordion-collapse collapse" data-bs-parent="#copilotFaqAccordion">
                        <div class="accordion-body text-white-50 py-2 px-3 small border-top border-secondary border-opacity-25" style="font-size: 0.80rem;">
                            <ul class="ps-3 mb-1">
                                <li><strong class="text-success">Solvente:</strong> Fecha de vencimiento futura. No tiene deuda.</li>
                                <li><strong class="text-warning">Por Vencer:</strong> Faltan entre 1 y 5 días para la fecha de corte.</li>
                                <li><strong class="text-danger">Vencido:</strong> Pasó la fecha de corte y tiene saldo pendiente.</li>
                                <li><strong class="text-secondary">Suspendido:</strong> Se aplicó un corte técnico operativo.</li>
                            </ul>
                            <a href="index.php?url=antenas" class="btn btn-outline-info btn-sm py-0 px-2 mt-1">Ir a Antenas</a>
                        </div>
                    </div>
                </div>

                <!-- FAQ 2: Exoneración -->
                <div class="accordion-item bg-dark border-secondary border-opacity-25 rounded-3 mb-2 overflow-hidden copilot-faq-item" data-keywords="exonerar exoneracion perdonar deuda saldo restablecer cuenta">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed bg-dark text-white py-2 px-3 small" type="button" data-bs-toggle="collapse" data-bs-target="#copilotFaq2">
                            ¿Cómo exonerar una deuda previa de una antena?
                        </button>
                    </h2>
                    <div id="copilotFaq2" class="accordion-collapse collapse" data-bs-parent="#copilotFaqAccordion">
                        <div class="accordion-body text-white-50 py-2 px-3 small border-top border-secondary border-opacity-25" style="font-size: 0.80rem;">
                            Solo los administradores pueden hacerlo:
                            <ol class="ps-3 mb-1">
                                <li>Ve al módulo de <strong>Antenas Starlink</strong>.</li>
                                <li>Haz clic en el botón <strong>"Exonerar"</strong> de la fila correspondiente.</li>
                                <li>Ingresa el motivo (ej. arreglo comercial) y confirma.</li>
                            </ol>
                            La deuda previa quedará en $0.00 y se recalculará desde el mes actual.
                        </div>
                    </div>
                </div>

                <!-- FAQ 3: QR Recibo -->
                <div class="accordion-item bg-dark border-secondary border-opacity-25 rounded-3 mb-2 overflow-hidden copilot-faq-item" data-keywords="recibo comprobante factura qr codigo verificacion pdf imprimir cliente">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed bg-dark text-white py-2 px-3 small" type="button" data-bs-toggle="collapse" data-bs-target="#copilotFaq3">
                            ¿Cómo funciona el recibo con código QR?
                        </button>
                    </h2>
                    <div id="copilotFaq3" class="accordion-collapse collapse" data-bs-parent="#copilotFaqAccordion">
                        <div class="accordion-body text-white-50 py-2 px-3 small border-top border-secondary border-opacity-25" style="font-size: 0.80rem;">
                            Cada pago aprobado genera un comprobante oficial en tamaño carta:
                            <ul class="ps-3 mb-1">
                                <li>En el módulo <strong>Cobranzas y Pagos</strong>, presiona el botón del documento <i class="bi bi-file-earmark-pdf"></i>.</li>
                                <li>El recibo incluye un <strong>código QR dinámico</strong> que al ser escaneado por el cliente o auditor valida la autenticidad del pago en el sistema.</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- FAQ 4: WhatsApp -->
                <div class="accordion-item bg-dark border-secondary border-opacity-25 rounded-3 mb-2 overflow-hidden copilot-faq-item" data-keywords="whatsapp mensaje recordatorio cobrar notificacion cobro telefono celular">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed bg-dark text-white py-2 px-3 small" type="button" data-bs-toggle="collapse" data-bs-target="#copilotFaq4">
                            ¿Cómo enviar recordatorios por WhatsApp?
                        </button>
                    </h2>
                    <div id="copilotFaq4" class="accordion-collapse collapse" data-bs-parent="#copilotFaqAccordion">
                        <div class="accordion-body text-white-50 py-2 px-3 small border-top border-secondary border-opacity-25" style="font-size: 0.80rem;">
                            En la tabla de <strong>Antenas</strong> o en la ficha de <strong>Clientes</strong>:
                            <ul class="ps-3 mb-1">
                                <li>Ubica la antena por vencer o vencida.</li>
                                <li>Haz clic en el botón verde con el ícono de <strong class="text-success"><i class="bi bi-whatsapp"></i> Recordar</strong>.</li>
                                <li>Se abrirá WhatsApp con un mensaje preconfigurado indicando el monto exacto adeudado y el serial de la antena.</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- FAQ 5: 2FA -->
                <div class="accordion-item bg-dark border-secondary border-opacity-25 rounded-3 mb-2 overflow-hidden copilot-faq-item" data-keywords="2fa totp autenticacion doble factor seguridad google authenticator clave token">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed bg-dark text-white py-2 px-3 small" type="button" data-bs-toggle="collapse" data-bs-target="#copilotFaq5">
                            ¿Cómo activar el doble factor de autenticación (2FA)?
                        </button>
                    </h2>
                    <div id="copilotFaq5" class="accordion-collapse collapse" data-bs-parent="#copilotFaqAccordion">
                        <div class="accordion-body text-white-50 py-2 px-3 small border-top border-secondary border-opacity-25" style="font-size: 0.80rem;">
                            <ol class="ps-3 mb-1">
                                <li>Haz clic en tu nombre en la barra superior o presiona <kbd>Ctrl + K</kbd> y escribe <em>Seguridad</em>.</li>
                                <li>En el panel de <strong>Seguridad</strong>, activa <strong>2FA (TOTP)</strong>.</li>
                                <li>Escanea el código QR con <strong>Google Authenticator</strong> o <strong>Authy</strong> e introduce el código de 6 dígitos para confirmar.</li>
                            </ol>
                        </div>
                    </div>
                </div>

                <!-- FAQ 6: PWA / APK -->
                <div class="accordion-item bg-dark border-secondary border-opacity-25 rounded-3 mb-2 overflow-hidden copilot-faq-item" data-keywords="instalar pwa apk celular movil android iphone app pantalla de inicio">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed bg-dark text-white py-2 px-3 small" type="button" data-bs-toggle="collapse" data-bs-target="#copilotFaq6">
                            ¿Cómo instalar la app en el teléfono (PWA)?
                        </button>
                    </h2>
                    <div id="copilotFaq6" class="accordion-collapse collapse" data-bs-parent="#copilotFaqAccordion">
                        <div class="accordion-body text-white-50 py-2 px-3 small border-top border-secondary border-opacity-25" style="font-size: 0.80rem;">
                            <ul class="ps-3 mb-1">
                                <li><strong>En Android (Chrome):</strong> Abre el menú de 3 puntos (⋮) y presiona <em>"Instalar aplicación"</em> o <em>"Agregar a la pantalla principal"</em>.</li>
                                <li><strong>En iPhone / iOS (Safari):</strong> Toca el botón Compartir <i class="bi bi-box-arrow-up"></i> y selecciona <em>"Agregar al inicio"</em>.</li>
                            </ul>
                            Funcionará como una app nativa a pantalla completa.
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- 5. Enlace al Manual Completo -->
        <div class="mt-auto pt-3 border-top border-secondary border-opacity-25">
            <a href="index.php?url=manual" class="btn btn-outline-primary btn-sm w-100 d-flex align-items-center justify-content-center gap-2">
                <i class="bi bi-book-half"></i>
                <span>Abrir Manual Completo (10 Capítulos)</span>
            </a>
            <div class="text-center text-white-50 mt-2" style="font-size: 0.70rem;">
                Starlink Control Manager • Asistente sin IA
            </div>
        </div>

    </div>
</div>

<!-- Tarjeta Tooltip Flotante para el Spotlight del Tour Guiado -->
<div id="copilotTourTooltip" class="copilot-tour-card d-none" role="dialog" aria-modal="true" aria-labelledby="copilotTourTitle">
    <div class="copilot-tour-header d-flex align-items-center justify-content-between mb-2">
        <span class="badge bg-primary text-white" id="copilotTourBadge" style="font-size: 0.68rem;">Paso 1 de 4</span>
        <button type="button" class="btn-close btn-close-white btn-sm" id="copilotTourCloseBtn" aria-label="Cerrar tour"></button>
    </div>
    <h6 class="text-white fw-bold mb-1" id="copilotTourTitle">Paso del Tour</h6>
    <p class="text-white-50 small mb-3" id="copilotTourText" style="font-size: 0.82rem; line-height: 1.4;"></p>
    <div class="d-flex align-items-center justify-content-between gap-2">
        <button type="button" class="btn btn-sm btn-outline-secondary" id="copilotTourPrevBtn">
            <i class="bi bi-arrow-left me-1"></i>Anterior
        </button>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-sm btn-link text-white-50 text-decoration-none" id="copilotTourExitBtn">
                Salir
            </button>
            <button type="button" class="btn btn-sm btn-primary" id="copilotTourNextBtn">
                Siguiente<i class="bi bi-arrow-right ms-1"></i>
            </button>
        </div>
    </div>
</div>
