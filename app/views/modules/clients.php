<?php
$userRole = isset($_SESSION['user_role']) ? $_SESSION['user_role'] : '';
$isAdmin = $userRole === 'Administrador';

$totalClients = !empty($data['clients']) ? count($data['clients']) : 0;
$withAntennas = 0;
if (!empty($data['clients'])) {
    foreach ($data['clients'] as $cl) {
        if (!empty($cl['starlinks'])) {
            $withAntennas++;
        }
    }
}
?>
<div class="clients-page">
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h3 class="text-white mb-1"><i class="bi bi-people-fill me-2 text-primary"></i>Directorio de Clientes</h3>
        <p class="text-white-50 mb-0">Gestión de abonados, datos de contacto y terminales Starlink vinculadas.</p>
    </div>
    <?php if ($isAdmin): ?>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalClient">
            <i class="bi bi-person-plus-fill me-2"></i>Nuevo Cliente
        </button>
    <?php endif; ?>
</div>

<!-- Mini Stats Clientes -->
<div class="row g-3 mb-4">
    <div class="col-sm-4">
        <div class="card card-custom p-3 border-start border-primary border-4 shadow-sm">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-white-50 small text-uppercase fw-bold">Total Clientes</div>
                    <div class="fs-4 fw-bold text-white tech-mono mt-1"><?php echo $totalClients; ?></div>
                    <div class="small text-white-50">Base de datos de abonados</div>
                </div>
                <div class="fs-1 text-primary opacity-75 metric-icon-glow">
                    <i class="bi bi-person-vcard-fill"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="card card-custom p-3 border-start border-success border-4 shadow-sm">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-white-50 small text-uppercase fw-bold">Con Servicio Activo</div>
                    <div class="fs-4 fw-bold text-success tech-mono mt-1">
                        <span class="status-pulse-online"></span><?php echo $withAntennas; ?>
                    </div>
                    <div class="small text-white-50">Terminales satelitales asociadas</div>
                </div>
                <div class="fs-1 text-success opacity-75 metric-icon-success">
                    <i class="bi bi-broadcast-pin"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="card card-custom p-3 border-start border-secondary border-4 shadow-sm">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-white-50 small text-uppercase fw-bold">Sin Terminal</div>
                    <div class="fs-4 fw-bold text-white-50 tech-mono mt-1"><?php echo ($totalClients - $withAntennas); ?></div>
                    <div class="small text-white-50">En proceso de contratación</div>
                </div>
                <div class="fs-1 text-secondary opacity-75">
                    <i class="bi bi-person-dash"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="mb-3 d-flex justify-content-end gap-2 flex-wrap">
    <button type="button" class="btn btn-sm btn-outline-success btn-export-csv" data-table=".datatable" data-filename="clientes_pctronic.csv" title="Exportar directorio de clientes a Excel (.csv)">
        <i class="bi bi-file-earmark-excel me-1"></i>Excel (.csv)
    </button>
    <input id="search_clients" class="form-control form-control-sm w-100 w-md-25" style="max-width: 280px;" placeholder="Buscar cliente por nombre o cédula...">
</div>

