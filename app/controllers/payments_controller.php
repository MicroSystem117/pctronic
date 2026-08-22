<?php

class PaymentsController extends Controller {
    private $paymentModel;

    public function __construct() {
        $this->paymentModel = new PaymentModel();
    }

    public function index() {
        $db = Database::connect();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!isset($_SESSION['user_id'])) {
                header('Location: index.php?url=payments&status=access_denied');
                exit();
            }

            $antenna_id   = isset($_POST['antenna_id']) ? intval($_POST['antenna_id']) : 0;
            $amount       = isset($_POST['amount']) ? trim($_POST['amount']) : '';
            $currency     = isset($_POST['currency']) ? trim($_POST['currency']) : '';
            $payment_date = isset($_POST['payment_date']) && !empty($_POST['payment_date']) ? $_POST['payment_date'] : date('Y-m-d');

            if ($antenna_id > 0 && !empty($amount) && !empty($currency) && in_array($currency, ['USD', 'VES', 'USDT'], true)) {
                if ($this->isExpectador() && !$this->paymentModel->antennaBelongsToUser($antenna_id, $this->getUserCi())) {
                    header('Location: index.php?url=payments&status=access_denied');
                    exit();
                }

                $saved = $this->paymentModel->register($antenna_id, $amount, $currency, $payment_date);
                $status = $saved ? 'payment_success' : 'error';
                header("Location: index.php?url=payments&status=" . $status);
                exit();
            }

            header('Location: index.php?url=payments&status=empty');
            exit();
        }

        if (isset($_GET['action']) && $_GET['action'] === 'delete') {
            if (!isset($_SESSION['user_id'])) {
                header('Location: index.php?url=payments&status=access_denied');
                exit();
            }

            $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
            if ($id > 0 && $this->isExpectador() && !$this->paymentModel->paymentBelongsToUser($id, $this->getUserCi())) {
                header('Location: index.php?url=payments&status=access_denied');
                exit();
            }

            if ($id > 0) {
                $deleted = $this->paymentModel->delete($id);
                $status = $deleted ? 'payment_deleted' : 'error';
                header("Location: index.php?url=payments&status=" . $status);
                exit();
            }
        }

        // Soporte de filtros por fecha (GET)
        $from = isset($_GET['from']) && !empty($_GET['from']) ? $_GET['from'] : null;
        $to = isset($_GET['to']) && !empty($_GET['to']) ? $_GET['to'] : null;

        $payments = $this->paymentModel->getAll($this->getUserRole(), $this->getUserCi(), $from, $to);
        $antennas = $this->paymentModel->getAntennasForPayment($this->getUserRole(), $this->getUserCi());

        $data = [
            'page_title' => 'Pagos - Starlink Control',
            'payments'   => $payments,
            'antennas'   => $antennas
        ];

        $this->render('modules/payments', $data);
    }
}
