<?php
$userRole = isset($_SESSION['user_role']) ? $_SESSION['user_role'] : '';
$isAdmin = $userRole === 'Administrador';
$isExpectador = !empty($data['isExpectador']) || ($userRole === 'Expectador');
$hasSecQuestions = isset($data['has_sec_questions']) ? $data['has_sec_questions'] : false;

$mrr = isset($data['mrr']) ? (float)$data['mrr'] : 0.0;
$collectedThisMonth = isset($data['collected_this_month']) ? (float)$data['collected_this_month'] : 0.0;
$debtOverview = isset($data['debt_overview']) ? $data['debt_overview'] : ['total_activas' => 0, 'al_dia' => 0, 'con_deuda' => 0, 'total_deuda' => 0.0];
$collectionTrend = isset($data['collection_trend']) ? $data['collection_trend'] : [];
$upcomingExpirations = isset($data['upcoming_expirations']) ? $data['upcoming_expirations'] : [];
$clientSummary = isset($data['client_summary']) ? $data['client_summary'] : null;
?>

<div class="mb-4 d-flex flex-wrap justify-content-between align-items-center gap-3">
    <div>
        <h2 class="mb-1"><i class="bi bi-speedometer2 text-primary me-2"></i><?php echo $isExpectador ? 'Portal de Autogestión' : 'Panel de Control Principal'; ?></h2>
        <p class="text-white-50 mb-0">
            <?php echo $isExpectador ? 'Bienvenido, ' . htmlspecialchars($_SESSION['user'] ?? 'Cliente') . ' • Consulta tus servicios y pagos.' : 'Métricas financieras, gestión técnica de antenas y cobranzas de Starlink Control.'; ?>
        </p>
    </div>
    <?php if ($isExpectador): ?>
        <a href="index.php?url=payments" class="btn btn-success">
            <i class="bi bi-cash-coin me-1"></i> Reportar Pago
        </a>
    <?php else: ?>
        <div class="d-flex gap-2">
            <a href="index.php?url=payments" class="btn btn-sm btn-outline-success">
                <i class="bi bi-cash-stack me-1"></i> Pagos
            </a>
            <a href="index.php?url=antenas" class="btn btn-sm btn-outline-primary">
                <i class="bi bi-broadcast me-1"></i> Antenas
            </a>
        </div>
    <?php endif; ?>
</div>

<?php if (!$hasSecQuestions): ?>
    <div class="alert alert-warning alert-dismissible fade show" role="alert">
        <i class="bi bi-shield-exclamation me-1"></i>
        <strong>Seguridad:</strong> Aún no tienes preguntas de seguridad configuradas.
        <a href="index.php?url=auth/manage_security" class="alert-link">Configurar ahora</a>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Cerrar"></button>
    </div>
<?php endif; ?>

