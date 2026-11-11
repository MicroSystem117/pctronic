<?php
$userRole = isset($_SESSION['user_role']) ? $_SESSION['user_role'] : '';
$isAdmin = $userRole === 'Administrador';
$isExpectador = $userRole === 'Expectador';

$totalAntenas = !empty($data['antenas']) ? count($data['antenas']) : 0;
$countAlDia = 0;
$countPendiente = 0;
$countMora = 0;
if (!empty($data['antenas'])) {
    foreach ($data['antenas'] as $a) {
        $st = $a['status_label'] ?? '';
        $ov = intval($a['overdue_months'] ?? 0);
        if ($ov > 0) {
            $countMora++;
        } elseif ($st === 'Pagado') {
            $countAlDia++;
        } else {
            $countPendiente++;
        }
    }
}
?>
<div class="antenas-page">
<div class="antenas-heading d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h2 class="mb-1"><i class="bi bi-broadcast text-info me-2"></i>Antenas Starlink Registradas</h2>
        <p class="text-white-50 mb-0">Consola de monitoreo de kits satelitales, estado operativo y recaudación en tiempo real.</p>
    </div>
    <?php if ($isAdmin): ?>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalAntena">
            <i class="bi bi-plus-circle me-1"></i> Registrar Antena
        </button>
    <?php endif; ?>
</div>

