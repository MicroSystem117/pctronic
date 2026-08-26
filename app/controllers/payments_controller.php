<?php

class PaymentsController extends Controller {
    private $paymentModel;

    public function __construct() {
        $this->paymentModel = new PaymentModel();
    }

    public function index() {
        $isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!isset($_SESSION['user_id'])) {
                header('Location: index.php?url=payments&status=access_denied');
                exit();
            }

            $antenna_ids  = isset($_POST['antenna_ids']) && is_array($_POST['antenna_ids']) ? array_map('intval', $_POST['antenna_ids']) : [];
            $antenna_ids  = array_values(array_unique(array_filter($antenna_ids, function ($antennaId) {
                return $antennaId > 0;
            })));
            $amount       = isset($_POST['amount']) ? trim($_POST['amount']) : '';
            $currency     = isset($_POST['currency']) ? trim($_POST['currency']) : '';
            $payment_date = isset($_POST['payment_date']) && !empty($_POST['payment_date']) ? $_POST['payment_date'] : date('Y-m-d');
            $receiptPath  = $this->uploadReceipt();

            if ($receiptPath === false) {
                header('Location: index.php?url=payments&status=payment_receipt_invalid');
                exit();
            }

            if (!empty($antenna_ids) && !empty($amount) && !empty($currency) && in_array($currency, ['USD', 'VES', 'USDT'], true)) {
                if ($this->isExpectador()) {
                    foreach ($antenna_ids as $antennaId) {
                        if (!$this->paymentModel->antennaBelongsToUser($antennaId, $this->getUserCi())) {
                            header('Location: index.php?url=payments&status=access_denied');
                            exit();
                        }
                    }
                }

                $amounts = $this->paymentModel->getAmountsForAntennas($antenna_ids);
                if (count($amounts) !== count($antenna_ids)) {
                    header('Location: index.php?url=payments&status=error');
                    exit();
                }

                $saved = $this->paymentModel->registerMany(
                    $antenna_ids,
                    $amounts,
                    $currency,
                    $payment_date,
                    intval($_SESSION['user_id']),
                    $this->isExpectador() ? 'Pendiente' : 'Aprobado',
                    $receiptPath
                );
                if (!$saved && $receiptPath !== null) {
                    $uploadedFile = __DIR__ . '/../../public/' . $receiptPath;
                    if (is_file($uploadedFile)) {
                        unlink($uploadedFile);
                    }
                }
                $status = $saved ? ($this->isExpectador() ? 'payment_pending' : 'payment_success') : 'error';
                if ($isAjax) {
                    header('Content-Type: application/json; charset=utf-8');
                    echo json_encode(['success' => (bool) $saved, 'status' => $status]);
                    exit();
                }
                header("Location: index.php?url=payments&status=" . $status);
                exit();
            }

            header('Location: index.php?url=payments&status=empty');
            exit();
        }

        if (isset($_GET['action']) && $_GET['action'] === 'sync') {
            $payments = $this->paymentModel->getAll($this->getUserRole(), $this->getUserCi());
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(array_map(function ($payment) {
                return [
                    'id' => (int) $payment['id_payment'],
                    'status' => $payment['status'] ?? 'Pendiente',
                    'serial' => $payment['serial'],
                    'client' => $payment['cliente'],
                    'amount' => $payment['amount'],
                    'currency' => $payment['currency'],
                    'date' => date('d/m/Y', strtotime($payment['payment_date'])),
                    'receipt' => $payment['receipt_path'] ?? ''
                ];
            }, $payments));
            exit();
        }

        if (isset($_GET['action']) && $_GET['action'] === 'delete') {
            if (!isset($_SESSION['user_id'])) {
                header('Location: index.php?url=payments&status=access_denied');
                exit();
            }

            if (!$this->isAdmin() && !$this->isModerator()) {
                header('Location: index.php?url=payments&status=access_denied');
                exit();
            }

            $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
            if ($id > 0) {
                $deleted = $this->paymentModel->delete($id);
                $status = $deleted ? 'payment_deleted' : 'error';
                if ($isAjax) {
                    header('Content-Type: application/json; charset=utf-8');
                    echo json_encode(['success' => (bool) $deleted, 'status' => $status, 'id' => $id]);
                    exit();
                }
                header("Location: index.php?url=payments&status=" . $status);
                exit();
            }
        }

        if (isset($_GET['action']) && in_array($_GET['action'], ['approve', 'reject'], true)) {
            if (!isset($_SESSION['user_id']) || (!$this->isAdmin() && !$this->isModerator())) {
                header('Location: index.php?url=payments&status=access_denied');
                exit();
            }

            $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
            $status = $_GET['action'] === 'approve' ? 'Aprobado' : 'Rechazado';
            $updated = $id > 0 && $this->paymentModel->review($id, $status, intval($_SESSION['user_id']));
            if ($isAjax) {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['success' => (bool) $updated, 'status' => $status, 'id' => $id]);
                exit();
            }
            header('Location: index.php?url=payments&status=' . ($updated ? 'payment_reviewed' : 'error'));
            exit();
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

    private function uploadReceipt() {
        if (!isset($_FILES['payment_receipt']) || $_FILES['payment_receipt']['error'] === UPLOAD_ERR_NO_FILE) {
            return null;
        }

        $file = $_FILES['payment_receipt'];
        if ($file['error'] !== UPLOAD_ERR_OK || $file['size'] > 5 * 1024 * 1024 || !is_uploaded_file($file['tmp_name'])) {
            return false;
        }

        $mimeTypes = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            'application/pdf' => 'pdf'
        ];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!isset($mimeTypes[$mime])) {
            return false;
        }

        $directory = __DIR__ . '/../../public/uploads/payment_receipts';
        if (!is_dir($directory) && !mkdir($directory, 0755, true)) {
            return false;
        }

        $filename = bin2hex(random_bytes(16)) . '.' . $mimeTypes[$mime];
        if (!move_uploaded_file($file['tmp_name'], $directory . '/' . $filename)) {
            return false;
        }

        return 'uploads/payment_receipts/' . $filename;
    }
}