<div class="card card-custom p-3 clients-card">
    <div class="table-responsive">
        <table class="table table-dark table-hover mb-0 datatable pdf-exportable table-mobile-cards" data-pdf-title="Clientes">
    <thead class="table-light">
        <tr>
            <th>Cédula</th>
            <th>Cliente</th>
            <th>Contacto</th>
            <th>Antenas Asoc.</th>
            <?php if ($isAdmin): ?>
                <th>Acciones</th>
            <?php endif; ?>
        </tr>
    </thead>
    <tbody>
        <?php if(!empty($data['clients'])): ?>
            <?php foreach($data['clients'] as $c): ?>
                <?php 
                    $starlinksRaw = trim($c['starlinks'] ?? '');
                    $starlinksArr = !empty($starlinksRaw) ? array_filter(explode(',', $starlinksRaw)) : [];
                    $starlinkCount = count($starlinksArr);
                ?>
                <tr data-name="<?php echo htmlspecialchars($c['name'] . ' ' . $c['surname'], ENT_QUOTES); ?>"
                    data-ci="<?php echo htmlspecialchars($c['ci'] ?? '', ENT_QUOTES); ?>"
                    data-phone="<?php echo htmlspecialchars($c['phone'], ENT_QUOTES); ?>">
                    <td data-label="Cédula">
                        <span class="td-value">
                            <?php if (!empty($c['ci'])): ?>
                                <span class="d-inline-flex align-items-center gap-1">
                                    <code class="text-info"><?php echo htmlspecialchars($c['ci']); ?></code>
                                    <button type="button" class="btn btn-link btn-sm p-0 text-white-50 btn-copy" data-copy="<?php echo htmlspecialchars($c['ci'], ENT_QUOTES); ?>" title="Copiar Cédula">
                                        <i class="bi bi-clipboard"></i>
                                    </button>
                                </span>
                            <?php else: ?>
                                <span class="text-white-50">N/A</span>
                            <?php endif; ?>
                        </span>
                    </td>
                    <td data-label="Cliente">
                        <span class="td-value text-white fw-bold">
                            <?php echo htmlspecialchars($c['name'] . ' ' . $c['surname']); ?>
                        </span>
                    </td>
                    <td data-label="Contacto">
                        <span class="td-value">
                            <?php if (!empty($c['phone'])): ?>
                                <span class="d-inline-flex align-items-center gap-1">
                                    <code class="text-white"><?php echo htmlspecialchars($c['phone']); ?></code>
                                    <a href="https://wa.me/<?php echo format_whatsapp_phone($c['phone']); ?>" target="_blank" class="text-success ms-1 text-decoration-none" title="Chatear por WhatsApp">
                                        <i class="bi bi-whatsapp"></i>
                                    </a>
                                    <a href="tel:<?php echo htmlspecialchars($c['phone'], ENT_QUOTES); ?>" class="text-info ms-1 text-decoration-none" title="Llamar directamente">
                                        <i class="bi bi-telephone-fill"></i>
                                    </a>
                                    <button type="button" class="btn btn-link btn-sm p-0 text-white-50 btn-copy ms-1" data-copy="<?php echo htmlspecialchars($c['phone'], ENT_QUOTES); ?>" title="Copiar Teléfono">
                                        <i class="bi bi-clipboard"></i>
                                    </button>
                                </span>
                            <?php else: ?>
                                <span class="text-white-50">Sin teléfono</span>
                            <?php endif; ?>
                        </span>
                    </td>
                    <td data-label="Antenas Asoc.">
                        <span class="td-value">
                            <?php if ($starlinkCount > 0): ?>
                                <button type="button" class="btn btn-sm btn-outline-info btn-view-client-starlinks"
                                        data-client-id="<?php echo $c['id_client']; ?>"
                                        data-client-name="<?php echo htmlspecialchars($c['name'] . ' ' . $c['surname'], ENT_QUOTES); ?>"
                                        data-starlinks="<?php echo htmlspecialchars($c['starlinks'] ?? '', ENT_QUOTES); ?>"
                                        data-bs-toggle="modal"
                                        data-bs-target="#modalClientStarlinks"
                                        title="Ver detalle de antenas">
                                    <span class="status-pulse-online"></span>
                                    <span class="fw-bold"><?php echo $starlinkCount; ?></span> <?php echo $starlinkCount === 1 ? 'antena' : 'antenas'; ?>
                                </button>
                            <?php else: ?>
                                <span class="badge bg-secondary-subtle border border-secondary text-white-50">0 antenas</span>
                            <?php endif; ?>
                        </span>
                    </td>
                    <?php if ($isAdmin): ?>
                        <td data-label="Acciones" class="text-nowrap">
                            <div class="d-inline-flex gap-1 w-100 flex-wrap">
                            <a href="index.php?url=payments&client=<?php echo urlencode($c['name'] . ' ' . $c['surname']); ?>"
                               class="btn btn-sm btn-outline-primary"
                               title="Ver estado de cuenta e historial de pagos">
                                <i class="bi bi-receipt"></i><span class="action-btn-text ms-1">Pagos</span>
                            </a>
                            <button class="btn btn-sm btn-success btn-view-client-starlinks"
                                    data-client-id="<?php echo $c['id_client']; ?>"
                                    data-client-name="<?php echo htmlspecialchars($c['name'] . ' ' . $c['surname'], ENT_QUOTES); ?>"
                                    data-starlinks="<?php echo htmlspecialchars($c['starlinks'] ?? '', ENT_QUOTES); ?>"
                                    data-bs-toggle="modal"
                                    data-bs-target="#modalClientStarlinks"
                                    title="Ver Starlink asociadas">
                                <i class="bi bi-broadcast"></i><span class="action-btn-text ms-1">Antenas</span>
                            </button>
                            <button class="btn btn-sm btn-info btn-edit-client" 
                                    data-id="<?php echo $c['id_client']; ?>"
                                    data-name="<?php echo htmlspecialchars($c['name'], ENT_QUOTES); ?>"
                                    data-surname="<?php echo htmlspecialchars($c['surname'], ENT_QUOTES); ?>"
                                    data-ci="<?php echo htmlspecialchars($c['ci'] ?? '', ENT_QUOTES); ?>"
                                    data-phone="<?php echo htmlspecialchars($c['phone'], ENT_QUOTES); ?>"
                                    data-bs-toggle="modal" 
                                    data-bs-target="#modalClient">
                                <i class="bi bi-pencil"></i><span class="action-btn-text ms-1">Editar</span>
                            </button>
                            <a href="index.php?url=clients&action=delete&id=<?php echo $c['id_client']; ?>" 
                               class="btn btn-sm btn-danger" 
                               data-confirm-text="¿Seguro que deseas eliminar a este cliente? Se desvincularán sus antenas."
                               title="Eliminar cliente">
                                <i class="bi bi-trash"></i><span class="action-btn-text ms-1">Eliminar</span>
                            </a>
                            </div>
                        </td>
                    <?php endif; ?>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="<?php echo $isAdmin ? 5 : 4; ?>" class="text-center py-3 text-muted">No hay clientes registrados.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>
    </div>
