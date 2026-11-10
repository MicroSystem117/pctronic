<?php

class AntenaController extends Controller {
    private $antenaModel;
    private $accountModel;

    public function __construct() {
        $this->antenaModel = new AntenaModel();
        $this->accountModel = new AccountModel();
    }

    public function index() {
        // 1. Procesar inserción o acciones si vienen por POST
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCsrfToken();

            if (!$this->isAdmin()) {
                header('Location: index.php?url=antenas&status=access_denied');
                exit();
            }

            $action = $_POST['action'] ?? null;
            if ($action === 'delete') {
                $id = isset($_POST['id']) ? intval($_POST['id']) : (isset($_POST['id_starlink']) ? intval($_POST['id_starlink']) : 0);
                if ($id > 0) {
                    $deleted = $this->antenaModel->delete($id);
                    $status = $deleted ? 'deleted' : 'error';
                    if ($deleted) {
                        LogModel::log('ELIMINAR', 'Antenas', "Eliminó la antena ID $id");
                    }
                    header("Location: index.php?url=antenas&status=" . $status);
                    exit();
                }
            }

            // Determinar si es creación o actualización mediante un id_starlink en el POST
            $id_starlink = isset($_POST['id_starlink']) ? intval($_POST['id_starlink']) : 0;
            $client_id  = isset($_POST['client']) ? intval($_POST['client']) : 0;
            $account_id = isset($_POST['account_id']) && $_POST['account_id'] !== '' ? intval($_POST['account_id']) : null;
            $serial     = isset($_POST['serial']) ? trim($_POST['serial']) : '';
            $nickname   = isset($_POST['nickname']) ? trim($_POST['nickname']) : '';
            $kit        = isset($_POST['kit']) ? trim($_POST['kit']) : '';
            $date       = isset($_POST['date']) ? $_POST['date'] : date('Y-m-d');
            $pay        = isset($_POST['pay']) && $_POST['pay'] !== '' ? intval($_POST['pay']) : null;
            $plan_id    = isset($_POST['plan']) ? intval($_POST['plan']) : 0;
            $country_id = $account_id ? $this->accountModel->getCountryId($account_id) : 0;

            if ($account_id === null || $account_id <= 0 || $country_id <= 0) {
                header('Location: index.php?url=antenas&status=account_country_required');
                exit();
            }

            if ($client_id > 0 && $account_id !== null && $account_id > 0 && !empty($serial) && !empty($kit) && $plan_id > 0 && $country_id > 0) {
                // Validar día de pago si viene definido
                if ($pay !== null && ($pay < 1 || $pay > 31)) {
                    header("Location: index.php?url=antenas&status=invalid_pay");
                    exit();
                }

                if ($this->antenaModel->checkSerialExists($serial, $id_starlink)) {
                    header("Location: index.php?url=antenas&status=antenna_exists");
                    exit();
                }

                if ($id_starlink > 0) {
                    $saved = $this->antenaModel->update($id_starlink, $client_id, $account_id, $serial, $nickname, $kit, $date, $pay, $plan_id, $country_id);
                    $status = $saved ? 'updated' : 'error';
                    if ($saved) {
                        LogModel::log('EDITAR', 'Antenas', "Actualizó la antena serial '$serial'" . ($nickname ? " ($nickname)" : "") . " (ID: $id_starlink)");
                    }
                } else {
                    $saved = $this->antenaModel->register($client_id, $account_id, $serial, $nickname, $kit, $date, $pay, $plan_id, $country_id);
                    $status = $saved ? 'success' : 'error';
                    if ($saved) {
                        LogModel::log('CREAR', 'Antenas', "Registró nueva antena serial '$serial'" . ($nickname ? " ($nickname)" : ""));
                    }
                }

                header("Location: index.php?url=antenas&status=" . $status);
                exit();
            } else {
                header("Location: index.php?url=antenas&status=empty");
                exit();
            }
        }

        // 2. Cargar listas auxiliares dinámicas para los SELECT del formulario modal (solo administradores y moderadores)
        $isExp    = $this->isExpectador();
        $clientes = !$isExp ? (new ClientModel())->getSelectOptions() : [];
        $planes   = (new PlanModel())->getSelectOptions();
        $cuentas  = !$isExp ? $this->accountModel->getSelectOptions() : [];

        // 3. Obtener el listado principal de antenas
        $antenasList = $this->antenaModel->getAll($this->getUserRole(), $this->getUserCi());

        $data = [
            'page_title' => 'Gestión de Antenas - Starlink Control',
            'antenas'    => $antenasList,
            'clientes'   => $clientes,
            'planes'     => $planes,
            'cuentas'    => $cuentas
        ];

        // Renderizar la vista
        $this->render('modules/antenas', $data);
    }
}