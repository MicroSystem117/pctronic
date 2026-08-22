<?php

// CAMBIA ESTA LÍNEA (Asegúrate de poner la 's' al final de Accounts)
class AccountsController extends Controller {
    private $accountModel;

    public function __construct() {
        // Aquí instanciamos el modelo. Como tu modelo se llama AccountModel, lo dejamos así
        $this->accountModel = new AccountModel();
    }

    public function index() {
        $db = Database::connect();
        // Procesar inserción o actualización si el formulario fue enviado
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!$this->isAdmin()) {
                header('Location: index.php?url=accounts&status=access_denied');
                exit();
            }

            $id = isset($_POST['id_accounts']) ? intval($_POST['id_accounts']) : 0;
            $owner = isset($_POST['owner']) ? trim($_POST['owner']) : '';
            $acc   = isset($_POST['acc']) ? trim($_POST['acc']) : '';
            $email = isset($_POST['email']) ? trim($_POST['email']) : '';
            $date  = isset($_POST['create_date']) && !empty($_POST['create_date']) ? $_POST['create_date'] : date('Y-m-d');
            $countryId = isset($_POST['countries']) && $_POST['countries'] !== '' ? intval($_POST['countries']) : null;

            if (!empty($owner) && !empty($acc) && !empty($email)) {
                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    header("Location: index.php?url=accounts&status=invalid_email");
                    exit();
                }

                $currentAccount = null;
                if ($id > 0) {
                    $stmt = $db->prepare("SELECT acc, email FROM accounts WHERE id_accounts = :id");
                    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
                    $stmt->execute();
                    $currentAccount = $stmt->fetch(PDO::FETCH_ASSOC);
                }

                $accChanged = !$currentAccount || trim($currentAccount['acc']) !== trim($acc);
                $emailChanged = !$currentAccount || trim($currentAccount['email']) !== trim($email);
                $duplicate = null;

                if ($accChanged || $emailChanged) {
                    $conditions = [];
                    $params = [];
                    if ($accChanged) {
                        $conditions[] = 'TRIM(acc) = TRIM(:acc)';
                        $params[':acc'] = $acc;
                    }
                    if ($emailChanged) {
                        $conditions[] = 'TRIM(email) = TRIM(:email)';
                        $params[':email'] = $email;
                    }

                    $sql = "SELECT id_accounts, acc, email FROM accounts WHERE (" . implode(' OR ', $conditions) . ") AND id_accounts <> :id";
                    $params[':id'] = $id;
                    $stmt = $db->prepare($sql);
                    $stmt->execute($params);
                    $duplicate = $stmt->fetch(PDO::FETCH_ASSOC);
                }

                if ($duplicate) {
                    if (trim($duplicate['acc']) === trim($acc)) {
                        header("Location: index.php?url=accounts&status=account_exists");
                        exit();
                    }
                    if (trim($duplicate['email']) === trim($email)) {
                        header("Location: index.php?url=accounts&status=email_exists");
                        exit();
                    }
                }

                if ($id > 0) {
                    $saved = $this->accountModel->update($id, $owner, $acc, $email, $date, $countryId);
                    $status = $saved ? 'updated' : 'error';
                } else {
                    $saved = $this->accountModel->register($owner, $acc, $email, $date, $countryId);
                    $status = $saved ? 'success' : 'error';
                }

                header("Location: index.php?url=accounts&status=" . $status);
                exit();
            } else {
                header("Location: index.php?url=accounts&status=empty");
                exit();
            }
        }

        // Acción de eliminación via GET
        if (isset($_GET['action']) && $_GET['action'] === 'delete') {
            if (!$this->isAdmin()) {
                header('Location: index.php?url=accounts&status=access_denied');
                exit();
            }

            $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
            if ($id > 0) {
                $preserveAntennas = !isset($_GET['preserve_antennas']) || $_GET['preserve_antennas'] === '1';
                $deleted = $this->accountModel->delete($id, $preserveAntennas);
                $status = $deleted ? 'deleted' : 'error';
                header("Location: index.php?url=accounts&status=" . $status);
                exit();
            }
        }

        if ($this->isExpectador()) {
            header('Location: index.php?url=dashboard&status=access_denied');
            exit();
        }

        $countries = $db->query("SELECT id_country, country FROM country ORDER BY country ASC")->fetchAll(PDO::FETCH_ASSOC);

        // Cargar el listado para la tabla visual
        $accountsList = $this->accountModel->getAll();

        $data = [
            'page_title' => 'Cuentas Starlink - Starlink Control',
            'accounts'   => $accountsList,
            'countries'  => $countries
        ];

        // Renderizar la vista correspondiente
        $this->render('modules/accounts', $data);
    }
}