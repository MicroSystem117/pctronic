<?php
$userRole = isset($_SESSION['user_role']) ? $_SESSION['user_role'] : '';
$currentUserId = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : 0;
$isAdmin = $userRole === 'Administrador';
$canManage = !empty($data['can_manage']);
$canEdit = $canManage || $userRole === 'Moderador';
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-person-gear"></i> Gestión de Usuarios</h2>
    <?php if ($canManage): ?>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalUser" type="button">
            <i class="bi bi-person-plus"></i> Nuevo Usuario
        </button>
    <?php endif; ?>
</div>

<div class="mb-3 d-flex justify-content-end">
    <input id="search_users" class="form-control form-control-sm w-25" placeholder="Buscar usuarios...">
</div>

<div class="card card-custom p-3">
    <div class="table-responsive">
        <table class="table table-dark table-hover mb-0 datatable pdf-exportable" data-pdf-title="Usuarios">
            <thead class="table-light">
                <tr>
                    <th>Nombre</th>
                    <th>Cédula</th>
                    <th>Fecha de nacimiento</th>
                    <th>Rol</th>
                    <?php if ($canEdit): ?><th>Acciones</th><?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($data['users'])): ?>
                    <?php foreach ($data['users'] as $user): ?>
                        <?php $isCurrentUser = intval($user['id_user']) === $currentUserId; ?>
                        <tr data-name="<?php echo htmlspecialchars($user['name'] . ' ' . $user['surname'], ENT_QUOTES); ?>"
                            data-ci="<?php echo htmlspecialchars($user['ci'] ?? '', ENT_QUOTES); ?>"
                            data-birth="<?php echo htmlspecialchars($user['birth'] ?? '', ENT_QUOTES); ?>"
                            data-level="<?php echo intval($user['id_level']); ?>">
                            <td><?php echo htmlspecialchars(trim($user['name'] . ' ' . $user['surname'])); ?></td>
                            <td><code><?php echo htmlspecialchars($user['ci'] ?? '-'); ?></code></td>
                            <td><?php echo !empty($user['birth']) ? date('d/m/Y', strtotime($user['birth'])) : '-'; ?></td>
                            <td><span class="badge bg-primary"><?php echo htmlspecialchars($user['user_role'] ?? 'Sin rol'); ?></span></td>
                            <?php if ($canEdit): ?><td>
                                <button class="btn btn-sm btn-info btn-edit-user" type="button"
                                        data-id="<?php echo intval($user['id_user']); ?>"
                                        data-name="<?php echo htmlspecialchars($user['name'], ENT_QUOTES); ?>"
                                        data-surname="<?php echo htmlspecialchars($user['surname'], ENT_QUOTES); ?>"
                                        data-ci="<?php echo htmlspecialchars($user['ci'] ?? '', ENT_QUOTES); ?>"
                                        data-birth="<?php echo htmlspecialchars($user['birth'] ?? '', ENT_QUOTES); ?>"
                                        data-level="<?php echo intval($user['id_level']); ?>"
                                        data-bs-toggle="modal" data-bs-target="#modalUser">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <?php if ($canManage && !$isCurrentUser): ?>
                                    <a href="index.php?url=users&action=delete&id=<?php echo intval($user['id_user']); ?>" class="btn btn-sm btn-danger" onclick="return confirm('¿Eliminar este usuario?');">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                <?php else: ?>
                                    <span class="text-white-50 small ms-1">Sesión actual</span>
                                <?php endif; ?>
                            </td><?php endif; ?>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="<?php echo $canEdit ? 5 : 4; ?>" class="text-center py-4 text-muted">No hay usuarios registrados.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php if ($canManage): ?>
<div class="modal fade" id="modalUser" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content text-white">
            <div class="modal-header border-secondary">
                <h5 class="modal-title" id="modalUserTitle"><i class="bi bi-person-plus-fill"></i> Nuevo Usuario</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <form action="index.php?url=users" method="POST">
                <input type="hidden" id="id_user" name="id_user" value="0">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Nombre <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="user_name" name="name" pattern="[A-Za-zÁÉÍÓÚáéíóúÑñÜüÀ-ÿ' -]+" oninput="this.value = this.value.replace(/[0-9]/g, '')" title="No se permiten números" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Apellido <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="user_surname" name="surname" pattern="[A-Za-zÁÉÍÓÚáéíóúÑñÜüÀ-ÿ' -]+" oninput="this.value = this.value.replace(/[0-9]/g, '')" title="No se permiten números" required>
                        </div>
                    </div>
                    <div class="row g-3 mt-1">
                        <div class="col-md-6">
                            <label class="form-label">Cédula <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="user_ci" name="ci" inputmode="numeric" pattern="[0-9]+" oninput="this.value = this.value.replace(/[^0-9]/g, '')" title="Solo se permiten números" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Fecha de nacimiento</label>
                            <input type="date" class="form-control" id="user_birth" name="birth">
                        </div>
                    </div>
                    <div class="mb-3 mt-3">
                        <label class="form-label">Rol <span class="text-danger">*</span></label>
                        <select class="form-select" id="user_level" name="id_level" required <?php echo !$canManage ? 'disabled' : ''; ?>>
                            <option value="">Seleccionar rol</option>
                            <?php foreach (($data['levels'] ?? []) as $level): ?>
                                <option value="<?php echo intval($level['id_level']); ?>"><?php echo htmlspecialchars($level['user_role']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Contraseña <span id="passwordRequired" class="text-danger">*</span></label>
                        <input type="password" class="form-control" id="user_password" name="password" minlength="6">
                        <div id="passwordHelp" class="form-text text-white-50">Obligatoria para usuarios nuevos. Déjala vacía para conservarla al editar.</div>
                    </div>
                </div>
                <div class="modal-footer border-secondary">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar Usuario</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('modalUser');
    const addButton = document.querySelector('[data-bs-target="#modalUser"]:not(.btn-edit-user)');
    const title = document.getElementById('modalUserTitle');
    const password = document.getElementById('user_password');
    const passwordRequired = document.getElementById('passwordRequired');

    function resetUserForm() {
        document.getElementById('id_user').value = '0';
        document.getElementById('user_name').value = '';
        document.getElementById('user_surname').value = '';
        document.getElementById('user_ci').value = '';
        document.getElementById('user_birth').value = '';
        document.getElementById('user_level').value = '';
        password.value = '';
        password.required = true;
        passwordRequired.style.display = '';
        title.innerHTML = '<i class="bi bi-person-plus-fill"></i> Nuevo Usuario';
    }

    addButton?.addEventListener('click', resetUserForm);
    document.querySelectorAll('.btn-edit-user').forEach(function (button) {
        button.addEventListener('click', function () {
            document.getElementById('id_user').value = this.dataset.id;
            document.getElementById('user_name').value = this.dataset.name;
            document.getElementById('user_surname').value = this.dataset.surname;
            document.getElementById('user_ci').value = this.dataset.ci;
            document.getElementById('user_birth').value = this.dataset.birth;
            document.getElementById('user_level').value = this.dataset.level;
            password.value = '';
            password.required = false;
            passwordRequired.style.display = 'none';
            title.innerHTML = '<i class="bi bi-pencil-square"></i> Editar Usuario';
        });
    });
});
</script>
