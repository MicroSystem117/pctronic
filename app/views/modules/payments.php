<?php
$userRole = isset($_SESSION['user_role']) ? $_SESSION['user_role'] : '';
$isLoggedIn = isset($_SESSION['user_id']);
$canReview = $userRole === 'Administrador' || $userRole === 'Moderador';
$canDelete = $canReview;
?>
<div class="d-flex justify-content-between align-items-center mb-4">
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

<div class="mb-3 d-flex justify-content-end gap-2 flex-wrap">
    <div class="input-group input-group-sm w-auto">
        <label class="input-group-text bg-secondary text-white border-secondary" for="search_payments_type">Buscar por</label>
        <select id="search_payments_type" class="form-select form-select-sm">
            <option value="all">Todos</option>
            <option value="cliente">Cliente</option>
            <option value="antena">Antena</option>
            <option value="plan">Plan</option>
            <option value="currency">Moneda</option>
            <option value="status">Estado</option>
        </select>
    </div>
    <input id="search_payments" class="form-control form-control-sm w-25" placeholder="Buscar...">
    <form class="d-flex align-items-center" method="GET" action="index.php?url=payments">
        <input type="hidden" name="url" value="payments">
        <label class="form-label me-2 text-white-50 small">Desde</label>
        <input type="date" name="from" class="form-control form-control-sm me-2" value="<?php echo isset($_GET['from']) ? htmlspecialchars($_GET['from']) : ''; ?>">
        <label class="form-label me-2 text-white-50 small">Hasta</label>
        <input type="date" name="to" class="form-control form-control-sm me-2" value="<?php echo isset($_GET['to']) ? htmlspecialchars($_GET['to']) : ''; ?>">
        <button class="btn btn-sm btn-outline-light me-2" type="submit">Filtrar</button>
        <a class="btn btn-sm btn-outline-secondary" href="index.php?url=payments">Limpiar</a>
    </form>
</div>

