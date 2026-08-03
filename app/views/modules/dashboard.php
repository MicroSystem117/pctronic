<?php
$userRole = isset($_SESSION['user_role']) ? $_SESSION['user_role'] : '';
$isAdmin = $userRole === 'Administrador';
$hasSecQuestions = isset($data['has_sec_questions']) ? $data['has_sec_questions'] : false;
?>
<div class="mb-4">
    <h2><i class="bi bi-speedometer2"></i> Panel de Control Principal</h2>
    <p class="text-white-50">Bienvenido al sistema de gestión técnica de Starlink Control.</p>
</div>

<?php if (!$hasSecQuestions): ?>
    <div class="alert alert-warning alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle-fill"></i>
        <strong>Recordatorio:</strong> No tienes preguntas de seguridad registradas. Ante cualquier inconveniente no podrás recuperar tu contraseña.
        <a href="index.php?url=auth/manage_security" class="alert-link">Configurar ahora</a>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Cerrar"></button>
    </div>
<?php endif; ?>

<div class="row g-4 mb-5">
    <div class="col-md-4">
        <div class="card card-custom p-4 h-100 border-start border-primary border-4">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <h6 class="text-white-50 text-uppercase small mb-1">Clientes Activos</h6>
                    <h2 class="display-6 fw-bold mb-0"><?php echo $data['total_clients']; ?></h2>
                </div>
                <div class="fs-1 text-primary">
                    <i class="bi bi-people-fill"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card card-custom p-4 h-100 border-start border-success border-4">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div>
                    <h6 class="text-white-50 text-uppercase small mb-1">Antenas por Plan</h6>
                    <h2 class="display-6 fw-bold mb-0"><?php echo $data['total_antenas']; ?></h2>
                </div>
                <div class="fs-1 text-success">
                    <i class="bi bi-broadcast-pin"></i>
                </div>
            </div>
            <div class="text-white-75 small">
                <?php if (!empty($data['antenas_by_plan'])): ?>
                    <?php
                        $planTotals = [];
                        foreach ($data['antenas_by_plan'] as $plan) {
                            $planTotals[$plan['nombre_plan']] = (int) $plan['total_antenas'];
                        }
                        $orderedPlanNames = ['Inactive', 'Standby', 'Residencial', 'Itinerante 100GB', 'Itinerante Ilimitado'];
                    ?>
                    <ul class="list-unstyled mb-0">
                        <?php foreach ($orderedPlanNames as $planName): ?>
                            <li class="d-flex justify-content-between align-items-center py-1 border-bottom border-secondary border-opacity-25">
                                <span><?php echo htmlspecialchars($planName); ?></span>
                                <span class="badge bg-secondary ms-2"><?php echo isset($planTotals[$planName]) ? $planTotals[$planName] : 0; ?></span>
                            </li>
                        <?php endforeach; ?>
                        <li class="d-flex justify-content-between align-items-center py-1 border-bottom border-secondary border-opacity-25">
                            <span>Total de antenas activas</span>
                            <span class="badge bg-success ms-2"><?php echo isset($data['active_antennas']) ? $data['active_antennas'] : 0; ?></span>
                        </li>
                    </ul>
                <?php else: ?>
                    <p class="mb-0 text-white-50">No hay antenas registradas asociadas a planes.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card card-custom p-4 h-100 border-start border-warning border-4">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <h6 class="text-white-50 text-uppercase small mb-1">Cuentas Admin (ACC)</h6>
                    <h2 class="display-6 fw-bold mb-0"><?php echo $data['total_accounts']; ?></h2>
                </div>
                <div class="fs-1 text-warning">
                    <i class="bi bi-person-badge-fill"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-12">
        <div class="card card-custom p-3">
            <h5><i class="bi bi-calendar3"></i> Calendario de Días de Pago</h5>
            <hr class="border-secondary">
            <?php
                $month = isset($data['calendar_month']) ? intval($data['calendar_month']) : intval(date('n'));
                $year = isset($data['calendar_year']) ? intval($data['calendar_year']) : intval(date('Y'));
                // permitir navegación por querystring
                if (isset($_GET['month']) && isset($_GET['year'])) { $month = intval($_GET['month']); $year = intval($_GET['year']); }
                $firstDay = strtotime("{$year}-{$month}-01");
                $startWeekday = intval(date('w', $firstDay)); // 0 (Sun) - 6 (Sat)
                $daysInMonth = intval(date('t', $firstDay));
                $payDays = isset($data['pay_days']) ? $data['pay_days'] : [];
                $monthLabel = date('F Y', $firstDay);
                if (class_exists('IntlDateFormatter')) {
                    $formatter = new IntlDateFormatter('es_ES', IntlDateFormatter::LONG, IntlDateFormatter::NONE, 'Europe/Madrid', IntlDateFormatter::GREGORIAN, 'MMMM yyyy');
                    $monthLabel = $formatter->format($firstDay);
                }

                // colores por plan (ajusta según necesites)
                $planColors = [
                    'Premium' => 'badge bg-success',
                    'Standard' => 'badge bg-info text-dark',
                    'Basico' => 'badge bg-secondary',
                ];

                // prev/next mes
                $prevMonth = $month - 1; $prevYear = $year;
                if ($prevMonth < 1) { $prevMonth = 12; $prevYear = $year - 1; }
                $nextMonth = $month + 1; $nextYear = $year;
                if ($nextMonth > 12) { $nextMonth = 1; $nextYear = $year + 1; }
            ?>

            <div class="d-flex justify-content-between align-items-center mb-2">
                <div>
                    <a class="btn btn-sm btn-outline-light" href="index.php?url=dashboard&month=<?php echo $prevMonth; ?>&year=<?php echo $prevYear; ?>">&laquo; Mes Anterior</a>
                    <span class="mx-3 fw-bold"><?php echo htmlspecialchars($monthLabel, ENT_QUOTES, 'UTF-8'); ?></span>
                    <a class="btn btn-sm btn-outline-light" href="index.php?url=dashboard&month=<?php echo $nextMonth; ?>&year=<?php echo $nextYear; ?>">Mes Siguiente &raquo;</a>
                </div>
                <div>
                    <a class="btn btn-sm btn-outline-light" href="index.php?url=dashboard&action=export_calendar&month=<?php echo $month; ?>&year=<?php echo $year; ?>">Exportar CSV</a>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="table-responsive">
                        <table class="table table-dark table-bordered mb-0 calendar-table">
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
                                                echo "<button type=\"button\" class=\"btn btn-sm btn-light day-summary-badge\" data-day=\"{$day}\" data-rows=\"{$dataRows}\">" . $count . "</button>";
                                            }
                                            echo "</div><div class=\"mt-2 small calendar-cell-content\">";
                                            if (isset($payDays[$day])) {
                                                echo "<a href=\"#\" class=\"calendar-day-link text-decoration-none\" data-day=\"{$day}\" data-rows=\"{$dataRows}\">";
                                                foreach ($payDays[$day] as $a) {
                                                    $label = htmlspecialchars($a['serial'] . ' — ' . ($a['cliente'] ?? '-'));
                                                    echo "<div class=\"py-1 text-white text-truncate\">{$label}</div>";
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

            <!-- Modales -->
            <div class="modal fade" id="dayModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered">
                    <div class="modal-content bg-dark text-white">
                        <div class="modal-header">
                            <h5 class="modal-title" id="dayModalTitle">Detalles</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body" id="dayModalBody"></div>
                    </div>
                </div>
            </div>

            <div class="modal fade" id="legendModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-sm modal-dialog-centered">
                    <div class="modal-content bg-dark text-white">
                        <div class="modal-header">
                            <h5 class="modal-title">Leyenda</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body small">
                            <?php foreach ($planColors as $pname => $cls): ?>
                                <div class="d-flex align-items-center mb-2">
                                    <span class="<?php echo $cls; ?> me-2" style="width:18px;height:18px;display:inline-block;border-radius:3px"></span>
                                    <div><?php echo htmlspecialchars($pname); ?></div>
                                </div>
                            <?php endforeach; ?>
                            <p class="small text-white-50">Haga clic en un día o en el resumen para ver detalles (serial, cliente, plan, cuenta).</p>
                        </div>
                    </div>
                </div>
            </div>

            <script>
                (function(){
                    var planColors = <?php echo json_encode($planColors); ?>;
                    function buildList(rows){
                        if(!rows || rows.length === 0) return '<p class="text-white-50">No hay pagos.</p>';
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
                            html += '<div class="small text-white-50 mt-1">' + (r.cuenta_starlink || '') + '</div></div>';
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
                            var modal = new bootstrap.Modal(modalEl);
                            modal.show();
                        });
                    });
                })();
            </script>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card card-custom p-4 mb-4">
            <h5><i class="bi bi-lightning-charge"></i> Estado del Servidor</h5>
            <hr class="border-secondary">
            <p>La base de datos <code>Starlink_PCtronic</code> se encuentra conectada correctamente vía PDO de manera segura.</p>
            <p class="mb-0 text-white-50">Puedes usar el menú lateral para navegar entre los módulos de configuración física y de cuentas de servicio técnico.</p>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="card card-custom p-4 text-center">
            <h5>Acceso Rápido</h5>
            <hr class="border-secondary">
            <div class="d-grid gap-2">
                <?php if ($isAdmin): ?>
                    <a href="index.php?url=antenas" class="btn btn-primary btn-sm"><i class="bi bi-plus-circle"></i> Registrar Nuevo Equipo</a>
                <?php endif; ?>
                <a href="index.php?url=accounts" class="btn btn-outline-light btn-sm"><i class="bi bi-person-badge"></i> Ver Cuentas Admin</a>
            </div>
        </div>
    </div>
</div>