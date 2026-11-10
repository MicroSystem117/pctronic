<?php

class CountryController extends Controller {
    private $countryModel;

    public function __construct() {
        $this->countryModel = new CountryModel();
    }

    public function index() {
        if ($this->isExpectador()) {
            header('Location: index.php?url=dashboard&status=access_denied');
            exit();
        }

        // Crear / actualizar / eliminar
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCsrfToken();

            if (!$this->isAdmin()) {
                header('Location: index.php?url=countries&status=access_denied');
                exit();
            }

            $action = $_POST['action'] ?? null;
            if ($action === 'delete') {
                $id = isset($_POST['id']) ? intval($_POST['id']) : (isset($_POST['id_country']) ? intval($_POST['id_country']) : 0);
                if ($id > 0) {
                    $deleted = $this->countryModel->delete($id);
                    $status = $deleted ? 'deleted' : 'error';
                    if ($deleted) {
                        LogModel::log('ELIMINAR', 'Países', "Eliminó el país ID $id");
                    }
                    header("Location: index.php?url=countries&status=" . $status);
                    exit();
                }
            }

            $id = isset($_POST['id_country']) ? intval($_POST['id_country']) : 0;
            $countryName = isset($_POST['country']) ? trim($_POST['country']) : '';

            if (!empty($countryName)) {
                if ($id > 0) {
                    $saved = $this->countryModel->update($id, $countryName);
                    $status = $saved ? 'updated' : 'error';
                    if ($saved) {
                        LogModel::log('EDITAR', 'Países', "Actualizó el país '$countryName' (ID: $id)");
                    }
                } else {
                    $saved = $this->countryModel->register($countryName);
                    $status = $saved ? 'success' : 'error';
                    if ($saved) {
                        LogModel::log('CREAR', 'Países', "Registró nuevo país '$countryName'");
                    }
                }

                header("Location: index.php?url=countries&status=" . $status);
                exit();
            } else {
                header("Location: index.php?url=countries&status=empty");
                exit();
            }
        }

        $countries = $this->countryModel->getAll();

        $data = [
            'page_title' => 'Países - Starlink Control',
            'countries' => $countries
        ];

        $this->render('modules/countries', $data);
    }
}
