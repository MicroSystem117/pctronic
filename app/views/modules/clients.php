<?php
$userRole = isset($_SESSION['user_role']) ? $_SESSION['user_role'] : '';
$isAdmin = $userRole === 'Administrador';
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="text-white"><i class="bi bi-people-fill me-2 text-primary"></i>Clientes Registrados</h3>
    <?php if ($isAdmin): ?>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalClient">
            <i class="bi bi-person-plus-fill me-2"></i>Nuevo Cliente
        </button>
    <?php endif; ?>
</div>

<div class="mb-3 d-flex justify-content-end">
    <input id="search_clients" class="form-control form-control-sm w-25" placeholder="Buscar clientes...">
</div>

<div class="card card-custom p-3">
    <div class="table-responsive">
        <table class="table table-dark table-hover mb-0 datatable pdf-exportable" data-pdf-title="Clientes">
    <thead class="table-light">
        <tr>
            <th>Cédula</th>
            <th>Cliente</th>
            <th>Teléfono</th>
            <?php if ($isAdmin): ?>
                <th>Acciones</th>
            <?php endif; ?>
        </tr>
    </thead>
    <tbody>
        <?php if(!empty($data['clients'])): ?>
            <?php foreach($data['clients'] as $c): ?>
                <tr data-name="<?php echo htmlspecialchars($c['name'] . ' ' . $c['surname'], ENT_QUOTES); ?>"
                    data-ci="<?php echo htmlspecialchars($c['ci'] ?? '', ENT_QUOTES); ?>"
                    data-phone="<?php echo htmlspecialchars($c['phone'], ENT_QUOTES); ?>">
                    <td><?php echo !empty($c['ci']) ? $c['ci'] : '<span class="text-white-50">N/A</span>'; ?></td>
                    <td><?php echo $c['name'] . ' ' . $c['surname']; ?></td>
                    <td><code><?php echo $c['phone']; ?></code></td>
                    <?php if ($isAdmin): ?>
                        <td>
                            <button class="btn btn-sm btn-success btn-view-client-starlinks"
                                    data-client-id="<?php echo $c['id_client']; ?>"
                                    data-client-name="<?php echo htmlspecialchars($c['name'] . ' ' . $c['surname'], ENT_QUOTES); ?>"
                                    data-starlinks="<?php echo htmlspecialchars($c['starlinks'] ?? '', ENT_QUOTES); ?>"
                                    data-bs-toggle="modal"
                                    data-bs-target="#modalClientStarlinks"
                                    title="Ver Starlink asociadas">
                                <i class="bi bi-broadcast"></i>
                            </button>
                            <button class="btn btn-sm btn-info btn-edit-client" 
                                    data-id="<?php echo $c['id_client']; ?>"
                                    data-name="<?php echo htmlspecialchars($c['name'], ENT_QUOTES); ?>"
                                    data-surname="<?php echo htmlspecialchars($c['surname'], ENT_QUOTES); ?>"
                                    data-ci="<?php echo htmlspecialchars($c['ci'] ?? '', ENT_QUOTES); ?>"
                                    data-phone="<?php echo htmlspecialchars($c['phone'], ENT_QUOTES); ?>"
                                    data-bs-toggle="modal" 
                                    data-bs-target="#modalClient">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <a href="index.php?url=clients&action=delete&id=<?php echo $c['id_client']; ?>" 
                               class="btn btn-sm btn-danger" 
                               onclick="return confirm('¿Seguro que deseas eliminar a este cliente? Se desvincularán sus antenas.');">
                                <i class="bi bi-trash"></i>
                            </a>
                        </td>
                    <?php endif; ?>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="<?php echo $isAdmin ? 4 : 3; ?>" class="text-center py-3 text-muted">No hay clientes registrados.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>
    </div>
</div>

<?php if ($isAdmin): ?>
<div class="modal fade" id="modalClient" data-bs-backdrop="static" tabindex="-1" aria-labelledby="modalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content bg-dark text-white border-secondary">
            <div class="modal-header border-secondary">
                <h5 class="modal-title" id="modalLabel"><i class="bi bi-person-plus-fill me-2 text-primary"></i>Registrar Nuevo Cliente</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="index.php?url=clients" method="POST">
                <input type="hidden" id="id_client" name="id_client" value="0">

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
    <div class="modal-dialog modal-dialog-centered">
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
document.addEventListener("DOMContentLoaded", function() {
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