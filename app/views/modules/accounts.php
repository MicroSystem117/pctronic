<?php
$userRole = isset($_SESSION['user_role']) ? $_SESSION['user_role'] : '';
$isAdmin = $userRole === 'Administrador';
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-person-badge"></i> Cuentas Administrativas Starlink</h2>
    <?php if ($isAdmin): ?>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalAccount">
            <i class="bi bi-plus-circle"></i> Agregar Cuenta
        </button>
    <?php endif; ?>
</div>

<div class="mb-3 d-flex justify-content-end">
    <input id="search_accounts" class="form-control form-control-sm w-25" placeholder="Buscar cuentas...">
</div>

<div class="card card-custom p-3">
    <div class="table-responsive">
        <table class="table table-dark table-hover mb-0 datatable pdf-exportable" data-pdf-title="Cuentas Starlink">
            <thead class="table-light">
                <tr>
                    <th>Titular / Propietario</th>
                    <th>Número de Cuenta (ACC)</th>
                    <th>Email</th>
                    <th>País</th>
                    <th>Antenas</th>
                    <th>Fecha de Registro</th>
                    <?php if ($isAdmin): ?>
                        <th>Acciones</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php if(!empty($data['accounts'])): ?>
                    <?php foreach($data['accounts'] as $account): ?>
                        <tr data-owner="<?php echo htmlspecialchars($account['owner'], ENT_QUOTES); ?>"
                            data-acc="<?php echo htmlspecialchars($account['acc'], ENT_QUOTES); ?>"
                            data-email="<?php echo htmlspecialchars($account['email'], ENT_QUOTES); ?>"
                            data-country="<?php echo htmlspecialchars($account['country_id'] ?? '', ENT_QUOTES); ?>"
                            data-date="<?php echo htmlspecialchars($account['create_date'], ENT_QUOTES); ?>">
                            <td><strong><?php echo $account['owner']; ?></strong></td>
                            <td><code><?php echo $account['acc']; ?></code></td>
                            <td><?php echo htmlspecialchars($account['email']); ?></td>
                            <td><?php echo htmlspecialchars($account['pais'] ?? 'Sin país'); ?></td>
                            <td><span class="badge bg-info text-dark"><?php echo intval($account['starlink_count'] ?? 0); ?></span></td>
                            <td><?php echo date('d/m/Y', strtotime($account['create_date'])); ?></td>
                    <?php if ($isAdmin): ?>
                        <td>
                            <button class="btn btn-sm btn-success btn-view-account-starlinks"
                                    data-account-id="<?php echo $account['id_accounts']; ?>"
                                    data-account-name="<?php echo htmlspecialchars($account['owner'], ENT_QUOTES); ?>"
                                    data-starlinks="<?php echo htmlspecialchars($account['starlinks'] ?? '', ENT_QUOTES); ?>"
                                    data-bs-toggle="modal"
                                    data-bs-target="#modalAccountStarlinks"
                                    title="Ver Starlink asociadas">
                                <i class="bi bi-broadcast"></i>
                            </button>
                            <button class="btn btn-sm btn-info btn-edit-account"
                                    data-id="<?php echo $account['id_accounts']; ?>"
                                    data-owner="<?php echo htmlspecialchars($account['owner'], ENT_QUOTES); ?>"
                                    data-acc="<?php echo htmlspecialchars($account['acc'], ENT_QUOTES); ?>"
                                    data-email="<?php echo htmlspecialchars($account['email'], ENT_QUOTES); ?>"
                                    data-date="<?php echo $account['create_date']; ?>"
                                    data-bs-toggle="modal" data-bs-target="#modalAccount">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <a href="index.php?url=accounts&action=delete&id=<?php echo $account['id_accounts']; ?>" class="btn btn-sm btn-danger" onclick="return prepareAccountDelete(this);">
                                <i class="bi bi-trash"></i>
                            </a>
                        </td>
                    <?php endif; ?>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="<?php echo $isAdmin ? 7 : 6; ?>" class="text-center py-4 text-muted">
                            <i class="bi bi-exclamation-circle d-block mb-2 fs-3"></i> No hay cuentas Starlink registradas en el sistema.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php if ($isAdmin): ?>
