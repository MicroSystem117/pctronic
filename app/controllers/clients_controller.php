<?php

class ClientsController extends Controller {
    private $clientModel;

    public function __construct() {
        // Instanciamos el modelo singular correctamente
        $this->clientModel = new ClientModel();
    }

    public function index() {
        if ($this->isExpectador()) {
            header('Location: index.php?url=dashboard&status=access_denied');
            exit();
        }

        // --- 1. ACCIÓN: ELIMINAR CLIENTE ---
        if (isset($_GET['action']) && $_GET['action'] === 'delete') {
            if (!$this->isAdmin()) {
                header('Location: index.php?url=clients&status=access_denied');
                exit();
            }

            $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
            if ($id > 0) {
                $deleted = $this->clientModel->delete($id);
                $status = $deleted ? 'deleted' : 'error';
                header("Location: index.php?url=clients&status=" . $status);
                exit();
            }
        }

        // --- 2. ACCIÓN: PROCESAR FORMULARIO (POST) ---
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!$this->isAdmin()) {
                header('Location: index.php?url=clients&status=access_denied');
                exit();
            }

            $id      = isset($_POST['id_client']) ? intval($_POST['id_client']) : 0;
            $name    = isset($_POST['name']) ? trim($_POST['name']) : '';
            $surname = isset($_POST['surname']) ? trim($_POST['surname']) : '';
            
            // Validar cédula: si viene vacía mandamos null para que MariaDB no la ponga en 0
            $ci      = isset($_POST['ci']) && $_POST['ci'] !== '' ? intval($_POST['ci']) : null;
            $phone   = isset($_POST['phone']) ? trim($_POST['phone']) : '';

            if (!empty($name) && !empty($surname) && !empty($phone)) {
                if ($id > 0) {
                    // EDICIÓN
                    $saved = $this->clientModel->update($id, $name, $surname, $ci, $phone);
                    $status = $saved ? 'updated' : 'error';
                } else {
                    // REGISTRO NUEVO
                    $saved = $this->clientModel->register($name, $surname, $ci, $phone);
                    $status = $saved ? 'success' : 'error';
                }
                
                // Redirección limpia al listado en plural
                header("Location: index.php?url=clients&status=" . $status);
                exit();
            } else {
                header("Location: index.php?url=clients&status=empty");
                exit();
            }
        }

        // --- 3. CARGAR LISTADO ---
        $clientsList = $this->clientModel->getAll($this->isExpectador() ? $this->getUserCi() : null);

        $data = [
            'page_title' => 'Gestión de Clientes - Starlink Control',
            'clients'    => $clientsList
        ];

        // Llamamos a la vista sin el .php (ya que tu método render lo incluye solo)
        $this->render('modules/clients', $data);
    }
}