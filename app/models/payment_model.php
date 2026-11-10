<?php

class PaymentModel {
    private $db;
    private $lastError = null;

    public function __construct() {
        $this->db = Database::connect();
        $this->ensureSchema();
    }

    public function getLastError() {
        return $this->lastError;
    }

    /**
     * Asegura que las tablas y columnas necesarias para pagos existan en la BD (migraciones automáticas).
     */
    private function ensureSchema() {
        static $checked = false;
        if ($checked) return;
        $checked = true;

        try {
            // 1. Asegurar tabla payment_antennas
            $this->db->exec("CREATE TABLE IF NOT EXISTS `payment_antennas` (
                `payment_id` INT NOT NULL,
                `antenna_id` INT NOT NULL,
                PRIMARY KEY (`payment_id`, `antenna_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

            // 2. Verificar columnas en payments
            $columns = $this->db->query("SHOW COLUMNS FROM payments")->fetchAll(PDO::FETCH_COLUMN);
            if (!in_array('status', $columns, true)) {
                $this->db->exec("ALTER TABLE payments ADD COLUMN status ENUM('Pendiente', 'Aprobado', 'Rechazado') NOT NULL DEFAULT 'Pendiente'");
            }
            if (!in_array('receipt_path', $columns, true)) {
                $this->db->exec("ALTER TABLE payments ADD COLUMN receipt_path VARCHAR(255) NULL");
            }
            if (!in_array('submitted_by', $columns, true)) {
                $this->db->exec("ALTER TABLE payments ADD COLUMN submitted_by INT NULL");
            }
            if (!in_array('reviewed_by', $columns, true)) {
                $this->db->exec("ALTER TABLE payments ADD COLUMN reviewed_by INT NULL");
            }
            if (!in_array('reviewed_at', $columns, true)) {
                $this->db->exec("ALTER TABLE payments ADD COLUMN reviewed_at DATETIME NULL");
            }
            if (!in_array('months_paid', $columns, true)) {
                $this->db->exec("ALTER TABLE payments ADD COLUMN months_paid INT NOT NULL DEFAULT 1");
            }
        } catch (Throwable $e) {
            error_log("PaymentModel::ensureSchema warning: " . $e->getMessage());
        }
    }

    /**
     * Obtener todos los pagos registrados con información de antena y cliente.
     */
    public function getAll($userRole = 'Administrador', $ci = null, $from = null, $to = null) {
        try {
            $sql = "SELECT 
                        pay.id_payment,
                        pay.payment_date,
                        pay.amount,
                        pay.currency,
                        pay.created_at,
                        pay.receipt_path,
                        pay.status,
                        pay.reviewed_at,
                        COALESCE(pay.months_paid, 1) AS months_paid,
                        GROUP_CONCAT(DISTINCT a.serial ORDER BY a.serial SEPARATOR ', ') AS serial,
                        GROUP_CONCAT(DISTINCT NULLIF(TRIM(a.nickname), '') ORDER BY NULLIF(TRIM(a.nickname), '') SEPARATOR ', ') AS nickname,
                        GROUP_CONCAT(DISTINCT a.pay ORDER BY a.pay SEPARATOR ', ') AS pay_day,
                        GROUP_CONCAT(DISTINCT CONCAT(c.name, ' ', c.surname) ORDER BY c.name SEPARATOR ', ') AS cliente,
                        GROUP_CONCAT(DISTINCT c.ci ORDER BY c.ci SEPARATOR ', ') AS client_ci,
                        GROUP_CONCAT(DISTINCT c.phone ORDER BY c.phone SEPARATOR ', ') AS client_phone,
                        GROUP_CONCAT(DISTINCT p.plan ORDER BY p.plan SEPARATOR ', ') AS nombre_plan,
                        GROUP_CONCAT(DISTINCT p.price ORDER BY p.price SEPARATOR ', ') AS plan_price,
                        GROUP_CONCAT(DISTINCT co.country ORDER BY co.country SEPARATOR ', ') AS pais
                    FROM payments pay
                    INNER JOIN payment_antennas pa ON pay.id_payment = pa.payment_id
                    INNER JOIN antenas a ON pa.antenna_id = a.id_starlink
                    LEFT JOIN client c ON a.client = c.id_client
                    LEFT JOIN plan p ON a.plan = p.id_plan
                    LEFT JOIN country co ON a.country = co.id_country";

            $params = [];
            $where = [];
            if ($userRole === 'Expectador' && $ci !== null) {
                $where[] = "c.ci = :ci";
                $params[':ci'] = $ci;
            }
            if (!empty($from)) {
                $where[] = "pay.payment_date >= :from";
                $params[':from'] = $from;
            }
            if (!empty($to)) {
                $where[] = "pay.payment_date <= :to";
                $params[':to'] = $to;
            }
            if (!empty($where)) {
                $sql .= " WHERE " . implode(' AND ', $where);
            }
            $sql .= " GROUP BY pay.id_payment, pay.payment_date, pay.amount, pay.currency, pay.created_at, pay.receipt_path, pay.status, pay.reviewed_at, pay.months_paid";
            $sql .= " ORDER BY pay.payment_date DESC, pay.id_payment DESC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            error_log("Error en PaymentModel::getAll -> " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener un pago específico con todos sus datos detallados para recibo o factura.
     */
    public function getById($id, $userRole = 'Administrador', $ci = null) {
        try {
            $sql = "SELECT 
                        pay.id_payment,
                        pay.payment_date,
                        pay.amount,
                        pay.currency,
                        pay.created_at,
                        pay.receipt_path,
                        pay.status,
                        pay.reviewed_at,
                        COALESCE(pay.months_paid, 1) AS months_paid,
                        GROUP_CONCAT(DISTINCT a.serial ORDER BY a.serial SEPARATOR ', ') AS serial,
                        GROUP_CONCAT(DISTINCT NULLIF(TRIM(a.nickname), '') ORDER BY NULLIF(TRIM(a.nickname), '') SEPARATOR ', ') AS nickname,
                        GROUP_CONCAT(DISTINCT a.pay ORDER BY a.pay SEPARATOR ', ') AS pay_day,
                        GROUP_CONCAT(DISTINCT CONCAT(c.name, ' ', c.surname) ORDER BY c.name SEPARATOR ', ') AS cliente,
                        GROUP_CONCAT(DISTINCT c.ci ORDER BY c.ci SEPARATOR ', ') AS client_ci,
                        GROUP_CONCAT(DISTINCT c.phone ORDER BY c.phone SEPARATOR ', ') AS client_phone,
                        GROUP_CONCAT(DISTINCT p.plan ORDER BY p.plan SEPARATOR ', ') AS nombre_plan,
                        GROUP_CONCAT(DISTINCT p.price ORDER BY p.price SEPARATOR ', ') AS plan_price,
                        GROUP_CONCAT(DISTINCT co.country ORDER BY co.country SEPARATOR ', ') AS pais
                    FROM payments pay
                    INNER JOIN payment_antennas pa ON pay.id_payment = pa.payment_id
                    INNER JOIN antenas a ON pa.antenna_id = a.id_starlink
                    LEFT JOIN client c ON a.client = c.id_client
                    LEFT JOIN plan p ON a.plan = p.id_plan
                    LEFT JOIN country co ON a.country = co.id_country
                    WHERE pay.id_payment = :id";
            $params = [':id' => (int) $id];
            if ($userRole === 'Expectador' && $ci !== null) {
                $sql .= " AND c.ci = :ci";
                $params[':ci'] = $ci;
            }
            $sql .= " GROUP BY pay.id_payment, pay.payment_date, pay.amount, pay.currency, pay.created_at, pay.receipt_path, pay.status, pay.reviewed_at, pay.months_paid";

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            error_log("Error en PaymentModel::getById -> " . $e->getMessage());
            return null;
        }
    }

    /**
     * Obtener todas las antenas disponibles para registrar pagos, con datos de cliente y última transacción.
     */
    public function getAntennasForPayment($userRole = 'Administrador', $ci = null) {
        try {
            $statusColumn = $this->db->query("SHOW COLUMNS FROM payments LIKE 'status'")->fetch(PDO::FETCH_ASSOC);
            $lastStatusSelect = $statusColumn ? ', p1.status AS last_status' : ', NULL AS last_status';
            $approvedFilter = $statusColumn ? " WHERE status = 'Aprobado'" : '';

            $sql = "SELECT 
                        a.id_starlink,
                        a.serial,
                        a.nickname,
                        a.date,
                        a.pay,
                        a.debt_exempt_until,
                        a.debt_exempt_note,
                        a.debt_exempt_at,
                        CONCAT(c.name, ' ', c.surname) AS cliente,
                        c.phone AS client_phone,
                        a.client AS client_id,
                        p.plan AS nombre_plan,
                        p.price AS plan_price,
                        co.country AS pais,
                        CONCAT(acc.owner, ' • ', acc.acc) AS cuenta_starlink
                    FROM antenas a
                    LEFT JOIN client c ON a.client = c.id_client
                    LEFT JOIN plan p ON a.plan = p.id_plan
                    LEFT JOIN country co ON a.country = co.id_country
                    LEFT JOIN accounts acc ON a.account_id = acc.id_accounts";

            $params = [];
            if ($userRole === 'Expectador' && $ci !== null) {
                $sql .= " WHERE c.ci = :ci";
                $params[':ci'] = $ci;
            }

            $sql .= " ORDER BY a.id_starlink DESC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $antenas = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Obtener pagos aprobados para calcular estado
            $paymentsSql = "SELECT antenna_id, payment_date, amount, currency, status, months_paid 
                            FROM (
                                SELECT pa.antenna_id, p.payment_date, p.amount, p.currency, p.status, COALESCE(p.months_paid, 1) AS months_paid
                                FROM payments p
                                INNER JOIN payment_antennas pa ON p.id_payment = pa.payment_id
                                WHERE p.status = 'Aprobado'
                                UNION ALL
                                SELECT p.antenna_id, p.payment_date, p.amount, p.currency, p.status, COALESCE(p.months_paid, 1) AS months_paid
                                FROM payments p
                                LEFT JOIN payment_antennas pa ON p.id_payment = pa.payment_id
                                WHERE p.status = 'Aprobado' AND pa.payment_id IS NULL AND p.antenna_id IS NOT NULL
                            ) ap
                            ORDER BY payment_date ASC";
            $paymentsStmt = $this->db->query($paymentsSql);
            $paymentsRows = $paymentsStmt ? $paymentsStmt->fetchAll(PDO::FETCH_ASSOC) : [];

            $paymentsByAntenna = [];
            foreach ($paymentsRows as $pRow) {
                $antId = (int)$pRow['antenna_id'];
                if (!isset($paymentsByAntenna[$antId])) {
                    $paymentsByAntenna[$antId] = [];
                }
                $paymentsByAntenna[$antId][] = $pRow;
            }

            foreach ($antenas as &$antena) {
                $antId = (int)$antena['id_starlink'];
                $antPayments = $paymentsByAntenna[$antId] ?? [];
                $debtStats = AntenaModel::calculateDebtInfo($antena, $antPayments);
                $antena = array_merge($antena, $debtStats);
            }
            unset($antena);

            usort($antenas, function($a, $b) {
                $dayA = (int)($a['due_day'] ?? 0);
                $dayB = (int)($b['due_day'] ?? 0);
                if ($dayA === $dayB) {
                    return $b['id_starlink'] <=> $a['id_starlink'];
                }
                return $dayA <=> $dayB;
            });

            return $antenas;
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            error_log("Error en PaymentModel::getAntennasForPayment -> " . $e->getMessage());
            return [];
        }
    }

    public function antennaBelongsToUser($antennaId, $ci) {
        $stmt = $this->db->prepare('SELECT a.id_starlink FROM antenas a INNER JOIN client c ON a.client = c.id_client WHERE a.id_starlink = :antenna_id AND c.ci = :ci');
        $stmt->execute([':antenna_id' => $antennaId, ':ci' => $ci]);
        return (bool) $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function paymentBelongsToUser($paymentId, $ci) {
        $stmt = $this->db->prepare('SELECT pay.id_payment FROM payments pay INNER JOIN payment_antennas pa ON pay.id_payment = pa.payment_id INNER JOIN antenas a ON pa.antenna_id = a.id_starlink INNER JOIN client c ON a.client = c.id_client WHERE pay.id_payment = :payment_id AND c.ci = :ci');
        $stmt->execute([':payment_id' => $paymentId, ':ci' => $ci]);
        return (bool) $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Registrar un nuevo pago para una antena.
     */
    public function register($antenna_id, $amount, $currency, $payment_date, $submittedBy, $status, $receiptPath = null, $monthsPaid = 1) {
        try {
            $stmt = $this->db->prepare("SELECT client FROM antenas WHERE id_starlink = :id");
            $stmt->bindParam(':id', $antenna_id, PDO::PARAM_INT);
            $stmt->execute();
            $antenna = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$antenna) {
                return false;
            }

            $client_id = $antenna['client'] ?? null;
            $monthsPaid = max(1, intval($monthsPaid));

            $sql = "INSERT INTO payments (antenna_id, client_id, amount, currency, payment_date, submitted_by, status, receipt_path, months_paid)
                    VALUES (:antenna_id, :client_id, :amount, :currency, :payment_date, :submitted_by, :status, :receipt_path, :months_paid)";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':antenna_id', $antenna_id, PDO::PARAM_INT);

            if (empty($client_id)) {
                $stmt->bindValue(':client_id', null, PDO::PARAM_NULL);
            } else {
                $stmt->bindParam(':client_id', $client_id, PDO::PARAM_INT);
            }

            $stmt->bindParam(':amount', $amount);
            $stmt->bindParam(':currency', $currency, PDO::PARAM_STR);
            $stmt->bindParam(':payment_date', $payment_date, PDO::PARAM_STR);
            $stmt->bindParam(':submitted_by', $submittedBy, PDO::PARAM_INT);
            $stmt->bindParam(':status', $status, PDO::PARAM_STR);
            $stmt->bindValue(':receipt_path', $receiptPath, $receiptPath === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
            $stmt->bindParam(':months_paid', $monthsPaid, PDO::PARAM_INT);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error en PaymentModel::register -> " . $e->getMessage());
            return false;
        }
    }

    public function registerMany($antennaIds, $amounts, $currency, $paymentDate, $submittedBy, $status, $receiptPath = null, $monthsPaid = 1) {
        $isNested = $this->db->inTransaction();
        try {
            if (!$isNested) {
                $this->db->beginTransaction();
            }

            $firstAntennaId = (int) $antennaIds[0];
            $antennaStmt = $this->db->prepare('SELECT client FROM antenas WHERE id_starlink = :id');
            $antennaStmt->execute([':id' => $firstAntennaId]);
            $antenna = $antennaStmt->fetch(PDO::FETCH_ASSOC);
            if (!$antenna) {
                if (!$isNested && $this->db->inTransaction()) {
                    $this->db->rollBack();
                }
                $this->lastError = 'La antena seleccionada no existe en la base de datos.';
                return false;
            }

            $clientId = !empty($antenna['client']) ? (int)$antenna['client'] : null;
            $monthsPaid = max(1, intval($monthsPaid));
            $totalAmount = array_sum(array_map('floatval', $amounts)) * $monthsPaid;

            $stmt = $this->db->prepare(
                'INSERT INTO payments (antenna_id, client_id, amount, currency, payment_date, submitted_by, status, receipt_path, months_paid)
                 VALUES (:antenna_id, :client_id, :amount, :currency, :payment_date, :submitted_by, :status, :receipt_path, :months_paid)'
            );
            $stmt->execute([
                ':antenna_id' => $firstAntennaId,
                ':client_id' => $clientId,
                ':amount' => $totalAmount,
                ':currency' => $currency,
                ':payment_date' => $paymentDate,
                ':submitted_by' => $submittedBy > 0 ? $submittedBy : null,
                ':status' => $status,
                ':receipt_path' => $receiptPath,
                ':months_paid' => $monthsPaid
            ]);

            $paymentId = $this->db->lastInsertId();
            $linkStmt = $this->db->prepare('INSERT IGNORE INTO payment_antennas (payment_id, antenna_id) VALUES (:payment_id, :antenna_id)');
            foreach ($antennaIds as $antennaId) {
                $linkStmt->execute([':payment_id' => $paymentId, ':antenna_id' => $antennaId]);
            }

            if (!$isNested) {
                $this->db->commit();
            }
            return true;
        } catch (PDOException $e) {
            if (!$isNested && $this->db->inTransaction()) {
                $this->db->rollBack();
            }
            $this->lastError = $e->getMessage();
            error_log("Error en PaymentModel::registerMany -> " . $e->getMessage());
            return false;
        }
    }

    public function getAmountsForAntennas($antennaIds) {
        if (empty($antennaIds)) {
            return [];
        }

        try {
            $placeholders = [];
            $params = [];
            foreach (array_values($antennaIds) as $index => $antennaId) {
                $placeholder = ':antenna' . $index;
                $placeholders[] = $placeholder;
                $params[$placeholder] = $antennaId;
            }

            $stmt = $this->db->prepare(
                'SELECT a.id_starlink, COALESCE(p.price, 0) AS price
                 FROM antenas a
                 LEFT JOIN plan p ON a.plan = p.id_plan
                 WHERE a.id_starlink IN (' . implode(', ', $placeholders) . ')'
            );
            $stmt->execute($params);

            $amounts = [];
            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
                $amounts[(int) $row['id_starlink']] = floatval($row['price']);
            }
            return $amounts;
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            error_log("Error en PaymentModel::getAmountsForAntennas -> " . $e->getMessage());
            return [];
        }
    }

    public function review($id, $status, $reviewedBy) {
        try {
            $stmt = $this->db->prepare("UPDATE payments SET status = :status, reviewed_by = :reviewed_by, reviewed_at = NOW() WHERE id_payment = :id AND status = 'Pendiente'");
            return $stmt->execute([
                ':id' => $id,
                ':status' => $status,
                ':reviewed_by' => $reviewedBy
            ]);
        } catch (PDOException $e) {
            error_log("Error en PaymentModel::review -> " . $e->getMessage());
            return false;
        }
    }

    /**
     * Eliminar un pago por ID.
     */
    public function delete($id) {
        try {
            $sql = "DELETE FROM payments WHERE id_payment = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error en PaymentModel::delete -> " . $e->getMessage());
            return false;
        }
    }

    /**
     * Total recaudado en un mes específico (pagos aprobados)
     */
    public function getMonthlyApprovedSum($year, $month) {
        try {
            $stmt = $this->db->prepare("SELECT COALESCE(SUM(amount), 0) AS total FROM payments WHERE status = 'Aprobado' AND YEAR(payment_date) = :year AND MONTH(payment_date) = :month");
            $stmt->execute([':year' => $year, ':month' => $month]);
            return (float)$stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Error en PaymentModel::getMonthlyApprovedSum -> " . $e->getMessage());
            return 0.0;
        }
    }

    /**
     * Tendencia de recaudación de los últimos N meses (para gráficos)
     */
    public function getCollectionTrend($months = 6) {
        try {
            $trend = [];
            $spanishMonths = [
                1 => 'Ene', 2 => 'Feb', 3 => 'Mar', 4 => 'Abr',
                5 => 'May', 6 => 'Jun', 7 => 'Jul', 8 => 'Ago',
                9 => 'Sep', 10 => 'Oct', 11 => 'Nov', 12 => 'Dic'
            ];

            for ($i = $months - 1; $i >= 0; $i--) {
                $targetTime = strtotime("-{$i} month");
                $m = (int)date('n', $targetTime);
                $y = (int)date('Y', $targetTime);
                $label = $spanishMonths[$m] . ' ' . $y;

                $total = $this->getMonthlyApprovedSum($y, $m);
                $trend[] = [
                    'year' => $y,
                    'month' => $m,
                    'label' => $label,
                    'total' => $total
                ];
            }
            return $trend;
        } catch (Throwable $e) {
            error_log("Error en PaymentModel::getCollectionTrend -> " . $e->getMessage());
            return [];
        }
    }

    /**
     * Conteos de pagos por estado (Aprobado, Pendiente, Rechazado)
     */
    public function getPaymentStatusCounts($month = null, $year = null) {
        try {
            $sql = "SELECT status, COUNT(*) AS count, COALESCE(SUM(amount), 0) AS total FROM payments";
            $params = [];
            if ($month !== null && $year !== null) {
                $sql .= " WHERE YEAR(payment_date) = :year AND MONTH(payment_date) = :month";
                $params[':year'] = $year;
                $params[':month'] = $month;
            }
            $sql .= " GROUP BY status";
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $summary = [
                'Aprobado' => ['count' => 0, 'total' => 0.0],
                'Pendiente' => ['count' => 0, 'total' => 0.0],
                'Rechazado' => ['count' => 0, 'total' => 0.0]
            ];
            foreach ($results as $r) {
                if (isset($summary[$r['status']])) {
                    $summary[$r['status']] = [
                        'count' => (int)$r['count'],
                        'total' => (float)$r['total']
                    ];
                }
            }
            return $summary;
        } catch (PDOException $e) {
            error_log("Error en PaymentModel::getPaymentStatusCounts -> " . $e->getMessage());
            return ['Aprobado' => ['count' => 0, 'total' => 0.0], 'Pendiente' => ['count' => 0, 'total' => 0.0], 'Rechazado' => ['count' => 0, 'total' => 0.0]];
        }
    }

    /**
     * Obtener antenas cuyos días de pago vencen en los próximos X días
     */
    public function getUpcomingExpirations($daysAhead = 7) {
        try {
            $today = new DateTime('today');
            $currentDay = (int)$today->format('j');
            $daysInMonth = (int)$today->format('t');

            $targetDays = [];
            for ($d = 0; $d <= $daysAhead; $d++) {
                $calcDay = $currentDay + $d;
                if ($calcDay <= $daysInMonth) {
                    $targetDays[] = $calcDay;
                }
            }

            if (empty($targetDays)) {
                return [];
            }

            $inPlaceholders = implode(',', array_fill(0, count($targetDays), '?'));
            $sql = "SELECT a.id_starlink, a.serial, a.nickname, a.pay, a.date,
                           c.id_client, CONCAT(c.name, ' ', c.surname) AS cliente, c.phone, c.ci,
                           p.plan AS nombre_plan, p.price AS plan_price
                    FROM antenas a
                    INNER JOIN client c ON a.client = c.id_client
                    INNER JOIN plan p ON a.plan = p.id_plan
                    WHERE a.pay IN ($inPlaceholders) AND p.plan NOT IN ('Inactive', 'Standby')
                    ORDER BY a.pay ASC, c.name ASC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute($targetDays);
            $antenas = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Filtrar las que ya pagaron este mes
            $upcoming = [];
            foreach ($antenas as $ant) {
                $payStmt = $this->db->prepare("SELECT COUNT(*) FROM payments pay
                                               INNER JOIN payment_antennas pa ON pay.id_payment = pa.payment_id
                                               WHERE pa.antenna_id = :id AND pay.status = 'Aprobado'
                                               AND YEAR(pay.payment_date) = :year AND MONTH(pay.payment_date) = :month");
                $payStmt->execute([
                    ':id' => $ant['id_starlink'],
                    ':year' => (int)$today->format('Y'),
                    ':month' => (int)$today->format('n')
                ]);
                $alreadyPaid = (int)$payStmt->fetchColumn() > 0;

                $dueDay = (int)$ant['pay'];
                $daysRemaining = $dueDay - $currentDay;

                $ant['already_paid'] = $alreadyPaid;
                $ant['days_remaining'] = $daysRemaining;
                $upcoming[] = $ant;
            }

            return $upcoming;
        } catch (Throwable $e) {
            error_log("Error en PaymentModel::getUpcomingExpirations -> " . $e->getMessage());
            return [];
        }
    }

    /**
     * Resumen financiero de un cliente para su portal de autogestión
     */
    public function getClientSummary($ci) {
        try {
            if (empty($ci)) return null;

            // Total antenas del cliente
            $stmtAnt = $this->db->prepare("SELECT a.id_starlink, a.serial, a.nickname, a.pay, p.plan AS nombre_plan, p.price AS plan_price
                                           FROM antenas a
                                           INNER JOIN client c ON a.client = c.id_client
                                           INNER JOIN plan p ON a.plan = p.id_plan
                                           WHERE c.ci = :ci");
            $stmtAnt->execute([':ci' => $ci]);
            $antennas = $stmtAnt->fetchAll(PDO::FETCH_ASSOC);

            $monthlyTotal = 0.0;
            foreach ($antennas as $a) {
                if (!in_array($a['nombre_plan'], ['Inactive', 'Standby'], true)) {
                    $monthlyTotal += (float)$a['plan_price'];
                }
            }

            // Pagos recientes del cliente
            $stmtPay = $this->db->prepare("SELECT pay.*, GROUP_CONCAT(a.serial SEPARATOR ', ') AS antenas
                                           FROM payments pay
                                           INNER JOIN payment_antennas pa ON pay.id_payment = pa.payment_id
                                           INNER JOIN antenas a ON pa.antenna_id = a.id_starlink
                                           INNER JOIN client c ON a.client = c.id_client
                                           WHERE c.ci = :ci
                                           GROUP BY pay.id_payment
                                           ORDER BY pay.payment_date DESC LIMIT 10");
            $stmtPay->execute([':ci' => $ci]);
            $recentPayments = $stmtPay->fetchAll(PDO::FETCH_ASSOC);

            return [
                'antennas' => $antennas,
                'total_antennas' => count($antennas),
                'monthly_total' => $monthlyTotal,
                'recent_payments' => $recentPayments
            ];
        } catch (Throwable $e) {
            error_log("Error en PaymentModel::getClientSummary -> " . $e->getMessage());
            return null;
        }
    }
}
