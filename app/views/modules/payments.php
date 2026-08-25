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
                <table class="table table-dark table-hover mb-0 pdf-exportable" data-pdf-title="Historial de pagos">
                    <thead class="table-light">
                        <tr>
                            <th>Antena</th>
                            <th>Cliente</th>
                            <th>Monto</th>
                            <th>Moneda</th>
                            <th>Fecha Pago</th>
                            <th>Estado</th>
                            <?php if ($canDelete || $canReview): ?>
                                <th>Acciones</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($data['payments'])): ?>
                            <?php foreach ($data['payments'] as $payment): ?>
                                <tr data-serial="<?php echo htmlspecialchars($payment['serial'], ENT_QUOTES); ?>"
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
                                    <?php if ($canDelete || $canReview): ?>
                                        <td>
                                            <?php if ($canReview && ($payment['status'] ?? '') === 'Pendiente'): ?>
                                                <a href="index.php?url=payments&action=approve&id=<?php echo $payment['id_payment']; ?>" class="btn btn-sm btn-success" title="Aprobar pago"><i class="bi bi-check-lg"></i></a>
                                                <a href="index.php?url=payments&action=reject&id=<?php echo $payment['id_payment']; ?>" class="btn btn-sm btn-warning" title="Rechazar pago"><i class="bi bi-x-lg"></i></a>
                                            <?php endif; ?>
                                            <?php if ($canDelete): ?>
                                                <a href="index.php?url=payments&action=delete&id=<?php echo $payment['id_payment']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('¿Eliminar este pago?');" title="Eliminar pago"><i class="bi bi-trash"></i></a>
                                            <?php endif; ?>
                                        </td>
                                    <?php endif; ?>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="<?php echo ($canDelete || $canReview) ? 7 : 6; ?>" class="text-center py-4 text-muted">No hay pagos registrados.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php if ($isLoggedIn): ?>
<div class="modal fade" id="modalPayment" data-bs-backdrop="static" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content card-custom text-white">
            <div class="modal-header border-secondary">
                <h5 class="modal-title"><i class="bi bi-wallet2 me-2"></i> Registrar Pago</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <form action="index.php?url=payments" method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Antena</label>
                        <select class="form-select" id="payment_antenna" name="antenna_id" required>
                            <option value="">-- Seleccionar antena --</option>
                            <?php foreach ($data['antennas'] as $antena): ?>
                                <option value="<?php echo $antena['id_starlink']; ?>"
                                        data-client="<?php echo htmlspecialchars($antena['cliente'], ENT_QUOTES); ?>"
                                        data-payday="<?php echo htmlspecialchars($antena['pay'] ?? '', ENT_QUOTES); ?>"
                                        data-serial="<?php echo htmlspecialchars($antena['serial'], ENT_QUOTES); ?>">
                                    <?php echo $antena['serial']; ?> — <?php echo $antena['cliente']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Monto <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" class="form-control" name="amount" placeholder="Ej: 120.00" required>
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
                            <input type="text" class="form-control" id="payment_client" disabled>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Día de cobro configurado</label>
                        <input type="text" class="form-control" id="payment_payday" disabled>
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
document.addEventListener('DOMContentLoaded', function() {
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

    const paymentAntenna = document.getElementById('payment_antenna');
    const paymentClient = document.getElementById('payment_client');
    const paymentPayday = document.getElementById('payment_payday');

    if (paymentAntenna) {
        paymentAntenna.addEventListener('change', function() {
            const selected = this.selectedOptions[0];
            paymentClient.value = selected.dataset.client || '';
            paymentPayday.value = selected.dataset.payday ? 'Día ' + selected.dataset.payday : 'No configurado';
        });

        const modal = document.getElementById('modalPayment');
        if (modal) {
            modal.addEventListener('show.bs.modal', function () {
                paymentAntenna.value = '';
                paymentClient.value = '';
                paymentPayday.value = '';
            });
        }
    }
});
</script>