<div class="row">
    <div class="col-lg-6 mb-4">
        <div class="card card-custom p-3 h-100">
            <h5 class="mb-3">Estado de Pago por Antena</h5>
            <div class="table-responsive">
                <table class="table table-dark table-hover mb-0 pdf-exportable" data-pdf-title="Estado de pago por antena">
                    <thead class="table-light">
                        <tr>
                            <th>Serial</th>
                            <th>Cliente</th>
                            <th>Vence</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($data['antennas'])): ?>
                            <?php foreach ($data['antennas'] as $antena): ?>
                                <?php
                                    $statusLabel = '<span class="text-white-50">-</span>';
                                    if (isset($antena['pay']) && $antena['pay'] !== null && $antena['pay'] !== '') {
                                        $dueDay = intval($antena['pay']);
                                        $today = new DateTime();
                                        $isPaid = false;

                                        if (!empty($antena['last_payment_date'])) {
                                            $lastPaymentDate = DateTime::createFromFormat('Y-m-d', $antena['last_payment_date']);
                                            if ($lastPaymentDate && $lastPaymentDate->format('Y-m') === $today->format('Y-m')) {
                                                $isPaid = true;
                                            }
                                        }

                                        if ($isPaid) {
                                            $statusLabel = '<span class="badge bg-success text-dark">Pagado</span>';
                                        } else {
                                            if (intval($today->format('j')) <= $dueDay) {
                                                $statusLabel = '<span class="badge bg-warning text-dark">Pendiente</span>';
                                            } else {
                                                $statusLabel = '<span class="badge bg-danger text-white">Atrasado</span>';
                                            }
                                        }
                                    }
                                ?>
                                <tr data-serial="<?php echo htmlspecialchars($antena['serial'], ENT_QUOTES); ?>"
                                    data-cliente="<?php echo htmlspecialchars($antena['cliente'], ENT_QUOTES); ?>"
                                    data-plan="<?php echo htmlspecialchars($antena['nombre_plan'], ENT_QUOTES); ?>"
                                    data-status="<?php echo strip_tags($statusLabel); ?>"
                                    data-pay="<?php echo htmlspecialchars($antena['pay'] ?? '', ENT_QUOTES); ?>">
                                    <td><code><?php echo $antena['serial']; ?></code></td>
                                    <td><?php echo $antena['cliente']; ?></td>
                                    <td><?php echo isset($antena['pay']) && $antena['pay'] !== null && $antena['pay'] !== '' ? htmlspecialchars(intval($antena['pay'])) : '<span class="text-white-50">N/A</span>'; ?></td>
                                    <td><?php echo $statusLabel; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="text-center py-4 text-muted">No hay antenas disponibles.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-6 mb-4">
        <div class="card card-custom p-3 h-100">
            <h5 class="mb-3">Historial de Pagos</h5>
            <div class="table-responsive">
                <table class="table table-dark table-hover mb-0 pdf-exportable payment-history-table" data-pdf-title="Historial de pagos">
                    <thead class="table-light">
                        <tr>
                            <th>Antena</th>
                            <th>Cliente</th>
                            <th>Monto</th>
                            <th>Moneda</th>
                            <th>Fecha Pago</th>
                            <th>Estado</th>
                            <th>Comprobante</th>
                            <?php if ($canDelete || $canReview): ?>
                                <th>Acciones</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($data['payments'])): ?>
                            <?php foreach ($data['payments'] as $payment): ?>
                                <tr data-payment-id="<?php echo (int) $payment['id_payment']; ?>"
                                    data-serial="<?php echo htmlspecialchars($payment['serial'], ENT_QUOTES); ?>"
                                    data-cliente="<?php echo htmlspecialchars($payment['cliente'], ENT_QUOTES); ?>"
                                    data-plan="<?php echo htmlspecialchars($payment['nombre_plan'], ENT_QUOTES); ?>"
                                    data-currency="<?php echo htmlspecialchars($payment['currency'], ENT_QUOTES); ?>"
                                    data-status="<?php echo htmlspecialchars($payment['status'] ?? 'Pendiente', ENT_QUOTES); ?>"
                                    data-payment-date="<?php echo htmlspecialchars($payment['payment_date'], ENT_QUOTES); ?>">
                                    <td><code><?php echo $payment['serial']; ?></code></td>
                                    <td><?php echo $payment['cliente']; ?></td>
                                    <td><?php echo number_format($payment['amount'], 2, ',', '.'); ?></td>
                                    <td><?php echo htmlspecialchars($payment['currency']); ?></td>
                                    <td><?php echo date('d/m/Y', strtotime($payment['payment_date'])); ?></td>
                                    <td>
                                        <?php
                                            $paymentStatus = $payment['status'] ?? 'Pendiente';
                                            $statusClass = $paymentStatus === 'Aprobado' ? 'bg-success text-dark' : ($paymentStatus === 'Rechazado' ? 'bg-danger text-white' : 'bg-warning text-dark');
                                        ?>
                                        <span class="badge <?php echo $statusClass; ?>"><?php echo htmlspecialchars($paymentStatus); ?></span>
                                    </td>
                                    <td>
                                        <?php if (!empty($payment['receipt_path'])): ?>
                                            <a href="<?php echo htmlspecialchars($payment['receipt_path'], ENT_QUOTES); ?>" target="_blank" rel="noopener" class="btn btn-sm btn-outline-info" title="Ver comprobante"><i class="bi bi-paperclip"></i></a>
                                        <?php else: ?>
                                            <span class="text-white-50">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <?php if ($canDelete || $canReview): ?>
                                        <td>
                                            <?php if ($canReview && ($payment['status'] ?? '') === 'Pendiente'): ?>
                                                <button type="button" class="btn btn-sm btn-outline-info payment-review-button"
                                                        data-bs-toggle="modal" data-bs-target="#modalReviewPayment"
                                                    data-id="<?php echo $payment['id_payment']; ?>"
                                                        data-serial="<?php echo htmlspecialchars($payment['serial'], ENT_QUOTES); ?>"
                                                        data-client="<?php echo htmlspecialchars($payment['cliente'], ENT_QUOTES); ?>"
                                                        data-amount="<?php echo htmlspecialchars(number_format($payment['amount'], 2, ',', '.'), ENT_QUOTES); ?>"
                                                        data-currency="<?php echo htmlspecialchars($payment['currency'], ENT_QUOTES); ?>"
                                                        data-date="<?php echo htmlspecialchars(date('d/m/Y', strtotime($payment['payment_date'])), ENT_QUOTES); ?>"
                                                        data-receipt="<?php echo htmlspecialchars($payment['receipt_path'] ?? '', ENT_QUOTES); ?>"
                                                        title="Revisar pago"><i class="bi bi-search me-1"></i>Revisar</button>
                                                <a href="index.php?url=payments&action=approve&id=<?php echo $payment['id_payment']; ?>" class="btn btn-sm btn-success payment-action" title="Aprobar pago"><i class="bi bi-check-lg"></i></a>
                                                <a href="index.php?url=payments&action=reject&id=<?php echo $payment['id_payment']; ?>" class="btn btn-sm btn-warning payment-action" title="Rechazar pago"><i class="bi bi-x-lg"></i></a>
                                            <?php endif; ?>
                                            <?php if ($canDelete): ?>
                                                <a href="index.php?url=payments&action=delete&id=<?php echo $payment['id_payment']; ?>" class="btn btn-sm btn-danger payment-delete-action" title="Eliminar pago"><i class="bi bi-trash"></i></a>
                                            <?php endif; ?>
                                        </td>
                                    <?php endif; ?>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="<?php echo ($canDelete || $canReview) ? 8 : 7; ?>" class="text-center py-4 text-muted">No hay pagos registrados.</td>
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
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content card-custom text-white">
            <div class="modal-header border-secondary">
                <h5 class="modal-title"><i class="bi bi-search me-2"></i>Revisar pago</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <dl class="row mb-3">
                    <dt class="col-sm-4">Antena</dt><dd class="col-sm-8" id="review_payment_serial"></dd>
                    <dt class="col-sm-4">Cliente</dt><dd class="col-sm-8" id="review_payment_client"></dd>
                    <dt class="col-sm-4">Monto</dt><dd class="col-sm-8" id="review_payment_amount"></dd>
                    <dt class="col-sm-4">Fecha</dt><dd class="col-sm-8" id="review_payment_date"></dd>
                </dl>
                <div id="review_payment_receipt_container" class="d-none">
                    <h6>Comprobante</h6>
                    <iframe id="review_payment_receipt" class="w-100 border border-secondary rounded" style="height: 420px;" title="Comprobante del pago"></iframe>
                </div>
                <p id="review_payment_no_receipt" class="text-white-50 mb-0">Este pago no tiene comprobante adjunto.</p>
            </div>
            <div class="modal-footer border-secondary">
                <a id="review_payment_reject" class="btn btn-warning" href="#"><i class="bi bi-x-lg me-1"></i>Rechazar</a>
                <a id="review_payment_approve" class="btn btn-success" href="#"><i class="bi bi-check-lg me-1"></i>Aprobar</a>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<?php if ($isLoggedIn): ?>
