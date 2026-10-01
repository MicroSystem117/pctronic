<?php
$userRole = isset($_SESSION['user_role']) ? $_SESSION['user_role'] : '';
$isAdmin = $userRole === 'Administrador';
$isExpectador = $userRole === 'Expectador';
?>
<div class="antenas-page">
<div class="antenas-heading d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-broadcast"></i> Antenas Starlink Registradas</h2>
    <?php if ($isAdmin): ?>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalAntena">
            <i class="bi bi-plus-circle"></i> Registrar Antena
        </button>
    <?php endif; ?>
</div>

<div class="antenas-filters mb-3 d-flex justify-content-end gap-2 flex-wrap">
    <div class="input-group input-group-sm w-auto">
        <label class="input-group-text bg-secondary text-white border-secondary" for="search_antenas_type">Buscar por</label>
        <select id="search_antenas_type" class="form-select form-select-sm">
            <option value="all">Todos</option>
            <option value="cliente">Cliente</option>
            <option value="cuenta">Cuenta</option>
            <option value="serial">Serial</option>
            <option value="kit">Kit</option>
            <option value="nickname">Nickname</option>
        </select>
    </div>
    <input id="search_antenas" class="form-control form-control-sm antenas-search-input" placeholder="Buscar...">
</div>

<div class="card card-custom antenas-card p-3">
    <div class="table-responsive">
        <table class="table table-dark table-hover mb-0 datatable pdf-exportable table-mobile-cards" data-pdf-title="Antenas Starlink">
            <thead class="table-light">
                <tr>
                    <th>Asignada a</th>
                    <th>Nickname</th>
                    <th>Kit No.</th>
                    <th>Serial No.</th>
                    <th>Plan</th>
                    <th>País Región</th>
                    <th>Cuenta Starlink</th>
                    <th>Día Pago</th>
                    <th>Estado Pago</th>
                    <th>Fecha Inst.</th>
                    <?php if ($isAdmin): ?>
                        <th>Acciones</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php if(!empty($data['antenas'])): ?>
                    <?php foreach($data['antenas'] as $antena): ?>
                        <tr
                            data-serial="<?php echo htmlspecialchars($antena['serial'], ENT_QUOTES); ?>"
                            data-nickname="<?php echo htmlspecialchars($antena['nickname'] ?? '', ENT_QUOTES); ?>"
                            data-kit="<?php echo htmlspecialchars($antena['kit'], ENT_QUOTES); ?>"
                            data-cliente="<?php echo htmlspecialchars($antena['cliente'], ENT_QUOTES); ?>"
                            data-cuenta="<?php echo htmlspecialchars($antena['cuenta_starlink'] ?? '', ENT_QUOTES); ?>"
                            data-plan="<?php echo htmlspecialchars($antena['nombre_plan'], ENT_QUOTES); ?>"
                            data-country="<?php echo htmlspecialchars($antena['pais'], ENT_QUOTES); ?>"
                            data-date="<?php echo htmlspecialchars($antena['date'], ENT_QUOTES); ?>"
                            data-pay="<?php echo htmlspecialchars($antena['pay'] ?? '', ENT_QUOTES); ?>"
                            data-overdue-months="<?php echo intval($antena['overdue_months'] ?? 0); ?>"
                            data-overdue-amount="<?php echo number_format((float)($antena['overdue_amount'] ?? 0), 2, '.', ''); ?>"
                            data-is-exempt="<?php echo !empty($antena['is_exempt']) ? '1' : '0'; ?>"
                            data-exempt-until="<?php echo htmlspecialchars($antena['debt_exempt_until'] ?? '', ENT_QUOTES); ?>"
                            data-exempt-note="<?php echo htmlspecialchars($antena['debt_exempt_note'] ?? '', ENT_QUOTES); ?>"
                        >
                            <td data-label="Asignada a"><span class="td-value text-white fw-bold"><?php echo htmlspecialchars($antena['cliente']); ?></span></td>
                            <td data-label="Nickname"><?php echo !empty($antena['nickname']) ? '<span class="td-value">' . htmlspecialchars($antena['nickname']) . '</span>' : '<span class="text-white-50">-</span>'; ?></td>
                            <td data-label="Kit"><code><?php echo htmlspecialchars($antena['kit']); ?></code></td>
                            <td data-label="Serial"><code><?php echo htmlspecialchars($antena['serial']); ?></code></td>
                            <td data-label="Plan"><span class="badge bg-info text-dark"><?php echo htmlspecialchars($antena['nombre_plan']); ?></span></td>
                            <td data-label="País"><span class="td-value"><?php echo htmlspecialchars($antena['pais']); ?></span></td>
                            <td data-label="Cuenta Starlink">
                                <?php echo !empty($antena['cuenta_starlink']) ? '<span class="td-value">' . htmlspecialchars($antena['cuenta_starlink']) . '</span>' : '<span class="text-white-50">Sin Cuenta Vinc.</span>'; ?>
                            </td>
                            <td data-label="Día de pago"><?php echo isset($antena['pay']) && $antena['pay'] !== null && $antena['pay'] !== '' ? '<span class="td-value">Día ' . htmlspecialchars(intval($antena['pay'])) . '</span>' : '<span class="text-white-50">-</span>'; ?></td>
                            <td data-label="Estado de pago">
                                <?php
                                    $overdueMonths = intval($antena['overdue_months'] ?? 0);
                                    $overdueAmount = floatval($antena['overdue_amount'] ?? 0.0);
                                    $isPaid = ($antena['status_label'] ?? '') === 'Pagado';
                                    $isAtrasado = ($antena['status_label'] ?? '') === 'Atrasado';

                                    $waReminderUrl = null;
                                    if (!$isPaid && !empty($antena['client_phone'])) {
                                        $waReminderUrl = build_whatsapp_reminder_url(
                                            $antena['cliente'] ?? '',
                                            $antena['client_phone'] ?? '',
                                            $antena['serial'] ?? '',
                                            $antena['nickname'] ?? '',
                                            $antena['nombre_plan'] ?? '',
                                            $antena['plan_price'] ?? '',
                                            $antena['due_day'] ?? $antena['pay'] ?? '',
                                            $antena['status_label'] ?? 'Pendiente',
                                            $overdueMonths,
                                            $overdueAmount
                                        );
                                    }
                                ?>
                                <div>
                                    <?php if ($overdueMonths > 0): ?>
                                        <span class="badge bg-danger text-white">
                                            <i class="bi bi-exclamation-triangle-fill me-1"></i><?php echo $overdueMonths . ($overdueMonths === 1 ? ' mes atrasado' : ' meses atrasados'); ?>
                                        </span>
                                        <div class="small text-danger fw-semibold mt-1">
                                            Deuda: $<?php echo number_format($overdueAmount, 2); ?>
                                        </div>
                                    <?php elseif ($isPaid): ?>
                                        <span class="badge bg-success text-dark">
                                            <i class="bi bi-check-circle-fill me-1"></i>Al día
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-warning text-dark">
                                            <i class="bi bi-clock-history me-1"></i>Pendiente
                                        </span>
                                        <div class="small text-white-50 mt-1">
                                            Vence día <?php echo htmlspecialchars($antena['due_day'] ?? $antena['pay'] ?? ''); ?>
                                        </div>
                                    <?php endif; ?>

                                    <?php if (!empty($antena['is_exempt']) && !empty($antena['debt_exempt_until'])): ?>
                                        <div class="mt-1">
                                            <span class="badge bg-secondary-subtle text-info border border-info border-opacity-25" style="font-size: 0.72rem;" title="<?php echo htmlspecialchars('Deuda previa exonerada hasta el ' . date('d/m/Y', strtotime($antena['debt_exempt_until'])) . (!empty($antena['debt_exempt_note']) ? ' — ' . $antena['debt_exempt_note'] : '')); ?>">
                                                <i class="bi bi-shield-check me-1"></i>Exonerada (<?php echo date('d/m/Y', strtotime($antena['debt_exempt_until'])); ?>)
                                            </span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td data-label="Fecha de instalación"><span class="td-value"><?php echo date('d/m/Y', strtotime($antena['date'])); ?></span></td>
                            <?php if ($isAdmin): ?>
                                <td data-label="Acciones" class="text-nowrap">
                                    <div class="d-inline-flex gap-1 flex-wrap">
                                        <?php if ($waReminderUrl): ?>
                                            <a href="<?php echo htmlspecialchars($waReminderUrl, ENT_QUOTES); ?>" target="_blank"
                                               class="btn btn-sm btn-whatsapp text-white"
                                               title="Enviar recordatorio de pago por WhatsApp">
                                                <i class="bi bi-whatsapp"></i><span class="action-btn-text ms-1">Recordar</span>
                                            </a>
                                        <?php endif; ?>
                                        <button type="button" 
                                            class="btn btn-sm <?php echo !empty($antena['is_exempt']) ? 'btn-outline-info' : 'btn-outline-warning'; ?> btn-exonerate-debt"
                                            data-id="<?php echo $antena['id_starlink']; ?>"
                                            data-serial="<?php echo htmlspecialchars($antena['serial'], ENT_QUOTES); ?>"
                                            data-nickname="<?php echo htmlspecialchars($antena['nickname'] ?? '', ENT_QUOTES); ?>"
                                            data-cliente="<?php echo htmlspecialchars($antena['cliente'] ?? '', ENT_QUOTES); ?>"
                                            data-plan="<?php echo htmlspecialchars($antena['nombre_plan'] ?? '', ENT_QUOTES); ?>"
                                            data-price="<?php echo htmlspecialchars($antena['plan_price'] ?? '0', ENT_QUOTES); ?>"
                                            data-date="<?php echo htmlspecialchars($antena['date'] ?? '', ENT_QUOTES); ?>"
                                            data-due-day="<?php echo htmlspecialchars($antena['due_day'] ?? $antena['pay'] ?? '', ENT_QUOTES); ?>"
                                            data-overdue-months="<?php echo intval($antena['overdue_months'] ?? 0); ?>"
                                            data-overdue-amount="<?php echo number_format((float)($antena['overdue_amount'] ?? 0), 2, '.', ''); ?>"
                                            data-is-exempt="<?php echo !empty($antena['is_exempt']) ? '1' : '0'; ?>"
                                            data-exempt-until="<?php echo htmlspecialchars($antena['debt_exempt_until'] ?? '', ENT_QUOTES); ?>"
                                            data-exempt-note="<?php echo htmlspecialchars($antena['debt_exempt_note'] ?? '', ENT_QUOTES); ?>"
                                            data-bs-toggle="modal" 
                                            data-bs-target="#modalExonerateDebt"
                                            title="<?php echo !empty($antena['is_exempt']) ? 'Gestionar exoneración de deuda' : 'Exonerar deuda pasada'; ?>">
                                            <i class="bi <?php echo !empty($antena['is_exempt']) ? 'bi-shield-fill-check' : 'bi-shield-check'; ?>"></i>
                                            <span class="action-btn-text ms-1"><?php echo !empty($antena['is_exempt']) ? 'Exonerada' : 'Exonerar'; ?></span>
                                        </button>
                                        <button class="btn btn-sm btn-info btn-edit-antena"
                                            data-id="<?php echo $antena['id_starlink']; ?>"
                                            data-serial="<?php echo htmlspecialchars($antena['serial'], ENT_QUOTES); ?>"
                                            data-nickname="<?php echo htmlspecialchars($antena['nickname'] ?? '', ENT_QUOTES); ?>"
                                            data-kit="<?php echo htmlspecialchars($antena['kit'], ENT_QUOTES); ?>"
                                            data-client-id="<?php echo $antena['client_id']; ?>"
                                            data-account-id="<?php echo htmlspecialchars($antena['account_id'] ?? '', ENT_QUOTES); ?>"
                                            data-plan-id="<?php echo $antena['plan_id']; ?>"
                                            data-country-id="<?php echo $antena['country_id']; ?>"
                                            data-date="<?php echo $antena['date']; ?>"
                                            data-pay="<?php echo htmlspecialchars($antena['pay'] ?? '', ENT_QUOTES); ?>"
                                            data-bs-toggle="modal" data-bs-target="#modalAntena"
                                            title="Editar antena">
                                            <i class="bi bi-pencil"></i><span class="action-btn-text ms-1">Editar</span>
                                        </button>
                                        <a href="index.php?url=antenas&action=delete&id=<?php echo $antena['id_starlink']; ?>" 
                                            class="btn btn-sm btn-danger" 
                                            data-confirm-text="¿Deseas eliminar la antena con serial <?php echo htmlspecialchars($antena['serial'], ENT_QUOTES); ?>?"
                                            title="Eliminar antena">
                                            <i class="bi bi-trash"></i><span class="action-btn-text ms-1">Eliminar</span>
                                        </a>
                                    </div>
                                </td>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="<?php echo $isAdmin ? 11 : 10; ?>" class="text-center py-4 text-muted">
                            <i class="bi bi-info-circle d-block mb-2 fs-3"></i> No hay antenas mapeadas en el sistema.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php if ($isAdmin): ?>
