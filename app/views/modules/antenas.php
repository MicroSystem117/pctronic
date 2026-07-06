<?php
$userRole = isset($_SESSION['user_role']) ? $_SESSION['user_role'] : '';
$isAdmin = $userRole === 'Administrador';
$isExpectador = $userRole === 'Expectador';
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-broadcast"></i> Antenas Starlink Registradas</h2>
    <?php if ($isAdmin): ?>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalAntena">
            <i class="bi bi-plus-circle"></i> Registrar Antena
        </button>
    <?php endif; ?>
</div>

<div class="mb-3 d-flex justify-content-end gap-2 flex-wrap">
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
    <input id="search_antenas" class="form-control form-control-sm w-25" placeholder="Buscar...">
</div>

<div class="card card-custom p-3">
    <div class="table-responsive">
        <table class="table table-dark table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>ID</th>
                    <th>Serial No.</th>
                    <th>Nickname</th>
                    <th>Kit No.</th>
                    <th>Asignada a</th>
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
                        >
                            <td><?php echo $antena['id_starlink']; ?></td>
                            <td><code><?php echo $antena['serial']; ?></code></td>
                            <td><?php echo !empty($antena['nickname']) ? htmlspecialchars($antena['nickname']) : '<span class="text-white-50">-</span>'; ?></td>
                            <td><code><?php echo $antena['kit']; ?></code></td>
                            <td><?php echo $antena['cliente']; ?></td>
                            <td><span class="badge bg-info text-dark"><?php echo $antena['nombre_plan']; ?></span></td>
                            <td><?php echo $antena['pais']; ?></td>
                            <td>
                                <?php echo !empty($antena['cuenta_starlink']) ? $antena['cuenta_starlink'] : '<span class="text-white-50">Sin Cuenta Vinc.</span>'; ?>
                            </td>
                            <td><?php echo isset($antena['pay']) && $antena['pay'] !== null && $antena['pay'] !== '' ? htmlspecialchars(intval($antena['pay'])) : '<span class="text-white-50">-</span>'; ?></td>
                            <td>
                                <?php
                                    $paymentStatus = '<span class="text-white-50">-</span>';
                                    if (isset($antena['pay']) && $antena['pay'] !== null && $antena['pay'] !== '') {
                                        $dueDay = intval($antena['pay']);
                                        $today = new DateTime();
                                        $isCurrentMonthPaid = false;

                                        if (!empty($antena['last_payment_date'])) {
                                            $lastPaymentDate = DateTime::createFromFormat('Y-m-d', $antena['last_payment_date']);
                                            if ($lastPaymentDate && $lastPaymentDate->format('Y-m') === $today->format('Y-m')) {
                                                $isCurrentMonthPaid = true;
                                            }
                                        }

                                        if ($isCurrentMonthPaid) {
                                            $paymentStatus = '<span class="badge bg-success text-dark">Pagado</span>';
                                        } else {
                                            if (intval($today->format('j')) <= $dueDay) {
                                                $paymentStatus = '<span class="badge bg-warning text-dark">Pendiente</span>';
                                            } else {
                                                $paymentStatus = '<span class="badge bg-danger text-white">Atrasado</span>';
                                            }
                                        }
                                    }
                                    echo $paymentStatus;
                                ?>
                            </td>
                            <td><?php echo date('d/m/Y', strtotime($antena['date'])); ?></td>
                            <?php if ($isAdmin): ?>
                                <td>
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
                                        data-bs-toggle="modal" data-bs-target="#modalAntena">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <a href="index.php?url=antenas&action=delete&id=<?php echo $antena['id_starlink']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('¿Eliminar antena?');">
                                        <i class="bi bi-trash"></i>
                                    </a>
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
    <div class="modal-dialog modal-dialog-centered modal-lg">
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
                            <label class="form-label">Cuenta Starlink Administrativa</label>
                            <select class="form-select" id="antena_account" name="account_id">
                                <option value="">-- Sin cuenta (Opcional) --</option>
                                <?php foreach($data['cuentas'] as $acc): ?>
                                    <option value="<?php echo $acc['id_accounts']; ?>"><?php echo $acc['info_cuenta']; ?></option>
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
                            <label class="form-label">País / Región <span class="text-danger">*</span></label>
                            <select class="form-select" id="antena_country" name="country" required>
                                <option value="">-- Seleccionar --</option>
                                <?php foreach($data['paises'] as $co): ?>
                                    <option value="<?php echo $co['id_country']; ?>"><?php echo $co['country']; ?></option>
                                <?php endforeach; ?>
                            </select>
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

<script>
document.addEventListener('DOMContentLoaded', function(){
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

            const planSelect = document.getElementById('antena_plan');
            if (planId) {
                planSelect.value = planId;
            }

            const countrySelect = document.getElementById('antena_country');
            if (countryId) {
                countrySelect.value = countryId;
            }

            document.getElementById('antena_pay').value = this.getAttribute('data-pay') || '';
        });
    });

    // Buscador para antenas
    var searchAnt = document.getElementById('search_antenas');
    if (searchAnt) {
        var searchType = document.getElementById('search_antenas_type');

        function getSearchValue(row, type) {
            switch (type) {
                case 'serial':
                    return row.dataset.serial || '';
                case 'nickname':
                    return row.dataset.nickname || '';
                case 'kit':
                    return row.dataset.kit || '';
                case 'cliente':
                    return row.dataset.cliente || '';
                case 'cuenta':
                    return row.dataset.cuenta || '';
                case 'all':
                default:
                    return row.textContent || '';
            }
        }

        function filterRows() {
            var q = searchAnt.value.trim().toLowerCase();
            var type = searchType ? searchType.value : 'all';
            var rows = document.querySelectorAll('.card .table tbody tr');

            for (var i = 0; i < rows.length; i++) {
                var row = rows[i];
                if (q === '') {
                    row.style.display = '';
                    continue;
                }

                var text = getSearchValue(row, type).toLowerCase();
                row.style.display = text.indexOf(q) !== -1 ? '' : 'none';
            }
        }

        searchAnt.addEventListener('input', filterRows);
        if (searchType) {
            searchType.addEventListener('change', filterRows);
        }
    }
});
</script>