<div class="modal fade" id="modalPayment" data-bs-backdrop="static" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
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
                        <input type="search" class="form-control form-control-sm mb-2" id="payment_client_search" placeholder="Buscar antenas por cliente..." autocomplete="off">
                        <div class="border border-secondary rounded p-2" id="payment_antennas" style="max-height: 260px; overflow-y: auto;">
                            <?php foreach ($data['antennas'] as $antena): ?>
                                <div class="form-check payment-antenna-option">
                                    <input class="form-check-input payment-antenna"
                                           type="checkbox"
                                           name="antenna_ids[]"
                                           value="<?php echo $antena['id_starlink']; ?>"
                                           data-client="<?php echo htmlspecialchars($antena['cliente'], ENT_QUOTES); ?>"
                                           data-payday="<?php echo htmlspecialchars($antena['pay'] ?? '', ENT_QUOTES); ?>"
                                           data-price="<?php echo htmlspecialchars($antena['plan_price'] ?? '0', ENT_QUOTES); ?>"
                                           id="payment_antenna_<?php echo $antena['id_starlink']; ?>">
                                    <label class="form-check-label" for="payment_antenna_<?php echo $antena['id_starlink']; ?>">
                                        <?php echo htmlspecialchars($antena['serial']); ?> — <?php echo htmlspecialchars($antena['cliente']); ?>
                                    </label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Monto por antena <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" min="0" class="form-control" id="payment_amount" name="amount" placeholder="Se calcula según el plan" readonly required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Moneda <span class="text-danger">*</span></label>
                            <select class="form-select" name="currency" required>
                                <option value="">-- Seleccionar --</option>
                                <option value="USD">USD</option>
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

