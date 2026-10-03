<?php
$userRole = isset($_SESSION['user_role']) ? $_SESSION['user_role'] : '';
$isLoggedIn = isset($_SESSION['user_id']);
$isAdmin = $userRole === 'Administrador';
?>
<div class="logs-page">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h3 class="text-white mb-1">
                <i class="bi bi-journal-text me-2 text-info"></i>Registros de Actividad y Auditoría
            </h3>
            <p class="text-white-50 mb-0">
                Supervisa y audita las acciones, operaciones y accesos realizados por todos los usuarios en el sistema.
            </p>
        </div>
        <?php if ($isAdmin): ?>
            <div class="d-flex gap-2 flex-wrap">
                <button type="button" class="btn btn-sm btn-outline-warning" data-bs-toggle="modal" data-bs-target="#modalCleanLogs">
                    <i class="bi bi-clock-history me-1"></i>Depurar Antiguos
                </button>
                <button type="button" class="btn btn-sm btn-outline-danger" id="btnClearAllLogs">
                    <i class="bi bi-trash3 me-1"></i>Vaciar Historial
                </button>
            </div>
        <?php endif; ?>
    </div>

    <!-- Tarjetas de métricas -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-lg-3">
            <div class="card card-custom p-3 h-100 border-secondary bg-dark shadow-sm">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-white-50 small text-uppercase fw-semibold">Total Registros</div>
                        <div class="fs-4 fw-bold text-white mt-1"><?php echo number_format($stats['total'] ?? 0); ?></div>
                    </div>
                    <div class="bg-primary bg-opacity-25 text-primary p-3 rounded-circle">
                        <i class="bi bi-journal-check fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card card-custom p-3 h-100 border-secondary bg-dark shadow-sm">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-white-50 small text-uppercase fw-semibold">Acciones Hoy</div>
                        <div class="fs-4 fw-bold text-success mt-1"><?php echo number_format($stats['today'] ?? 0); ?></div>
                    </div>
                    <div class="bg-success bg-opacity-25 text-success p-3 rounded-circle">
                        <i class="bi bi-calendar-check fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card card-custom p-3 h-100 border-secondary bg-dark shadow-sm">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-white-50 small text-uppercase fw-semibold">Usuarios Activos Hoy</div>
                        <div class="fs-4 fw-bold text-warning mt-1"><?php echo number_format($stats['users_today'] ?? 0); ?></div>
                    </div>
                    <div class="bg-warning bg-opacity-25 text-warning p-3 rounded-circle">
                        <i class="bi bi-people-fill fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card card-custom p-3 h-100 border-secondary bg-dark shadow-sm">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-white-50 small text-uppercase fw-semibold">Módulo Concurrido Hoy</div>
                        <div class="fs-6 fw-bold text-info mt-1 text-truncate" style="max-width: 160px;" title="<?php echo htmlspecialchars($stats['top_module'] ?? 'N/A'); ?>">
                            <?php echo htmlspecialchars($stats['top_module'] ?? 'N/A'); ?>
                        </div>
                    </div>
                    <div class="bg-info bg-opacity-25 text-info p-3 rounded-circle">
                        <i class="bi bi-activity fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Barra de Filtros -->
    <div class="card card-custom p-3 mb-4 border-secondary bg-dark shadow-sm">
        <form method="GET" action="index.php" class="row g-2 align-items-end">
            <input type="hidden" name="url" value="logs">
            
            <div class="col-md-2 col-6">
                <label class="form-label text-white-50 small mb-1">Desde</label>
                <input type="date" name="from" class="form-control form-control-sm" value="<?php echo htmlspecialchars($filters['from'] ?? ''); ?>">
            </div>
            
            <div class="col-md-2 col-6">
                <label class="form-label text-white-50 small mb-1">Hasta</label>
                <input type="date" name="to" class="form-control form-control-sm" value="<?php echo htmlspecialchars($filters['to'] ?? ''); ?>">
            </div>

            <div class="col-md-2 col-6">
                <label class="form-label text-white-50 small mb-1">Módulo</label>
                <select name="module" class="form-select form-select-sm">
                    <option value="all">Todos los módulos</option>
                    <?php if (!empty($filterOptions['modules'])): ?>
                        <?php foreach ($filterOptions['modules'] as $mod): ?>
                            <option value="<?php echo htmlspecialchars($mod); ?>" <?php echo ($filters['module'] ?? '') === $mod ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($mod); ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>

            <div class="col-md-2 col-6">
                <label class="form-label text-white-50 small mb-1">Acción</label>
                <select name="action" class="form-select form-select-sm">
                    <option value="all">Todas las acciones</option>
                    <?php if (!empty($filterOptions['actions'])): ?>
                        <?php foreach ($filterOptions['actions'] as $act): ?>
                            <option value="<?php echo htmlspecialchars($act); ?>" <?php echo ($filters['action'] ?? '') === $act ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($act); ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>

            <div class="col-md-2 col-6">
                <label class="form-label text-white-50 small mb-1">Usuario</label>
                <select name="user_id" class="form-select form-select-sm">
                    <option value="">Todos los usuarios</option>
                    <?php if (!empty($users)): ?>
                        <?php foreach ($users as $u): ?>
                            <option value="<?php echo $u['id_user']; ?>" <?php echo ($filters['user_id'] ?? null) == $u['id_user'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($u['user'] . ' (' . $u['name'] . ')'); ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>

            <div class="col-md-2 col-6 d-flex gap-1">
                <button type="submit" class="btn btn-sm btn-primary flex-fill">
                    <i class="bi bi-funnel me-1"></i>Filtrar
                </button>
                <a href="index.php?url=logs" class="btn btn-sm btn-outline-secondary" title="Limpiar filtros">
                    <i class="bi bi-x-circle"></i>
                </a>
            </div>
        </form>
    </div>

    <!-- Tabla de Registros -->
    <div class="card card-custom p-3 border-secondary bg-dark shadow-sm">
        <div class="table-responsive">
            <table class="table table-dark table-hover mb-0 datatable pdf-exportable table-mobile-cards" 
                   data-pdf-title="Registros de Actividad y Auditoría" 
                   data-pdf-orientation="landscape">
                <thead class="table-light">
                    <tr>
                        <th style="width: 160px;">Fecha y Hora</th>
                        <th>Usuario</th>
                        <th>Rol</th>
                        <th>Módulo</th>
                        <th>Acción</th>
                        <th>Detalle / Descripción</th>
                        <th>Dirección IP</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($logs)): ?>
                        <?php foreach ($logs as $log): ?>
                            <?php
                                // Determinar estilo de badge según la acción
                                $act = strtoupper($log['action']);
                                $actionBadgeClass = 'bg-secondary';
                                if (in_array($act, ['CREAR', 'REGISTRO', 'APROBAR'], true)) {
                                    $actionBadgeClass = 'bg-success text-dark fw-bold';
                                } elseif (in_array($act, ['EDITAR', 'EXONERAR', 'ACTUALIZAR', 'CAMBIO_ROL'], true)) {
                                    $actionBadgeClass = 'bg-warning text-dark fw-bold';
                                } elseif (in_array($act, ['ELIMINAR', 'RECHAZAR', 'QUITAR_EXONERACION', 'VACIAR', 'LIMPIAR'], true)) {
                                    $actionBadgeClass = 'bg-danger text-white fw-bold';
                                } elseif (in_array($act, ['LOGIN', 'LOGOUT', 'RESPALDO', 'RESTAURAR'], true)) {
                                    $actionBadgeClass = 'bg-info text-dark fw-bold';
                                }

                                // Determinar icono según módulo
                                $modIcon = 'bi-gear';
                                $mod = $log['module'];
                                if ($mod === 'Autenticación') $modIcon = 'bi-shield-lock';
                                elseif ($mod === 'Antenas') $modIcon = 'bi-router';
                                elseif ($mod === 'Pagos') $modIcon = 'bi-currency-dollar';
                                elseif ($mod === 'Clientes') $modIcon = 'bi-people';
                                elseif ($mod === 'Planes') $modIcon = 'bi-list-ul';
                                elseif ($mod === 'Cuentas') $modIcon = 'bi-person-badge';
                                elseif ($mod === 'Países') $modIcon = 'bi-geo-alt';
                                elseif ($mod === 'Usuarios') $modIcon = 'bi-person-gear';
                                elseif ($mod === 'Base de Datos') $modIcon = 'bi-hdd-network';
                                elseif ($mod === 'Auditoría') $modIcon = 'bi-journal-text';
                            ?>
                            <tr>
                                <td data-label="Fecha y Hora" class="text-nowrap">
                                    <span class="td-value small text-white-50">
                                        <i class="bi bi-clock me-1"></i><?php echo date('d/m/Y H:i:s', strtotime($log['created_at'])); ?>
                                    </span>
                                </td>
                                <td data-label="Usuario">
                                    <span class="td-value fw-semibold text-white">
                                        <i class="bi bi-person-circle me-1 text-info"></i><?php echo htmlspecialchars($log['user_name']); ?>
                                    </span>
                                </td>
                                <td data-label="Rol">
                                    <span class="td-value">
                                        <span class="badge bg-secondary-subtle border border-secondary text-white-50" style="font-size: 0.75rem;">
                                            <?php echo htmlspecialchars($log['user_role']); ?>
                                        </span>
                                    </span>
                                </td>
                                <td data-label="Módulo">
                                    <span class="td-value">
                                        <span class="badge bg-dark border border-secondary text-info" style="font-size: 0.75rem;">
                                            <i class="bi <?php echo $modIcon; ?> me-1"></i><?php echo htmlspecialchars($log['module']); ?>
                                        </span>
                                    </span>
                                </td>
                                <td data-label="Acción">
                                    <span class="td-value">
                                        <span class="badge <?php echo $actionBadgeClass; ?>" style="font-size: 0.75rem;">
                                            <?php echo htmlspecialchars($log['action']); ?>
                                        </span>
                                    </span>
                                </td>
                                <td data-label="Detalle / Descripción">
                                    <span class="td-value text-break" style="max-width: 450px;">
                                        <?php echo htmlspecialchars($log['description']); ?>
                                    </span>
                                </td>
                                <td data-label="Dirección IP">
                                    <span class="td-value small">
                                        <code><?php echo htmlspecialchars($log['ip_address'] ?? '127.0.0.1'); ?></code>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">
                                <i class="bi bi-info-circle me-1"></i>No hay registros de actividad que coincidan con los filtros seleccionados.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php if ($isAdmin): ?>
