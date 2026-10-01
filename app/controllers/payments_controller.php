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
                if ($isAjax) {
                    header('Content-Type: application/json; charset=utf-8');
                    echo json_encode(['success' => false, 'message' => 'Sesión expirada o no autorizada. Por favor inicia sesión nuevamente.']);
                    exit();
                }
                header('Location: index.php?url=payments&status=access_denied');
                exit();
            }

            $antenna_ids  = isset($_POST['antenna_ids']) && is_array($_POST['antenna_ids']) ? array_map('intval', $_POST['antenna_ids']) : [];
            $antenna_ids  = array_values(array_unique(array_filter($antenna_ids, function ($antennaId) {
                return $antennaId > 0;
            })));
            $amount       = isset($_POST['amount']) ? trim($_POST['amount']) : '';
            $currency     = isset($_POST['currency']) ? trim($_POST['currency']) : '';
            
            // Tratamiento robusto de fecha
            $rawDate      = isset($_POST['payment_date']) ? trim($_POST['payment_date']) : '';
            $timestamp    = !empty($rawDate) ? strtotime(str_replace('/', '-', $rawDate)) : time();
            $payment_date = $timestamp ? date('Y-m-d', $timestamp) : date('Y-m-d');

            $uploadError  = null;
            $receiptPath  = $this->uploadReceipt($uploadError);

            if ($receiptPath === false) {
                if ($isAjax) {
                    header('Content-Type: application/json; charset=utf-8');
                    echo json_encode([
                        'success' => false,
                        'message' => $uploadError ?: 'El comprobante no es válido o no se pudo guardar en el servidor.'
                    ]);
                    exit();
                }
                header('Location: index.php?url=payments&status=payment_receipt_invalid');
                exit();
            }

            if (empty($antenna_ids)) {
                if ($isAjax) {
                    header('Content-Type: application/json; charset=utf-8');
                    echo json_encode(['success' => false, 'message' => 'Debes seleccionar al menos una antena para registrar el pago.']);
                    exit();
                }
                header('Location: index.php?url=payments&status=empty');
                exit();
            }

            if (empty($amount) || !is_numeric($amount) || floatval($amount) <= 0) {
                if ($isAjax) {
                    header('Content-Type: application/json; charset=utf-8');
                    echo json_encode(['success' => false, 'message' => 'El monto es inválido o no se ha calculado según los planes de las antenas.']);
                    exit();
                }
                header('Location: index.php?url=payments&status=empty');
                exit();
            }

            if (empty($currency) || !in_array($currency, ['USD', 'VES', 'USDT'], true)) {
                if ($isAjax) {
                    header('Content-Type: application/json; charset=utf-8');
                    echo json_encode(['success' => false, 'message' => 'Debes seleccionar una moneda válida (USD, Bolívares o USDT).']);
                    exit();
                }
                header('Location: index.php?url=payments&status=empty');
                exit();
            }

            if ($this->isExpectador()) {
                foreach ($antenna_ids as $antennaId) {
                    if (!$this->paymentModel->antennaBelongsToUser($antennaId, $this->getUserCi())) {
                        if ($isAjax) {
                            header('Content-Type: application/json; charset=utf-8');
                            echo json_encode(['success' => false, 'message' => 'No tienes permiso para registrar pagos en una o más de las antenas seleccionadas.']);
                            exit();
                        }
                        header('Location: index.php?url=payments&status=access_denied');
                        exit();
                    }
                }
            }

            $amounts = $this->paymentModel->getAmountsForAntennas($antenna_ids);
            if (count($amounts) !== count($antenna_ids)) {
                if ($isAjax) {
                    header('Content-Type: application/json; charset=utf-8');
                    echo json_encode(['success' => false, 'message' => 'No se encontraron las antenas seleccionadas en la base de datos.']);
                    exit();
                }
                header('Location: index.php?url=payments&status=error');
                exit();
            }

            // Si los planes no tienen precio configurado (0), prorratear el monto ingresado
            $totalCalculated = array_sum(array_map('floatval', $amounts));
            if ($totalCalculated <= 0 && floatval($amount) > 0) {
                $splitAmount = floatval($amount) / count($antenna_ids);
                foreach ($antenna_ids as $id) {
                    $amounts[$id] = $splitAmount;
                }
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

            if (!$saved) {
                if ($receiptPath !== null) {
                    $uploadedFile = __DIR__ . '/../../public/' . $receiptPath;
                    if (is_file($uploadedFile)) {
                        unlink($uploadedFile);
                    }
                }
                $dbError = $this->paymentModel->getLastError();
                if ($isAjax) {
                    header('Content-Type: application/json; charset=utf-8');
                    echo json_encode([
                        'success' => false,
                        'message' => 'Error al registrar el pago en la base de datos' . ($dbError ? ': ' . $dbError : '.')
                    ]);
                    exit();
                }
                header("Location: index.php?url=payments&status=error");
                exit();
            }

            $status = $this->isExpectador() ? 'payment_pending' : 'payment_success';
            if ($isAjax) {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['success' => true, 'status' => $status]);
                exit();
            }
            header("Location: index.php?url=payments&status=" . $status);
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
            if (!isset($_SESSION['user_id']) || (!$this->isAdmin() && !$this->isModerator())) {
                if ($isAjax) {
                    header('Content-Type: application/json; charset=utf-8');
                    echo json_encode(['success' => false, 'message' => 'No tienes permisos para realizar esta acción.']);
                    exit();
                }
                header('Location: index.php?url=payments&status=access_denied');
                exit();
            }

            $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
            if ($id > 0) {
                $deleted = $this->paymentModel->delete($id);
                $status = $deleted ? 'payment_deleted' : 'error';
                if ($isAjax) {
                    header('Content-Type: application/json; charset=utf-8');
                    echo json_encode([
                        'success' => (bool) $deleted,
                        'status' => $status,
                        'id' => $id,
                        'message' => $deleted ? 'Pago eliminado correctamente.' : 'No se pudo eliminar el pago.'
                    ]);
                    exit();
                }
                header("Location: index.php?url=payments&status=" . $status);
                exit();
            }
        }

        if (isset($_GET['action']) && in_array($_GET['action'], ['approve', 'reject'], true)) {
            if (!isset($_SESSION['user_id']) || (!$this->isAdmin() && !$this->isModerator())) {
                if ($isAjax) {
                    header('Content-Type: application/json; charset=utf-8');
                    echo json_encode(['success' => false, 'message' => 'No tienes permisos para realizar esta acción.']);
                    exit();
                }
                header('Location: index.php?url=payments&status=access_denied');
                exit();
            }

            $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
            $status = $_GET['action'] === 'approve' ? 'Aprobado' : 'Rechazado';
            $updated = $id > 0 && $this->paymentModel->review($id, $status, intval($_SESSION['user_id']));
            if ($isAjax) {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode([
                    'success' => (bool) $updated,
                    'status' => $status,
                    'id' => $id,
                    'message' => $updated ? 'Estado del pago actualizado correctamente.' : 'No se pudo actualizar el estado del pago.'
                ]);
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

    private function uploadReceipt(&$uploadError = null) {
        if (!isset($_FILES['payment_receipt']) || $_FILES['payment_receipt']['error'] === UPLOAD_ERR_NO_FILE) {
            return null;
        }

        $file = $_FILES['payment_receipt'];
        if ($file['error'] !== UPLOAD_ERR_OK) {
            switch ($file['error']) {
                case UPLOAD_ERR_INI_SIZE:
                case UPLOAD_ERR_FORM_SIZE:
                    $uploadError = 'El comprobante excede el tamaño máximo permitido por el servidor.';
                    break;
                case UPLOAD_ERR_PARTIAL:
                    $uploadError = 'El comprobante solo se subió parcialmente.';
                    break;
                case UPLOAD_ERR_NO_TMP_DIR:
                    $uploadError = 'El servidor no tiene configurada la carpeta temporal para subidas.';
                    break;
                case UPLOAD_ERR_CANT_WRITE:
                    $uploadError = 'No se pudo escribir el comprobante en el disco del servidor.';
                    break;
                default:
                    $uploadError = 'Error al subir el comprobante (código: ' . $file['error'] . ').';
            }
            return false;
        }

        if ($file['size'] > 5 * 1024 * 1024) {
            $uploadError = 'El comprobante supera el tamaño máximo permitido de 5 MB.';
            return false;
        }

        if (!is_uploaded_file($file['tmp_name'])) {
            $uploadError = 'El archivo de comprobante subido no es válido.';
            return false;
        }

        $mimeTypes = [
            'image/jpeg' => 'jpg',
            'image/pjpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            'application/pdf' => 'pdf'
        ];

        $mime = null;
        if (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            if ($finfo) {
                $mime = finfo_file($finfo, $file['tmp_name']);
                finfo_close($finfo);
            }
        }
        if (!$mime && function_exists('mime_content_type')) {
            $mime = @mime_content_type($file['tmp_name']);
        }
        if (!$mime) {
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $extMap = [
                'jpg' => 'image/jpeg',
                'jpeg' => 'image/jpeg',
                'png' => 'image/png',
                'webp' => 'image/webp',
                'pdf' => 'application/pdf'
            ];
            $mime = $extMap[$ext] ?? null;
        }

        if (!isset($mimeTypes[$mime])) {
            $uploadError = 'Formato de comprobante no admitido (' . ($mime ? htmlspecialchars($mime) : 'desconocido') . '). Solo se admiten JPG, PNG, WEBP o PDF.';
            return false;
        }

        $directory = __DIR__ . '/../../public/uploads/payment_receipts';
        if (!is_dir($directory)) {
            if (!@mkdir($directory, 0775, true)) {
                $uploadError = 'No se pudo crear la carpeta public/uploads/payment_receipts en el servidor. Verifica los permisos (ejecuta: sudo chown -R www-data:www-data public/uploads).';
                return false;
            }
        }

        if (!is_writable($directory)) {
            @chmod($directory, 0775);
            if (!is_writable($directory)) {
                $uploadError = 'El servidor web no tiene permisos de escritura en la carpeta public/uploads/payment_receipts del VPS. Ejecuta: sudo chown -R www-data:www-data public/uploads && sudo chmod -R 775 public/uploads';
                return false;
            }
        }

        $filename = bin2hex(random_bytes(16)) . '.' . $mimeTypes[$mime];
        $destPath = $directory . '/' . $filename;
        if (!move_uploaded_file($file['tmp_name'], $destPath)) {
            $uploadError = 'No se pudo mover el comprobante al destino. Revisa los permisos de disco en el VPS.';
            return false;
        }
        @chmod($destPath, 0664);

        return 'uploads/payment_receipts/' . $filename;
    }
}