<div class="modal fade" id="modalAntena" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg antenas-form-dialog">
        <div class="modal-content card-custom text-white">
            <div class="modal-header border-secondary">
                <h5 class="modal-title"><i class="bi bi-plus-circle-fill"></i> Registrar Nueva Antena</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="index.php?url=antenas" method="POST">
                <input type="hidden" id="id_starlink" name="id_starlink" value="0">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Número de Serial <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="antena_serial" name="serial" placeholder="Ej: 4PBA00426393" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Número de Kit <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="antena_kit" name="kit" placeholder="Ej: KIT400445557" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nickname</label>
                            <input type="text" class="form-control" id="antena_nickname" name="nickname" placeholder="Ej: Casa, Oficina, Cliente VIP">
                            <div class="form-text text-white-50">Etiqueta opcional para identificar la antena.</div>
                        </div>
                        <div class="col-md-6 mb-3"></div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Cliente Responsable <span class="text-danger">*</span></label>
                            <select class="form-select" id="antena_client" name="client" required>
                                <option value="">-- Seleccionar Cliente --</option>
                                <?php foreach($data['clientes'] as $c): ?>
                                    <option value="<?php echo $c['id_client']; ?>"><?php echo $c['nombre']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Cuenta Starlink Administrativa <span class="text-danger">*</span></label>
                            <select class="form-select" id="antena_account" name="account_id" required>
                                <option value="">-- Seleccionar cuenta --</option>
                                <?php foreach($data['cuentas'] as $acc): ?>
                                    <option value="<?php echo $acc['id_accounts']; ?>"
                                            data-country-id="<?php echo htmlspecialchars($acc['country_id'] ?? '', ENT_QUOTES); ?>"
                                            data-country-name="<?php echo htmlspecialchars($acc['country_name'] ?? 'Sin país configurado', ENT_QUOTES); ?>">
                                        <?php echo htmlspecialchars($acc['info_cuenta']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Plan del Servicio <span class="text-danger">*</span></label>
                            <select class="form-select" id="antena_plan" name="plan" required>
                                <option value="">-- Seleccionar --</option>
                                <?php foreach($data['planes'] as $p): ?>
                                    <option value="<?php echo $p['id_plan']; ?>"><?php echo $p['plan']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">País de origen</label>
                            <input type="text" class="form-control" id="antena_country" value="Selecciona una cuenta" readonly>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Fecha de Alta <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="antena_date" name="date" value="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                    </div>

                            <div class="mb-3">
                                <label class="form-label">Día de Pago Mensual</label>
                                <input type="number" class="form-control" id="antena_pay" name="pay" min="1" max="31" placeholder="Ej: 16">
                                <div class="form-text text-white-50">Ingresa el día del mes en que se cobra (1-31).</div>
                            </div>
                </div>
                <div class="modal-footer border-secondary">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar Equipo</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalExonerateDebt" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content card-custom text-white">
            <div class="modal-header border-secondary">
                <h5 class="modal-title"><i class="bi bi-shield-check text-warning me-2"></i>Exonerar Deuda Pasada</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formExonerateDebt" action="index.php?url=antenas" method="POST">
                <input type="hidden" name="action" value="save_debt_exemption">
                <input type="hidden" name="id_starlink" id="exonerate_id_starlink" value="0">
                <input type="hidden" name="remove_exemption" id="exonerate_remove_input" value="0">

                <div class="modal-body">
                    <!-- Resumen del equipo y estado de deuda -->
                    <div class="card bg-black bg-opacity-40 border-secondary mb-3 p-3">
                        <div class="row g-2 align-items-center">
                            <div class="col-md-6">
                                <div class="text-white-50 small">Antena / Serial</div>
                                <div class="fw-bold fs-6" id="exonerate_summary_serial">-</div>
                                <div class="small text-info" id="exonerate_summary_nickname"></div>
                            </div>
                            <div class="col-md-6">
                                <div class="text-white-50 small">Cliente Asignado</div>
                                <div class="fw-bold" id="exonerate_summary_client">-</div>
                            </div>
                            <div class="col-md-6 mt-2">
                                <div class="text-white-50 small">Plan Contratado</div>
                                <div id="exonerate_summary_plan">-</div>
                            </div>
                            <div class="col-md-6 mt-2">
                                <div class="text-white-50 small">Estado actual de deuda</div>
                                <div id="exonerate_summary_debt">-</div>
                            </div>
                        </div>
                    </div>

                    <!-- Alerta de exoneración activa previa si existe -->
                    <div id="exonerate_active_alert" class="alert alert-info border-info d-none mb-3 py-2">
                        <i class="bi bi-info-circle-fill me-1"></i>
                        <span>Esta antena tiene una exoneración activa registrada hasta el </span>
                        <strong id="exonerate_active_date"></strong>.
                        <div class="small mt-1 text-white-50" id="exonerate_active_note_wrap">
                            Motivo: <span id="exonerate_active_note" class="fst-italic text-white"></span>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Selecciona hasta qué fecha exonerar la deuda:</label>
                        <div class="d-flex flex-column gap-2">
                            <div class="form-check p-3 rounded border border-secondary border-opacity-50 bg-black bg-opacity-20">
                                <input class="form-check-input" type="radio" name="exemption_mode" id="mode_current_month" value="current_month" checked>
                                <label class="form-check-label w-100" for="mode_current_month">
                                    <div class="fw-bold text-white">Exonerar meses pasados (Iniciar cobro desde este mes: <?php echo date('m/Y'); ?>)</div>
                                    <div class="small text-white-50">Condonará todas las cuotas de meses anteriores. El cliente queda al día con el pasado y solo se le cobrará a partir del ciclo en curso (01/<?php echo date('m/Y'); ?>). Ideal para antenas viejas agregadas recientemente.</div>
                                </label>
                            </div>

                            <div class="form-check p-3 rounded border border-secondary border-opacity-50 bg-black bg-opacity-20">
                                <input class="form-check-input" type="radio" name="exemption_mode" id="mode_next_month" value="next_month">
                                <label class="form-check-label w-100" for="mode_next_month">
                                    <div class="fw-bold text-white">Exonerar totalmente hasta el próximo mes (Paz y salvo total: <?php echo date('m/Y', strtotime('+1 month')); ?>)</div>
                                    <div class="small text-white-50">Exonera tanto meses pasados como el mes en curso. Su primer ciclo a cobrar empezará a partir del día 01/<?php echo date('m/Y', strtotime('+1 month')); ?>.</div>
                                </label>
                            </div>

                            <div class="form-check p-3 rounded border border-secondary border-opacity-50 bg-black bg-opacity-20">
                                <input class="form-check-input" type="radio" name="exemption_mode" id="mode_custom" value="custom">
                                <label class="form-check-label w-100" for="mode_custom">
                                    <div class="fw-bold text-white">Personalizar fecha de corte / inicio de cobro</div>
                                    <div class="small text-white-50">Ingresa manualmente la fecha a partir de la cual se empezará a contabilizar la deuda. Los períodos anteriores a esta fecha no generarán atraso.</div>
                                    <div class="mt-2" id="custom_exempt_date_wrap" style="display: none;">
                                        <input type="date" class="form-control" name="custom_exempt_until" id="custom_exempt_until" value="<?php echo date('Y-m-01'); ?>">
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Motivo u observación (opcional)</label>
                        <input type="text" class="form-control" name="debt_exempt_note" id="exonerate_note" placeholder="Ej: Antena antigua incorporada al sistema, condonación acordada, etc.">
                        <div class="form-text text-white-50">Quedará registrado como justificación de la exoneración.</div>
                    </div>
                </div>

                <div class="modal-footer border-secondary d-flex justify-content-between">
                    <div>
                        <button type="button" class="btn btn-outline-danger d-none" id="btn_remove_exemption">
                            <i class="bi bi-trash me-1"></i>Quitar Exoneración
                        </button>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-warning text-dark fw-bold">
                            <i class="bi bi-shield-check me-1"></i>Aplicar Exoneración
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>
</div>

<script>
function initializeAntenaForm() {
    document.querySelectorAll('.btn-edit-antena').forEach(btn => {
        btn.addEventListener('click', function(){
            document.getElementById('id_starlink').value = this.getAttribute('data-id');
            document.getElementById('antena_serial').value = this.getAttribute('data-serial');
            document.getElementById('antena_nickname').value = this.getAttribute('data-nickname');
            document.getElementById('antena_kit').value = this.getAttribute('data-kit');
            const clientId = this.getAttribute('data-client-id');
            const accountId = this.getAttribute('data-account-id');
            const planId = this.getAttribute('data-plan-id');
            const countryId = this.getAttribute('data-country-id');
            const dateVal = this.getAttribute('data-date');
            document.getElementById('antena_date').value = dateVal;

            const clientSelect = document.getElementById('antena_client');
            if (clientId) {
                clientSelect.value = clientId;
            }

            const accountSelect = document.getElementById('antena_account');
            accountSelect.value = accountId || '';
            const selectedAccount = accountSelect.selectedOptions[0];
            document.getElementById('antena_country').value = selectedAccount ? (selectedAccount.getAttribute('data-country-name') || 'Sin país configurado') : 'Selecciona una cuenta';

            const planSelect = document.getElementById('antena_plan');
            if (planId) {
                planSelect.value = planId;
            }

            document.getElementById('antena_pay').value = this.getAttribute('data-pay') || '';
        });
    });

    const accountSelect = document.getElementById('antena_account');
    if (accountSelect) {
        accountSelect.addEventListener('change', function() {
            const selected = this.selectedOptions[0];
            document.getElementById('antena_country').value = selected ? (selected.getAttribute('data-country-name') || 'Sin país configurado') : 'Selecciona una cuenta';
        });
    }

    // Modal de exoneración de deuda
    document.querySelectorAll('.btn-exonerate-debt').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            const serial = this.getAttribute('data-serial');
            const nickname = this.getAttribute('data-nickname');
            const cliente = this.getAttribute('data-cliente');
            const plan = this.getAttribute('data-plan');
            const price = parseFloat(this.getAttribute('data-price') || 0);
            const overdueMonths = parseInt(this.getAttribute('data-overdue-months') || 0, 10);
            const overdueAmount = parseFloat(this.getAttribute('data-overdue-amount') || 0);
            const isExempt = this.getAttribute('data-is-exempt') === '1';
            const exemptUntil = this.getAttribute('data-exempt-until') || '';
            const exemptNote = this.getAttribute('data-exempt-note') || '';

            document.getElementById('exonerate_id_starlink').value = id;
            document.getElementById('exonerate_remove_input').value = '0';
            document.getElementById('exonerate_summary_serial').textContent = serial;
            document.getElementById('exonerate_summary_nickname').textContent = nickname ? ('“' + nickname + '”') : '';
            document.getElementById('exonerate_summary_client').textContent = cliente || 'Sin asignar';
            document.getElementById('exonerate_summary_plan').textContent = plan + (price > 0 ? (' ($' + price.toFixed(2) + '/mes)') : '');

            const debtContainer = document.getElementById('exonerate_summary_debt');
            if (overdueMonths > 0) {
                debtContainer.innerHTML = '<span class="badge bg-danger text-white"><i class="bi bi-exclamation-triangle-fill me-1"></i>' + overdueMonths + (overdueMonths === 1 ? ' mes atrasado' : ' meses atrasados') + '</span> <span class="text-danger fw-bold ms-1">($' + overdueAmount.toFixed(2) + ')</span>';
            } else {
                debtContainer.innerHTML = '<span class="badge bg-success text-dark"><i class="bi bi-check-circle-fill me-1"></i>Al día (Sin deuda pendiente)</span>';
            }

            const alertBox = document.getElementById('exonerate_active_alert');
            const btnRemove = document.getElementById('btn_remove_exemption');
            const noteWrap = document.getElementById('exonerate_active_note_wrap');
            const noteSpan = document.getElementById('exonerate_active_note');
            const noteInput = document.getElementById('exonerate_note');

            if (isExempt && exemptUntil) {
                alertBox.classList.remove('d-none');
                let parts = exemptUntil.split('-');
                let dateFormatted = parts.length === 3 ? (parts[2] + '/' + parts[1] + '/' + parts[0]) : exemptUntil;
                document.getElementById('exonerate_active_date').textContent = dateFormatted;

                if (exemptNote) {
                    noteWrap.style.display = 'block';
                    noteSpan.textContent = exemptNote;
                    noteInput.value = exemptNote;
                } else {
                    noteWrap.style.display = 'none';
                    noteInput.value = '';
                }

                btnRemove.classList.remove('d-none');
            } else {
                alertBox.classList.add('d-none');
                noteWrap.style.display = 'none';
                noteInput.value = '';
                btnRemove.classList.add('d-none');
            }

            // Restablecer radio de modo
            const modeCurrent = document.getElementById('mode_current_month');
            if (modeCurrent) modeCurrent.checked = true;
            const customWrap = document.getElementById('custom_exempt_date_wrap');
            if (customWrap) customWrap.style.display = 'none';
        });
    });

    // Cambios en los radio buttons del modo de exoneración
    const radioModes = document.querySelectorAll('input[name="exemption_mode"]');
    const customWrap = document.getElementById('custom_exempt_date_wrap');
    radioModes.forEach(radio => {
        radio.addEventListener('change', function() {
            if (customWrap) {
                customWrap.style.display = (this.value === 'custom') ? 'block' : 'none';
            }
        });
    });

    // Botón para quitar exoneración
    const btnRemove = document.getElementById('btn_remove_exemption');
    if (btnRemove) {
        btnRemove.addEventListener('click', function() {
            const executeRemove = function() {
                document.getElementById('exonerate_remove_input').value = '1';
                document.getElementById('formExonerateDebt').submit();
            };

            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: '¿Quitar exoneración?',
                    text: 'Se restablecerá el cálculo histórico original de deuda según la fecha de instalación inicial.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Sí, quitar exoneración',
                    cancelButtonText: 'Cancelar',
                    customClass: { popup: 'swal-custom-dark' }
                }).then(result => {
                    if (result.isConfirmed) {
                        executeRemove();
                    }
                });
            } else if (confirm('¿Deseas quitar la exoneración y restablecer el cálculo original de deuda?')) {
                executeRemove();
            }
        });
    }
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeAntenaForm, { once: true });
} else {
    initializeAntenaForm();
}
</script>