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

        // --- 1. ACCIONES POST (REGISTRO, EDICIÓN Y ELIMINACIÓN SEGURA) ---
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCsrfToken();

            if (!$this->isAdmin()) {
                header('Location: index.php?url=clients&status=access_denied');
                exit();
            }

            $action = $_POST['action'] ?? null;
            if ($action === 'delete') {
                $id = isset($_POST['id']) ? intval($_POST['id']) : (isset($_POST['id_client']) ? intval($_POST['id_client']) : 0);
                if ($id > 0) {
                    $deleted = $this->clientModel->delete($id);
                    $status = $deleted ? 'deleted' : 'error';
                    if ($deleted) {
                        LogModel::log('ELIMINAR', 'Clientes', "Eliminó el cliente ID $id");
                    }
                    header("Location: index.php?url=clients&status=" . $status);
                    exit();
                }
            }

            $id      = isset($_POST['id_client']) ? intval($_POST['id_client']) : 0;
            $name    = isset($_POST['name']) ? trim($_POST['name']) : '';
            $surname = isset($_POST['surname']) ? trim($_POST['surname']) : '';
            
            // Validar cédula: si viene vacía mandamos null
            $ci      = isset($_POST['ci']) && trim($_POST['ci']) !== '' ? trim($_POST['ci']) : null;
            $phone   = isset($_POST['phone']) ? trim($_POST['phone']) : '';

            if (!empty($name) && !empty($surname) && !empty($phone)) {
                if ($id > 0) {
                    // EDICIÓN
                    $saved = $this->clientModel->update($id, $name, $surname, $ci, $phone);
                    $status = $saved ? 'updated' : 'error';
                    if ($saved) {
                        LogModel::log('EDITAR', 'Clientes', "Actualizó los datos del cliente '$name $surname' (ID: $id)");
                    }
                } else {
                    // REGISTRO NUEVO
                    $saved = $this->clientModel->register($name, $surname, $ci, $phone);
                    $status = $saved ? 'success' : 'error';
                    if ($saved) {
                        LogModel::log('CREAR', 'Clientes', "Registró nuevo cliente '$name $surname' (Tel: $phone)");
                    }
                }
                
                // Redirección limpia al listado en plural
                header("Location: index.php?url=clients&status=" . $status);
                exit();
            } else {
                header("Location: index.php?url=clients&status=empty");
                exit();
            }
        }

        // --- 2. CARGAR LISTADO ---
        $clientsList = $this->clientModel->getAll();

        $data = [
            'page_title' => 'Gestión de Clientes - Starlink Control',
            'clients'    => $clientsList
        ];

        // Llamamos a la vista sin el .php (ya que tu método render lo incluye solo)
        $this->render('modules/clients', $data);
    }
}