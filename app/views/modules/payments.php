<?php
$userRole = isset($_SESSION['user_role']) ? $_SESSION['user_role'] : '';
$isLoggedIn = isset($_SESSION['user_id']);
$canReview = $userRole === 'Administrador' || $userRole === 'Moderador';
$canDelete = $canReview;
?>
<div class="payments-page">
<div class="payments-heading d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="text-white"><i class="bi bi-currency-dollar me-2 text-primary"></i>Pagos Registrados</h3>
        <p class="text-white-50 mb-0">Registra los pagos de los clientes, selecciona la forma de pago y visualiza el estado de cada antena.</p>
    </div>
    <?php if ($isLoggedIn): ?>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalPayment">
            <i class="bi bi-plus-circle-fill me-2"></i>Registrar Pago
        </button>
    <?php endif; ?>
</div>

<div class="payments-filters mb-3 d-flex justify-content-end gap-2 flex-wrap">
    <div class="input-group input-group-sm w-auto">
        <label class="input-group-text bg-secondary text-white border-secondary" for="search_payments_type">Buscar por</label>
        <select id="search_payments_type" class="form-select form-select-sm">
            <option value="all">Todos</option>
            <option value="cliente">Cliente</option>
            <option value="antena">Antena</option>
            <option value="nickname">Nickname</option>
            <option value="plan">Plan</option>
            <option value="currency">Moneda</option>
            <option value="status">Estado</option>
        </select>
    </div>
    <input id="search_payments" class="form-control form-control-sm payments-search-input" placeholder="Buscar...">
    <form class="payments-date-filters d-flex align-items-center" method="GET" action="index.php?url=payments">
        <input type="hidden" name="url" value="payments">
        <label class="form-label me-2 text-white-50 small">Desde</label>
        <input type="date" name="from" class="form-control form-control-sm me-2" value="<?php echo isset($_GET['from']) ? htmlspecialchars($_GET['from']) : ''; ?>">
        <label class="form-label me-2 text-white-50 small">Hasta</label>
        <input type="date" name="to" class="form-control form-control-sm me-2" value="<?php echo isset($_GET['to']) ? htmlspecialchars($_GET['to']) : ''; ?>">
        <button class="btn btn-sm btn-outline-light me-2" type="submit">Filtrar</button>
        <a class="btn btn-sm btn-outline-secondary" href="index.php?url=payments">Limpiar</a>
    </form>
</div>

<div class="payments-panels row">
    <div class="col-lg-6 mb-4">
        <div class="card card-custom payments-panel p-3 h-100">
            <h5 class="mb-3">Estado de Pago por Antena</h5>
            <div class="table-responsive">
                <table class="table table-dark table-hover mb-0 pdf-exportable table-mobile-cards" data-pdf-title="Estado de pago por antena">
                    <thead class="table-light">
                        <tr>
                            <th>Serial</th>
                            <th>Cliente</th>
                            <th>Vence</th>
                            <th>Estado</th>
                            <?php if ($canReview): ?>
                                <th>Acciones</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($data['antennas'])): ?>
                            <?php foreach ($data['antennas'] as $antena): ?>
                                <?php
                                    $overdueMonths = intval($antena['overdue_months'] ?? 0);
                                    $overdueAmount = floatval($antena['overdue_amount'] ?? 0.0);
                                    $isPaid = ($antena['status_label'] ?? '') === 'Pagado';
                                    $isAtrasado = ($antena['status_label'] ?? '') === 'Atrasado';

                                    if ($overdueMonths > 0) {
                                        $statusLabel = '<span class="badge bg-danger text-white"><i class="bi bi-exclamation-triangle-fill me-1"></i>' . $overdueMonths . ($overdueMonths === 1 ? ' mes atrasado' : ' meses atrasados') . '</span>';
                                    } elseif ($isPaid) {
                                        $statusLabel = '<span class="badge bg-success text-dark"><i class="bi bi-check-circle-fill me-1"></i>Al día</span>';
                                    } else {
                                        $statusLabel = '<span class="badge bg-warning text-dark"><i class="bi bi-clock-history me-1"></i>Pendiente</span>';
                                    }

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
                                <tr data-serial="<?php echo htmlspecialchars($antena['serial'], ENT_QUOTES); ?>"
                                    data-antena="<?php echo htmlspecialchars($antena['serial'], ENT_QUOTES); ?>"
                                    data-nickname="<?php echo htmlspecialchars($antena['nickname'] ?? '', ENT_QUOTES); ?>"
                                    data-cliente="<?php echo htmlspecialchars($antena['cliente'], ENT_QUOTES); ?>"
                                    data-plan="<?php echo htmlspecialchars($antena['nombre_plan'], ENT_QUOTES); ?>"
                                    data-status="<?php echo strip_tags($statusLabel); ?>"
                                    data-pay="<?php echo htmlspecialchars($antena['due_day'] ?? $antena['pay'] ?? '', ENT_QUOTES); ?>">
                                    <td data-label="Serial">
                                        <code><?php echo htmlspecialchars($antena['serial']); ?></code>
                                        <?php if (!empty($antena['nickname'])): ?>
                                            <div class="small text-info mt-1"><i class="bi bi-tag me-1"></i><?php echo htmlspecialchars($antena['nickname']); ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td data-label="Cliente"><span class="td-value"><?php echo htmlspecialchars($antena['cliente']); ?></span></td>
                                    <td data-label="Vence"><span class="td-value"><?php echo isset($antena['due_day']) && $antena['due_day'] !== null && $antena['due_day'] !== '' ? 'Día ' . htmlspecialchars(intval($antena['due_day'])) : '<span class="text-white-50">N/A</span>'; ?></span></td>
                                    <td data-label="Estado">
                                        <div class="d-inline-flex flex-column align-items-start">
                                            <div><?php echo $statusLabel; ?></div>
                                            <?php if ($overdueMonths > 0): ?>
                                                <div class="small text-danger fw-semibold mt-1">
                                                    Deuda: $<?php echo number_format($overdueAmount, 2); ?>
                                                </div>
                                            <?php endif; ?>
                                            <?php if (!empty($antena['is_exempt']) && !empty($antena['debt_exempt_until'])): ?>
                                                <div class="mt-1">
                                                    <span class="badge bg-secondary-subtle text-info border border-info border-opacity-25" style="font-size: 0.70rem;" title="<?php echo htmlspecialchars('Deuda previa exonerada hasta el ' . date('d/m/Y', strtotime($antena['debt_exempt_until']))); ?>">
                                                        <i class="bi bi-shield-check me-1"></i>Exonerada (<?php echo date('d/m/Y', strtotime($antena['debt_exempt_until'])); ?>)
                                                    </span>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <?php if ($canReview): ?>
                                        <td data-label="Acciones" class="text-nowrap">
                                            <div class="d-inline-flex gap-1 flex-wrap">
                                                <?php if ($waReminderUrl): ?>
                                                    <a href="<?php echo htmlspecialchars($waReminderUrl, ENT_QUOTES); ?>" target="_blank" class="btn btn-sm btn-whatsapp text-white" title="Enviar recordatorio por WhatsApp">
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
                                                    data-unpaid-cycles="<?php echo htmlspecialchars(json_encode($antena['unpaid_cycles'] ?? []), ENT_QUOTES); ?>"
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#modalExonerateDebt"
                                                    title="<?php echo !empty($antena['is_exempt']) ? 'Gestionar exoneración de deuda' : 'Exonerar deuda pasada'; ?>">
                                                    <i class="bi <?php echo !empty($antena['is_exempt']) ? 'bi-shield-fill-check' : 'bi-shield-check'; ?>"></i>
                                                    <span class="action-btn-text ms-1"><?php echo !empty($antena['is_exempt']) ? 'Exonerada' : 'Exonerar'; ?></span>
                                                </button>
                                            </div>
                                        </td>
                                    <?php endif; ?>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="<?php echo $canReview ? 5 : 4; ?>" class="text-center py-4 text-muted">No hay antenas disponibles.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-6 mb-4">
        <div class="card card-custom payments-panel p-3 h-100">
            <h5 class="mb-3">Historial de Pagos</h5>
            <div class="table-responsive">
                <table class="table table-dark table-hover mb-0 pdf-exportable payment-history-table table-mobile-cards" data-pdf-title="Historial de pagos">
                    <thead class="table-light">
                        <tr>
                            <th>Antena</th>
                            <th>Cliente</th>
                            <th class="text-nowrap">Monto</th>
                            <th class="text-nowrap">Moneda</th>
                            <th class="text-nowrap">Fecha Pago</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($data['payments'])): ?>
                            <?php foreach ($data['payments'] as $payment): ?>
                                <tr data-payment-id="<?php echo (int) $payment['id_payment']; ?>"
                                    data-serial="<?php echo htmlspecialchars($payment['serial'], ENT_QUOTES); ?>"
                                    data-antena="<?php echo htmlspecialchars($payment['serial'], ENT_QUOTES); ?>"
                                    data-nickname="<?php echo htmlspecialchars($payment['nickname'] ?? '', ENT_QUOTES); ?>"
                                    data-cliente="<?php echo htmlspecialchars($payment['cliente'], ENT_QUOTES); ?>"
                                    data-plan="<?php echo htmlspecialchars($payment['nombre_plan'], ENT_QUOTES); ?>"
                                    data-currency="<?php echo htmlspecialchars($payment['currency'], ENT_QUOTES); ?>"
                                    data-status="<?php echo htmlspecialchars($payment['status'] ?? 'Pendiente', ENT_QUOTES); ?>"
                                    data-payment-date="<?php echo htmlspecialchars($payment['payment_date'], ENT_QUOTES); ?>">
                                    <td data-label="Antena">
                                        <code><?php echo htmlspecialchars($payment['serial']); ?></code>
                                        <?php if (!empty($payment['nickname'])): ?>
                                            <div class="small text-info mt-1"><i class="bi bi-tag me-1"></i><?php echo htmlspecialchars($payment['nickname']); ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td data-label="Cliente"><span class="td-value"><?php echo htmlspecialchars($payment['cliente']); ?></span></td>
                                    <td data-label="Monto" class="text-nowrap">
                                        <span class="td-value"><?php echo number_format($payment['amount'], 2, ',', '.'); ?></span>
                                        <?php $mPaid = intval($payment['months_paid'] ?? 1); ?>
                                        <?php if ($mPaid > 1): ?>
                                            <div class="small text-info mt-1"><i class="bi bi-calendar-check me-1"></i><?php echo $mPaid; ?> meses</div>
                                        <?php endif; ?>
                                    </td>
                                    <td data-label="Moneda" class="text-nowrap"><span class="td-value badge bg-secondary"><?php echo htmlspecialchars($payment['currency']); ?></span></td>
                                    <td data-label="Fecha Pago" class="text-nowrap"><span class="td-value"><?php echo date('d/m/Y', strtotime($payment['payment_date'])); ?></span></td>
                                    <td data-label="Estado">
                                        <?php
                                            $paymentStatus = $payment['status'] ?? 'Pendiente';
                                            $statusClass = $paymentStatus === 'Aprobado' ? 'bg-success text-dark' : ($paymentStatus === 'Rechazado' ? 'bg-danger text-white' : 'bg-warning text-dark');
                                        ?>
                                        <span class="badge <?php echo $statusClass; ?>"><?php echo htmlspecialchars($paymentStatus); ?></span>
                                    </td>
                                    <td data-label="Acciones" class="text-nowrap">
                                        <div class="d-inline-flex align-items-center gap-1">
                                            <?php if (!empty($payment['receipt_path'])): ?>
                                                <a href="<?php echo htmlspecialchars($payment['receipt_path'], ENT_QUOTES); ?>" target="_blank" rel="noopener" class="btn btn-sm btn-outline-info" title="Ver comprobante adjunto"><i class="bi bi-paperclip"></i></a>
                                            <?php endif; ?>
                                            <button type="button" class="btn btn-sm btn-outline-info btn-generate-receipt"
                                                    data-id="<?php echo (int) $payment['id_payment']; ?>"
                                                    data-serial="<?php echo htmlspecialchars($payment['serial'], ENT_QUOTES); ?>"
                                                    data-nickname="<?php echo htmlspecialchars($payment['nickname'] ?? '', ENT_QUOTES); ?>"
                                                    data-client="<?php echo htmlspecialchars($payment['cliente'], ENT_QUOTES); ?>"
                                                    data-ci="<?php echo htmlspecialchars($payment['client_ci'] ?? '', ENT_QUOTES); ?>"
                                                    data-phone="<?php echo htmlspecialchars($payment['client_phone'] ?? '', ENT_QUOTES); ?>"
                                                    data-plan="<?php echo htmlspecialchars($payment['nombre_plan'] ?? '', ENT_QUOTES); ?>"
                                                    data-plan-price="<?php echo htmlspecialchars($payment['plan_price'] ?? '', ENT_QUOTES); ?>"
                                                    data-country="<?php echo htmlspecialchars($payment['pais'] ?? '', ENT_QUOTES); ?>"
                                                    data-pay-day="<?php echo htmlspecialchars($payment['pay_day'] ?? '', ENT_QUOTES); ?>"
                                                    data-amount="<?php echo htmlspecialchars($payment['amount'], ENT_QUOTES); ?>"
                                                    data-months="<?php echo intval($payment['months_paid'] ?? 1); ?>"
                                                    data-currency="<?php echo htmlspecialchars($payment['currency'], ENT_QUOTES); ?>"
                                                    data-date="<?php echo htmlspecialchars(date('d/m/Y', strtotime($payment['payment_date'])), ENT_QUOTES); ?>"
                                                    data-receipt="<?php echo htmlspecialchars($payment['receipt_path'] ?? '', ENT_QUOTES); ?>"
                                                    data-status="<?php echo htmlspecialchars($payment['status'] ?? 'Pendiente', ENT_QUOTES); ?>"
                                                    title="Ver / Descargar Recibo Digital">
                                                <i class="bi bi-file-earmark-text me-1"></i><span class="action-btn-text">Recibo</span>
                                            </button>
                                            <?php if (!empty($payment['client_phone'])): ?>
                                                <?php
                                                    $waReceiptUrl = build_whatsapp_receipt_url(
                                                        $payment['cliente'],
                                                        $payment['client_phone'],
                                                        $payment['id_payment'],
                                                        $payment['serial'],
                                                        $payment['nickname'] ?? '',
                                                        $payment['nombre_plan'] ?? '',
                                                        $payment['amount'],
                                                        $payment['currency'],
                                                        $payment['payment_date'],
                                                        $payment['months_paid'] ?? 1,
                                                        $payment['status'] ?? 'Aprobado'
                                                    );
                                                ?>
                                                <a href="<?php echo htmlspecialchars($waReceiptUrl, ENT_QUOTES); ?>" target="_blank" rel="noopener" class="btn btn-sm btn-outline-success" title="Enviar comprobante por WhatsApp">
                                                    <i class="bi bi-whatsapp"></i>
                                                </a>
                                            <?php endif; ?>
                                            <?php if ($canReview && ($payment['status'] ?? '') === 'Pendiente'): ?>
                                                <button type="button" class="btn btn-sm btn-outline-info payment-review-button"
                                                        data-bs-toggle="modal" data-bs-target="#modalReviewPayment"
                                                        data-id="<?php echo $payment['id_payment']; ?>"
                                                        data-serial="<?php echo htmlspecialchars($payment['serial'], ENT_QUOTES); ?>"
                                                        data-nickname="<?php echo htmlspecialchars($payment['nickname'] ?? '', ENT_QUOTES); ?>"
                                                        data-client="<?php echo htmlspecialchars($payment['cliente'], ENT_QUOTES); ?>"
                                                        data-amount="<?php echo htmlspecialchars(number_format($payment['amount'], 2, ',', '.'), ENT_QUOTES); ?>"
                                                        data-months="<?php echo intval($payment['months_paid'] ?? 1); ?>"
                                                        data-currency="<?php echo htmlspecialchars($payment['currency'], ENT_QUOTES); ?>"
                                                        data-date="<?php echo htmlspecialchars(date('d/m/Y', strtotime($payment['payment_date'])), ENT_QUOTES); ?>"
                                                        data-receipt="<?php echo htmlspecialchars($payment['receipt_path'] ?? '', ENT_QUOTES); ?>"
                                                        data-ci="<?php echo htmlspecialchars($payment['client_ci'] ?? '', ENT_QUOTES); ?>"
                                                        data-phone="<?php echo htmlspecialchars($payment['client_phone'] ?? '', ENT_QUOTES); ?>"
                                                        data-plan="<?php echo htmlspecialchars($payment['nombre_plan'] ?? '', ENT_QUOTES); ?>"
                                                        data-plan-price="<?php echo htmlspecialchars($payment['plan_price'] ?? '', ENT_QUOTES); ?>"
                                                        data-country="<?php echo htmlspecialchars($payment['pais'] ?? '', ENT_QUOTES); ?>"
                                                        data-pay-day="<?php echo htmlspecialchars($payment['pay_day'] ?? '', ENT_QUOTES); ?>"
                                                        data-status="<?php echo htmlspecialchars($payment['status'] ?? 'Pendiente', ENT_QUOTES); ?>"
                                                        title="Revisar pago"><i class="bi bi-search me-1"></i><span class="action-btn-text">Revisar</span></button>
                                                <a href="index.php?url=payments&action=approve&id=<?php echo $payment['id_payment']; ?>" class="btn btn-sm btn-success payment-action" title="Aprobar pago"><i class="bi bi-check-lg me-1"></i><span class="action-btn-text">Aprobar</span></a>
                                                <a href="index.php?url=payments&action=reject&id=<?php echo $payment['id_payment']; ?>" class="btn btn-sm btn-warning payment-action" title="Rechazar pago"><i class="bi bi-x-lg me-1"></i><span class="action-btn-text">Rechazar</span></a>
                                            <?php endif; ?>
                                            <?php if ($canDelete): ?>
                                                <a href="index.php?url=payments&action=delete&id=<?php echo $payment['id_payment']; ?>" class="btn btn-sm btn-danger payment-delete-action" title="Eliminar pago"><i class="bi bi-trash me-1"></i><span class="action-btn-text">Eliminar</span></a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">No hay pagos registrados.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php if ($canReview): ?>
