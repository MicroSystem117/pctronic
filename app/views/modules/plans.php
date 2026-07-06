<?php
$userRole = isset($_SESSION['user_role']) ? $_SESSION['user_role'] : '';
$isAdmin = $userRole === 'Administrador';
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-list-ul"></i> Planes de Servicio</h2>
    <?php if ($isAdmin): ?>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalPlan">
            <i class="bi bi-plus-circle"></i> Agregar Plan
        </button>
    <?php endif; ?>
</div>

<div class="mb-3 d-flex justify-content-end">
    <input id="search_plans" class="form-control form-control-sm w-25" placeholder="Buscar planes...">
</div>

<div class="card card-custom p-3">
    <div class="table-responsive">
        <table class="table table-dark table-hover mb-0">
            <thead class="table-light">
                <tr>
                            <th>ID</th>
                            <th>Plan</th>
                            <th>Precio</th>
                            <?php if ($isAdmin): ?>
                                <th>Acciones</th>
                            <?php endif; ?>
                        </tr>
            </thead>
            <tbody>
                <?php if(!empty($data['plans'])): ?>
                    <?php foreach($data['plans'] as $p): ?>
                        <tr>
                            <td><?php echo $p['id_plan']; ?></td>
                            <td><?php echo $p['plan']; ?></td>
                            <td><?php echo isset($p['price']) ? '$'.number_format($p['price'], 0, ',', '.') : '-'; ?></td>
                            <?php if ($isAdmin): ?>
                                <td>
                                    <button class="btn btn-sm btn-info btn-edit-plan" 
                                            data-id="<?php echo $p['id_plan']; ?>"
                                            data-plan="<?php echo htmlspecialchars($p['plan'], ENT_QUOTES); ?>"
                                            data-price="<?php echo isset($p['price']) ? $p['price'] : 0; ?>"
                                            data-bs-toggle="modal" data-bs-target="#modalPlan">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <a href="index.php?url=plans&action=delete&id=<?php echo $p['id_plan']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Eliminar plan?');">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                </td>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="3" class="text-center py-3 text-muted">No hay planes registrados.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php if ($isAdmin): ?>
<div class="modal fade" id="modalPlan" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content bg-dark text-white border-secondary">
            <div class="modal-header border-secondary">
                <h5 class="modal-title"><i class="bi bi-plus-circle-fill"></i> Plan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="index.php?url=plans" method="POST">
                <input type="hidden" id="id_plan" name="id_plan" value="0">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nombre del Plan <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="plan_name" name="plan" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Precio (numérico) <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="plan_price" name="price" min="0" required>
                    </div>
                </div>
                <div class="modal-footer border-secondary">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', function(){
    document.querySelectorAll('.btn-edit-plan').forEach(btn => {
        btn.addEventListener('click', function(){
            document.getElementById('id_plan').value = this.getAttribute('data-id');
            document.getElementById('plan_name').value = this.getAttribute('data-plan');
            document.getElementById('plan_price').value = this.getAttribute('data-price');
        });
    });

    // Buscador local para planes
    const searchPlans = document.getElementById('search_plans');
    if (searchPlans) {
        searchPlans.addEventListener('input', function(){
            const q = this.value.trim().toLowerCase();
            const rows = document.querySelectorAll('.card .table tbody tr');
            rows.forEach(r => r.style.display = q === '' ? '' : (r.textContent.toLowerCase().includes(q) ? '' : 'none'));
        });
    }
});
</script>