<script>
document.addEventListener('app:content-ready', function() {
    const searchInput = document.getElementById('search_payments');
    const searchType = document.getElementById('search_payments_type');
    const rows = document.querySelectorAll('.card .table tbody tr');

    function filterRows() {
        const q = searchInput.value.trim().toLowerCase();
        const type = searchType.value;

        rows.forEach(row => {
            const value = row.dataset[type] || row.textContent;
            row.style.display = q === '' || value.toLowerCase().includes(q) ? '' : 'none';
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
    const paymentClient = document.getElementById('payment_client');
    const paymentPayday = document.getElementById('payment_payday');
    const paymentSelectAll = document.getElementById('payment_select_all');
    const reviewButtons = document.querySelectorAll('.payment-review-button');
    const reviewReceiptContainer = document.getElementById('review_payment_receipt_container');
    const reviewReceipt = document.getElementById('review_payment_receipt');
    const reviewNoReceipt = document.getElementById('review_payment_no_receipt');
    const paymentForm = document.getElementById('payment_form');

    function showPaymentMessage(message, type) {
        document.querySelectorAll('.alert').forEach(existingMessage => {
            if (existingMessage.textContent.includes(message)) existingMessage.remove();
        });
        const messageBox = document.createElement('div');
        messageBox.className = 'alert alert-' + type + ' alert-dismissible fade show payment-live-message';
        messageBox.dataset.message = message;
        messageBox.setAttribute('role', 'alert');
        messageBox.innerHTML = message + '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>';
        (document.querySelector('main') || document.body).prepend(messageBox);
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
            ? '<a href="' + escapePaymentValue(payment.receipt) + '" target="_blank" rel="noopener" class="btn btn-sm btn-outline-info" title="Ver comprobante"><i class="bi bi-paperclip"></i></a>'
            : '<span class="text-white-50">-</span>';
                const actions = <?php echo $canReview ? 'true' : 'false'; ?>
                        ? (payment.status === 'Pendiente'
                                ? '<button type="button" class="btn btn-sm btn-outline-info payment-review-button" data-bs-toggle="modal" data-bs-target="#modalReviewPayment" data-id="' + payment.id + '" data-serial="' + escapePaymentValue(payment.serial) + '" data-client="' + escapePaymentValue(payment.client) + '" data-amount="' + escapePaymentValue(Number(payment.amount).toLocaleString('es-VE', { minimumFractionDigits: 2, maximumFractionDigits: 2 })) + '" data-currency="' + escapePaymentValue(payment.currency) + '" data-date="' + escapePaymentValue(payment.date) + '" data-receipt="' + escapePaymentValue(payment.receipt) + '" title="Revisar pago"><i class="bi bi-search me-1"></i>Revisar</button>' +
                                    '<a href="index.php?url=payments&action=approve&id=' + payment.id + '" class="btn btn-sm btn-success payment-action" title="Aprobar pago"><i class="bi bi-check-lg"></i></a>' +
                                    '<a href="index.php?url=payments&action=reject&id=' + payment.id + '" class="btn btn-sm btn-warning payment-action" title="Rechazar pago"><i class="bi bi-x-lg"></i></a>'
                                : '') +
                            '<a href="index.php?url=payments&action=delete&id=' + payment.id + '" class="btn btn-sm btn-danger payment-delete-action" title="Eliminar pago"><i class="bi bi-trash"></i></a>'
                        : '';
        const row = document.createElement('tr');
        row.dataset.paymentId = payment.id;
        row.dataset.status = payment.status;
        row.innerHTML = '<td><code>' + escapePaymentValue(payment.serial) + '</code></td>' +
            '<td>' + escapePaymentValue(payment.client) + '</td>' +
            '<td>' + Number(payment.amount).toLocaleString('es-VE', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + '</td>' +
            '<td>' + escapePaymentValue(payment.currency) + '</td>' +
            '<td>' + escapePaymentValue(payment.date) + '</td>' +
            '<td><span class="badge ' + statusClass + '">' + escapePaymentValue(payment.status) + '</span></td>' +
            '<td>' + receipt + '</td>' +
            (actions ? '<td>' + actions + '</td>' : '');
        return row;
    }

    let paymentSyncInProgress = false;

    function synchronizePayments() {
        if (document.hidden || paymentSyncInProgress) return;
        paymentSyncInProgress = true;
        fetch('index.php?url=payments&action=sync', { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(response => response.json())
            .then(payments => {
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
                                if (!window.confirm('¿Eliminar este pago?')) return;
                                const row = this.closest('tr');
                                processPaymentAction(this, row, () => row.remove());
                            });
                        }
                    });
                    historyBody.querySelectorAll('.payment-review-button').forEach(bindReviewButton);
                }
            })
                .catch(() => {})
                .finally(() => { paymentSyncInProgress = false; });
    }

            window.setInterval(synchronizePayments, 2000);
            document.addEventListener('visibilitychange', function() {
            if (!document.hidden) synchronizePayments();
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
                .then(response => response.json())
                .then(result => {
                    if (!result.success) throw new Error();
                    bootstrap.Modal.getInstance(document.getElementById('modalPayment')).hide();
                    this.reset();
                    paymentCheckboxes.forEach(checkbox => checkbox.closest('.payment-antenna-option').classList.remove('payment-antenna-selected'));
                    paymentAmount.value = '';
                    window.history.replaceState({}, document.title, 'index.php?url=payments');
                    synchronizePayments();
                    showPaymentMessage(result.status === 'payment_pending' ? 'Pago cargado y enviado a revisión.' : 'Pago registrado correctamente.', 'success');
                })
                .catch(() => showPaymentMessage('No se pudo registrar el pago.', 'danger'))
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
            if (!window.confirm('¿Eliminar este pago?')) return;
            const row = this.closest('tr');
            processPaymentAction(this, row, () => {
                row.remove();
                showPaymentMessage('Pago eliminado correctamente.', 'success');
            });
        });
    });

    if (paymentAntennas) {
        function updatePaymentSummary() {
            const selected = Array.from(paymentCheckboxes).filter(checkbox => checkbox.checked);
            paymentCheckboxes.forEach(checkbox => {
                checkbox.closest('.payment-antenna-option').classList.toggle('payment-antenna-selected', checkbox.checked);
            });
            const total = selected.reduce((sum, option) => sum + (parseFloat(option.dataset.price) || 0), 0);
            const clients = [...new Set(selected.map(option => option.dataset.client || 'Cliente no identificado'))];
            paymentAmount.value = selected.length ? total.toFixed(2) : '';
            paymentClient.value = selected.length
                ? clients.join(', ')
                : 'Selecciona una o varias antenas';
            paymentPayday.value = selected.length
                ? selected.map(option => option.dataset.payday ? 'Día ' + option.dataset.payday : 'No configurado').join(', ')
                : 'Selecciona una o varias antenas';
            }

            paymentCheckboxes.forEach(checkbox => checkbox.addEventListener('change', updatePaymentSummary));

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
                    const client = option.querySelector('.payment-antenna').dataset.client.toLowerCase();
                    option.style.display = query === '' || client.includes(query) ? '' : 'none';
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
                paymentClient.value = 'Selecciona una o varias antenas';
                paymentPayday.value = 'Selecciona una o varias antenas';
                paymentAmount.value = '';
                if (paymentSelectAll) {
                    paymentSelectAll.innerHTML = '<i class="bi bi-check2-square me-1"></i>Seleccionar todas';
                }
            });
        }
    }

    function bindReviewButton(button) {
        if (button.dataset.bound) return;
        button.dataset.bound = 'true';
        button.addEventListener('click', function() {
            document.getElementById('review_payment_serial').textContent = this.dataset.serial;
            document.getElementById('review_payment_client').textContent = this.dataset.client;
            document.getElementById('review_payment_amount').textContent = this.dataset.amount + ' ' + this.dataset.currency;
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
});
</script>
