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

            if (!empty($owner) && !empty($acc) && !empty($email)) {
                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    header("Location: index.php?url=accounts&status=invalid_email");
                    exit();
                }

                $stmt = $db->prepare("SELECT id_accounts, acc, email FROM accounts WHERE (acc = :acc OR email = :email)" . ($id > 0 ? " AND id_accounts != :id" : ""));
                $stmt->bindParam(':acc', $acc, PDO::PARAM_STR);
                $stmt->bindParam(':email', $email, PDO::PARAM_STR);
                if ($id > 0) {
                    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                }
                $stmt->execute();
                $duplicate = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($duplicate) {
                    if ($duplicate['acc'] === $acc) {
                        header("Location: index.php?url=accounts&status=account_exists");
                        exit();
                    }
                    if ($duplicate['email'] === $email) {
                        header("Location: index.php?url=accounts&status=email_exists");
                        exit();
                    }
                }

                if ($id > 0) {
                    $saved = $this->accountModel->update($id, $owner, $acc, $email, $date);
                    $status = $saved ? 'updated' : 'error';
                } else {
                    $saved = $this->accountModel->register($owner, $acc, $email, $date);
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
                $deleted = $this->accountModel->delete($id);
                $status = $deleted ? 'deleted' : 'error';
                header("Location: index.php?url=accounts&status=" . $status);
                exit();
            }
        }

        if ($this->isExpectador()) {
            header('Location: index.php?url=dashboard&status=access_denied');
            exit();
        }

        // Cargar el listado para la tabla visual
        $accountsList = $this->accountModel->getAll();

        $data = [
            'page_title' => 'Cuentas Starlink - Starlink Control',
            'accounts'   => $accountsList
        ];

        // Renderizar la vista correspondiente
        $this->render('modules/accounts', $data);
    }
}