<div class="modal fade" id="modalReviewPayment" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg payments-review-dialog">
        <div class="modal-content card-custom text-white">
            <div class="modal-header border-secondary">
                <h5 class="modal-title"><i class="bi bi-search me-2"></i>Revisar pago</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <dl class="row mb-3">
                    <dt class="col-sm-4">Antena</dt>
                    <dd class="col-sm-8">
                        <span id="review_payment_serial"></span>
                        <span id="review_payment_nickname" class="badge bg-secondary-subtle text-info border border-info border-opacity-25 ms-2 d-none"></span>
                    </dd>
                    <dt class="col-sm-4">Cliente</dt><dd class="col-sm-8" id="review_payment_client"></dd>
                    <dt class="col-sm-4">Monto</dt>
                    <dd class="col-sm-8">
                        <span id="review_payment_amount" class="fw-bold"></span>
                        <span id="review_payment_months" class="badge bg-info text-dark ms-2 d-none"></span>
                    </dd>
                    <dt class="col-sm-4">Fecha</dt><dd class="col-sm-8" id="review_payment_date"></dd>
                </dl>
                <div id="review_payment_receipt_container" class="d-none">
                    <h6>Comprobante</h6>
                    <iframe id="review_payment_receipt" class="w-100 border border-secondary rounded" style="height: 320px; max-height: 40vh;" title="Comprobante del pago"></iframe>
                </div>
                <p id="review_payment_no_receipt" class="text-white-50 mb-0">Este pago no tiene comprobante adjunto.</p>
            </div>
            <div class="modal-footer border-secondary d-flex justify-content-between">
                <button type="button" class="btn btn-outline-info" id="review_payment_btn_receipt">
                    <i class="bi bi-file-earmark-text me-1"></i>Ver Recibo Digital
                </button>
                <div class="d-flex gap-2">
                    <a id="review_payment_reject" class="btn btn-warning" href="#"><i class="bi bi-x-lg me-1"></i>Rechazar</a>
                    <a id="review_payment_approve" class="btn btn-success" href="#"><i class="bi bi-check-lg me-1"></i>Aprobar</a>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<?php if ($isLoggedIn): ?>
<div class="modal fade" id="modalPayment" data-bs-backdrop="static" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg payments-form-dialog">
        <div class="modal-content card-custom text-white">
            <div class="modal-header border-secondary">
                <h5 class="modal-title"><i class="bi bi-wallet2 me-2"></i> Registrar Pago</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <form id="payment_form" action="index.php?url=payments" method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <label class="form-label">Antenas <span class="text-danger">*</span></label>
                            <button type="button" class="btn btn-sm btn-outline-light" id="payment_select_all">
                                <i class="bi bi-check2-square me-1"></i>Seleccionar todas
                            </button>
                        </div>
                        <input type="search" class="form-control form-control-sm mb-2" id="payment_client_search" placeholder="Buscar antenas por cliente, serial o nickname..." autocomplete="off">
                        <div class="border border-secondary rounded p-2" id="payment_antennas" style="max-height: 260px; overflow-y: auto;">
                            <?php foreach ($data['antennas'] as $antena): ?>
                                <div class="form-check payment-antenna-option">
                                    <input class="form-check-input payment-antenna"
                                           type="checkbox"
                                           name="antenna_ids[]"
                                           value="<?php echo $antena['id_starlink']; ?>"
                                           data-client="<?php echo htmlspecialchars($antena['cliente'], ENT_QUOTES); ?>"
                                           data-serial="<?php echo htmlspecialchars($antena['serial'], ENT_QUOTES); ?>"
                                           data-nickname="<?php echo htmlspecialchars($antena['nickname'] ?? '', ENT_QUOTES); ?>"
                                           data-payday="<?php echo htmlspecialchars($antena['pay'] ?? '', ENT_QUOTES); ?>"
                                           data-price="<?php echo htmlspecialchars($antena['plan_price'] ?? '0', ENT_QUOTES); ?>"
                                           id="payment_antenna_<?php echo $antena['id_starlink']; ?>">
                                    <label class="form-check-label" for="payment_antenna_<?php echo $antena['id_starlink']; ?>">
                                        <code><?php echo htmlspecialchars($antena['serial']); ?></code>
                                        <?php if (!empty($antena['nickname'])): ?>
                                            <span class="badge bg-secondary-subtle text-info border border-info border-opacity-25 ms-1"><?php echo htmlspecialchars($antena['nickname']); ?></span>
                                        <?php endif; ?>
                                         — <?php echo htmlspecialchars($antena['cliente']); ?>
                                    </label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label" for="payment_months">Meses a abonar <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="number" min="1" max="60" class="form-control text-center fw-bold" id="payment_months" name="months_paid" value="1" required>
                                <span class="input-group-text bg-secondary text-white border-secondary">mes(es)</span>
                            </div>
                            <div class="d-flex gap-1 mt-1 flex-wrap">
                                <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-2 btn-month-preset active" data-months="1" style="font-size: 0.75rem;">1m</button>
                                <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-2 btn-month-preset" data-months="2" style="font-size: 0.75rem;">2m</button>
                                <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-2 btn-month-preset" data-months="3" style="font-size: 0.75rem;">3m</button>
                                <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-2 btn-month-preset" data-months="6" style="font-size: 0.75rem;">6m</button>
                                <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-2 btn-month-preset" data-months="12" style="font-size: 0.75rem;">1 año</button>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label" for="payment_amount">Total a pagar <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" min="0" class="form-control fw-bold text-success" id="payment_amount" name="amount" placeholder="Se calcula según el plan" readonly required>
                            <div class="small text-white-50 mt-1" id="payment_amount_breakdown"></div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Moneda <span class="text-danger">*</span></label>
                            <select class="form-select" name="currency" required>
                                <option value="USD" selected>USD</option>
                                <option value="VES">Bolívares</option>
                                <option value="USDT">USDT</option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Fecha de Pago <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" name="payment_date" value="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Cliente</label>
                            <input type="text" class="form-control" id="payment_client" value="Selecciona una o varias antenas" disabled>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Días de cobro configurados</label>
                        <input type="text" class="form-control" id="payment_payday" value="Selecciona una o varias antenas" disabled>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="payment_receipt">Comprobante de pago</label>
                        <input type="file" class="form-control" id="payment_receipt" name="payment_receipt" accept="image/jpeg,image/png,image/webp,application/pdf">
                        <small class="text-white-50">Opcional. Imágenes o PDF, máximo 5 MB.</small>
                    </div>
                </div>
                <div class="modal-footer border-secondary">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar Pago</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<?php if ($canReview): ?>