<?php if (!$isExpectador): ?>
    <!-- ==========================================
         MÉTRICAS EJECUTIVAS Y FINANCIERAS (ADMIN/MOD)
         ========================================== -->
    <div class="row g-3 mb-4">
        <!-- MRR: Facturación Mensual Proyectada -->
        <div class="col-sm-6 col-xl-3">
            <div class="card card-custom p-3 h-100 border-start border-primary border-4 shadow-sm">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-white-50 small text-uppercase fw-bold">Facturación Mensual (MRR)</div>
                        <h3 class="fw-bold mb-0 text-white mt-1">$<?php echo number_format($mrr, 2); ?></h3>
                        <div class="text-white-50 small mt-1">Ingreso mensual proyectado</div>
                    </div>
                    <div class="fs-1 text-primary opacity-75">
                        <i class="bi bi-graph-up-arrow"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recaudado este mes -->
        <div class="col-sm-6 col-xl-3">
            <div class="card card-custom p-3 h-100 border-start border-success border-4 shadow-sm">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-white-50 small text-uppercase fw-bold">Cobrado este Mes</div>
                        <h3 class="fw-bold mb-0 text-success mt-1">$<?php echo number_format($collectedThisMonth, 2); ?></h3>
                        <div class="text-white-50 small mt-1">
                            <?php $pct = $mrr > 0 ? round(($collectedThisMonth / $mrr) * 100) : 0; ?>
                            <span class="badge bg-success bg-opacity-25 text-success"><?php echo $pct; ?>% recaudado</span>
                        </div>
                    </div>
                    <div class="fs-1 text-success opacity-75">
                        <i class="bi bi-wallet2"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Salud de cartera / Morosidad -->
        <div class="col-sm-6 col-xl-3">
            <div class="card card-custom p-3 h-100 border-start border-warning border-4 shadow-sm">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-white-50 small text-uppercase fw-bold">Salud de Cartera</div>
                        <h3 class="fw-bold mb-0 text-white mt-1"><?php echo $data['active_antennas']; ?> <span class="fs-6 fw-normal text-white-50">activas</span></h3>
                        <div class="small mt-1">
                            <span class="badge bg-success me-1"><?php echo $debtOverview['al_dia']; ?> al día</span>
                            <span class="badge bg-danger"><?php echo $debtOverview['con_deuda']; ?> en mora</span>
                        </div>
                    </div>
                    <div class="fs-1 text-warning opacity-75">
                        <i class="bi bi-exclamation-diamond-fill"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Clientes y Cuentas ACC -->
        <div class="col-sm-6 col-xl-3">
            <div class="card card-custom p-3 h-100 border-start border-info border-4 shadow-sm">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-white-50 small text-uppercase fw-bold">Clientes & Cuentas ACC</div>
                        <h3 class="fw-bold mb-0 text-white mt-1"><?php echo $data['total_clients']; ?> <span class="fs-6 fw-normal text-white-50">clientes</span></h3>
                        <div class="text-white-50 small mt-1">
                            <i class="bi bi-person-badge me-1"></i><?php echo $data['total_accounts']; ?> cuentas Starlink activas
                        </div>
                    </div>
                    <div class="fs-1 text-info opacity-75">
                        <i class="bi bi-people-fill"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- GRÁFICOS INTERACTIVOS (CHART.JS) -->
    <div class="row g-3 mb-4">
        <!-- Gráfico de Recaudación Mensual -->
        <div class="col-lg-8">
            <div class="card card-custom p-3 h-100 shadow-sm">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0 text-white"><i class="bi bi-bar-chart-line-fill text-primary me-2"></i>Recaudación Histórica (Últimos 6 Meses)</h5>
                    <span class="badge bg-primary bg-opacity-25 text-primary">USD ($)</span>
                </div>
                <div style="position: relative; height: 260px; width: 100%;">
                    <canvas id="chartCollectionTrend"></canvas>
                </div>
            </div>
        </div>

        <!-- Gráfico de Distribución por Plan -->
        <div class="col-lg-4">
            <div class="card card-custom p-3 h-100 shadow-sm">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0 text-white"><i class="bi bi-pie-chart-fill text-success me-2"></i>Antenas por Plan</h5>
                    <span class="badge bg-secondary"><?php echo $data['total_antenas']; ?> total</span>
                </div>
                <div style="position: relative; height: 260px; width: 100%;">
                    <canvas id="chartPlansDistribution"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- TABLA DE PRÓXIMOS VENCIMIENTOS CON COBRO RÁPIDO POR WHATSAPP -->
    <?php if (!empty($upcomingExpirations)): ?>
    <div class="row mb-4">
        <div class="col-12">
            <div class="card card-custom p-3 shadow-sm border border-warning border-opacity-50">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <div>
                        <h5 class="text-warning mb-0"><i class="bi bi-bell-fill me-2"></i>Próximos Vencimientos (Siguientes 7 Días)</h5>
                        <small class="text-white-50">Equipos que deben pagar en los próximos días. Puedes notificar al cliente directamente por WhatsApp.</small>
                    </div>
                    <span class="badge bg-warning text-dark fw-bold"><?php echo count($upcomingExpirations); ?> equipos</span>
                </div>
                <div class="table-responsive mt-2">
                    <table class="table table-dark table-hover table-sm align-middle mb-0">
                        <thead>
                            <tr class="text-white-50 small">
                                <th>Serial / Antena</th>
                                <th>Cliente</th>
                                <th>Teléfono</th>
                                <th>Plan</th>
                                <th>Monto</th>
                                <th>Día de Pago</th>
                                <th>Estado</th>
                                <th class="text-end">Acción Rápida</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($upcomingExpirations as $exp): ?>
                                <?php
                                    $dueDay = (int)$exp['pay'];
                                    $daysRemaining = $exp['days_remaining'];
                                    $alreadyPaid = !empty($exp['already_paid']);
                                    $phoneClean = preg_replace('/[^0-9]/', '', $exp['phone'] ?? '');

                                    $waMsg = "Hola " . ($exp['cliente'] ?? '') . ", le escribimos de PCtronic. Le recordamos que su servicio Starlink (" . $exp['serial'] . ") tiene fecha de corte el día " . $dueDay . " por un monto de $" . number_format($exp['plan_price'], 2) . " USD. Por favor recuerde reportar su comprobante para mantener el servicio activo. ¡Gracias!";
                                    $waUrl = "https://api.whatsapp.com/send?phone=" . urlencode($phoneClean) . "&text=" . urlencode($waMsg);
                                ?>
                                <tr>
                                    <td>
                                        <div class="fw-bold"><?php echo htmlspecialchars($exp['serial']); ?></div>
                                        <?php if (!empty($exp['nickname'])): ?>
                                            <small class="text-white-50"><?php echo htmlspecialchars($exp['nickname']); ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($exp['cliente'] ?? '-'); ?></td>
                                    <td>
                                        <?php if (!empty($exp['phone'])): ?>
                                            <a href="tel:<?php echo htmlspecialchars($exp['phone']); ?>" class="text-white text-decoration-none small">
                                                <i class="bi bi-telephone me-1 text-white-50"></i><?php echo htmlspecialchars($exp['phone']); ?>
                                            </a>
                                        <?php else: ?>
                                            <span class="text-white-50 small">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><span class="badge bg-secondary"><?php echo htmlspecialchars($exp['nombre_plan']); ?></span></td>
                                    <td class="fw-bold text-info">$<?php echo number_format($exp['plan_price'], 2); ?></td>
                                    <td>Día <?php echo $dueDay; ?> de cada mes</td>
                                    <td>
                                        <?php if ($alreadyPaid): ?>
                                            <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Pagado</span>
                                        <?php elseif ($daysRemaining === 0): ?>
                                            <span class="badge bg-danger"><i class="bi bi-exclamation-circle me-1"></i>Vence Hoy</span>
                                        <?php elseif ($daysRemaining > 0): ?>
                                            <span class="badge bg-warning text-dark"><i class="bi bi-clock me-1"></i>En <?php echo $daysRemaining; ?> día(s)</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">Vencido</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end">
                                        <?php if (!empty($phoneClean) && !$alreadyPaid): ?>
                                            <a href="<?php echo htmlspecialchars($waUrl, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener" class="btn btn-sm btn-success py-0 px-2" title="Enviar recordatorio por WhatsApp">
                                                <i class="bi bi-whatsapp me-1"></i>Cobrar
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

