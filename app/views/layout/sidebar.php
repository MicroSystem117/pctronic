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

// Telemetría rápida en vivo para micro-badges
$suspendedCount = 0;
$pendingPaymentsCount = 0;
if ($isLoggedIn) {
    try {
        $db = Database::getInstance()->getConnection();
        $st1 = $db->query("SELECT COUNT(*) FROM antenas WHERE is_suspended = 1");
        if ($st1) {
            $suspendedCount = (int)$st1->fetchColumn();
        }
        $st2 = $db->query("SELECT COUNT(*) FROM payments WHERE status = 'Pendiente'");
        if ($st2) {
            $pendingPaymentsCount = (int)$st2->fetchColumn();
        }
    } catch (\Throwable $e) {
        // Fallback silencioso
    }
}

$menuSections = [
    'Operaciones y Telemetría' => [
        [
            'url' => 'dashboard',
            'icon' => 'bi bi-speedometer2',
            'label' => 'Dashboard',
            'badge' => 'LIVE',
            'badge_class' => 'badge-pulse-live',
            'show' => true,
        ],
        [
            'url' => 'antenas',
            'icon' => 'bi bi-broadcast',
            'label' => 'Antenas Starlink',
            'badge' => $suspendedCount > 0 ? "$suspendedCount susp." : null,
            'badge_class' => 'bg-warning-subtle text-warning border border-warning',
            'show' => true,
        ],
        [
            'url' => 'payments',
            'icon' => 'bi bi-currency-dollar',
            'label' => 'Cobranzas y Pagos',
            'badge' => $pendingPaymentsCount > 0 ? "$pendingPaymentsCount pend." : null,
            'badge_class' => 'bg-danger-subtle text-danger border border-danger',
            'show' => true,
        ],
        [
            'url' => 'clients',
            'icon' => 'bi bi-people',
            'label' => 'Directorio Clientes',
            'show' => !$isExpectador,
        ],
    ],
    'Gestión y Catálogo' => [
        [
            'url' => 'accounts',
            'icon' => 'bi bi-building',
            'label' => 'Cuentas Starlink',
            'show' => !$isExpectador,
        ],
        [
            'url' => 'plans',
            'icon' => 'bi bi-box-seam',
            'label' => 'Planes de Servicio',
            'show' => true,
        ],
        [
            'url' => 'countries',
            'icon' => 'bi bi-globe-americas',
            'label' => 'Países y Regiones',
            'show' => !$isExpectador,
        ],
    ],
    'Sistema y Seguridad' => [
        [
            'url' => 'users',
            'icon' => 'bi bi-person-gear',
            'label' => 'Gestión de Usuarios',
            'show' => $userRole === 'Administrador' || $userRole === 'Moderador',
        ],
        [
            'url' => 'logs',
            'icon' => 'bi bi-shield-check',
            'label' => 'Auditoría y Logs',
            'show' => $userRole === 'Administrador' || $userRole === 'Moderador',
        ],
        [
            'url' => 'database',
            'icon' => 'bi bi-database-check',
            'label' => 'Base de Datos y Backup',
            'show' => $userRole === 'Administrador',
        ],
    ],
];
?>
<div class="d-flex flex-column h-100 p-3 text-white sidebar-panel" id="sidebarPanel">
    <!-- Header de la Barra Lateral con Logo Oficial -->
    <div class="d-flex align-items-center justify-content-between mb-2">
        <a href="index.php?url=dashboard" class="d-flex align-items-center me-md-auto text-white text-decoration-none sidebar-brand">
            <img src="<?php echo htmlspecialchars(app_url('assets/Logo.png'), ENT_QUOTES, 'UTF-8'); ?>" alt="Starlink Control Manager" class="img-fluid sidebar-logo" style="max-height: 48px; width: auto; object-fit: contain;">
        </a>
        <button class="btn btn-sm btn-outline-secondary text-white-50 sidebar-toggle" type="button" onclick="toggleSidebar()" aria-label="Cerrar menú">
            <i class="bi bi-x-lg"></i>
        </button>
    </div>

    <hr class="border-secondary border-opacity-25 my-2">

    <!-- Navegación Estructurada por Secciones -->
    <div class="sidebar-scrollable flex-grow-1 overflow-y-auto pe-1">
        <ul class="nav nav-pills flex-column sidebar-nav mb-0">
            <?php if (!$isLoggedIn): ?>
                <div class="sidebar-section-title">ACCESO</div>
                <?php foreach ($guestItems as $item): ?>
                    <li class="nav-item mb-1">
                        <a href="index.php?url=<?php echo $item['url']; ?>" class="nav-link <?php echo $currentRoute === $item['url'] ? 'active' : ''; ?>">
                            <div class="d-flex align-items-center">
                                <i class="<?php echo $item['icon']; ?> me-2 fs-6"></i>
                                <span class="nav-label"><?php echo htmlspecialchars($item['label'], ENT_QUOTES, 'UTF-8'); ?></span>
                            </div>
                        </a>
                    </li>
                <?php endforeach; ?>
            <?php else: ?>
                <?php foreach ($menuSections as $sectionTitle => $items): ?>
                    <?php 
                        // Verificar si al menos un item de la sección está visible
                        $hasVisibleItems = false;
                        foreach ($items as $it) {
                            if (!empty($it['show'])) {
                                $hasVisibleItems = true;
                                break;
                            }
                        }
                        if (!$hasVisibleItems) continue;
                    ?>
                    <div class="sidebar-section-title"><?php echo htmlspecialchars($sectionTitle, ENT_QUOTES, 'UTF-8'); ?></div>
                    <?php foreach ($items as $item): ?>
                        <?php if (!$item['show']) continue; ?>
                        <?php 
                            $isActive = ($currentRoute === $item['url'] || ($item['url'] === 'dashboard' && ($currentRoute === '' || $currentRoute === 'dashboard')));
                        ?>
                        <li class="nav-item mb-1">
                            <a href="index.php?url=<?php echo $item['url']; ?>" class="nav-link <?php echo $isActive ? 'active' : ''; ?>">
                                <div class="d-flex align-items-center text-truncate">
                                    <i class="<?php echo $item['icon']; ?> me-2 fs-6 flex-shrink-0"></i>
                                    <span class="nav-label text-truncate"><?php echo htmlspecialchars($item['label'], ENT_QUOTES, 'UTF-8'); ?></span>
                                </div>
                                <?php if (!empty($item['badge'])): ?>
                                    <span class="badge <?php echo $item['badge_class'] ?? 'bg-primary'; ?> ms-2" style="font-size: 0.68rem;">
                                        <?php echo htmlspecialchars($item['badge']); ?>
                                    </span>
                                <?php endif; ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                <?php endforeach; ?>
            <?php endif; ?>
        </ul>
    </div>

    <?php if ($isLoggedIn): ?>
        <!-- Ficha de Usuario en la parte inferior (Sidebar Footer) -->
        <div class="sidebar-user-footer pt-2 mt-2 border-top border-secondary border-opacity-25">
            <div class="d-flex align-items-center justify-content-between p-2 rounded-3 bg-dark bg-opacity-75 border border-secondary border-opacity-25">
                <div class="d-flex align-items-center gap-2 overflow-hidden">
                    <div class="user-avatar position-relative flex-shrink-0">
                        <span class="avatar-circle bg-primary text-white d-flex align-items-center justify-content-center rounded-circle fw-bold" style="width: 36px; height: 36px; font-size: 0.85rem;">
                            <?php 
                                $userName = $_SESSION['user'] ?? 'Usuario';
                                $words = explode(' ', trim($userName));
                                $initials = strtoupper(substr($words[0] ?? 'U', 0, 1) . substr($words[1] ?? '', 0, 1));
                                echo htmlspecialchars($initials);
                            ?>
                        </span>
                        <span class="position-absolute bottom-0 end-0 p-1 bg-success border border-dark rounded-circle" style="transform: translate(20%, 20%);" title="En línea"></span>
                    </div>
                    <div class="user-meta text-truncate">
                        <div class="text-white small fw-bold text-truncate" title="<?php echo htmlspecialchars($userName); ?>">
                            <?php echo htmlspecialchars($userName); ?>
                        </div>
                        <div class="d-flex align-items-center gap-1">
                            <span class="badge border border-info-subtle text-info p-0" style="font-size: 0.68rem; background: transparent;">
                                <?php echo htmlspecialchars($userRole ?: 'Operador'); ?>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-1 flex-shrink-0 ms-1">
                    <a href="index.php?url=auth/manage_security" class="btn btn-sm btn-outline-secondary p-1" title="Seguridad y 2FA">
                        <i class="bi bi-shield-lock text-info"></i>
                    </a>
                    <a href="index.php?url=logout" class="btn btn-sm btn-outline-danger p-1" title="Cerrar sesión">
                        <i class="bi bi-box-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
function toggleSidebar() {
    const desktopSidebar = document.getElementById('desktopSidebar');
    const overlay = document.getElementById('sidebarOverlay');
    const headerButton = document.getElementById('desktopSidebarToggleButton');
    const isOpen = desktopSidebar ? desktopSidebar.classList.toggle('sidebar-open') : false;

    if (overlay) {
        overlay.classList.toggle('show', isOpen);
    }

    if (headerButton) {
        const icon = headerButton.querySelector('i');
        if (icon) {
            icon.className = isOpen ? 'bi bi-layout-sidebar-inset-reverse' : 'bi bi-layout-sidebar-inset';
        }
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
