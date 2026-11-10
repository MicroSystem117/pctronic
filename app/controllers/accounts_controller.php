<?php

// CAMBIA ESTA LÍNEA (Asegúrate de poner la 's' al final de Accounts)
class AccountsController extends Controller {
    private $accountModel;

    public function __construct() {
        // Aquí instanciamos el modelo. Como tu modelo se llama AccountModel, lo dejamos así
        $this->accountModel = new AccountModel();
    }

    public function index() {
        if ($this->isExpectador()) {
            header('Location: index.php?url=dashboard&status=access_denied');
            exit();
        }

        // Procesar inserción, actualización o eliminación si el formulario fue enviado
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCsrfToken();

            if (!$this->isAdmin()) {
                header('Location: index.php?url=accounts&status=access_denied');
                exit();
            }

            $action = $_POST['action'] ?? null;
            if ($action === 'delete') {
                $id = isset($_POST['id']) ? intval($_POST['id']) : (isset($_POST['id_accounts']) ? intval($_POST['id_accounts']) : 0);
                if ($id > 0) {
                    $preserveAntennas = !isset($_POST['preserve_antennas']) || $_POST['preserve_antennas'] === '1';
                    $deleted = $this->accountModel->delete($id, $preserveAntennas);
                    $status = $deleted ? 'deleted' : 'error';
                    if ($deleted) {
                        LogModel::log('ELIMINAR', 'Cuentas', "Eliminó la cuenta ID $id");
                    }
                    header("Location: index.php?url=accounts&status=" . $status);
                    exit();
                }
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

                $duplicate = $this->accountModel->findDuplicate($acc, $email, $id);

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
                    if ($saved) {
                        LogModel::log('EDITAR', 'Cuentas', "Actualizó la cuenta '$owner' ($email) ID: $id");
                    }
                } else {
                    $saved = $this->accountModel->register($owner, $acc, $email, $date, $countryId);
                    $status = $saved ? 'success' : 'error';
                    if ($saved) {
                        LogModel::log('CREAR', 'Cuentas', "Registró nueva cuenta '$owner' ($email)");
                    }
                }

                header("Location: index.php?url=accounts&status=" . $status);
                exit();
            } else {
                header("Location: index.php?url=accounts&status=empty");
                exit();
            }
        }

        $countryModel = new CountryModel();
        $countries = $countryModel->getAll();

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