<?php else: ?>
    <!-- ==========================================
         PORTAL DE AUTOGESTIÓN DE CLIENTE (EXPECTADOR)
         ========================================== -->
    <div class="row g-3 mb-4">
        <!-- Antenas del Cliente -->
        <div class="col-md-4">
            <div class="card card-custom p-4 h-100 border-start border-primary border-4 shadow-sm">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-white-50 small text-uppercase fw-bold">Mis Antenas Starlink</div>
                        <h2 class="display-6 fw-bold mb-0 text-white"><?php echo $clientSummary['total_antennas'] ?? 0; ?></h2>
                        <div class="text-white-50 small mt-1">Servicios activos asociados</div>
                    </div>
                    <div class="fs-1 text-primary">
                        <i class="bi bi-broadcast-pin"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Mensualidad Total -->
        <div class="col-md-4">
            <div class="card card-custom p-4 h-100 border-start border-success border-4 shadow-sm">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-white-50 small text-uppercase fw-bold">Cuota Mensual Total</div>
                        <h2 class="display-6 fw-bold mb-0 text-success">$<?php echo number_format($clientSummary['monthly_total'] ?? 0, 2); ?></h2>
                        <div class="text-white-50 small mt-1">USD por mes</div>
                    </div>
                    <div class="fs-1 text-success">
                        <i class="bi bi-calendar-check"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Acción Rápida Reportar Pago -->
        <div class="col-md-4">
            <div class="card card-custom p-4 h-100 border-start border-warning border-4 shadow-sm d-flex flex-column justify-content-between">
                <div>
                    <div class="text-white-50 small text-uppercase fw-bold">Reportar Transferencia</div>
                    <p class="text-white-50 small mt-1 mb-3">¿Realizaste un pago? Sube tu comprobante para validación inmediata.</p>
                </div>
                <a href="index.php?url=payments" class="btn btn-warning fw-bold text-dark w-100">
                    <i class="bi bi-upload me-1"></i> Reportar Comprobante
                </a>
            </div>
        </div>
    </div>

    <!-- MIS PAGOS RECIENTES (PORTAL CLIENTE) -->
    <?php if (!empty($clientSummary['recent_payments'])): ?>
    <div class="row mb-4">
        <div class="col-12">
            <div class="card card-custom p-3 shadow-sm">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0 text-white"><i class="bi bi-receipt-cutoff text-primary me-2"></i>Mis Pagos Reportados Recientes</h5>
                    <a href="index.php?url=payments" class="btn btn-sm btn-outline-light">Ver todos mis pagos</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-dark table-hover table-sm align-middle mb-0">
                        <thead>
                            <tr class="text-white-50 small">
                                <th>Fecha de Pago</th>
                                <th>Monto</th>
                                <th>Antena(s)</th>
                                <th>Estado</th>
                                <th>Comprobante</th>
                                <th class="text-end">Recibo</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($clientSummary['recent_payments'] as $rp): ?>
                                <tr>
                                    <td><?php echo date('d/m/Y', strtotime($rp['payment_date'])); ?></td>
                                    <td class="fw-bold text-info">$<?php echo number_format($rp['amount'], 2); ?> <?php echo htmlspecialchars($rp['currency']); ?></td>
                                    <td><?php echo htmlspecialchars($rp['antenas'] ?? '-'); ?></td>
                                    <td>
                                        <?php if ($rp['status'] === 'Aprobado'): ?>
                                            <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Aprobado</span>
                                        <?php elseif ($rp['status'] === 'Rechazado'): ?>
                                            <span class="badge bg-danger"><i class="bi bi-x-circle me-1"></i>Rechazado</span>
                                        <?php else: ?>
                                            <span class="badge bg-warning text-dark"><i class="bi bi-clock me-1"></i>En Revisión</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($rp['receipt_path'])): ?>
                                            <a href="<?php echo htmlspecialchars($rp['receipt_path'], ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener" class="btn btn-sm btn-outline-info py-0 px-2">
                                                <i class="bi bi-paperclip"></i> Ver
                                            </a>
                                        <?php else: ?>
                                            <span class="text-white-50 small">Sin adjunto</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end">
                                        <?php if ($rp['status'] === 'Aprobado'): ?>
                                            <a href="index.php?url=payments/receipt&id=<?php echo $rp['id_payment']; ?>" target="_blank" class="btn btn-sm btn-outline-light py-0 px-2" title="Descargar / Imprimir Recibo">
                                                <i class="bi bi-printer me-1"></i>Recibo
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
<?php endif; ?>

