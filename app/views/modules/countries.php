<?php
$userRole = isset($_SESSION['user_role']) ? $_SESSION['user_role'] : '';
$isAdmin = $userRole === 'Administrador';
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-geo-alt"></i> Países / Regiones</h2>
    <?php if ($isAdmin): ?>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalCountry">
            <i class="bi bi-plus-circle"></i> Agregar País
        </button>
    <?php endif; ?>
</div>

<div class="mb-3 d-flex justify-content-end">
    <input id="search_countries" class="form-control form-control-sm w-25" placeholder="Buscar países...">
</div>

<div class="card card-custom p-3">
    <?php if(!empty($data['countries'])): ?>
        <div class="row g-3" id="countriesCards">
            <?php foreach($data['countries'] as $c): ?>
                <div class="col-md-6 col-xl-4 country-card"
                     data-country="<?php echo htmlspecialchars($c['country'], ENT_QUOTES); ?>">
                    <div class="card h-100 card-custom p-4 border-start border-info border-4">
                        <div class="d-flex justify-content-between align-items-start gap-2">
                            <div>
                                <div class="text-white-50 small text-uppercase mb-1">País / Región</div>
                                <h4 class="mb-1"><?php echo htmlspecialchars($c['country']); ?></h4>
                            </div>
                            <i class="bi bi-geo-alt-fill fs-3 text-info"></i>
                        </div>

                        <?php if ($isAdmin): ?>
                            <div class="mt-4 d-flex gap-2">
                                <button class="btn btn-sm btn-info btn-edit-country"
                                        data-id="<?php echo $c['id_country']; ?>"
                                        data-country="<?php echo htmlspecialchars($c['country'], ENT_QUOTES); ?>"
                                        data-bs-toggle="modal" data-bs-target="#modalCountry">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <a href="index.php?url=countries&action=delete&id=<?php echo $c['id_country']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Eliminar país?');">
                                    <i class="bi bi-trash"></i>
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="text-center py-4 text-white-50">No hay países registrados.</div>
    <?php endif; ?>
</div>

<?php if ($isAdmin): ?>
<div class="modal fade" id="modalCountry" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content bg-dark text-white border-secondary">
            <div class="modal-header border-secondary">
                <h5 class="modal-title"><i class="bi bi-plus-circle-fill"></i> País / Región</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="index.php?url=countries" method="POST">
                <input type="hidden" id="id_country" name="id_country" value="0">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nombre del País <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="country_name" name="country" required>
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
    document.querySelectorAll('.btn-edit-country').forEach(btn => {
        btn.addEventListener('click', function(){
            document.getElementById('id_country').value = this.getAttribute('data-id');
            document.getElementById('country_name').value = this.getAttribute('data-country');
        });
    });

    // Buscador local para países en vista tipo tarjetas
    const searchCountries = document.getElementById('search_countries');
    if (searchCountries) {
        searchCountries.addEventListener('input', function(){
            const q = this.value.trim().toLowerCase();
            const cards = document.querySelectorAll('.country-card');
            cards.forEach(card => {
                const text = card.textContent.toLowerCase();
                card.style.display = q === '' || text.includes(q) ? '' : 'none';
            });
        });
    }
});
</script>
