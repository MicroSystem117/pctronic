<?php

class AntenaController extends Controller {
    private $antenaModel;

    public function __construct() {
        $this->antenaModel = new AntenaModel();
    }

    public function index() {
        $db = Database::connect();

        // 1. Procesar inserción si viene por POST
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!$this->isAdmin()) {
                header('Location: index.php?url=antenas&status=access_denied');
                exit();
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
            $country_id = isset($_POST['country']) ? intval($_POST['country']) : 0;

            if ($client_id > 0 && !empty($serial) && !empty($kit) && $plan_id > 0 && $country_id > 0) {
                // Validar día de pago si viene definido
                if ($pay !== null && ($pay < 1 || $pay > 31)) {
                    header("Location: index.php?url=antenas&status=invalid_pay");
                    exit();
                }

                $stmt = $db->prepare("SELECT id_starlink FROM antenas WHERE serial = :serial" . ($id_starlink > 0 ? " AND id_starlink != :id" : ""));
                $stmt->bindParam(':serial', $serial, PDO::PARAM_STR);
                if ($id_starlink > 0) {
                    $stmt->bindParam(':id', $id_starlink, PDO::PARAM_INT);
                }
                $stmt->execute();
                $duplicate = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($duplicate) {
                    header("Location: index.php?url=antenas&status=antenna_exists");
                    exit();
                }

                if ($id_starlink > 0) {
                    $saved = $this->antenaModel->update($id_starlink, $client_id, $account_id, $serial, $nickname, $kit, $date, $pay, $plan_id, $country_id);
                    $status = $saved ? 'updated' : 'error';
                } else {
                    $saved = $this->antenaModel->register($client_id, $account_id, $serial, $nickname, $kit, $date, $pay, $plan_id, $country_id);
                    $status = $saved ? 'success' : 'error';
                }

                header("Location: index.php?url=antenas&status=" . $status);
                exit();
            } else {
                header("Location: index.php?url=antenas&status=empty");
                exit();
            }
        }

        // 1b. Acción de eliminación via GET
        if (isset($_GET['action']) && $_GET['action'] === 'delete') {
            if (!$this->isAdmin()) {
                header('Location: index.php?url=antenas&status=access_denied');
                exit();
            }

            $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
            if ($id > 0) {
                $deleted = $this->antenaModel->delete($id);
                $status = $deleted ? 'deleted' : 'error';
                header("Location: index.php?url=antenas&status=" . $status);
                exit();
            }
        }

        // 2. Cargar listas auxiliares dinámicas para los SELECT del formulario modal
        $clientes = $db->query("SELECT id_client, CONCAT(name, ' ', surname) AS nombre FROM client ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
        $planes   = $db->query("SELECT id_plan, plan FROM plan ORDER BY id_plan ASC")->fetchAll(PDO::FETCH_ASSOC);
        $paises   = $db->query("SELECT id_country, country FROM country ORDER BY country ASC")->fetchAll(PDO::FETCH_ASSOC);
        $cuentas  = $db->query("SELECT id_accounts, CONCAT(owner, ' • ', acc) AS info_cuenta FROM accounts ORDER BY owner ASC")->fetchAll(PDO::FETCH_ASSOC);

        // 3. Obtener el listado principal de antenas
        $antenasList = $this->antenaModel->getAll($this->getUserRole(), $this->getUserCi());

        $data = [
            'page_title' => 'Gestión de Antenas - Starlink Control',
            'antenas'    => $antenasList,
            'clientes'   => $clientes,
            'planes'     => $planes,
            'paises'     => $paises,
            'cuentas'    => $cuentas
        ];

        // Renderizar la vista
        $this->render('modules/antenas', $data);
    }
}