<!-- ==========================================
     CALENDARIO DE DÍAS DE PAGO (GLOBAL / AMBOS ROLES)
     ========================================== -->
<div class="row mt-2">
    <div class="col-12">
        <div class="card card-custom p-3 calendar-modal-host shadow-sm">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h5 class="mb-0 text-white"><i class="bi bi-calendar3 text-primary me-2"></i>Calendario de Días de Cobro<?php echo $isExpectador ? ' de mis antenas' : ''; ?></h5>
                <div>
                    <a class="btn btn-sm btn-outline-light" href="index.php?url=dashboard&action=export_calendar&month=<?php echo $month ?? date('n'); ?>&year=<?php echo $year ?? date('Y'); ?>">
                        <i class="bi bi-file-earmark-spreadsheet me-1"></i> Exportar CSV
                    </a>
                </div>
            </div>
            <hr class="border-secondary border-opacity-50">
            <?php
                $month = isset($data['calendar_month']) ? intval($data['calendar_month']) : intval(date('n'));
                $year = isset($data['calendar_year']) ? intval($data['calendar_year']) : intval(date('Y'));
                if (isset($_GET['month']) && isset($_GET['year'])) { $month = intval($_GET['month']); $year = intval($_GET['year']); }
                $firstDay = strtotime("{$year}-{$month}-01");
                $startWeekday = intval(date('w', $firstDay));
                $daysInMonth = intval(date('t', $firstDay));
                $payDays = isset($data['pay_days']) ? $data['pay_days'] : [];
                $monthLabel = date('F Y', $firstDay);
                if (class_exists('IntlDateFormatter')) {
                    $formatter = new IntlDateFormatter('es_ES', IntlDateFormatter::LONG, IntlDateFormatter::NONE, 'Europe/Madrid', IntlDateFormatter::GREGORIAN, 'MMMM yyyy');
                    $monthLabel = $formatter->format($firstDay);
                }

                $planColors = [
                    'Premium' => 'badge bg-success',
                    'Standard' => 'badge bg-info text-dark',
                    'Basico' => 'badge bg-secondary',
                ];

                $prevMonth = $month - 1; $prevYear = $year;
                if ($prevMonth < 1) { $prevMonth = 12; $prevYear = $year - 1; }
                $nextMonth = $month + 1; $nextYear = $year;
                if ($nextMonth > 12) { $nextMonth = 1; $nextYear = $year + 1; }
            ?>

            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <a class="btn btn-sm btn-outline-light" href="index.php?url=dashboard&month=<?php echo $prevMonth; ?>&year=<?php echo $prevYear; ?>">&laquo; Mes Anterior</a>
                    <span class="mx-3 fw-bold text-white text-capitalize"><?php echo htmlspecialchars($monthLabel, ENT_QUOTES, 'UTF-8'); ?></span>
                    <a class="btn btn-sm btn-outline-light" href="index.php?url=dashboard&month=<?php echo $nextMonth; ?>&year=<?php echo $nextYear; ?>">Mes Siguiente &raquo;</a>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="mobile-table-hint d-md-none mb-2">
                        <i class="bi bi-arrow-left-right text-info"></i> Desliza para ver el calendario completo
                    </div>
                    <div class="table-responsive">
                        <table class="table table-dark table-bordered mb-0 calendar-table pdf-exportable" data-pdf-title="Calendario de pagos">
                            <thead class="table-light text-dark">
                                <tr>
                                    <th>Dom</th>
                                    <th>Lun</th>
                                    <th>Mar</th>
                                    <th>Mié</th>
                                    <th>Jue</th>
                                    <th>Vie</th>
                                    <th>Sáb</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $day = 1;
                                $started = false;
                                for ($row = 0; $row < 6; $row++):
                                    echo "<tr>";
                                    for ($col = 0; $col < 7; $col++):
                                        if (!$started && $col === $startWeekday) { $started = true; }
                                        if (!$started) {
                                            echo "<td class=\"bg-dark\">&nbsp;</td>";
                                            continue;
                                        }
                                        if ($day > $daysInMonth) {
                                            echo "<td class=\"bg-dark\">&nbsp;</td>";
                                        } else {
                                            $cellClass = isset($payDays[$day]) ? 'table-warning text-dark' : '';
                                            echo "<td class=\"position-relative {$cellClass}\">";
                                            echo "<div class=\"calendar-day-header\"><strong>" . $day . "</strong>";
                                            if (isset($payDays[$day])) {
                                                $rows = $payDays[$day];
                                                $count = count($rows);
                                                $dataRows = htmlspecialchars(json_encode($rows), ENT_QUOTES, 'UTF-8');
                                                echo "<button type=\"button\" class=\"btn btn-sm btn-light text-dark fw-bold day-summary-badge\" data-day=\"{$day}\" data-rows=\"{$dataRows}\">" . $count . "</button>";
                                            }
                                            echo "</div><div class=\"mt-2 small calendar-cell-content\">";
                                            if (isset($payDays[$day])) {
                                                echo "<a href=\"#\" class=\"calendar-day-link text-decoration-none\" data-day=\"{$day}\" data-rows=\"{$dataRows}\">";
                                                foreach ($payDays[$day] as $a) {
                                                    $label = htmlspecialchars($a['serial'] . ' — ' . ($a['cliente'] ?? '-'));
                                                    echo "<div class=\"py-1 text-dark fw-semibold text-truncate\">{$label}</div>";
                                                }
                                                echo "</a>";
                                            }
                                            echo "</div></td>";
                                        }
                                        $day++;
                                    endfor;
                                    echo "</tr>";
                                    if ($day > $daysInMonth) break;
                                endfor;
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Modales del Calendario -->
            <div class="modal fade" id="dayModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                    <div class="modal-content bg-dark text-white">
                        <div class="modal-header">
                            <h5 class="modal-title" id="dayModalTitle">Detalles</h5>
                            <button type="button" class="btn-close btn-close-white" id="dayModalClose" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body" id="dayModalBody"></div>
                    </div>
                </div>
            </div>

            <script>
                (function(){
                    var planColors = <?php echo json_encode($planColors); ?>;
                    function buildList(rows){
                        if(!rows || rows.length === 0) return '<p class="text-white-50">No hay pagos para este día.</p>';
                        var html = '<div class="list-group">';
                        rows.forEach(function(r){
                            var plan = r.nombre_plan || '';
                            var badgeClass = planColors[plan] || 'badge bg-secondary';
                            html += '<div class="list-group-item bg-dark text-white border-secondary d-flex justify-content-between align-items-start">';
                            html += '<div><div class="fw-bold">' + (r.serial || '-') + '</div><div class="small text-white-50">' + (r.cliente || '') + '</div></div>';
                            var status = r.payment_status || '';
                            var statusClass = '';
                            if (status === 'Pagado') statusClass = 'badge bg-success text-dark';
                            else if (status === 'Pendiente') statusClass = 'badge bg-warning text-dark';
                            else if (status === 'Atrasado') statusClass = 'badge bg-danger text-white';
                            html += '<div class="text-end">';
                            html += '<span class="' + badgeClass + '">' + plan + '</span>';
                            if (status) {
                                html += '<div class="small mt-1"><span class="' + statusClass + '">' + status + '</span></div>';
                            }
                            <?php if (!$isExpectador): ?>
                                html += '<div class="small text-white-50 mt-1">' + (r.cuenta_starlink || '') + '</div>';
                            <?php endif; ?>
                            html += '</div>';
                            html += '</div>';
                        });
                        html += '</div>';
                        return html;
                    }

                    document.querySelectorAll('.day-summary-badge, .calendar-day-link').forEach(function(el){
                        el.addEventListener('click', function(e){
                            e.preventDefault();
                            var rows = [];
                            try { rows = JSON.parse(this.getAttribute('data-rows') || '[]'); } catch(err){ rows = []; }
                            var day = this.getAttribute('data-day') || '';
                            var body = document.getElementById('dayModalBody');
                            var title = document.getElementById('dayModalTitle');
                            title.textContent = 'Día ' + day + ' — ' + (rows.length) + ' pago(s)';
                            body.innerHTML = buildList(rows);
                            var modalEl = document.getElementById('dayModal');
                            var modal = bootstrap.Modal.getOrCreateInstance(modalEl);
                            modal.show();
                        });
                    });

                    document.getElementById('dayModal').addEventListener('hidden.bs.modal', function(){
                        document.getElementById('dayModalBody').innerHTML = '';
                    });

                    document.getElementById('dayModalClose').addEventListener('click', function(){
                        var dayModal = document.getElementById('dayModal');
                        bootstrap.Modal.getOrCreateInstance(dayModal).hide();
                        dayModal.classList.remove('show');
                        dayModal.setAttribute('aria-hidden', 'true');
                        dayModal.style.display = 'none';
                        document.body.classList.remove('modal-open');
                        document.body.style.removeProperty('padding-right');
                        document.querySelectorAll('.modal-backdrop').forEach(function(backdrop){
                            backdrop.remove();
                        });
                    });
                })();
            </script>
        </div>
    </div>
