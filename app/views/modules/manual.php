<?php
$userRole = $data['userRole'] ?? 'Expectador';
$isAdmin = $data['isAdmin'] ?? false;
$isModerator = $data['isModerator'] ?? false;
?>
<div class="manual-page">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h3 class="text-white mb-1">
                <i class="bi bi-book-half me-2 text-primary"></i>Manual de Operaciones y Usuario
            </h3>
            <p class="text-white-50 mb-0">Guía técnica y operativa para el uso de Starlink Control Manager.</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <button type="button" class="btn btn-outline-light btn-sm" onclick="window.print()">
                <i class="bi bi-printer me-1"></i>Imprimir Guía
            </button>
            <a href="MANUAL_DE_USUARIO.md" class="btn btn-primary btn-sm" download="Manual_Starlink_Control.md">
                <i class="bi bi-download me-1"></i>Descargar Markdown
            </a>
        </div>
    </div>

    <!-- Buscador Rápido de Temas del Manual -->
    <div class="card card-custom p-3 mb-4 shadow-sm">
        <div class="input-group input-group-sm">
            <span class="input-group-text bg-dark text-info border-secondary"><i class="bi bi-search"></i></span>
            <input type="text" id="searchManualInput" class="form-control bg-dark text-white border-secondary" placeholder="Buscar función o procedimiento (ej. 'corte', 'qr', 'pago', 'recibo', 'whatsapp', '2fa')...">
            <button class="btn btn-outline-secondary" type="button" onclick="document.getElementById('searchManualInput').value=''; filterManualTopics();">Limpiar</button>
        </div>
    </div>

    <div class="row g-4">
        <!-- Índice Lateral Rápido (Desktop) -->
        <div class="col-lg-3 d-none d-lg-block">
            <div class="card card-custom p-3 sticky-top" style="top: 80px; z-index: 10;">
                <h6 class="text-uppercase text-white-50 small fw-bold mb-3 tech-mono">Capítulos</h6>
                <div class="list-group list-group-flush manual-nav-index small">
                    <a href="#cap-1" class="list-group-item list-group-item-action bg-transparent text-white border-secondary border-opacity-25 py-2"><i class="bi bi-shield-check me-2 text-info"></i>1. Roles y Permisos</a>
                    <a href="#cap-2" class="list-group-item list-group-item-action bg-transparent text-white border-secondary border-opacity-25 py-2"><i class="bi bi-key me-2 text-info"></i>2. Acceso y 2FA</a>
                    <a href="#cap-3" class="list-group-item list-group-item-action bg-transparent text-white border-secondary border-opacity-25 py-2"><i class="bi bi-broadcast me-2 text-info"></i>3. Antenas y Cortes</a>
                    <a href="#cap-4" class="list-group-item list-group-item-action bg-transparent text-white border-secondary border-opacity-25 py-2"><i class="bi bi-currency-dollar me-2 text-info"></i>4. Pagos y Recibos QR</a>
                    <a href="#cap-5" class="list-group-item list-group-item-action bg-transparent text-white border-secondary border-opacity-25 py-2"><i class="bi bi-people me-2 text-info"></i>5. Directorio Clientes</a>
                    <a href="#cap-6" class="list-group-item list-group-item-action bg-transparent text-white border-secondary border-opacity-25 py-2"><i class="bi bi-speedometer2 me-2 text-info"></i>6. Dashboard y Calendario</a>
                    <a href="#cap-7" class="list-group-item list-group-item-action bg-transparent text-white border-secondary border-opacity-25 py-2"><i class="bi bi-database me-2 text-info"></i>7. Catálogo y Backups</a>
                    <a href="#cap-8" class="list-group-item list-group-item-action bg-transparent text-white border-secondary border-opacity-25 py-2"><i class="bi bi-keyboard me-2 text-info"></i>8. Atajos y Excel</a>
                    <a href="#cap-9" class="list-group-item list-group-item-action bg-transparent text-white border-secondary border-opacity-25 py-2"><i class="bi bi-phone me-2 text-info"></i>9. PWA / App Móvil</a>
                </div>
            </div>
        </div>

        <!-- Contenido Central del Manual -->
        <div class="col-lg-9">
            <div class="accordion manual-accordion" id="manualAccordion">

                <!-- CAPÍTULO 1 -->
                <div class="accordion-item card card-custom mb-3 border-0 overflow-hidden manual-topic" id="cap-1">
                    <h2 class="accordion-header">
                        <button class="accordion-button bg-dark text-white fw-bold py-3 px-4" type="button" data-bs-toggle="collapse" data-bs-target="#collapse1" aria-expanded="true">
                            <i class="bi bi-shield-check text-primary fs-5 me-2"></i> 1. Introducción y Matriz de Roles
                        </button>
                    </h2>
                    <div id="collapse1" class="accordion-collapse collapse show">
                        <div class="accordion-body p-4 text-white-50">
                            <p class="text-white">Starlink Control Manager organiza la gestión de terminales bajo un estricto modelo de permisos (RBAC):</p>
                            <div class="table-responsive">
                                <table class="table table-dark table-sm border-secondary">
                                    <thead>
                                        <tr class="text-white">
                                            <th>Acción / Permiso</th>
                                            <th>Administrador</th>
                                            <th>Moderador</th>
                                            <th>Expectador</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>Dashboard & Finanzas</td>
                                            <td><span class="badge bg-success">Completo</span></td>
                                            <td><span class="badge bg-success">Completo</span></td>
                                            <td><span class="badge bg-secondary">Solo sus antenas</span></td>
                                        </tr>
                                        <tr>
                                            <td>Crear / Editar / Eliminar Antenas</td>
                                            <td><span class="badge bg-success">Sí</span></td>
                                            <td><span class="badge bg-danger">No</span></td>
                                            <td><span class="badge bg-danger">No</span></td>
                                        </tr>
                                        <tr>
                                            <td>Aplicar Corte Técnico / Reactivar</td>
                                            <td><span class="badge bg-success">Sí</span></td>
                                            <td><span class="badge bg-danger">No</span></td>
                                            <td><span class="badge bg-danger">No</span></td>
                                        </tr>
                                        <tr>
                                            <td>Registrar Pagos</td>
                                            <td><span class="badge bg-success">Sí</span></td>
                                            <td><span class="badge bg-success">Sí</span></td>
                                            <td><span class="badge bg-info text-dark">Reportar abono</span></td>
                                        </tr>
                                        <tr>
                                            <td>Validar / Aprobar Comprobantes</td>
                                            <td><span class="badge bg-success">Sí</span></td>
                                            <td><span class="badge bg-success">Sí</span></td>
                                            <td><span class="badge bg-danger">No</span></td>
                                        </tr>
                                        <tr>
                                            <td>Respaldos SQL & Gestión de Usuarios</td>
                                            <td><span class="badge bg-success">Sí</span></td>
                                            <td><span class="badge bg-danger">No</span></td>
                                            <td><span class="badge bg-danger">No</span></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- CAPÍTULO 2 -->
                <div class="accordion-item card card-custom mb-3 border-0 overflow-hidden manual-topic" id="cap-2">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed bg-dark text-white fw-bold py-3 px-4" type="button" data-bs-toggle="collapse" data-bs-target="#collapse2">
                            <i class="bi bi-key text-warning fs-5 me-2"></i> 2. Acceso, Seguridad y 2FA
                        </button>
                    </h2>
                    <div id="collapse2" class="accordion-collapse collapse">
                        <div class="accordion-body p-4 text-white-50">
                            <h6 class="text-white fw-bold"><i class="bi bi-check-circle me-1 text-success"></i> Inicio de Sesión y Recordar Cédula</h6>
                            <p>Ingresa tu cédula numérica y contraseña. Marca la casilla <strong>"Recordar mi cédula"</strong> para que el navegador mantenga tu número precargado y enfoque directamente la contraseña.</p>

                            <h6 class="text-white fw-bold mt-3"><i class="bi bi-qr-code-scan me-1 text-info"></i> Segundo Factor de Autenticación (2FA / TOTP)</h6>
                            <p>Para activar 2FA, dirígete a tu menú de usuario &gt; <strong>Seguridad</strong>. Escanea el código QR con Google Authenticator, Microsoft Authenticator o Authy. En cada acceso se te solicitará el código dinámico de 6 dígitos.</p>

                            <h6 class="text-white fw-bold mt-3"><i class="bi bi-shield-lock me-1 text-danger"></i> Control de Sesión Concurrente</h6>
                            <p>Si intentas iniciar sesión desde otro equipo mientras mantienes una sesión abierta, el sistema te pedirá responder tus preguntas secretas para autorizar el nuevo dispositivo y cerrar la sesión anterior.</p>
                        </div>
                    </div>
                </div>

                <!-- CAPÍTULO 3 -->
                <div class="accordion-item card card-custom mb-3 border-0 overflow-hidden manual-topic" id="cap-3">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed bg-dark text-white fw-bold py-3 px-4" type="button" data-bs-toggle="collapse" data-bs-target="#collapse3">
                            <i class="bi bi-broadcast text-info fs-5 me-2"></i> 3. Módulo de Antenas: Operación, Cortes y Cobro Rápido
                        </button>
                    </h2>
                    <div id="collapse3" class="accordion-collapse collapse">
                        <div class="accordion-body p-4 text-white-50">
                            <div class="row g-3 mb-3">
                                <div class="col-md-4">
                                    <div class="p-2 border border-success border-opacity-50 rounded bg-dark">
                                        <div class="text-success fw-bold"><span class="status-pulse-online"></span> Al día</div>
                                        <small>El ciclo actual está cancelado en su totalidad.</small>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="p-2 border border-warning border-opacity-50 rounded bg-dark">
                                        <div class="text-warning fw-bold"><span class="status-pulse-warning"></span> Pendiente</div>
                                        <small>Dentro del período del mes, en espera de pago.</small>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="p-2 border border-danger border-opacity-50 rounded bg-dark">
                                        <div class="text-danger fw-bold"><span class="status-pulse-danger"></span> Atrasado</div>
                                        <small>Mora activa. Muestra meses adeudados y total en USD.</small>
                                    </div>
                                </div>
                            </div>

                            <h6 class="text-white fw-bold"><i class="bi bi-slash-circle me-1 text-warning"></i> Corte Técnico y Reactivación</h6>
                            <p>Presiona el botón <strong>"Corte"</strong> en la fila de la antena. El sistema marcará la terminal con el distintivo <span class="badge bg-secondary border border-warning text-warning fw-bold">SUSPENDIDA</span> y registrará el evento en auditoría. Al regularizarse el pago, presiona <strong>"Activar"</strong> para reanudar el estatus activo.</p>

                            <h6 class="text-white fw-bold mt-3"><i class="bi bi-cash-stack me-1 text-success"></i> Botón Cobrar</h6>
                            <p>Al hacer clic en <strong>"Cobrar"</strong> en cualquier antena, te redirige inmediatamente al formulario de cobranza con el serial seleccionado y el monto mensual listo para registrar.</p>

                            <h6 class="text-white fw-bold mt-3"><i class="bi bi-whatsapp me-1 text-success"></i> Recordatorio de Pago por WhatsApp</h6>
                            <p>El botón <strong>"Recordar"</strong> genera el enlace directo de WhatsApp con un mensaje prearmado que contiene el nombre del abonado, apodo de la antena, serial, monto de deuda y enlace de contacto.</p>
                        </div>
                    </div>
                </div>

                <!-- CAPÍTULO 4 -->
                <div class="accordion-item card card-custom mb-3 border-0 overflow-hidden manual-topic" id="cap-4">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed bg-dark text-white fw-bold py-3 px-4" type="button" data-bs-toggle="collapse" data-bs-target="#collapse4">
                            <i class="bi bi-currency-dollar text-success fs-5 me-2"></i> 4. Centro de Pagos, Conciliación y Recibos con QR
                        </button>
                    </h2>
                    <div id="collapse4" class="accordion-collapse collapse">
                        <div class="accordion-body p-4 text-white-50">
                            <h6 class="text-white fw-bold"><i class="bi bi-wallet2 me-1 text-info"></i> Registro con Meses Adelantados</h6>
                            <p>Puedes registrar pagos para una o múltiples antenas. Usa los botones de meses rápidos (<code>1m</code>, <code>2m</code>, <code>3m</code>, <code>6m</code>, <code>1 año</code>) para que el importe se multiplique automáticamente.</p>

                            <h6 class="text-white fw-bold mt-3"><i class="bi bi-check2-all me-1 text-success"></i> Conciliación de Transferencias</h6>
                            <p>Revisa la imagen o PDF del comprobante bancario. Haz clic en <strong>"Aprobar"</strong> para asentar los fondos y actualizar la solvencia del cliente, o en <strong>"Rechazar"</strong> si los datos no coinciden.</p>

                            <h6 class="text-white fw-bold mt-3"><i class="bi bi-qr-code me-1 text-info"></i> Recibos Digitales Verificables</h6>
                            <p>Cada pago aprobado cuenta con un botón <strong>"Recibo"</strong> que genera un documento oficial con código QR. Al escanear el QR con la cámara de cualquier teléfono, se valida públicamente la autenticidad del pago en el servidor.</p>
                        </div>
                    </div>
                </div>

                <!-- CAPÍTULO 5 -->
                <div class="accordion-item card card-custom mb-3 border-0 overflow-hidden manual-topic" id="cap-5">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed bg-dark text-white fw-bold py-3 px-4" type="button" data-bs-toggle="collapse" data-bs-target="#collapse5">
                            <i class="bi bi-people text-primary fs-5 me-2"></i> 5. Directorio de Clientes y Estado de Cuenta
                        </button>
                    </h2>
                    <div id="collapse5" class="accordion-collapse collapse">
                        <div class="accordion-body p-4 text-white-50">
                            <p>Gestiona la cartera de clientes con acceso telefónico y digital en 1 clic:</p>
                            <ul>
                                <li><i class="bi bi-whatsapp text-success me-1"></i> <strong>WhatsApp Directo:</strong> Abre chat instantáneo con el cliente.</li>
                                <li><i class="bi bi-telephone-fill text-info me-1"></i> <strong>Llamada Telefónica:</strong> Dispara la marcación directa en tu smartphone.</li>
                                <li><i class="bi bi-receipt text-primary me-1"></i> <strong>Botón "Pagos":</strong> Filtra al instante el Centro de Cobranzas para consultar el estado de cuenta y comprobantes exclusivos de ese cliente.</li>
                                <li><i class="bi bi-broadcast text-info me-1"></i> <strong>Antenas:</strong> Despliega una ventana con los seriales de todos los terminales que tiene contratados.</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- CAPÍTULO 6 -->
                <div class="accordion-item card card-custom mb-3 border-0 overflow-hidden manual-topic" id="cap-6">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed bg-dark text-white fw-bold py-3 px-4" type="button" data-bs-toggle="collapse" data-bs-target="#collapse6">
                            <i class="bi bi-speedometer2 text-danger fs-5 me-2"></i> 6. Dashboard y Calendario de Vencimientos
                        </button>
                    </h2>
                    <div id="collapse6" class="accordion-collapse collapse">
                        <div class="accordion-body p-4 text-white-50">
                            <h6 class="text-white fw-bold">Uso de las Vistas del Calendario:</h6>
                            <p>El calendario agrupa las antenas por su día de corte mensual:</p>
                            <ul>
                                <li><strong>Vista Cuadrícula:</strong> Matriz del mes donde cada día con pagos tiene un badge circular con el total de terminales. Haz clic en cualquier día para ver la lista completa.</li>
                                <li><strong>Vista Agenda (Móvil):</strong> Lista cronológica día a día ideal para consultar desde teléfonos celulares. Incluye botón directo de cobro para cada cliente.</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- CAPÍTULO 7 -->
                <div class="accordion-item card card-custom mb-3 border-0 overflow-hidden manual-topic" id="cap-7">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed bg-dark text-white fw-bold py-3 px-4" type="button" data-bs-toggle="collapse" data-bs-target="#collapse7">
                            <i class="bi bi-database text-warning fs-5 me-2"></i> 7. Catálogo y Base de Datos (Respaldos)
                        </button>
                    </h2>
                    <div id="collapse7" class="accordion-collapse collapse">
                        <div class="accordion-body p-4 text-white-50">
                            <p>Exclusivo para administradores:</p>
                            <ul>
                                <li><strong>Cuentas Starlink:</strong> Agrupa terminales por cuenta maestra de correo satelital.</li>
                                <li><strong>Planes de Servicio:</strong> Ajusta tarifas mensuales en USD y nombres de planes.</li>
                                <li><strong>Países:</strong> Configuración regional y monedas.</li>
                                <li><strong>Base de Datos (`index.php?url=database`):</strong> Genera y descarga respaldos <code>.sql</code> de la base de datos con un solo clic.</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- CAPÍTULO 8 -->
                <div class="accordion-item card card-custom mb-3 border-0 overflow-hidden manual-topic" id="cap-8">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed bg-dark text-white fw-bold py-3 px-4" type="button" data-bs-toggle="collapse" data-bs-target="#collapse8">
                            <i class="bi bi-keyboard text-info fs-5 me-2"></i> 8. Atajos de Productividad y Exportación
                        </button>
                    </h2>
                    <div id="collapse8" class="accordion-collapse collapse">
                        <div class="accordion-body p-4 text-white-50">
                            <ul>
                                <li><strong>Command Palette (<kbd>Ctrl</kbd> + <kbd>K</kbd>):</strong> Abre el buscador universal de módulos y acciones desde cualquier pantalla.</li>
                                <li><strong>Exportación Excel (.csv):</strong> Botón verde presente en Antenas, Pagos y Clientes. Exporta con codificación UTF-8 compatible con Microsoft Excel y LibreOffice.</li>
                                <li><strong>Copiado en 1 Clic:</strong> Toca el ícono <i class="bi bi-clipboard"></i> al lado de cualquier serial, kit o teléfono para copiarlo inmediatamente sin seleccionar texto.</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- CAPÍTULO 9 -->
                <div class="accordion-item card card-custom mb-3 border-0 overflow-hidden manual-topic" id="cap-9">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed bg-dark text-white fw-bold py-3 px-4" type="button" data-bs-toggle="collapse" data-bs-target="#collapse9">
                            <i class="bi bi-phone text-success fs-5 me-2"></i> 9. Instalación como App Móvil (PWA / APK)
                        </button>
                    </h2>
                    <div id="collapse9" class="accordion-collapse collapse">
                        <div class="accordion-body p-4 text-white-50">
                            <p class="text-white">Puedes utilizar Starlink Control Manager como una app nativa en tu teléfono sin pasar por Play Store o App Store:</p>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="p-3 border border-secondary border-opacity-50 rounded bg-dark h-100">
                                        <h6 class="text-white fw-bold"><i class="bi bi-android2 text-success me-1"></i> En Android (Chrome)</h6>
                                        <ol class="small mb-0 ps-3">
                                            <li>Abre el sistema en Google Chrome.</li>
                                            <li>Toca los tres puntos (<code>⋮</code>) arriba a la derecha.</li>
                                            <li>Selecciona <strong>"Instalar aplicación"</strong> o <strong>"Agregar a la pantalla principal"</strong>.</li>
                                        </ol>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="p-3 border border-secondary border-opacity-50 rounded bg-dark h-100">
                                        <h6 class="text-white fw-bold"><i class="bi bi-apple text-info me-1"></i> En iPhone (Safari)</h6>
                                        <ol class="small mb-0 ps-3">
                                            <li>Abre el sistema en Safari.</li>
                                            <li>Toca el botón <strong>Compartir</strong> (cuadrado con flecha hacia arriba).</li>
                                            <li>Selecciona <strong>"Agregar a pantalla de inicio"</strong>.</li>
                                        </ol>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<script>
function filterManualTopics() {
    const q = document.getElementById('searchManualInput').value.trim().toLowerCase();
    const topics = document.querySelectorAll('.manual-topic');
    topics.forEach(t => {
        const text = t.textContent.toLowerCase();
        const collapseEl = t.querySelector('.accordion-collapse');
        if (q === '' || text.includes(q)) {
            t.style.display = '';
            if (q !== '' && collapseEl) {
                bootstrap.Collapse.getOrCreateInstance(collapseEl).show();
            }
        } else {
            t.style.display = 'none';
        }
    });
}

document.getElementById('searchManualInput')?.addEventListener('input', filterManualTopics);
</script>