</div>

<?php if ($isAdmin): ?>
<div class="modal fade" id="modalClient" data-bs-backdrop="static" tabindex="-1" aria-labelledby="modalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content bg-dark text-white border-secondary">
            <div class="modal-header border-secondary">
                <h5 class="modal-title" id="modalLabel"><i class="bi bi-person-plus-fill me-2 text-primary"></i>Registrar Nuevo Cliente</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="index.php?url=clients" method="POST">
                <?php echo Controller::getCsrfInput(); ?>
                <input type="hidden" id="id_client" name="id_client" value="0">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nombre <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="client_name" name="name" pattern="[A-Za-zÁÉÍÓÚáéíóúÑñÜüÀ-ÿ' -]+" oninput="this.value = this.value.replace(/[0-9]/g, '')" title="No se permiten números" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Apellido <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="client_surname" name="surname" pattern="[A-Za-zÁÉÍÓÚáéíóúÑñÜüÀ-ÿ' -]+" oninput="this.value = this.value.replace(/[0-9]/g, '')" title="No se permiten números" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Cédula (Opcional)</label>
                        <input type="text" class="form-control" id="client_ci" name="ci" inputmode="numeric" pattern="[0-9]+" oninput="this.value = this.value.replace(/[^0-9]/g, '')" title="Solo se permiten números">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Teléfono <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="client_phone" name="phone" required>
                    </div>
                </div>
                <div class="modal-footer border-secondary">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar Cliente</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<div class="modal fade" id="modalClientStarlinks" data-bs-backdrop="static" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content bg-dark text-white border-secondary">
            <div class="modal-header border-secondary">
                <h5 class="modal-title" id="modalClientStarlinksTitle"><i class="bi bi-broadcast me-2 text-primary"></i>Starlink del cliente</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="clientStarlinksList" class="d-flex flex-wrap gap-2"></div>
            </div>
            <div class="modal-footer border-secondary">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener("app:content-ready", function() {
    // Al hacer clic en el botón de registrar (limpiar modal)
    const btnAdd = document.querySelector('[data-bs-target="#modalClient"]:not(.btn-edit-client)');
    if(btnAdd) {
        btnAdd.addEventListener('click', function() {
            document.getElementById('id_client').value = "0";
            document.getElementById('client_name').value = "";
            document.getElementById('client_surname').value = "";
            document.getElementById('client_ci').value = "";
            document.getElementById('client_phone').value = "";
            document.querySelector('#modalClient .modal-title').innerHTML = '<i class="bi bi-person-plus-fill"></i> Registrar Nuevo Cliente';
        });
    }

    // Al hacer clic en editar (cargar datos en el modal)
    document.querySelectorAll('.btn-edit-client').forEach(button => {
        button.addEventListener('click', function() {
            document.getElementById('id_client').value = this.getAttribute('data-id');
            document.getElementById('client_name').value = this.getAttribute('data-name');
            document.getElementById('client_surname').value = this.getAttribute('data-surname');
            document.getElementById('client_ci').value = this.getAttribute('data-ci') || "";
            document.getElementById('client_phone').value = this.getAttribute('data-phone');
            
            document.querySelector('#modalClient .modal-title').innerHTML = '<i class="bi bi-pencil-square"></i> Editar Datos del Cliente';
        });
    });

    document.querySelectorAll('.btn-view-client-starlinks').forEach(button => {
        button.addEventListener('click', function() {
            const clientName = this.getAttribute('data-client-name') || 'Cliente';
            const starlinks = (this.getAttribute('data-starlinks') || '').trim();
            const listContainer = document.getElementById('clientStarlinksList');
            const title = document.getElementById('modalClientStarlinksTitle');

            title.innerHTML = '<i class="bi bi-broadcast me-2 text-primary"></i>Starlink de ' + clientName;

            if (!starlinks) {
                listContainer.innerHTML = '<span class="text-white-50">Este cliente no tiene Starlink asociadas.</span>';
                return;
            }

            const items = starlinks.split('||').map(item => item.trim()).filter(Boolean);
            listContainer.innerHTML = items.map(item => {
                return '<span class="badge bg-success text-dark px-3 py-2">' + item + '</span>';
            }).join('');
        });
    });

});
</script>
</div>