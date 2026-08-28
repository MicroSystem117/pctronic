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
    <?php if(!empty($data['plans'])): ?>
        <div class="row g-3" id="plansCards">
            <?php foreach($data['plans'] as $p): ?>
                <div class="col-md-6 col-xl-4 plan-card"
                     data-plan="<?php echo htmlspecialchars($p['plan'], ENT_QUOTES); ?>"
                     data-price="<?php echo htmlspecialchars((string)($p['price'] ?? 0), ENT_QUOTES); ?>">
                    <div class="card h-100 card-custom p-4 border-start border-primary border-4">
                        <div class="d-flex justify-content-between align-items-start gap-2">
                            <div>
                                <div class="text-white-50 small text-uppercase mb-1">Plan</div>
                                <h4 class="mb-1"><?php echo htmlspecialchars($p['plan']); ?></h4>
                            </div>
                            <span class="badge bg-primary text-dark fs-6">
                                <?php echo isset($p['price']) ? '$'.number_format($p['price'], 0, ',', '.') : '-'; ?>
                            </span>
                        </div>

                        <div class="mt-4 small text-white-50 d-flex justify-content-between align-items-center">
                            <span>Antenas asociadas</span>
                            <span class="badge bg-secondary"><?php echo isset($p['antenna_count']) ? (int)$p['antenna_count'] : 0; ?></span>
                        </div>

                        <?php if ($isAdmin): ?>
                            <div class="mt-3 d-flex gap-2">
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
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="text-center py-4 text-white-50">No hay planes registrados.</div>
    <?php endif; ?>
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
document.addEventListener('app:content-ready', function(){
    document.querySelectorAll('.btn-edit-plan').forEach(btn => {
        btn.addEventListener('click', function(){
            document.getElementById('id_plan').value = this.getAttribute('data-id');
            document.getElementById('plan_name').value = this.getAttribute('data-plan');
            document.getElementById('plan_price').value = this.getAttribute('data-price');
        });
    });

    // Buscador local para planes en vista tipo tarjetas
    const searchPlans = document.getElementById('search_plans');
    if (searchPlans) {
        searchPlans.addEventListener('input', function(){
            const q = this.value.trim().toLowerCase();
            const cards = document.querySelectorAll('.plan-card');
            cards.forEach(card => {
                const text = card.textContent.toLowerCase();
                card.style.display = q === '' || text.includes(q) ? '' : 'none';
            });
        });
    }
});
</script>
