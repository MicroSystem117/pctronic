<?php
$userRole = isset($_SESSION['user_role']) ? $_SESSION['user_role'] : '';
$isExpectador = $userRole === 'Expectador';
$currentUrl = isset($_GET['url']) ? rtrim($_GET['url'], '/') : 'dashboard';
?>
<!-- Barra de Navegación Inferior Nativa para Móviles (Visible solo en pantallas < 768px) -->
<nav class="bottom-nav d-md-none" id="mobileBottomNav" aria-label="Navegación móvil inferior">
    <div class="bottom-nav-container">
        <!-- 1. Inicio -->
        <a href="index.php?url=dashboard" class="bottom-nav-item <?php echo ($currentUrl === 'dashboard' || empty($currentUrl)) ? 'active' : ''; ?>">
            <i class="bi bi-speedometer2 bottom-nav-icon"></i>
            <span class="bottom-nav-label">Inicio</span>
        </a>

        <!-- 2. Antenas -->
        <a href="index.php?url=antenas" class="bottom-nav-item <?php echo ($currentUrl === 'antenas') ? 'active' : ''; ?>">
            <i class="bi bi-broadcast bottom-nav-icon"></i>
            <span class="bottom-nav-label">Antenas</span>
        </a>

        <!-- 3. Pagos -->
        <a href="index.php?url=payments" class="bottom-nav-item <?php echo ($currentUrl === 'payments') ? 'active' : ''; ?>">
            <i class="bi bi-cash-stack bottom-nav-icon"></i>
            <span class="bottom-nav-label">Pagos</span>
        </a>

        <?php if (!$isExpectador): ?>
            <!-- 4. Clientes (Admin / Mod) -->
            <a href="index.php?url=clients" class="bottom-nav-item <?php echo ($currentUrl === 'clients') ? 'active' : ''; ?>">
                <i class="bi bi-people bottom-nav-icon"></i>
                <span class="bottom-nav-label">Clientes</span>
            </a>
        <?php else: ?>
            <!-- 4. Seguridad (Expectador) -->
            <a href="index.php?url=auth/manage_security" class="bottom-nav-item <?php echo ($currentUrl === 'auth/manage_security') ? 'active' : ''; ?>">
                <i class="bi bi-shield-lock bottom-nav-icon"></i>
                <span class="bottom-nav-label">Seguridad</span>
            </a>
        <?php endif; ?>

        <!-- 5. Más Opciones (Abre Offcanvas) -->
        <button type="button" class="bottom-nav-item btn-link" data-bs-toggle="offcanvas" data-bs-target="#mobileSidebar" aria-controls="mobileSidebar" aria-label="Abrir menú completo">
            <i class="bi bi-grid-fill bottom-nav-icon"></i>
            <span class="bottom-nav-label">Menú</span>
        </button>
    </div>
</nav>

