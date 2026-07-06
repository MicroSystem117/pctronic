<?php

class CountryController extends Controller {
    private $countryModel;

    public function __construct() {
        $this->countryModel = new CountryModel();
    }

    public function index() {
        // Crear / actualizar
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!$this->isAdmin()) {
                header('Location: index.php?url=countries&status=access_denied');
                exit();
            }

            $id = isset($_POST['id_country']) ? intval($_POST['id_country']) : 0;
            $countryName = isset($_POST['country']) ? trim($_POST['country']) : '';

            if (!empty($countryName)) {
                if ($id > 0) {
                    $saved = $this->countryModel->update($id, $countryName);
                    $status = $saved ? 'updated' : 'error';
                } else {
                    $saved = $this->countryModel->register($countryName);
                    $status = $saved ? 'success' : 'error';
                }

                header("Location: index.php?url=countries&status=" . $status);
                exit();
            } else {
                header("Location: index.php?url=countries&status=empty");
                exit();
            }
        }

        // Eliminar
        if (isset($_GET['action']) && $_GET['action'] === 'delete') {
            if (!$this->isAdmin()) {
                header('Location: index.php?url=countries&status=access_denied');
                exit();
            }

            $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
            if ($id > 0) {
                $deleted = $this->countryModel->delete($id);
                $status = $deleted ? 'deleted' : 'error';
                header("Location: index.php?url=countries&status=" . $status);
                exit();
            }
        }

        if ($this->isExpectador()) {
            header('Location: index.php?url=dashboard&status=access_denied');
            exit();
        }

        $countries = $this->countryModel->getAll();

        $data = [
            'page_title' => 'Países - Starlink Control',
            'countries' => $countries
        ];

        $this->render('modules/countries', $data);
    }
}
