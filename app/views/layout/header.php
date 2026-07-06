<header class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center py-2 gap-2">
    <div class="d-flex align-items-center gap-2">
        <button id="desktopSidebarToggleButton" class="btn btn-outline-light btn-sm d-none d-md-inline-block" type="button" onclick="toggleDesktopSidebar()" aria-label="Alternar menú de escritorio">
            <i class="bi bi-layout-sidebar-inset"></i>
        </button>
        <button class="btn btn-outline-light btn-sm d-md-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileSidebar" aria-controls="mobileSidebar">
            <i class="bi bi-list"></i>
        </button>
        <h2 class="h4 text-white mb-0">Sistema de Gestión</h2>
    </div>
    <div class="text-start text-md-end">
        <span class="badge bg-secondary p-2"><i class="bi bi-person-circle me-1"></i> <?php echo isset($user) ? htmlspecialchars($user) : (isset($_SESSION['user']) ? htmlspecialchars($_SESSION['user']) : 'Invitado'); ?><?php echo isset($_SESSION['user_role']) ? ' - ' . htmlspecialchars($_SESSION['user_role']) : ''; ?></span>
    </div>
</header>