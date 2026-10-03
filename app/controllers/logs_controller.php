<?php

class LogsController extends Controller {
    private $logModel;

    public function __construct() {
        $this->logModel = new LogModel();
    }

    public function index() {
        if (!$this->isAdmin() && !$this->isModerator()) {
            header('Location: index.php?url=dashboard&status=access_denied');
            exit();
        }

        // Manejo de acciones administrativas (limpieza de registros)
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? null;

            if ($action === 'clear_old') {
                if (!$this->isAdmin()) {
                    header('Location: index.php?url=logs&status=access_denied');
                    exit();
                }
                $days = isset($_POST['days']) ? intval($_POST['days']) : 90;
                if ($days < 7) $days = 7;
                $cleared = $this->logModel->clearOld($days);
                if ($cleared) {
                    LogModel::log('LIMPIAR', 'Auditoría', "Eliminó registros con antigüedad mayor a $days días");
                }
                header('Location: index.php?url=logs&status=' . ($cleared ? 'logs_cleared_old' : 'error'));
                exit();
            }

            if ($action === 'clear_all') {
                if (!$this->isAdmin()) {
                    header('Location: index.php?url=logs&status=access_denied');
                    exit();
                }
                $cleared = $this->logModel->clearAll();
                if ($cleared) {
                    LogModel::log('VACIAR', 'Auditoría', 'Vació todos los registros históricos de auditoría');
                }
                header('Location: index.php?url=logs&status=' . ($cleared ? 'logs_cleared_all' : 'error'));
                exit();
            }
        }

        // Filtros desde GET
        $filters = [
            'from'    => isset($_GET['from']) && !empty($_GET['from']) ? trim($_GET['from']) : null,
            'to'      => isset($_GET['to']) && !empty($_GET['to']) ? trim($_GET['to']) : null,
            'module'  => isset($_GET['module']) && !empty($_GET['module']) ? trim($_GET['module']) : 'all',
            'action'  => isset($_GET['action']) && !empty($_GET['action']) ? trim($_GET['action']) : 'all',
            'search'  => isset($_GET['search']) && !empty($_GET['search']) ? trim($_GET['search']) : null,
            'user_id' => isset($_GET['user_id']) && !empty($_GET['user_id']) ? intval($_GET['user_id']) : null
        ];

        $logs = $this->logModel->getLogs($filters);
        $stats = $this->logModel->getStats();
        $filterOptions = $this->logModel->getFilterOptions();

        // Obtener lista de usuarios para filtro
        $users = [];
        try {
            $db = Database::connect();
            $stmt = $db->query("SELECT id_user, user, name FROM `user` ORDER BY user ASC");
            $users = $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
        } catch (Throwable $e) {
            $users = [];
        }

        $this->render('modules/logs', [
            'logs'          => $logs,
            'stats'         => $stats,
            'filterOptions' => $filterOptions,
            'filters'       => $filters,
            'users'         => $users,
            'isAdmin'       => $this->isAdmin(),
            'isModerator'   => $this->isModerator(),
            'page_title'    => 'Registros de Actividad - Starlink Control'
        ]);
    }
}
