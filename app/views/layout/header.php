<header class="app-header d-flex flex-column flex-md-row justify-content-between align-items-center py-3 px-3 gap-3 mb-3">
    <div class="d-flex align-items-center gap-2">
        <button id="desktopSidebarToggleButton" class="btn btn-outline-light btn-sm d-none d-md-inline-block" type="button" onclick="toggleSidebar()" aria-label="Alternar menú de escritorio">
            <i class="bi bi-layout-sidebar-inset"></i>
        </button>
        <button class="btn btn-outline-light btn-sm d-md-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileSidebar" aria-controls="mobileSidebar">
            <i class="bi bi-list"></i>
        </button>
        <h2 class="app-header-title h4 text-white mb-0">Sistema de Gestión</h2>
    </div>

    <div class="dropdown ms-auto">
        <button class="btn app-user-button dropdown-toggle d-flex align-items-center gap-2" type="button" data-bs-toggle="dropdown" aria-expanded="false">
            <span class="app-avatar d-inline-flex align-items-center justify-content-center rounded-circle">
                <i class="bi bi-person-fill"></i>
            </span>
            <span class="d-none d-md-inline text-white fw-semibold">
                <?php echo isset($user) ? htmlspecialchars($user) : (isset($_SESSION['user']) ? htmlspecialchars($_SESSION['user']) : 'Invitado'); ?>
            </span>
        </button>
        <ul class="dropdown-menu dropdown-menu-dark dropdown-menu-end shadow-lg border-0 rounded-4 p-2">
            <li>
                <span class="dropdown-item-text text-white-50 small px-2">Usuario activo</span>
            </li>
            <li><hr class="dropdown-divider border-secondary"></li>
            <li>
                <a class="dropdown-item rounded-3" href="index.php?url=auth/manage_security">
                    <i class="bi bi-shield-lock me-2"></i> Seguridad
                </a>
            </li>
            <li>
                <a class="dropdown-item rounded-3 text-danger" href="index.php?url=logout">
                    <i class="bi bi-box-arrow-right me-2"></i> Cerrar sesión
                </a>
            </li>
        </ul>
    </div>
</header>