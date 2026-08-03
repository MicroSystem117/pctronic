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
        'url' => 'auth/manage_security',
        'icon' => 'bi bi-shield-lock',
        'label' => 'Seguridad',
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
        'show' => true,
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
        'show' => !$isExpectador,
    ],
];
?>
<div class="d-flex flex-column p-3 text-white sidebar-panel" id="sidebarPanel">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <a href="index.php?url=dashboard" class="d-flex align-items-center me-md-auto text-white text-decoration-none sidebar-brand">
            <i class="bi bi-rocket-takeoff-fill me-2 fs-4 text-primary"></i>
            <span class="fs-5 fw-bold sidebar-label">Starlink Control</span>
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
            <li>
                <a href="index.php?url=logout" class="nav-link text-danger mb-2">
                    <i class="bi bi-box-arrow-right me-2"></i> Cerrar Sesión
                </a>
            </li>
        <?php endif; ?>
    </ul>
</div>

<script>
function toggleSidebar() {
    const panel = document.getElementById('sidebarPanel');
    const desktopSidebar = document.getElementById('desktopSidebar');
    const mainContent = document.getElementById('mainContent');
    const isCollapsed = panel.classList.toggle('collapsed');

    if (desktopSidebar) {
        desktopSidebar.classList.toggle('sidebar-hidden', isCollapsed);
    }

    if (mainContent) {
        mainContent.classList.toggle('full-width-content', isCollapsed);
    }

    const label = panel ? panel.querySelector('.sidebar-label') : null;
    if (label) {
        label.style.display = isCollapsed ? 'none' : '';
    }

    const nav = panel ? panel.querySelector('.sidebar-nav') : null;
    if (nav) {
        nav.style.display = isCollapsed ? 'none' : '';
    }

    const button = panel ? panel.querySelector('.sidebar-toggle i') : null;
    if (button) {
        button.className = isCollapsed ? 'bi bi-layout-sidebar-inset-reverse' : 'bi bi-list';
    }
}

function toggleDesktopSidebar() {
    const desktopSidebar = document.getElementById('desktopSidebar');
    const mainContent = document.getElementById('mainContent');
    const panel = document.getElementById('sidebarPanel');
    const headerButton = document.getElementById('desktopSidebarToggleButton');
    const isHidden = desktopSidebar ? desktopSidebar.classList.toggle('sidebar-hidden') : false;

    if (mainContent) {
        mainContent.classList.toggle('full-width-content', isHidden);
    }
    if (panel) {
        panel.classList.toggle('collapsed', isHidden);
        const label = panel.querySelector('.sidebar-label');
        if (label) {
            label.style.display = isHidden ? 'none' : '';
        }
        const nav = panel.querySelector('.sidebar-nav');
        if (nav) {
            nav.style.display = isHidden ? 'none' : '';
        }
        const button = panel.querySelector('.sidebar-toggle i');
        if (button) {
            button.className = isHidden ? 'bi bi-layout-sidebar-inset-reverse' : 'bi bi-list';
        }
    }
    if (headerButton) {
        const icon = headerButton.querySelector('i');
        if (icon) {
            icon.className = isHidden ? 'bi bi-layout-sidebar-inset-reverse' : 'bi bi-layout-sidebar-inset';
        }
    }
}
</script>