<!-- Modal Depurar Registros Antiguos -->
<div class="modal fade" id="modalCleanLogs" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-dark text-white border-secondary">
            <div class="modal-header border-secondary">
                <h5 class="modal-title">
                    <i class="bi bi-clock-history text-warning me-2"></i>Depurar Registros Antiguos
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <form action="index.php?url=logs" method="POST">
                <input type="hidden" name="action" value="clear_old">
                <div class="modal-body">
                    <p class="text-white-50">
                        Esta acción eliminará de forma permanente los registros de auditoría anteriores al período seleccionado para mantener optimizada la base de datos.
                    </p>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Eliminar registros más antiguos de:</label>
                        <select name="days" class="form-select">
                            <option value="30">30 días (1 mes)</option>
                            <option value="60">60 días (2 meses)</option>
                            <option value="90" selected>90 días (3 meses)</option>
                            <option value="180">180 días (6 meses)</option>
                            <option value="365">365 días (1 año)</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-secondary">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-warning text-dark fw-bold">
                        <i class="bi bi-trash me-1"></i>Depurar Registros
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<form id="formClearAllLogs" action="index.php?url=logs" method="POST" style="display: none;">
    <input type="hidden" name="action" value="clear_all">
</form>

<script>
document.addEventListener('app:content-ready', function() {
    const btnClearAll = document.getElementById('btnClearAllLogs');
    if (btnClearAll) {
        btnClearAll.addEventListener('click', function() {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: '¿Vaciar historial de registros?',
                    text: 'Se eliminarán absolutamente TODOS los registros de actividad del sistema. Esta acción no se puede deshacer.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Sí, vaciar todo',
                    cancelButtonText: 'Cancelar',
                    customClass: { popup: 'swal-custom-dark' }
                }).then((result) => {
                    if (result.isConfirmed) {
                        document.getElementById('formClearAllLogs').submit();
                    }
                });
            } else if (confirm('¿Estás seguro de que deseas eliminar TODOS los registros de actividad? Esta acción es irreversible.')) {
                document.getElementById('formClearAllLogs').submit();
            }
        });
    }
});
</script>
<?php endif; ?>
