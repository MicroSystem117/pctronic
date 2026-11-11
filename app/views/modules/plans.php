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
    <input id="search_plans" class="form-control form-control-sm w-100 w-md-25" style="max-width: 280px;" placeholder="Buscar planes...">
</div>

<div class="card card-custom p-3">
    <?php if(!empty($data['plans'])): ?>
        <div class="row g-3" id="plansCards">
            <?php foreach($data['plans'] as $p): ?>
                <?php 
                    $priceVal = isset($p['price']) ? (float)$p['price'] : 0.0;
                    $antCount = isset($p['antenna_count']) ? (int)$p['antenna_count'] : 0;
                ?>
                <div class="col-md-6 col-xl-4 plan-card"
                     data-plan="<?php echo htmlspecialchars($p['plan'], ENT_QUOTES); ?>"
                     data-price="<?php echo htmlspecialchars((string)($p['price'] ?? 0), ENT_QUOTES); ?>">
                    <div class="card h-100 card-custom p-4 border-start border-info border-4 shadow-sm position-relative">
                        <div class="d-flex justify-content-between align-items-start gap-2">
                            <div>
                                <span class="badge border border-info-subtle text-info text-uppercase px-2 py-1 mb-2" style="font-size: 0.70rem; background: rgba(56, 189, 248, 0.1);">
                                    <i class="bi bi-broadcast me-1"></i>Suscripción Starlink
                                </span>
                                <h4 class="mb-0 text-white fw-bold"><?php echo htmlspecialchars($p['plan']); ?></h4>
                            </div>
                            <div class="fs-2 text-info opacity-75 metric-icon-glow">
                                <i class="bi bi-hdd-network"></i>
                            </div>
                        </div>

                        <!-- Precio Grande y Tecnológico -->
                        <div class="my-3">
                            <span class="display-6 fw-bold text-white tech-mono">$<?php echo number_format($priceVal, 2); ?></span>
                            <span class="text-white-50 fs-6">/ mes</span>
                        </div>

                        <hr class="border-secondary my-2">

                        <!-- Métricas del Plan -->
                        <div class="small text-white-50 d-flex justify-content-between align-items-center my-2">
                            <span><i class="bi bi-router me-1 text-info"></i>Antenas conectadas</span>
                            <span class="badge bg-info-subtle text-info border border-info-subtle fw-bold tech-mono">
                                <span class="status-pulse-online"></span><?php echo $antCount; ?> activas
                            </span>
                        </div>

                        <div class="small text-white-50 d-flex justify-content-between align-items-center mb-3">
                            <span><i class="bi bi-cash-stack me-1 text-success"></i>Recaudación proyectada</span>
                            <span class="text-success fw-bold tech-mono">$<?php echo number_format($priceVal * $antCount, 2); ?></span>
                        </div>

                        <?php if ($isAdmin): ?>
                            <div class="mt-auto pt-2 d-flex gap-2">
                                <button class="btn btn-sm btn-outline-info flex-fill btn-edit-plan"
                                        data-id="<?php echo $p['id_plan']; ?>"
                                        data-plan="<?php echo htmlspecialchars($p['plan'], ENT_QUOTES); ?>"
                                        data-price="<?php echo isset($p['price']) ? $p['price'] : 0; ?>"
                                        data-bs-toggle="modal" data-bs-target="#modalPlan"
                                        title="Editar plan">
                                    <i class="bi bi-pencil me-1"></i>Editar
                                </button>
                                <a href="index.php?url=plans&action=delete&id=<?php echo $p['id_plan']; ?>" 
                                   class="btn btn-sm btn-outline-danger" 
                                   data-confirm-text="¿Deseas eliminar el plan <?php echo htmlspecialchars($p['plan'], ENT_QUOTES); ?>?"
                                   title="Eliminar plan">
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
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content bg-dark text-white border-secondary">
            <div class="modal-header border-secondary">
                <h5 class="modal-title"><i class="bi bi-plus-circle-fill"></i> Plan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="index.php?url=plans" method="POST">
                <?php echo Controller::getCsrfInput(); ?>
                <input type="hidden" id="id_plan" name="id_plan" value="0">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nombre del Plan <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="plan_name" name="plan" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Precio (numérico) <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="plan_price" name="price" min="0" step="0.01" required>
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
