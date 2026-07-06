<?php

class PlanController extends Controller {
    private $planModel;

    public function __construct() {
        $this->planModel = new PlanModel();
    }

    public function index() {
        // Crear / actualizar
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!$this->isAdmin()) {
                header('Location: index.php?url=plans&status=access_denied');
                exit();
            }

            $id = isset($_POST['id_plan']) ? intval($_POST['id_plan']) : 0;
            $planName = isset($_POST['plan']) ? trim($_POST['plan']) : '';
            $price = isset($_POST['price']) ? intval($_POST['price']) : 0;

            if (!empty($planName) && $price >= 0) {
                if ($id > 0) {
                    $saved = $this->planModel->update($id, $planName, $price);
                    $status = $saved ? 'updated' : 'error';
                } else {
                    $saved = $this->planModel->register($planName, $price);
                    $status = $saved ? 'success' : 'error';
                }

                header("Location: index.php?url=plans&status=" . $status);
                exit();
            } else {
                header("Location: index.php?url=plans&status=empty");
                exit();
            }
        }

        // Eliminar
        if (isset($_GET['action']) && $_GET['action'] === 'delete') {
            if (!$this->isAdmin()) {
                header('Location: index.php?url=plans&status=access_denied');
                exit();
            }

            $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
            if ($id > 0) {
                $deleted = $this->planModel->delete($id);
                $status = $deleted ? 'deleted' : 'error';
                header("Location: index.php?url=plans&status=" . $status);
                exit();
            }
        }

        $plans = $this->planModel->getAll();

        $data = [
            'page_title' => 'Planes - Starlink Control',
            'plans' => $plans
        ];

        $this->render('modules/plans', $data);
    }
}
