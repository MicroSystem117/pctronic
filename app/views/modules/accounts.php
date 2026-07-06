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
        <table class="table table-dark table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>ID Cuenta</th>
                    <th>Titular / Propietario</th>
                    <th>Número de Cuenta (ACC)</th>
                    <th>Email</th>
                    <th>Fecha de Registro</th>
                    <?php if ($isAdmin): ?>
                        <th>Acciones</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php if(!empty($data['accounts'])): ?>
                    <?php foreach($data['accounts'] as $account): ?>
                        <tr>
                            <td><?php echo $account['id_accounts']; ?></td>
                            <td><strong><?php echo $account['owner']; ?></strong></td>
                            <td><code><?php echo $account['acc']; ?></code></td>
                            <td><?php echo htmlspecialchars($account['email']); ?></td>
                            <td><?php echo date('d/m/Y', strtotime($account['create_date'])); ?></td>
                    <?php if ($isAdmin): ?>
                        <td>
                            <button class="btn btn-sm btn-info btn-edit-account"
                                    data-id="<?php echo $account['id_accounts']; ?>"
                                    data-owner="<?php echo htmlspecialchars($account['owner'], ENT_QUOTES); ?>"
                                    data-acc="<?php echo htmlspecialchars($account['acc'], ENT_QUOTES); ?>"
                                    data-email="<?php echo htmlspecialchars($account['email'], ENT_QUOTES); ?>"
                                    data-date="<?php echo $account['create_date']; ?>"
                                    data-bs-toggle="modal" data-bs-target="#modalAccount">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <a href="index.php?url=accounts&action=delete&id=<?php echo $account['id_accounts']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('¿Eliminar cuenta?');">
                                <i class="bi bi-trash"></i>
                            </a>
                        </td>
                    <?php endif; ?>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="text-center py-4 text-muted">
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

<script>
document.addEventListener('DOMContentLoaded', function(){
    document.querySelectorAll('.btn-edit-account').forEach(btn => {
        btn.addEventListener('click', function(){
            document.getElementById('id_accounts').value = this.getAttribute('data-id');
            document.getElementById('account_owner').value = this.getAttribute('data-owner');
            document.getElementById('account_acc').value = this.getAttribute('data-acc');
            document.getElementById('account_email').value = this.getAttribute('data-email');
            document.getElementById('account_date').value = this.getAttribute('data-date');
        });
    });

    // Buscador para cuentas
    const searchAccountsInput = document.getElementById('search_accounts');
    if (searchAccountsInput) {
        searchAccountsInput.addEventListener('input', function(){
            const q = this.value.trim().toLowerCase();
            const rows = document.querySelectorAll('.card .table tbody tr');
            rows.forEach(r => r.style.display = q === '' ? '' : (r.textContent.toLowerCase().includes(q) ? '' : 'none'));
        });
    }
});
</script>