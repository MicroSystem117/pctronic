<?php
$currentRoute = isset($_GET['url']) ? $_GET['url'] : 'dashboard';
$userRole = isset($_SESSION['user_role']) ? $_SESSION['user_role'] : '';
$isLoggedIn = isset($_SESSION['user_id']);
$isExpectador = $userRole === 'Expectador';

$guestItems = [
    [
        'url' => 'login',
        'icon' => 'bi bi-box-arrow-in-right',
        'label' => 'Iniciar Sesión',
    ],
    [
        'url' => 'register',
        'icon' => 'bi bi-person-plus',
        'label' => 'Registrarse',
    ],
];

$menuItems = [
    [
        'url' => 'dashboard',
        'icon' => 'bi bi-speedometer2',
        'label' => 'Dashboard',
        'show' => true,
    ],
    [
        'url' => 'countries',
        'icon' => 'bi bi-geo-alt',
        'label' => 'Países',
        'show' => !$isExpectador,
    ],
    [
        'url' => 'plans',
        'icon' => 'bi bi-list-ul',
        'label' => 'Planes',
        'show' => true,
    ],
    [
        'url' => 'clients',
        'icon' => 'bi bi-people',
        'label' => 'Clientes',
        'show' => !$isExpectador,
    ],
    [
        'url' => 'accounts',
        'icon' => 'bi bi-person-badge',
        'label' => 'Cuentas',
        'show' => !$isExpectador,
    ],
    [
        'url' => 'antenas',
        'icon' => 'bi bi-router',
        'label' => 'Antenas',
        'show' => true,
    ],
    [
        'url' => 'payments',
        'icon' => 'bi bi-currency-dollar',
        'label' => 'Pagos',
        'show' => true,
    ],
    [
        'url' => 'database',
        'icon' => 'bi bi-hdd-network',
        'label' => 'Base de Datos',
        'show' => $userRole === 'Administrador',
    ],
    [
        'url' => 'users',
        'icon' => 'bi bi-person-gear',
        'label' => 'Usuarios',
        'show' => $userRole === 'Administrador' || $userRole === 'Moderador',
    ],
];
?>
<div class="d-flex flex-column p-3 text-white sidebar-panel" id="sidebarPanel">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <a href="index.php?url=dashboard" class="d-flex align-items-center me-md-auto text-white text-decoration-none sidebar-brand">
            <img src="<?php echo htmlspecialchars(app_url('assets/Logo.png'), ENT_QUOTES, 'UTF-8'); ?>" alt="PCtronic" class="img-fluid sidebar-logo" style="max-height: 52px; width: auto;">
        </a>
        <button class="btn btn-sm btn-outline-light sidebar-toggle" type="button" onclick="toggleSidebar()" aria-label="Plegar menú">
            <i class="bi bi-list"></i>
        </button>
    </div>
    <hr class="border-secondary">
    <ul class="nav nav-pills flex-column mb-auto sidebar-nav">
        <?php if (!$isLoggedIn): ?>
            <?php foreach ($guestItems as $item): ?>
                <li class="nav-item">
                    <a href="index.php?url=<?php echo $item['url']; ?>" class="nav-link <?php echo $currentRoute === $item['url'] ? 'active' : ''; ?> mb-2">
                        <i class="<?php echo $item['icon']; ?> me-2"></i> <?php echo htmlspecialchars($item['label'], ENT_QUOTES, 'UTF-8'); ?>
                    </a>
                </li>
            <?php endforeach; ?>
        <?php else: ?>
            <?php foreach ($menuItems as $item): ?>
                <?php if (!$item['show']) continue; ?>
                <li class="nav-item">
                    <a href="index.php?url=<?php echo $item['url']; ?>" class="nav-link <?php echo ($currentRoute === $item['url'] || ($item['url'] === 'dashboard' && ($currentRoute === '' || $currentRoute === 'dashboard'))) ? 'active' : ''; ?> mb-2">
                        <i class="<?php echo $item['icon']; ?> me-2"></i> <?php echo htmlspecialchars($item['label'], ENT_QUOTES, 'UTF-8'); ?>
                    </a>
                </li>
            <?php endforeach; ?>
        <?php endif; ?>
    </ul>
</div>

<script>
function toggleSidebar() {
    const desktopSidebar = document.getElementById('desktopSidebar');
    const overlay = document.getElementById('sidebarOverlay');
    const panel = document.getElementById('sidebarPanel');
    const headerButton = document.getElementById('desktopSidebarToggleButton');
    const isOpen = desktopSidebar ? desktopSidebar.classList.toggle('sidebar-open') : false;

    if (overlay) {
        overlay.classList.toggle('show', isOpen);
    }

    if (panel) {
        panel.classList.toggle('collapsed', !isOpen);
    }

    if (headerButton) {
        const icon = headerButton.querySelector('i');
        if (icon) {
            icon.className = isOpen ? 'bi bi-layout-sidebar-inset-reverse' : 'bi bi-layout-sidebar-inset';
        }
    }

    const nav = panel ? panel.querySelector('.sidebar-nav') : null;
    if (nav) {
        nav.style.display = isOpen ? '' : 'none';
    }

    const brand = panel ? panel.querySelector('.sidebar-brand') : null;
    if (brand) {
        brand.style.justifyContent = isOpen ? 'flex-start' : 'center';
    }
}

document.addEventListener('DOMContentLoaded', function () {
    const overlay = document.getElementById('sidebarOverlay');
    if (overlay) {
        overlay.addEventListener('click', function () {
            const desktopSidebar = document.getElementById('desktopSidebar');
            if (desktopSidebar && desktopSidebar.classList.contains('sidebar-open')) {
                desktopSidebar.classList.remove('sidebar-open');
                overlay.classList.remove('show');
                const headerButton = document.getElementById('desktopSidebarToggleButton');
                if (headerButton) {
                    const icon = headerButton.querySelector('i');
                    if (icon) {
                        icon.className = 'bi bi-layout-sidebar-inset';
                    }
                }
            }
        });
    }
});
</script>