<div class="modal fade" id="modalExonerateDebt" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
        <div class="modal-content card-custom text-white">
            <div class="modal-header border-secondary">
                <h5 class="modal-title"><i class="bi bi-shield-check text-warning me-2"></i>Exonerar Deuda por Mes</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formExonerateDebt" action="index.php?url=payments" method="POST">
                <input type="hidden" name="action" value="save_debt_exemption">
                <input type="hidden" name="id_starlink" id="exonerate_id_starlink" value="0">
                <input type="hidden" name="remove_exemption" id="exonerate_remove_input" value="0">
                <input type="hidden" name="exempt_until" id="exonerate_exempt_until_input" value="">

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
                        <span>Esta antena tiene una exoneración activa previa registrada hasta </span>
                        <strong id="exonerate_active_date"></strong>.
                        <div class="small mt-1 text-white-50" id="exonerate_active_note_wrap">
                            Motivo: <span id="exonerate_active_note" class="fst-italic text-white"></span>
                        </div>
                    </div>

                    <!-- Selector visual mes por mes -->
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
                            <label class="form-label fw-bold mb-0">
                                <i class="bi bi-check2-square text-warning me-1"></i>Selecciona los meses que deseas perdonar / exonerar:
                            </label>
                            <div class="btn-group btn-group-sm">
                                <button type="button" class="btn btn-outline-warning py-0 px-2" id="btn_select_overdue_only" style="font-size: 0.75rem;">
                                    <i class="bi bi-calendar-x me-1"></i>Perdonar vencidos
                                </button>
                                <button type="button" class="btn btn-outline-success py-0 px-2" id="btn_select_all_months" style="font-size: 0.75rem;">
                                    <i class="bi bi-shield-fill-check me-1"></i>Paz y salvo total
                                </button>
                                <button type="button" class="btn btn-outline-secondary py-0 px-2" id="btn_unselect_all_months" style="font-size: 0.75rem;">
                                    <i class="bi bi-dash-square me-1"></i>Desmarcar
                                </button>
                            </div>
                        </div>

                        <!-- Lista interactiva de meses -->
                        <div class="border border-secondary rounded p-2 bg-black bg-opacity-20" id="exonerate_months_container" style="max-height: 250px; overflow-y: auto;">
                            <!-- Elementos inyectados dinámicamente -->
                        </div>
                        <div class="small text-white-50 mt-1">
                            <i class="bi bi-info-circle me-1"></i>Al marcar un mes, se exoneran automáticamente todos los meses anteriores a ese.
                        </div>
                    </div>

                    <!-- Resumen en vivo de la exoneración -->
                    <div class="card bg-black bg-opacity-40 border-secondary p-3 mb-3">
                        <div class="row text-center g-2 align-items-center">
                            <div class="col-4 border-end border-secondary">
                                <div class="text-white-50 small">Meses a perdonar</div>
                                <div class="fw-bold fs-5 text-warning" id="exonerate_preview_count">0 meses</div>
                                <div class="small text-success fw-semibold" id="exonerate_preview_amount">$0.00 perdonado</div>
                            </div>
                            <div class="col-4 border-end border-secondary">
                                <div class="text-white-50 small">Deuda restante</div>
                                <div class="fw-bold fs-5" id="exonerate_preview_remaining_count">0 meses</div>
                                <div class="small text-danger fw-semibold" id="exonerate_preview_remaining_amount">$0.00 pendiente</div>
                            </div>
                            <div class="col-4">
                                <div class="text-white-50 small">Empezará a cobrar en</div>
                                <div class="fw-bold fs-6 text-info mt-1" id="exonerate_preview_start_label">-</div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="exonerate_note">Motivo u observación (opcional)</label>
                        <input type="text" class="form-control" name="debt_exempt_note" id="exonerate_note" placeholder="Ej: Condonación acordada, equipo viejo añadido recientemente, etc.">
                        <div class="form-text text-white-50">Quedará registrado en el historial como justificación.</div>
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
                        <button type="submit" class="btn btn-warning text-dark fw-bold" id="btn_submit_exonerate">
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
document.addEventListener('app:content-ready', function() {
    const searchInput = document.getElementById('search_payments');
    const searchType = document.getElementById('search_payments_type');
    const rows = document.querySelectorAll('.card .table tbody tr');

    function filterRows() {
        const q = searchInput.value.trim().toLowerCase();
        const type = searchType.value;
        const allRows = document.querySelectorAll('.payments-panel .table tbody tr');

        allRows.forEach(row => {
            if (row.querySelector('td[colspan]')) {
                return;
            }

            let matches = false;
            if (q === '') {
                matches = true;
            } else if (type === 'all') {
                const combined = [
                    row.textContent,
                    row.dataset.nickname || '',
                    row.dataset.serial || '',
                    row.dataset.cliente || ''
                ].join(' ').toLowerCase();
                matches = combined.includes(q);
            } else if (type === 'nickname') {
                const nick = (row.dataset.nickname || '').toLowerCase();
                matches = nick.includes(q);
            } else if (type === 'cliente') {
                const client = (row.dataset.cliente || '').toLowerCase();
                matches = client.includes(q);
            } else if (type === 'antena') {
                const serial = (row.dataset.serial || row.dataset.antena || '').toLowerCase();
                matches = serial.includes(q);
            } else if (type === 'plan') {
                const plan = (row.dataset.plan || '').toLowerCase();
                matches = plan.includes(q);
            } else if (type === 'currency') {
                const curr = (row.dataset.currency || '').toLowerCase();
                matches = curr.includes(q);
            } else if (type === 'status') {
                const status = (row.dataset.status || '').toLowerCase();
                matches = status.includes(q);
            } else {
                const val = (row.dataset[type] || '').toLowerCase();
                matches = val.includes(q);
            }

            row.style.display = matches ? '' : 'none';
        });
    }

    if (searchInput) {
        searchInput.addEventListener('input', filterRows);
    }
    if (searchType) {
        searchType.addEventListener('change', filterRows);
    }

    const paymentAntennas = document.getElementById('payment_antennas');
    const paymentCheckboxes = document.querySelectorAll('.payment-antenna');
    const paymentClientSearch = document.getElementById('payment_client_search');
    const paymentAmount = document.getElementById('payment_amount');
    const paymentMonths = document.getElementById('payment_months');
    const paymentAmountBreakdown = document.getElementById('payment_amount_breakdown');
    const paymentClient = document.getElementById('payment_client');
    const paymentPayday = document.getElementById('payment_payday');
    const paymentSelectAll = document.getElementById('payment_select_all');
    const reviewButtons = document.querySelectorAll('.payment-review-button');
    const reviewReceiptContainer = document.getElementById('review_payment_receipt_container');
    const reviewReceipt = document.getElementById('review_payment_receipt');
    const reviewNoReceipt = document.getElementById('review_payment_no_receipt');
    const paymentForm = document.getElementById('payment_form');

    function showPaymentMessage(message, type) {
        if (typeof window.showSweetAlert === 'function') {
            window.showSweetAlert(type, message);
        } else if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: type === 'danger' ? 'error' : type,
                title: message,
                timer: 3500,
                showConfirmButton: false,
                customClass: { popup: 'swal-custom-toast' },
                toast: true,
                position: 'bottom-end'
            });
        }
    }

    function updatePaymentRow(row, status) {
        const badge = row.querySelector('.badge');
        if (badge) {
            badge.textContent = status;
            badge.className = 'badge ' + (status === 'Aprobado' ? 'bg-success text-dark' : 'bg-danger text-white');
        }
        row.dataset.status = status;
        row.querySelectorAll('.payment-action, .payment-review-button').forEach(action => action.remove());
    }

    function escapePaymentValue(value) {
        return String(value ?? '').replace(/[&<>'"]/g, character => ({
            '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#039;', '"': '&quot;'
        })[character]);
    }

    function createPaymentRow(payment) {
        const statusClass = payment.status === 'Aprobado' ? 'bg-success text-dark' : (payment.status === 'Rechazado' ? 'bg-danger text-white' : 'bg-warning text-dark');
        const receipt = payment.receipt
            ? '<a href="' + escapePaymentValue(payment.receipt) + '" target="_blank" rel="noopener" class="btn btn-sm btn-outline-info" title="Ver comprobante adjunto"><i class="bi bi-paperclip"></i></a>'
            : '<span class="text-white-50">-</span>';
        const monthsPaidVal = Number(payment.months_paid || 1);
        const monthsBadge = monthsPaidVal > 1
            ? '<div class="small text-info mt-1"><i class="bi bi-calendar-check me-1"></i>' + escapePaymentValue(monthsPaidVal) + ' meses</div>'
            : '';

        let receiptBtn = '<button type="button" class="btn btn-sm btn-outline-info btn-generate-receipt" ' +
            'data-id="' + payment.id + '" ' +
            'data-serial="' + escapePaymentValue(payment.serial) + '" ' +
            'data-nickname="' + escapePaymentValue(payment.nickname || '') + '" ' +
            'data-client="' + escapePaymentValue(payment.client) + '" ' +
            'data-ci="' + escapePaymentValue(payment.client_ci || '') + '" ' +
            'data-phone="' + escapePaymentValue(payment.client_phone || '') + '" ' +
            'data-plan="' + escapePaymentValue(payment.plan || '') + '" ' +
            'data-plan-price="' + escapePaymentValue(payment.plan_price || '') + '" ' +
            'data-country="' + escapePaymentValue(payment.country || '') + '" ' +
            'data-pay-day="' + escapePaymentValue(payment.pay_day || '') + '" ' +
            'data-amount="' + escapePaymentValue(payment.amount) + '" ' +
            'data-months="' + escapePaymentValue(monthsPaidVal) + '" ' +
            'data-currency="' + escapePaymentValue(payment.currency) + '" ' +
            'data-date="' + escapePaymentValue(payment.date) + '" ' +
            'data-receipt="' + escapePaymentValue(payment.receipt || '') + '" ' +
            'data-status="' + escapePaymentValue(payment.status) + '" ' +
            'title="Ver / Descargar Recibo Digital"><i class="bi bi-file-earmark-text me-1"></i><span class="action-btn-text">Recibo</span></button>';

        let waBtn = '';
        if (payment.client_phone) {
            const waUrl = buildReceiptWhatsAppUrl(payment, String(payment.id).padStart(6, '0'));
            waBtn = '<a href="' + escapePaymentValue(waUrl) + '" target="_blank" rel="noopener" class="btn btn-sm btn-outline-success" title="Enviar comprobante por WhatsApp"><i class="bi bi-whatsapp"></i></a>';
        }

        let adminActions = '';
        if (<?php echo $canReview ? 'true' : 'false'; ?> && payment.status === 'Pendiente') {
            adminActions += '<button type="button" class="btn btn-sm btn-outline-info payment-review-button" data-bs-toggle="modal" data-bs-target="#modalReviewPayment" ' +
                'data-id="' + payment.id + '" ' +
                'data-serial="' + escapePaymentValue(payment.serial) + '" ' +
                'data-nickname="' + escapePaymentValue(payment.nickname || '') + '" ' +
                'data-client="' + escapePaymentValue(payment.client) + '" ' +
                'data-amount="' + escapePaymentValue(Number(payment.amount).toLocaleString('es-VE', { minimumFractionDigits: 2, maximumFractionDigits: 2 })) + '" ' +
                'data-months="' + escapePaymentValue(monthsPaidVal) + '" ' +
                'data-currency="' + escapePaymentValue(payment.currency) + '" ' +
                'data-date="' + escapePaymentValue(payment.date) + '" ' +
                'data-receipt="' + escapePaymentValue(payment.receipt || '') + '" ' +
                'data-ci="' + escapePaymentValue(payment.client_ci || '') + '" ' +
                'data-phone="' + escapePaymentValue(payment.client_phone || '') + '" ' +
                'data-plan="' + escapePaymentValue(payment.plan || '') + '" ' +
                'data-plan-price="' + escapePaymentValue(payment.plan_price || '') + '" ' +
                'data-country="' + escapePaymentValue(payment.country || '') + '" ' +
                'data-pay-day="' + escapePaymentValue(payment.pay_day || '') + '" ' +
                'data-status="' + escapePaymentValue(payment.status) + '" ' +
                'title="Revisar pago"><i class="bi bi-search me-1"></i><span class="action-btn-text">Revisar</span></button>' +
                '<a href="index.php?url=payments&action=approve&id=' + payment.id + '" class="btn btn-sm btn-success payment-action" title="Aprobar pago"><i class="bi bi-check-lg me-1"></i><span class="action-btn-text">Aprobar</span></a>' +
                '<a href="index.php?url=payments&action=reject&id=' + payment.id + '" class="btn btn-sm btn-warning payment-action" title="Rechazar pago"><i class="bi bi-x-lg me-1"></i><span class="action-btn-text">Rechazar</span></a>';
        }
        if (<?php echo $canDelete ? 'true' : 'false'; ?>) {
            adminActions += '<a href="index.php?url=payments&action=delete&id=' + payment.id + '" class="btn btn-sm btn-danger payment-delete-action" title="Eliminar pago"><i class="bi bi-trash me-1"></i><span class="action-btn-text">Eliminar</span></a>';
        }

        const voucherBtn = payment.receipt
            ? '<a href="' + escapePaymentValue(payment.receipt) + '" target="_blank" rel="noopener" class="btn btn-sm btn-outline-info" title="Ver comprobante adjunto"><i class="bi bi-paperclip"></i></a>'
            : '';

        const actionsHtml = '<td data-label="Acciones" class="text-nowrap"><div class="d-inline-flex align-items-center gap-1">' +
            (voucherBtn ? voucherBtn + ' ' : '') + receiptBtn + (waBtn ? ' ' + waBtn : '') + (adminActions ? ' ' + adminActions : '') +
            '</div></td>';

        const row = document.createElement('tr');
        row.dataset.paymentId = payment.id;
        row.dataset.serial = payment.serial || '';
        row.dataset.antena = payment.serial || '';
        row.dataset.nickname = payment.nickname || '';
        row.dataset.cliente = payment.client || '';
        row.dataset.currency = payment.currency || '';
        row.dataset.status = payment.status;

        const nicknameHtml = payment.nickname
            ? '<div class="small text-info mt-1"><i class="bi bi-tag me-1"></i>' + escapePaymentValue(payment.nickname) + '</div>'
            : '';

        row.innerHTML = '<td data-label="Antena"><code>' + escapePaymentValue(payment.serial) + '</code>' + nicknameHtml + '</td>' +
            '<td data-label="Cliente"><span class="td-value">' + escapePaymentValue(payment.client) + '</span></td>' +
            '<td data-label="Monto"><span class="td-value">' + Number(payment.amount).toLocaleString('es-VE', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + '</span>' + monthsBadge + '</td>' +
            '<td data-label="Moneda"><span class="td-value badge bg-secondary">' + escapePaymentValue(payment.currency) + '</span></td>' +
            '<td data-label="Fecha Pago"><span class="td-value">' + escapePaymentValue(payment.date) + '</span></td>' +
            '<td data-label="Estado"><span class="badge ' + statusClass + '">' + escapePaymentValue(payment.status) + '</span></td>' +
            actionsHtml;
        return row;
    }

    let paymentSyncInProgress = false;
    let paymentSyncFailures = 0;

    function synchronizePayments() {
        // Evitar sincronizar si la pestaña está oculta, si hay petición en curso o si no hay conexión a internet
        if (document.hidden || paymentSyncInProgress || (navigator && !navigator.onLine)) {
            return;
        }

        // Si han fallado 3 intentos consecutivos (ej: VPS reiniciando o corte de red), esperar reconexión
        if (paymentSyncFailures >= 3) {
            return;
        }

        paymentSyncInProgress = true;
        const controller = typeof AbortController !== 'undefined' ? new AbortController() : null;
        const timeoutId = controller ? setTimeout(() => controller.abort(), 8000) : null;

        fetch('index.php?url=payments&action=sync', { 
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            signal: controller ? controller.signal : undefined
        })
            .then(response => {
                if (!response.ok) throw new Error('HTTP ' + response.status);
                return response.json();
            })
            .then(payments => {
                paymentSyncFailures = 0;
                if (!Array.isArray(payments)) return;

                const currentPayments = new Map(payments.map(payment => [String(payment.id), payment]));
                const historyBody = document.querySelector('.payment-history-table tbody');
                document.querySelectorAll('tr[data-payment-id]').forEach(row => {
                    const payment = currentPayments.get(row.dataset.paymentId);
                    if (!payment) {
                        row.remove();
                    } else if (row.dataset.status !== payment.status) {
                        updatePaymentRow(row, payment.status);
                    }
                });
                if (historyBody) {
                    payments.forEach(payment => {
                        if (!document.querySelector('tr[data-payment-id="' + payment.id + '"]')) {
                            historyBody.prepend(createPaymentRow(payment));
                        }
                    });
                    historyBody.querySelectorAll('.payment-action').forEach(action => {
                        if (!action.dataset.bound) {
                            action.dataset.bound = 'true';
                            action.addEventListener('click', function(event) {
                                event.preventDefault();
                                const row = this.closest('tr');
                                processPaymentAction(this, row, result => updatePaymentRow(row, result.status));
                            });
                        }
                    });
                    historyBody.querySelectorAll('.payment-delete-action').forEach(action => {
                        if (!action.dataset.bound) {
                            action.dataset.bound = 'true';
                            action.addEventListener('click', function(event) {
                                event.preventDefault();
                                const link = this;
                                const row = link.closest('tr');
                                Swal.fire({
                                    title: '¿Eliminar este pago?',
                                    text: 'Esta acción no se puede deshacer.',
                                    icon: 'warning',
                                    showCancelButton: true,
                                    confirmButtonColor: '#dc3545',
                                    cancelButtonColor: '#6c757d',
                                    confirmButtonText: 'Sí, eliminar',
                                    cancelButtonText: 'Cancelar',
                                    customClass: { popup: 'swal-custom-dark' }
                                }).then((result) => {
                                    if (result.isConfirmed) {
                                        processPaymentAction(link, row, () => row.remove());
                                    }
                                });
                            });
                        }
                    });
                    historyBody.querySelectorAll('.payment-review-button').forEach(bindReviewButton);
                    historyBody.querySelectorAll('.btn-generate-receipt').forEach(bindReceiptButton);
                }
            })
            .catch(() => {
                paymentSyncFailures++;
            })
            .finally(() => { 
                if (timeoutId) clearTimeout(timeoutId);
                paymentSyncInProgress = false; 
            });
    }

    // Limpiar temporizador previo si existía para evitar peticiones duplicadas
    if (window.paymentSyncTimer) {
        window.clearInterval(window.paymentSyncTimer);
    }
    // Sincronizar periódicamente cada 15 segundos
    window.paymentSyncTimer = window.setInterval(synchronizePayments, 15000);

    // Reanudar sincronización inmediata cuando el usuario vuelve a la pestaña o recupera la red
    document.addEventListener('visibilitychange', function() {
        if (!document.hidden) {
            paymentSyncFailures = 0;
            synchronizePayments();
        }
    });

    window.addEventListener('online', function() {
        paymentSyncFailures = 0;
        synchronizePayments();
    });

    function processPaymentAction(action, row, onSuccess) {
        action.classList.add('disabled');
        fetch(action.href, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(response => response.json())
            .then(result => {
                if (!result.success) throw new Error();
                onSuccess(result);
            })
            .catch(() => {
                action.classList.remove('disabled');
                showPaymentMessage('No se pudo actualizar el pago.', 'danger');
            });
    }

    if (paymentForm) {
        paymentForm.addEventListener('submit', function(event) {
            event.preventDefault();
            const submitButton = this.querySelector('button[type="submit"]');
            submitButton.disabled = true;
            fetch(this.action, {
                method: 'POST',
                body: new FormData(this),
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
                .then(async response => {
                    const contentType = response.headers.get('content-type') || '';
                    if (!contentType.includes('application/json')) {
                        const errorText = await response.text();
                        console.error('Server non-JSON response:', errorText);
                        throw new Error('El servidor devolvió una respuesta no válida (revisa la configuración o permisos en el VPS).');
                    }
                    return response.json();
                })
                .then(result => {
                    if (!result || !result.success) {
                        throw new Error((result && result.message) ? result.message : 'No se pudo registrar el pago.');
                    }
                    const modalEl = document.getElementById('modalPayment');
                    const modalInstance = bootstrap.Modal.getInstance(modalEl);
                    if (modalInstance) {
                        modalInstance.hide();
                    }
                    this.reset();
                    paymentCheckboxes.forEach(checkbox => checkbox.closest('.payment-antenna-option').classList.remove('payment-antenna-selected'));
                    paymentAmount.value = '';
                    window.history.replaceState({}, document.title, 'index.php?url=payments');
                    synchronizePayments();
                    showPaymentMessage(result.status === 'payment_pending' ? 'Pago cargado y enviado a revisión.' : 'Pago registrado correctamente.', 'success');
                })
                .catch(err => {
                    console.error('Payment submit error:', err);
                    showPaymentMessage(err.message || 'No se pudo registrar el pago.', 'danger');
                })
                .finally(() => { submitButton.disabled = false; });
        });
    }

    document.querySelectorAll('.payment-action').forEach(action => {
        action.addEventListener('click', function(event) {
            event.preventDefault();
            const row = this.closest('tr');
            processPaymentAction(this, row, result => {
                updatePaymentRow(row, result.status);
                showPaymentMessage('Estado del pago actualizado correctamente.', 'success');
            });
        });
    });

    document.querySelectorAll('.payment-delete-action').forEach(action => {
        action.addEventListener('click', function(event) {
            event.preventDefault();
            const link = this;
            const row = link.closest('tr');
            Swal.fire({
                title: '¿Eliminar este pago?',
                text: 'Esta acción no se puede deshacer.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar',
                customClass: { popup: 'swal-custom-dark' }
            }).then((result) => {
                if (result.isConfirmed) {
                    processPaymentAction(link, row, () => {
                        row.remove();
                        showPaymentMessage('Pago eliminado correctamente.', 'success');
                    });
                }
            });
        });
    });

    if (paymentAntennas) {
        function updatePaymentSummary() {
            const selected = Array.from(paymentCheckboxes).filter(checkbox => checkbox.checked);
            paymentCheckboxes.forEach(checkbox => {
                checkbox.closest('.payment-antenna-option').classList.toggle('payment-antenna-selected', checkbox.checked);
            });
            const subtotalPerMonth = selected.reduce((sum, option) => sum + (parseFloat(option.dataset.price) || 0), 0);
            const clients = [...new Set(selected.map(option => option.dataset.client || 'Cliente no identificado'))];
            const months = paymentMonths ? Math.max(1, parseInt(paymentMonths.value || '1', 10)) : 1;
            const total = subtotalPerMonth * months;

            paymentAmount.value = selected.length ? total.toFixed(2) : '';
            if (paymentAmountBreakdown) {
                if (selected.length && months > 1) {
                    paymentAmountBreakdown.textContent = '$' + subtotalPerMonth.toFixed(2) + '/mes × ' + months + ' meses = $' + total.toFixed(2);
                } else if (selected.length) {
                    paymentAmountBreakdown.textContent = '$' + subtotalPerMonth.toFixed(2) + ' (1 mes)';
                } else {
                    paymentAmountBreakdown.textContent = '';
                }
            }

            paymentClient.value = selected.length
                ? clients.join(', ')
                : 'Selecciona una o varias antenas';
            paymentPayday.value = selected.length
                ? selected.map(option => option.dataset.payday ? 'Día ' + option.dataset.payday : 'No configurado').join(', ')
                : 'Selecciona una o varias antenas';
        }

        paymentCheckboxes.forEach(checkbox => checkbox.addEventListener('change', updatePaymentSummary));

        if (paymentMonths) {
            paymentMonths.addEventListener('input', function() {
                const val = parseInt(this.value, 10);
                if (val < 1 || isNaN(val)) {
                    this.value = 1;
                }
                updatePaymentSummary();
                highlightPreset(this.value);
            });
            paymentMonths.addEventListener('change', function() {
                const val = parseInt(this.value, 10);
                if (val < 1 || isNaN(val)) {
                    this.value = 1;
                }
                updatePaymentSummary();
                highlightPreset(this.value);
            });
        }

        function highlightPreset(mVal) {
            document.querySelectorAll('.btn-month-preset').forEach(b => {
                if (b.dataset.months === String(mVal)) {
                    b.classList.remove('btn-outline-secondary');
                    b.classList.add('btn-primary', 'active');
                } else {
                    b.classList.remove('active', 'btn-primary');
                    b.classList.add('btn-outline-secondary');
                }
            });
        }

        document.querySelectorAll('.btn-month-preset').forEach(btn => {
            btn.addEventListener('click', function() {
                const m = parseInt(this.dataset.months, 10) || 1;
                if (paymentMonths) {
                    paymentMonths.value = m;
                    updatePaymentSummary();
                    highlightPreset(m);
                }
            });
        });

        if (paymentSelectAll) {
            paymentSelectAll.addEventListener('click', function() {
                const visibleCheckboxes = Array.from(paymentCheckboxes).filter(checkbox => {
                    return checkbox.closest('.payment-antenna-option').style.display !== 'none';
                });
                const selectAll = visibleCheckboxes.some(checkbox => !checkbox.checked);
                visibleCheckboxes.forEach(checkbox => checkbox.checked = selectAll);
                updatePaymentSummary();
                this.innerHTML = selectAll
                    ? '<i class="bi bi-dash-square me-1"></i>Quitar visibles'
                    : '<i class="bi bi-check2-square me-1"></i>Seleccionar visibles';
            });
        }

        if (paymentClientSearch) {
            paymentClientSearch.addEventListener('input', function() {
                const query = this.value.trim().toLowerCase();
                document.querySelectorAll('.payment-antenna-option').forEach(option => {
                    const cb = option.querySelector('.payment-antenna');
                    const client = (cb.dataset.client || '').toLowerCase();
                    const serial = (cb.dataset.serial || '').toLowerCase();
                    const nickname = (cb.dataset.nickname || '').toLowerCase();
                    option.style.display = query === '' || client.includes(query) || serial.includes(query) || nickname.includes(query) ? '' : 'none';
                });
            });
        }

        const modal = document.getElementById('modalPayment');
        if (modal) {
            modal.addEventListener('show.bs.modal', function () {
                paymentCheckboxes.forEach(checkbox => checkbox.checked = false);
                paymentCheckboxes.forEach(checkbox => checkbox.closest('.payment-antenna-option').classList.remove('payment-antenna-selected'));
                if (paymentClientSearch) {
                    paymentClientSearch.value = '';
                    paymentCheckboxes.forEach(checkbox => checkbox.closest('.payment-antenna-option').style.display = '');
                }
                if (paymentMonths) {
                    paymentMonths.value = 1;
                    highlightPreset(1);
                }
                if (paymentAmountBreakdown) {
                    paymentAmountBreakdown.textContent = '';
                }
                paymentClient.value = 'Selecciona una o varias antenas';
                paymentPayday.value = 'Selecciona una o varias antenas';
                paymentAmount.value = '';
                if (paymentSelectAll) {
                    paymentSelectAll.innerHTML = '<i class="bi bi-check2-square me-1"></i>Seleccionar todas';
                }
            });
        }
    }

    let currentReviewPaymentData = null;

    function bindReviewButton(button) {
        if (button.dataset.bound) return;
        button.dataset.bound = 'true';
        button.addEventListener('click', function() {
            currentReviewPaymentData = {
                id: this.dataset.id,
                serial: this.dataset.serial,
                nickname: this.dataset.nickname,
                client: this.dataset.client,
                client_ci: this.dataset.ci,
                client_phone: this.dataset.phone,
                plan: this.dataset.plan,
                plan_price: this.dataset.planPrice,
                country: this.dataset.country,
                pay_day: this.dataset.payDay,
                amount: this.dataset.amount ? parseFloat(this.dataset.amount.replace(/\./g, '').replace(',', '.')) : 0,
                months_paid: parseInt(this.dataset.months || '1', 10),
                currency: this.dataset.currency || 'USD',
                date: this.dataset.date,
                status: this.dataset.status || 'Pendiente'
            };

            document.getElementById('review_payment_serial').textContent = this.dataset.serial;
            const reviewNicknameEl = document.getElementById('review_payment_nickname');
            if (reviewNicknameEl) {
                if (this.dataset.nickname) {
                    reviewNicknameEl.textContent = this.dataset.nickname;
                    reviewNicknameEl.classList.remove('d-none');
                } else {
                    reviewNicknameEl.textContent = '';
                    reviewNicknameEl.classList.add('d-none');
                }
            }
            document.getElementById('review_payment_client').textContent = this.dataset.client;
            document.getElementById('review_payment_amount').textContent = this.dataset.amount + ' ' + this.dataset.currency;
            const reviewMonthsEl = document.getElementById('review_payment_months');
            if (reviewMonthsEl) {
                const months = parseInt(this.dataset.months || '1', 10);
                if (months > 1) {
                    reviewMonthsEl.textContent = months + ' meses abonados';
                    reviewMonthsEl.classList.remove('d-none');
                } else {
                    reviewMonthsEl.textContent = '';
                    reviewMonthsEl.classList.add('d-none');
                }
            }
            document.getElementById('review_payment_date').textContent = this.dataset.date;

            if (this.dataset.receipt) {
                reviewReceipt.src = this.dataset.receipt;
                reviewReceiptContainer.classList.remove('d-none');
                reviewNoReceipt.classList.add('d-none');
            } else {
                reviewReceipt.removeAttribute('src');
                reviewReceiptContainer.classList.add('d-none');
                reviewNoReceipt.classList.remove('d-none');
            }

            document.getElementById('review_payment_reject').href = 'index.php?url=payments&action=reject&id=' + this.dataset.id;
            document.getElementById('review_payment_approve').href = 'index.php?url=payments&action=approve&id=' + this.dataset.id;

            [document.getElementById('review_payment_reject'), document.getElementById('review_payment_approve')].forEach(action => {
                action.onclick = function(event) {
                    event.preventDefault();
                    const row = button.closest('tr');
                    processPaymentAction(this, row, result => {
                        updatePaymentRow(row, result.status);
                        bootstrap.Modal.getInstance(document.getElementById('modalReviewPayment')).hide();
                        showPaymentMessage('Estado del pago actualizado correctamente.', 'success');
                    });
                };
            });
        });
    }

    reviewButtons.forEach(bindReviewButton);

    const reviewBtnReceipt = document.getElementById('review_payment_btn_receipt');
    if (reviewBtnReceipt) {
        reviewBtnReceipt.addEventListener('click', function() {
            if (currentReviewPaymentData) {
                generatePaymentReceiptPdf(currentReviewPaymentData);
            }
        });
    }

    function buildReceiptWhatsAppUrl(payment, numRecibo) {
        const phone = payment.client_phone || payment.phone || '';
        let digits = String(phone).replace(/\D+/g, '');
        if (digits.startsWith('0')) {
            digits = '58' + digits.substring(1);
        } else if (digits.length === 10 && digits.startsWith('4')) {
            digits = '58' + digits;
        }

        const clientName = payment.client || 'Estimado/a cliente';
        const serial = payment.serial || 'N/A';
        const nickname = payment.nickname ? ' (' + payment.nickname + ')' : '';
        const plan = payment.plan || payment.nombre_plan || '';
        const months = parseInt(payment.months_paid || 1, 10);
        const monthsText = months > 1 ? (months + ' meses') : '1 mes';
        const amount = Number(payment.amount || 0).toLocaleString('es-VE', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' ' + (payment.currency || 'USD');
        const date = payment.date || payment.payment_date || '';
        const status = payment.status || 'Aprobado';
        const statusText = status === 'Aprobado' ? '✅ Pagado / Aprobado' : (status === 'Rechazado' ? '❌ Rechazado' : '⏳ Pendiente');

        let msg = `Hola *${clientName}*, le hacemos entrega de su comprobante de pago de *PCTRONIC* 🛰️📄\n\n`;
        msg += `🧾 *COMPROBANTE DE PAGO:* #REC-${numRecibo}\n`;
        msg += `📅 *Fecha de Pago:* ${date}\n`;
        msg += `👤 *Cliente:* ${clientName}\n`;
        msg += `📡 *Antena Starlink:* \`${serial}\`${nickname}\n`;
        if (plan) msg += `📦 *Plan:* ${plan}\n`;
        msg += `🗓️ *Periodo cubierto:* ${monthsText}\n`;
        msg += `💰 *Total cancelado:* *${amount}*\n`;
        msg += `📌 *Estado:* *${statusText}*\n\n`;
        msg += `¡Muchas gracias por su puntualidad y confianza! Su servicio se encuentra debidamente registrado. ✨\n\n`;
        msg += `_PCTRONIC C.A. • Soluciones en Telecomunicaciones_`;

        const encoded = encodeURIComponent(msg);
        return digits ? `https://wa.me/${digits}?text=${encoded}` : `https://wa.me/?text=${encoded}`;
    }

    function generatePaymentReceiptPdf(payment) {
        if (!window.jspdf || typeof window.jspdf.jsPDF !== 'function') {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'error',
                    title: 'Error de exportación',
                    text: 'No se pudo cargar el módulo de exportación PDF.',
                    customClass: { popup: 'swal-custom-dark' }
                });
            } else {
                alert('No se pudo cargar el módulo de exportación PDF.');
            }
            return;
        }

        const pdf = new window.jspdf.jsPDF({
            orientation: 'portrait',
            unit: 'mm',
            format: 'letter' // 215.9 x 279.4 mm
        });

        const pageWidth = pdf.internal.pageSize.getWidth();
        const pageHeight = pdf.internal.pageSize.getHeight();
        const margin = 14;

        // 1. Franja superior decorativa corporativa (Cyan / Sky)
        pdf.setFillColor(14, 165, 233); // #0ea5e9
        pdf.rect(0, 0, pageWidth, 4, 'F');

        // Barra secundaria delgada oscura
        pdf.setFillColor(15, 23, 42); // #0f172a
        pdf.rect(0, 4, pageWidth, 1.2, 'F');

        // 2. Encabezado institucional
        let textX = margin;
        if (typeof appPdfLogo !== 'undefined' && appPdfLogo.complete && appPdfLogo.naturalWidth > 0) {
            try {
                const logoAspect = appPdfLogo.naturalWidth / appPdfLogo.naturalHeight;
                const logoH = 13;
                const logoW = logoH * logoAspect;
                pdf.addImage(appPdfLogo, 'PNG', margin, 9, logoW, logoH);
                textX = margin + logoW + 5;
            } catch (e) {
                textX = margin;
            }
        }

        // Datos de la empresa emisora
        pdf.setFont('helvetica', 'bold');
        pdf.setFontSize(15);
        pdf.setTextColor(15, 23, 42); // #0f172a
        pdf.text('PCTRONIC C.A.', textX, 15);

        pdf.setFont('helvetica', 'normal');
        pdf.setFontSize(7.5);
        pdf.setTextColor(100, 116, 139); // #64748b
        pdf.text('Soluciones Integrales en Telecomunicaciones & Conectividad Starlink', textX, 19.5);
        pdf.text('Soporte Técnico Especializado • Gestión Satelital', textX, 23.5);

        // 3. Recuadro / Tarjeta de identificación del recibo (lado derecho)
        const cardW = 74;
        const cardH = 28;
        const cardX = pageWidth - margin - cardW;
        const cardY = 8.5;

        pdf.setFillColor(248, 250, 252); // #f8fafc
        pdf.setDrawColor(203, 213, 225); // #cbd5e1
        pdf.setLineWidth(0.3);
        pdf.roundedRect(cardX, cardY, cardW, cardH, 2, 2, 'FD');

        const numRecibo = String(payment.id || '').padStart(6, '0');
        pdf.setFont('helvetica', 'bold');
        pdf.setFontSize(9.5);
        pdf.setTextColor(14, 165, 233); // #0ea5e9
        pdf.text('COMPROBANTE DE PAGO', cardX + cardW / 2, cardY + 5.5, { align: 'center' });

        pdf.setFont('helvetica', 'bold');
        pdf.setFontSize(11);
        pdf.setTextColor(15, 23, 42);
        pdf.text('REC-#' + numRecibo, cardX + cardW / 2, cardY + 10.5, { align: 'center' });

        pdf.setDrawColor(226, 232, 240);
        pdf.line(cardX + 4, cardY + 12.5, cardX + cardW - 4, cardY + 12.5);

        pdf.setFont('helvetica', 'normal');
        pdf.setFontSize(7.5);
        pdf.setTextColor(71, 85, 105);
        const dateFormatted = payment.date || payment.payment_date || '';
        pdf.text('Fecha de Pago: ' + dateFormatted, cardX + 5, cardY + 16.5);
        pdf.text('Emisión: ' + new Date().toLocaleDateString('es-VE'), cardX + 5, cardY + 20.5);

        // Badge de estado dentro de la tarjeta
        const isApproved = (payment.status || '') === 'Aprobado';
        const isRejected = (payment.status || '') === 'Rechazado';
        const badgeColor = isApproved ? [16, 185, 129] : (isRejected ? [239, 68, 68] : [245, 158, 11]);
        const badgeText = isApproved ? 'PAGADO / APROBADO' : (isRejected ? 'RECHAZADO' : 'PENDIENTE');

        pdf.setFillColor(...badgeColor);
        pdf.roundedRect(cardX + cardW - 38, cardY + 22, 34, 4.5, 1, 1, 'F');
        pdf.setFont('helvetica', 'bold');
        pdf.setFontSize(6.8);
        pdf.setTextColor(255, 255, 255);
        pdf.text(badgeText, cardX + cardW - 21, cardY + 25.2, { align: 'center' });

        // Línea divisoria elegante
        pdf.setDrawColor(226, 232, 240);
        pdf.setLineWidth(0.4);
        pdf.line(margin, 39, pageWidth - margin, 39);

        // 4. Bloques de Información: DATOS DEL CLIENTE y DATOS DEL SERVICIO
        const boxY = 42;
        const boxW = (pageWidth - (margin * 2) - 6) / 2;
        const boxH = 33;

        // --- Caja Izquierda: DATOS DEL CLIENTE ---
        pdf.setFillColor(248, 250, 252);
        pdf.setDrawColor(226, 232, 240);
        pdf.roundedRect(margin, boxY, boxW, boxH, 2, 2, 'FD');

        pdf.setFillColor(241, 245, 249);
        pdf.rect(margin, boxY, boxW, 6.5, 'F');
        pdf.setFont('helvetica', 'bold');
        pdf.setFontSize(8);
        pdf.setTextColor(15, 23, 42);
        pdf.text('DATOS DEL CLIENTE', margin + 4, boxY + 4.8);

        pdf.setFont('helvetica', 'bold');
        pdf.setFontSize(7);
        pdf.setTextColor(100, 116, 139);
        pdf.text('Titular / Cliente:', margin + 4, boxY + 11);
        pdf.setFont('helvetica', 'bold');
        pdf.setFontSize(8.5);
        pdf.setTextColor(15, 23, 42);
        pdf.text(payment.client || 'Cliente General', margin + 4, boxY + 15);

        pdf.setFont('helvetica', 'bold');
        pdf.setFontSize(7);
        pdf.setTextColor(100, 116, 139);
        pdf.text('Cédula / RIF:', margin + 4, boxY + 20.5);
        pdf.setFont('helvetica', 'normal');
        pdf.setFontSize(8);
        pdf.setTextColor(30, 41, 59);
        pdf.text(payment.client_ci || payment.ci || 'No registrada', margin + 4, boxY + 24.5);

        pdf.setFont('helvetica', 'bold');
        pdf.setFontSize(7);
        pdf.setTextColor(100, 116, 139);
        pdf.text('Teléfono / WhatsApp:', margin + boxW / 2, boxY + 20.5);
        pdf.setFont('helvetica', 'normal');
        pdf.setFontSize(8);
        pdf.setTextColor(30, 41, 59);
        pdf.text(payment.client_phone || payment.phone || 'No registrado', margin + boxW / 2, boxY + 24.5);

        // --- Caja Derecha: DATOS DEL SERVICIO STARLINK ---
        const rightBoxX = margin + boxW + 6;
        pdf.setFillColor(248, 250, 252);
        pdf.roundedRect(rightBoxX, boxY, boxW, boxH, 2, 2, 'FD');

        pdf.setFillColor(241, 245, 249);
        pdf.rect(rightBoxX, boxY, boxW, 6.5, 'F');
        pdf.setFont('helvetica', 'bold');
        pdf.setFontSize(8);
        pdf.setTextColor(15, 23, 42);
        pdf.text('DATOS DEL SERVICIO STARLINK', rightBoxX + 4, boxY + 4.8);

        pdf.setFont('helvetica', 'bold');
        pdf.setFontSize(7);
        pdf.setTextColor(100, 116, 139);
        pdf.text('Terminal(es) / Serial:', rightBoxX + 4, boxY + 11);
        pdf.setFont('courier', 'bold');
        pdf.setFontSize(8.5);
        pdf.setTextColor(15, 23, 42);
        pdf.text(payment.serial || 'N/A', rightBoxX + 4, boxY + 15);

        if (payment.nickname) {
            pdf.setFont('helvetica', 'italic');
            pdf.setFontSize(7.5);
            pdf.setTextColor(14, 165, 233);
            pdf.text('(' + payment.nickname + ')', rightBoxX + 4, boxY + 19);
        }

        const planY = payment.nickname ? 23.5 : 21.5;
        pdf.setFont('helvetica', 'bold');
        pdf.setFontSize(7);
        pdf.setTextColor(100, 116, 139);
        pdf.text('Plan Contratado:', rightBoxX + 4, boxY + planY);
        pdf.setFont('helvetica', 'normal');
        pdf.setFontSize(8);
        pdf.setTextColor(30, 41, 59);
        const planText = (payment.plan || payment.nombre_plan || 'Estándar') + (payment.country ? ' (' + payment.country + ')' : '');
        pdf.text(planText, rightBoxX + 4, boxY + planY + 4);

        if (payment.pay_day) {
            pdf.setFont('helvetica', 'bold');
            pdf.setFontSize(7);
            pdf.setTextColor(100, 116, 139);
            pdf.text('Día de Corte:', rightBoxX + boxW / 2 + 5, boxY + planY);
            pdf.setFont('helvetica', 'normal');
            pdf.setFontSize(8);
            pdf.setTextColor(30, 41, 59);
            pdf.text('Día ' + payment.pay_day + ' de cada mes', rightBoxX + boxW / 2 + 5, boxY + planY + 4);
        }

        // 5. Tabla de Conceptos / Detalle del Pago (usando jsPDF AutoTable)
        const monthsPaid = Math.max(1, parseInt(payment.months_paid || 1, 10));
        const totalAmount = parseFloat(payment.amount || 0);
        const unitPrice = monthsPaid > 0 ? (totalAmount / monthsPaid) : totalAmount;
        const curr = payment.currency || 'USD';

        const formatCurrency = (val) => Number(val).toLocaleString('es-VE', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' ' + curr;

        let conceptDescription = 'Suscripción de Servicio de Internet Satelital Starlink\nPlan: ' + (payment.plan || payment.nombre_plan || 'Estándar');
        if (payment.nickname) {
            conceptDescription += '\nIdentificador: ' + payment.nickname;
        }
        if (payment.serial) {
            conceptDescription += '\nTerminal Serial: ' + payment.serial;
        }

        const tableRows = [
            [
                '1',
                conceptDescription,
                monthsPaid === 1 ? '1 mes' : (monthsPaid + ' meses'),
                formatCurrency(unitPrice),
                formatCurrency(totalAmount)
            ]
        ];

        pdf.autoTable({
            startY: boxY + boxH + 6,
            head: [['ÍTEM', 'CONCEPTO / DESCRIPCIÓN DEL SERVICIO', 'PERIODO / MESES', 'TARIFA MENSUAL', 'SUBTOTAL']],
            body: tableRows,
            margin: { left: margin, right: margin },
            theme: 'grid',
            headStyles: {
                fillColor: [15, 23, 42], // #0f172a
                textColor: [255, 255, 255],
                fontStyle: 'bold',
                fontSize: 8,
                halign: 'center',
                cellPadding: 3
            },
            columnStyles: {
                0: { cellWidth: 14, halign: 'center' },
                1: { cellWidth: 'auto', halign: 'left' },
                2: { cellWidth: 32, halign: 'center' },
                3: { cellWidth: 32, halign: 'right' },
                4: { cellWidth: 34, halign: 'right', fontStyle: 'bold' }
            },
            styles: {
                fontSize: 8,
                textColor: [30, 41, 59],
                lineColor: [226, 232, 240],
                lineWidth: 0.2,
                cellPadding: 3.5
            }
        });

        const finalY = pdf.lastAutoTable.finalY || (boxY + boxH + 35);

        // 6. Resumen de Totales y Notas
        const summaryW = 76;
        const summaryX = pageWidth - margin - summaryW;
        const summaryY = finalY + 4;

        pdf.setFillColor(248, 250, 252);
        pdf.setDrawColor(226, 232, 240);
        pdf.roundedRect(summaryX, summaryY, summaryW, 25, 2, 2, 'FD');

        pdf.setFont('helvetica', 'normal');
        pdf.setFontSize(7.5);
        pdf.setTextColor(100, 116, 139);
        pdf.text('Subtotal:', summaryX + 4, summaryY + 5.5);
        pdf.setTextColor(15, 23, 42);
        pdf.text(formatCurrency(totalAmount), summaryX + summaryW - 4, summaryY + 5.5, { align: 'right' });

        pdf.setTextColor(100, 116, 139);
        pdf.text('Periodo cancelado:', summaryX + 4, summaryY + 10.5);
        pdf.setTextColor(14, 165, 233);
        pdf.setFont('helvetica', 'bold');
        pdf.text(monthsPaid === 1 ? '1 Mes' : (monthsPaid + ' Meses continuos'), summaryX + summaryW - 4, summaryY + 10.5, { align: 'right' });

        // Franja destacada para el TOTAL CANCELADO
        pdf.setFillColor(14, 165, 233); // #0ea5e9
        pdf.roundedRect(summaryX + 2, summaryY + 14, summaryW - 4, 8.5, 1.5, 1.5, 'F');
        pdf.setFont('helvetica', 'bold');
        pdf.setFontSize(8.5);
        pdf.setTextColor(255, 255, 255);
        pdf.text('TOTAL PAGADO:', summaryX + 4.5, summaryY + 19.5);
        pdf.setFontSize(9.5);
        pdf.text(formatCurrency(totalAmount), summaryX + summaryW - 4.5, summaryY + 19.5, { align: 'right' });

        // --- Lado izquierdo: Información de la transacción y términos ---
        const notesW = pageWidth - (margin * 2) - summaryW - 6;
        const notesX = margin;
        const notesY = summaryY;

        pdf.setFillColor(248, 250, 252);
        pdf.setDrawColor(226, 232, 240);
        pdf.roundedRect(notesX, notesY, notesW, 25, 2, 2, 'FD');

        pdf.setFont('helvetica', 'bold');
        pdf.setFontSize(7.5);
        pdf.setTextColor(15, 23, 42);
        pdf.text('CONDICIONES Y OBSERVACIONES:', notesX + 4, notesY + 5.5);

        pdf.setFont('helvetica', 'normal');
        pdf.setFontSize(7);
        pdf.setTextColor(71, 85, 105);
        const notesText = '• Comprobante emitido electrónicamente como constancia de recepción de fondos.\n' +
                          '• El pago cubre ' + (monthsPaid === 1 ? '1 mensualidad' : (monthsPaid + ' mensualidades continuas')) + ' del servicio Starlink.\n' +
                          '• La confirmación mantiene el servicio satelital debidamente activo y al día.\n' +
                          '• Conserve este recibo para cualquier gestión de soporte o control contable.';
        pdf.text(notesText, notesX + 4, notesY + 9.5);

        // 7. Sello y Certificación Digital (Box inferior)
        const stampY = summaryY + 29;
        pdf.setDrawColor(203, 213, 225);
        pdf.setLineDashPattern([1.5, 1.5], 0);
        pdf.roundedRect(margin, stampY, pageWidth - (margin * 2), 16, 2, 2, 'D');
        pdf.setLineDashPattern([], 0);

        pdf.setFont('helvetica', 'bold');
        pdf.setFontSize(8);
        pdf.setTextColor(14, 165, 233);
        pdf.text('CERTIFICACIÓN Y ESTADO DE LA CUENTA', margin + 5, stampY + 5);

        pdf.setFont('helvetica', 'normal');
        pdf.setFontSize(7.2);
        pdf.setTextColor(71, 85, 105);
        pdf.text('Estado del servicio: CUENTA AL DÍA • Validación de cobro registrada en sistema PCTRONIC.', margin + 5, stampY + 9.5);
        pdf.text('Emisor autorizado: PCTRONIC C.A. • Verificación digital de pago #' + numRecibo + ' • Soporte Técnico Especializado.', margin + 5, stampY + 13.5);

        // 8. Pie de página decorativo corporativo
        const footerY = pageHeight - 12;
        pdf.setDrawColor(226, 232, 240);
        pdf.setLineWidth(0.4);
        pdf.line(margin, footerY, pageWidth - margin, footerY);

        pdf.setFont('helvetica', 'normal');
        pdf.setFontSize(7);
        pdf.setTextColor(148, 163, 184);
        pdf.text('PCTRONIC C.A. • Sistema de Gestión de Conectividad Satelital Starlink • Emitido: ' + new Date().toLocaleString('es-VE'), margin, footerY + 4.5);
        pdf.text('Página 1 de 1', pageWidth - margin, footerY + 4.5, { align: 'right' });

        pdf.setFillColor(14, 165, 233);
        pdf.rect(0, pageHeight - 2, pageWidth, 2, 'F');

        // 9. Abrir en el Modal de Vista Previa (#pdfPreviewModal)
        const filename = 'recibo-pctronic-REC-' + numRecibo + '.pdf';
        const pdfBlobUrl = URL.createObjectURL(pdf.output('blob'));

        const previewFrame = document.getElementById('pdfPreviewFrame');
        const downloadLink = document.getElementById('pdfDownloadLink');
        const previewTitle = document.getElementById('pdfPreviewTitle');
        const waLink = document.getElementById('pdfWhatsAppLink');

        if (window.receiptPreviewUrl) {
            URL.revokeObjectURL(window.receiptPreviewUrl);
        }
        window.receiptPreviewUrl = pdfBlobUrl;

        if (previewFrame) previewFrame.src = pdfBlobUrl;
        if (downloadLink) {
            downloadLink.href = pdfBlobUrl;
            downloadLink.download = filename;
        }
        if (previewTitle) {
            previewTitle.innerHTML = '<i class="bi bi-receipt text-info me-2"></i>Recibo de Pago <span class="badge bg-info text-dark ms-2">REC-' + numRecibo + '</span>';
        }

        // Configurar botón de WhatsApp en el modal
        if (waLink) {
            waLink.classList.remove('d-none');
            waLink.href = buildReceiptWhatsAppUrl(payment, numRecibo);
        }

        bootstrap.Modal.getOrCreateInstance(document.getElementById('pdfPreviewModal')).show();
    }

    function bindReceiptButton(button) {
        if (button.dataset.receiptBound) return;
        button.dataset.receiptBound = 'true';
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const data = {
                id: this.dataset.id,
                serial: this.dataset.serial,
                nickname: this.dataset.nickname,
                client: this.dataset.client,
                client_ci: this.dataset.ci,
                client_phone: this.dataset.phone,
                plan: this.dataset.plan,
                plan_price: this.dataset.planPrice,
                country: this.dataset.country,
                pay_day: this.dataset.payDay,
                amount: this.dataset.amount ? parseFloat(this.dataset.amount) : 0,
                months_paid: parseInt(this.dataset.months || '1', 10),
                currency: this.dataset.currency || 'USD',
                date: this.dataset.date,
                status: this.dataset.status || 'Pendiente'
            };
            generatePaymentReceiptPdf(data);
        });
    }

    document.querySelectorAll('.btn-generate-receipt').forEach(bindReceiptButton);

    // Modal de exoneración de deuda por antena (tipo por mes)
    let currentExonerateCycles = [];
    let currentAntennaPrice = 0;
    let currentAntennaIsExempt = false;

    function renderExonerateMonthsList(cycles, planPrice) {
        const container = document.getElementById('exonerate_months_container');
        if (!container) return;
        container.innerHTML = '';

        if (!cycles || cycles.length === 0) {
            container.innerHTML = '<div class="text-center py-3 text-white-50"><i class="bi bi-info-circle me-1"></i>No hay meses adeudados ni pendientes para esta antena.</div>';
            updateExoneratePreview(0, cycles, planPrice);
            return;
        }

        cycles.forEach((cycle, index) => {
            const item = document.createElement('div');
            item.className = 'd-flex justify-content-between align-items-center p-2 mb-1 rounded border border-secondary border-opacity-25 bg-black bg-opacity-30 exonerate-cycle-item';
            item.style.cursor = 'pointer';
            item.dataset.index = index;

            let badgeHtml = '';
            if (cycle.is_past) {
                badgeHtml = '<span class="badge bg-danger-subtle text-danger border border-danger border-opacity-25 ms-1" style="font-size: 0.70rem;">Mes vencido (día ' + cycle.due_day + ')</span>';
            } else if (cycle.is_current) {
                badgeHtml = '<span class="badge bg-warning-subtle text-warning border border-warning border-opacity-25 ms-1" style="font-size: 0.70rem;">Mes en curso (día ' + cycle.due_day + ')</span>';
            } else {
                badgeHtml = '<span class="badge bg-secondary-subtle text-info border border-info border-opacity-25 ms-1" style="font-size: 0.70rem;">Próximo mes</span>';
            }

            item.innerHTML = `
                <div class="d-flex align-items-center gap-2">
                    <input class="form-check-input exonerate-cycle-check m-0" type="checkbox" data-index="${index}" id="cycle_check_${index}">
                    <label class="form-check-label mb-0 fw-semibold text-white" for="cycle_check_${index}" style="cursor: pointer;">
                        ${escapePaymentValue(cycle.label)}
                    </label>
                    ${badgeHtml}
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="text-white-50 small">$${Number(cycle.amount || planPrice).toFixed(2)}</span>
                    <span class="badge bg-secondary text-white-50 cycle-action-badge" style="font-size: 0.72rem;">Al cobro</span>
                </div>
            `;

            item.addEventListener('click', function(e) {
                if (e.target.tagName && e.target.tagName.toLowerCase() === 'input') return;
                const checkbox = this.querySelector('.exonerate-cycle-check');
                if (checkbox) {
                    checkbox.checked = !checkbox.checked;
                    handleCycleCheckChange(index, checkbox.checked);
                }
            });

            container.appendChild(item);
        });

        container.querySelectorAll('.exonerate-cycle-check').forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const idx = parseInt(this.dataset.index, 10);
                handleCycleCheckChange(idx, this.checked);
            });
        });

        // Por defecto preseleccionar todos los meses vencidos pasados
        let defaultOverdueCount = 0;
        cycles.forEach((c, idx) => {
            if (c.is_past) defaultOverdueCount = idx + 1;
        });

        if (defaultOverdueCount > 0) {
            selectCyclesUpTo(defaultOverdueCount - 1);
        } else {
            updateExoneratePreview(0, cycles, planPrice);
        }
    }

    function handleCycleCheckChange(idx, isChecked) {
        if (isChecked) {
            selectCyclesUpTo(idx);
        } else {
            unselectCyclesFrom(idx);
        }
    }

    function selectCyclesUpTo(targetIdx) {
        const checkboxes = document.querySelectorAll('.exonerate-cycle-check');
        checkboxes.forEach((cb, i) => {
            cb.checked = (i <= targetIdx);
        });
        updateExoneratePreview(targetIdx + 1, currentExonerateCycles, currentAntennaPrice);
    }

    function unselectCyclesFrom(targetIdx) {
        const checkboxes = document.querySelectorAll('.exonerate-cycle-check');
        checkboxes.forEach((cb, i) => {
            if (i >= targetIdx) {
                cb.checked = false;
            }
        });
        const checkedCount = Array.from(checkboxes).filter(cb => cb.checked).length;
        updateExoneratePreview(checkedCount, currentExonerateCycles, currentAntennaPrice);
    }

    function updateExoneratePreview(checkedCount, cycles, planPrice) {
        const previewCount = document.getElementById('exonerate_preview_count');
        const previewAmount = document.getElementById('exonerate_preview_amount');
        const previewRemainingCount = document.getElementById('exonerate_preview_remaining_count');
        const previewRemainingAmount = document.getElementById('exonerate_preview_remaining_amount');
        const previewStartLabel = document.getElementById('exonerate_preview_start_label');
        const hiddenInput = document.getElementById('exonerate_exempt_until_input');

        document.querySelectorAll('.exonerate-cycle-item').forEach((item, i) => {
            const badge = item.querySelector('.cycle-action-badge');
            if (badge) {
                if (i < checkedCount) {
                    badge.className = 'badge bg-warning text-dark cycle-action-badge';
                    badge.innerHTML = '<i class="bi bi-shield-check me-1"></i>Exonerar';
                    item.classList.add('border-warning', 'border-opacity-50');
                } else {
                    badge.className = 'badge bg-secondary text-white-50 cycle-action-badge';
                    badge.innerHTML = '<i class="bi bi-cash me-1"></i>Al cobro';
                    item.classList.remove('border-warning', 'border-opacity-50');
                }
            }
        });

        const totalCycles = cycles ? cycles.length : 0;
        const remainingCycles = Math.max(0, totalCycles - checkedCount);

        if (previewCount) {
            previewCount.textContent = checkedCount + (checkedCount === 1 ? ' mes' : ' meses');
        }
        if (previewAmount) {
            const exemptAmt = checkedCount * planPrice;
            previewAmount.textContent = '$' + exemptAmt.toFixed(2) + ' perdonado';
        }

        if (previewRemainingCount) {
            previewRemainingCount.textContent = remainingCycles + (remainingCycles === 1 ? ' mes' : ' meses');
        }
        if (previewRemainingAmount) {
            const remainingAmt = remainingCycles * planPrice;
            previewRemainingAmount.textContent = '$' + remainingAmt.toFixed(2) + ' pendiente';
        }

        if (checkedCount > 0 && cycles && cycles[checkedCount - 1]) {
            const targetCycle = cycles[checkedCount - 1];
            if (hiddenInput) {
                hiddenInput.value = targetCycle.next_start;
            }
            if (previewStartLabel) {
                if (checkedCount < totalCycles && cycles[checkedCount]) {
                    previewStartLabel.innerHTML = '<span class="text-info">' + escapePaymentValue(cycles[checkedCount].label) + '</span>';
                } else {
                    previewStartLabel.innerHTML = '<span class="text-success"><i class="bi bi-check-all me-1"></i>Paz y salvo total</span>';
                }
            }
        } else {
            if (hiddenInput) {
                hiddenInput.value = '';
            }
            if (previewStartLabel) {
                if (totalCycles > 0 && cycles[0]) {
                    previewStartLabel.textContent = cycles[0].label + ' (Sin cambios)';
                } else {
                    previewStartLabel.textContent = '-';
                }
            }
        }
    }

    const btnSelectOverdue = document.getElementById('btn_select_overdue_only');
    if (btnSelectOverdue) {
        btnSelectOverdue.addEventListener('click', function() {
            let lastOverdueIdx = -1;
            currentExonerateCycles.forEach((c, idx) => {
                if (c.is_past) lastOverdueIdx = idx;
            });
            if (lastOverdueIdx >= 0) {
                selectCyclesUpTo(lastOverdueIdx);
            } else {
                showPaymentMessage('No hay meses anteriores vencidos.', 'info');
            }
        });
    }

    const btnSelectAllMonths = document.getElementById('btn_select_all_months');
    if (btnSelectAllMonths) {
        btnSelectAllMonths.addEventListener('click', function() {
            if (currentExonerateCycles.length > 0) {
                selectCyclesUpTo(currentExonerateCycles.length - 1);
            }
        });
    }

    const btnUnselectAllMonths = document.getElementById('btn_unselect_all_months');
    if (btnUnselectAllMonths) {
        btnUnselectAllMonths.addEventListener('click', function() {
            unselectCyclesFrom(0);
        });
    }

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
            let unpaidCycles = [];

            try {
                unpaidCycles = JSON.parse(this.getAttribute('data-unpaid-cycles') || '[]');
            } catch (e) {
                unpaidCycles = [];
            }

            currentExonerateCycles = unpaidCycles;
            currentAntennaPrice = price;
            currentAntennaIsExempt = isExempt;

            const idInput = document.getElementById('exonerate_id_starlink');
            if (idInput) idInput.value = id;
            const removeInput = document.getElementById('exonerate_remove_input');
            if (removeInput) removeInput.value = '0';
            const summarySerial = document.getElementById('exonerate_summary_serial');
            if (summarySerial) summarySerial.textContent = serial;
            const summaryNick = document.getElementById('exonerate_summary_nickname');
            if (summaryNick) summaryNick.textContent = nickname ? ('“' + nickname + '”') : '';
            const summaryClient = document.getElementById('exonerate_summary_client');
            if (summaryClient) summaryClient.textContent = cliente || 'Sin asignar';
            const summaryPlan = document.getElementById('exonerate_summary_plan');
            if (summaryPlan) summaryPlan.textContent = plan + (price > 0 ? (' ($' + price.toFixed(2) + '/mes)') : '');

            const debtContainer = document.getElementById('exonerate_summary_debt');
            if (debtContainer) {
                if (overdueMonths > 0) {
                    debtContainer.innerHTML = '<span class="badge bg-danger text-white"><i class="bi bi-exclamation-triangle-fill me-1"></i>' + overdueMonths + (overdueMonths === 1 ? ' mes atrasado' : ' meses atrasados') + '</span> <span class="text-danger fw-bold ms-1">($' + overdueAmount.toFixed(2) + ')</span>';
                } else {
                    debtContainer.innerHTML = '<span class="badge bg-success text-dark"><i class="bi bi-check-circle-fill me-1"></i>Al día (Sin deuda vencida)</span>';
                }
            }

            const alertBox = document.getElementById('exonerate_active_alert');
            const btnRemove = document.getElementById('btn_remove_exemption');
            const noteWrap = document.getElementById('exonerate_active_note_wrap');
            const noteSpan = document.getElementById('exonerate_active_note');
            const noteInput = document.getElementById('exonerate_note');

            if (alertBox && isExempt && exemptUntil) {
                alertBox.classList.remove('d-none');
                let parts = exemptUntil.split('-');
                let dateFormatted = parts.length === 3 ? (parts[2] + '/' + parts[1] + '/' + parts[0]) : exemptUntil;
                const activeDate = document.getElementById('exonerate_active_date');
                if (activeDate) activeDate.textContent = dateFormatted;

                if (exemptNote && noteWrap && noteSpan) {
                    noteWrap.style.display = 'block';
                    noteSpan.textContent = exemptNote;
                    if (noteInput) noteInput.value = exemptNote;
                } else {
                    if (noteWrap) noteWrap.style.display = 'none';
                    if (noteInput) noteInput.value = '';
                }

                if (btnRemove) btnRemove.classList.remove('d-none');
            } else {
                if (alertBox) alertBox.classList.add('d-none');
                if (noteWrap) noteWrap.style.display = 'none';
                if (noteInput) noteInput.value = '';
                if (btnRemove) btnRemove.classList.add('d-none');
            }

            // Renderizar la lista de meses
            renderExonerateMonthsList(unpaidCycles, price);
        });
    });

    const formExonerate = document.getElementById('formExonerateDebt');
    if (formExonerate) {
        formExonerate.addEventListener('submit', function(e) {
            const hiddenUntil = document.getElementById('exonerate_exempt_until_input');
            const removeInput = document.getElementById('exonerate_remove_input');
            if (removeInput && removeInput.value === '1') {
                return;
            }

            if (!hiddenUntil || !hiddenUntil.value) {
                e.preventDefault();
                if (currentAntennaIsExempt) {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            title: '¿Quitar la exoneración?',
                            text: 'No has seleccionado ningún mes para exonerar. ¿Deseas eliminar la exoneración previa y restablecer la deuda original?',
                            icon: 'question',
                            showCancelButton: true,
                            confirmButtonColor: '#dc3545',
                            cancelButtonColor: '#6c757d',
                            confirmButtonText: 'Sí, quitar exoneración',
                            cancelButtonText: 'Cancelar',
                            customClass: { popup: 'swal-custom-dark' }
                        }).then(res => {
                            if (res.isConfirmed) {
                                if (removeInput) removeInput.value = '1';
                                formExonerate.submit();
                            }
                        });
                    } else if (confirm('¿Deseas quitar la exoneración existente?')) {
                        if (removeInput) removeInput.value = '1';
                        formExonerate.submit();
                    }
                } else {
                    showPaymentMessage('Selecciona al menos un mes para exonerar o pulsa Cancelar.', 'warning');
                }
            }
        });
    }

    const btnRemoveExemption = document.getElementById('btn_remove_exemption');
    if (btnRemoveExemption) {
        btnRemoveExemption.addEventListener('click', function() {
            const executeRemove = function() {
                const removeInput = document.getElementById('exonerate_remove_input');
                if (removeInput) removeInput.value = '1';
                const form = document.getElementById('formExonerateDebt');
                if (form) form.submit();
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
});
</script>