<!-- Barra de Filtros Rápidos y Píldoras de Telemetría -->
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div class="pill-filters-container btn-group btn-group-sm flex-wrap" role="group" aria-label="Filtro rápido de estado">
        <button type="button" class="btn btn-outline-info active pill-filter-btn" data-table=".datatable" data-column="8" data-filter="">
            Todas <span class="badge bg-secondary ms-1"><?php echo $totalAntenas; ?></span>
        </button>
        <button type="button" class="btn btn-outline-success pill-filter-btn" data-table=".datatable" data-column="8" data-filter="Al día">
            <span class="status-pulse-online"></span>Al día <span class="badge bg-success ms-1"><?php echo $countAlDia; ?></span>
        </button>
        <button type="button" class="btn btn-outline-warning pill-filter-btn" data-table=".datatable" data-column="8" data-filter="Pendiente">
            <span class="status-pulse-warning"></span>Pendientes <span class="badge bg-warning text-dark ms-1"><?php echo $countPendiente; ?></span>
        </button>
        <button type="button" class="btn btn-outline-danger pill-filter-btn" data-table=".datatable" data-column="8" data-filter="atrasado">
            <span class="status-pulse-danger"></span>En Mora <span class="badge bg-danger ms-1"><?php echo $countMora; ?></span>
        </button>
    </div>

    <div class="antenas-filters d-flex justify-content-end gap-2 flex-wrap">
        <button type="button" class="btn btn-sm btn-outline-success btn-export-csv" data-table=".datatable" data-filename="antenas_starlink.csv" title="Exportar tabla completa a Excel (.csv)">
            <i class="bi bi-file-earmark-excel me-1"></i>Excel (.csv)
        </button>
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
        <input id="search_antenas" class="form-control form-control-sm antenas-search-input" placeholder="Buscar en tabla...">
    </div>
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
                            <td data-label="Nickname" class="<?php echo empty($antena['nickname']) ? 'empty-mobile-cell' : ''; ?>"><?php echo !empty($antena['nickname']) ? '<span class="td-value">' . htmlspecialchars($antena['nickname']) . '</span>' : '<span class="text-white-50">-</span>'; ?></td>
                            <td data-label="Kit">
                                <span class="d-inline-flex align-items-center gap-1">
                                    <code class="text-info"><?php echo htmlspecialchars($antena['kit']); ?></code>
                                    <button type="button" class="btn btn-link btn-sm p-0 text-white-50 btn-copy" data-copy="<?php echo htmlspecialchars($antena['kit'], ENT_QUOTES); ?>" title="Copiar Kit">
                                        <i class="bi bi-clipboard"></i>
                                    </button>
                                </span>
                            </td>
                            <td data-label="Serial">
                                <span class="d-inline-flex align-items-center gap-1">
                                    <code class="text-white"><?php echo htmlspecialchars($antena['serial']); ?></code>
                                    <button type="button" class="btn btn-link btn-sm p-0 text-white-50 btn-copy" data-copy="<?php echo htmlspecialchars($antena['serial'], ENT_QUOTES); ?>" title="Copiar Serial">
                                        <i class="bi bi-clipboard"></i>
                                    </button>
                                </span>
                            </td>
                            <td data-label="Plan"><span class="badge border border-info-subtle text-info fw-semibold" style="background: rgba(56, 189, 248, 0.1);"><?php echo htmlspecialchars($antena['nombre_plan']); ?></span></td>
                            <td data-label="País"><span class="td-value"><?php echo htmlspecialchars($antena['pais']); ?></span></td>
                            <td data-label="Cuenta Starlink">
                                <?php echo !empty($antena['cuenta_starlink']) ? '<span class="td-value">' . htmlspecialchars($antena['cuenta_starlink']) . '</span>' : '<span class="text-white-50">Sin Cuenta Vinc.</span>'; ?>
                            </td>
                            <td data-label="Día de pago">
                                <?php 
                                    $payDay = isset($antena['pay']) && $antena['pay'] !== null && $antena['pay'] !== '' ? intval($antena['pay']) : null;
                                    if ($payDay !== null && $payDay > 0) {
                                        $curDay = intval(date('j'));
                                        $diffDays = $payDay - $curDay;
                                        $cycleTag = '';
                                        if ($diffDays === 0) {
                                            $cycleTag = '<span class="badge bg-warning text-dark ms-1" style="font-size:0.68rem;"><i class="bi bi-bell-fill me-1"></i>Hoy</span>';
                                        } elseif ($diffDays > 0) {
                                            $cycleTag = '<span class="badge border border-info-subtle text-info ms-1" style="font-size:0.68rem;">en ' . $diffDays . 'd</span>';
                                        } else {
                                            $cycleTag = '<span class="badge bg-danger-subtle text-danger border border-danger-subtle ms-1" style="font-size:0.68rem;">hace ' . abs($diffDays) . 'd</span>';
                                        }
                                        echo '<span class="td-value tech-mono fw-semibold">Día ' . $payDay . '</span>' . $cycleTag;
                                    } else {
                                        echo '<span class="text-white-50">-</span>';
                                    }
                                ?>
                            </td>
                            <td data-label="Estado de pago">
                                <?php
                                    $overdueMonths = intval($antena['overdue_months'] ?? 0);
                                    $overdueAmount = floatval($antena['overdue_amount'] ?? 0.0);
                                    $isPaid = ($antena['status_label'] ?? '') === 'Pagado';
                                    $isAtrasado = ($antena['status_label'] ?? '') === 'Atrasado';
                                    $isSuspended = !empty($antena['is_suspended']);

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
                                    <?php if ($isSuspended): ?>
                                        <div class="mb-1">
                                            <span class="badge bg-secondary border border-warning text-warning fw-bold" title="<?php echo htmlspecialchars($antena['suspended_reason'] ?? 'Servicio suspendido'); ?>">
                                                <i class="bi bi-slash-circle me-1"></i>SUSPENDIDA
                                            </span>
                                        </div>
                                    <?php endif; ?>
                                    <?php if ($overdueMonths > 0): ?>
                                        <span class="badge bg-danger-subtle border border-danger text-danger fw-bold">
                                            <span class="status-pulse-danger"></span><?php echo $overdueMonths . ($overdueMonths === 1 ? ' mes atrasado' : ' meses atrasados'); ?>
                                        </span>
                                        <div class="small text-danger fw-semibold mt-1 tech-mono">
                                            Deuda: $<?php echo number_format($overdueAmount, 2); ?>
                                        </div>
                                    <?php elseif ($isPaid): ?>
                                        <span class="badge bg-success-subtle border border-success text-success fw-bold">
                                            <span class="status-pulse-online"></span>Al día
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-warning-subtle border border-warning text-warning fw-bold">
                                            <span class="status-pulse-warning"></span>Pendiente
                                        </span>
                                        <div class="small text-white-50 mt-1">
                                            Vence día <?php echo htmlspecialchars($antena['due_day'] ?? $antena['pay'] ?? ''); ?>
                                        </div>
                                    <?php endif; ?>

                                    <?php if (!empty($antena['is_exempt']) && !empty($antena['debt_exempt_until'])): ?>
                                        <div class="mt-1">
                                            <span class="badge bg-dark text-info border border-info border-opacity-50 fw-semibold" style="font-size: 0.72rem;" title="<?php echo htmlspecialchars('Deuda previa exonerada hasta el ' . date('d/m/Y', strtotime($antena['debt_exempt_until'])) . (!empty($antena['debt_exempt_note']) ? ' — ' . $antena['debt_exempt_note'] : '')); ?>">
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
                                        <a href="index.php?url=payments&quick_serial=<?php echo urlencode($antena['serial']); ?>" 
                                           class="btn btn-sm btn-success" 
                                           title="Cobro rápido: registrar pago para esta antena">
                                            <i class="bi bi-cash-stack"></i><span class="action-btn-text ms-1">Cobrar</span>
                                        </a>
                                        <form method="POST" action="index.php?url=antenas" class="d-inline" onsubmit="return confirm('<?php echo !empty($antena['is_suspended']) ? '¿Deseas reactivar el servicio de esta antena?' : '¿Deseas suspender operativamente esta antena (corte técnico)?'; ?>');">
                                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES); ?>">
                                            <input type="hidden" name="action" value="toggle_suspension">
                                            <input type="hidden" name="id_starlink" value="<?php echo (int)$antena['id_starlink']; ?>">
                                            <?php if (!empty($antena['is_suspended'])): ?>
                                                <button type="submit" class="btn btn-sm btn-outline-warning" title="Reactivar servicio suspendido">
                                                    <i class="bi bi-play-circle"></i><span class="action-btn-text ms-1">Activar</span>
                                                </button>
                                            <?php else: ?>
                                                <button type="submit" class="btn btn-sm btn-outline-secondary" title="Suspender servicio (Corte)">
                                                    <i class="bi bi-slash-circle"></i><span class="action-btn-text ms-1">Corte</span>
                                                </button>
                                            <?php endif; ?>
                                        </form>
                                        <?php if ($waReminderUrl): ?>
                                            <a href="<?php echo htmlspecialchars($waReminderUrl, ENT_QUOTES); ?>" target="_blank"
                                               class="btn btn-sm btn-whatsapp text-white"
                                               title="Enviar recordatorio de pago por WhatsApp">
                                                <i class="bi bi-whatsapp"></i><span class="action-btn-text ms-1">Recordar</span>
                                            </a>
                                        <?php endif; ?>
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
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg antenas-form-dialog">
        <div class="modal-content card-custom text-white">
            <div class="modal-header border-secondary">
                <h5 class="modal-title"><i class="bi bi-plus-circle-fill"></i> Registrar Nueva Antena</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="index.php?url=antenas" method="POST">
                <?php echo Controller::getCsrfInput(); ?>
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
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeAntenaForm, { once: true });
} else {
    initializeAntenaForm();
}
</script>