<div class="modal fade" id="modalAccount" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content card-custom text-white">
            <div class="modal-header border-secondary">
                <h5 class="modal-title"><i class="bi bi-person-plus-fill"></i> Registrar Cuenta Starlink</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="index.php?url=accounts" method="POST">
                <input type="hidden" id="id_accounts" name="id_accounts" value="0">
                <div class="modal-body">
                    
                    <div class="mb-3">
                        <label class="form-label">Nombre del Titular <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="account_owner" name="owner" placeholder="Ej: RONALD JOSE MUJICA" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Número ACC de la Cuenta <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="account_acc" name="acc" placeholder="Ej: ACC-4368746-12688-28" required>
                        <div class="form-text text-white-50">Asegúrate de ingresar el formato completo provisto por Starlink.</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Email de la Cuenta <span class="text-danger">*</span></label>
                        <input type="email" class="form-control" id="account_email" name="email" placeholder="ejemplo@starlink.com" required>
                        <div class="form-text text-white-50">Usa una dirección válida para comunicaciones y notificaciones.</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="account_country">País</label>
                        <select class="form-select" id="account_country" name="countries">
                            <option value="">Seleccionar país</option>
                            <?php foreach (($data['countries'] ?? []) as $country): ?>
                                <option value="<?php echo intval($country['id_country']); ?>"><?php echo htmlspecialchars($country['country']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Fecha de Vinculación</label>
                        <input type="date" class="form-control" id="account_date" name="create_date" value="<?php echo date('Y-m-d'); ?>">
                    </div>

                </div>
                <div class="modal-footer border-secondary">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar Cuenta</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<div class="modal fade" id="modalAccountStarlinks" data-bs-backdrop="static" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-dark text-white border-secondary">
            <div class="modal-header border-secondary">
                <h5 class="modal-title" id="modalAccountStarlinksTitle"><i class="bi bi-broadcast me-2 text-primary"></i>Starlink asociadas</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="accountStarlinksList" class="d-flex flex-wrap gap-2"></div>
            </div>
            <div class="modal-footer border-secondary">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<script>
document.querySelector('[data-bs-target="#modalAccount"]:not(.btn-edit-account)')?.addEventListener('click', function(){
    document.getElementById('id_accounts').value = '0';
    document.getElementById('account_owner').value = '';
    document.getElementById('account_acc').value = '';
    document.getElementById('account_email').value = '';
    document.getElementById('account_country').value = '';
    document.getElementById('account_date').value = '<?php echo date('Y-m-d'); ?>';
});

document.getElementById('modalAccount')?.addEventListener('show.bs.modal', function(event){
    const trigger = event.relatedTarget;
    const accountId = trigger?.classList.contains('btn-edit-account')
        ? trigger.getAttribute('data-id')
        : '0';
    document.getElementById('id_accounts').value = accountId || '0';
});

function prepareAccountDelete(link) {
    if (!confirm('¿Eliminar esta cuenta?')) {
        return false;
    }

    const preserveAntennas = confirm('¿Desea conservar las antenas asociadas?\n\nAceptar: conservar antenas y desvincularlas.\nCancelar: eliminar también las antenas.');
    if (!preserveAntennas && !confirm('Esta opción eliminará definitivamente las antenas asociadas. ¿Desea continuar?')) {
        return false;
    }

    link.href += '&preserve_antennas=' + (preserveAntennas ? '1' : '0');
    return true;
}

document.addEventListener('app:content-ready', function(){
    document.querySelectorAll('.btn-edit-account').forEach(btn => {
        btn.addEventListener('click', function(){
            document.getElementById('id_accounts').value = this.getAttribute('data-id');
            document.getElementById('account_owner').value = this.getAttribute('data-owner');
            document.getElementById('account_acc').value = this.getAttribute('data-acc');
            document.getElementById('account_email').value = this.getAttribute('data-email');
            document.getElementById('account_country').value = this.getAttribute('data-country') || '';
            document.getElementById('account_date').value = this.getAttribute('data-date');
        });
    });

    document.querySelectorAll('.btn-view-account-starlinks').forEach(btn => {
        btn.addEventListener('click', function(){
            const accountName = this.getAttribute('data-account-name') || 'Cuenta';
            const starlinks = (this.getAttribute('data-starlinks') || '').trim();
            const listContainer = document.getElementById('accountStarlinksList');
            const title = document.getElementById('modalAccountStarlinksTitle');

            title.innerHTML = '<i class="bi bi-broadcast me-2 text-primary"></i>Starlink de ' + accountName;

            if (!starlinks) {
                listContainer.innerHTML = '<span class="text-white-50">Esta cuenta no tiene Starlink asociadas.</span>';
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