</div>

<?php if (!$isExpectador): ?>
<!-- Inicialización de Gráficos con Chart.js -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // 1. Gráfico de Recaudación Mensual (Últimos 6 Meses)
    var trendCtx = document.getElementById('chartCollectionTrend');
    if (trendCtx && typeof Chart !== 'undefined') {
        var trendData = <?php echo json_encode($collectionTrend); ?>;
        var labels = trendData.map(function(item) { return item.label; });
        var totals = trendData.map(function(item) { return item.total; });

        new Chart(trendCtx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Recaudado ($ USD)',
                    data: totals,
                    backgroundColor: 'rgba(13, 110, 253, 0.75)',
                    borderColor: '#0d6efd',
                    borderWidth: 2,
                    borderRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return ' $' + Number(context.raw).toLocaleString('en-US', { minimumFractionDigits: 2 });
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: 'rgba(255, 255, 255, 0.1)' },
                        ticks: {
                            color: '#94a3b8',
                            callback: function(val) { return '$' + val; }
                        }
                    },
                    x: {
                        grid: { display: false },
                        ticks: { color: '#94a3b8' }
                    }
                }
            }
        });
    }

    // 2. Gráfico de Distribución por Plan (Doughnut)
    var plansCtx = document.getElementById('chartPlansDistribution');
    if (plansCtx && typeof Chart !== 'undefined') {
        var plansRaw = <?php echo json_encode($data['antenas_by_plan'] ?? []); ?>;
        var pLabels = plansRaw.map(function(item) { return item.nombre_plan; });
        var pValues = plansRaw.map(function(item) { return parseInt(item.total_antenas) || 0; });
        var pColors = ['#0ea5e9', '#10b981', '#f59e0b', '#6366f1', '#ec4899', '#64748b'];

        new Chart(plansCtx, {
            type: 'doughnut',
            data: {
                labels: pLabels,
                datasets: [{
                    data: pValues,
                    backgroundColor: pColors.slice(0, pLabels.length),
                    borderColor: '#161b22',
                    borderWidth: 3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { color: '#cbd5e1', boxWidth: 12, font: { size: 11 } }
                    }
                },
                cutout: '65%'
            }
        });
    }
});
</script>
<?php endif; ?>
