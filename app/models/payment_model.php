<?php

class PaymentModel {
    private $db;

    public function __construct() {
        $this->db = Database::connect();
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
                        GROUP_CONCAT(DISTINCT a.serial ORDER BY a.serial SEPARATOR ', ') AS serial,
                        GROUP_CONCAT(DISTINCT CONCAT(c.name, ' ', c.surname) ORDER BY c.name SEPARATOR ', ') AS cliente,
                        GROUP_CONCAT(DISTINCT p.plan ORDER BY p.plan SEPARATOR ', ') AS nombre_plan,
                        GROUP_CONCAT(DISTINCT co.country ORDER BY co.country SEPARATOR ', ') AS pais
                    FROM payments pay
                    INNER JOIN payment_antennas pa ON pay.id_payment = pa.payment_id
                    INNER JOIN antenas a ON pa.antenna_id = a.id_starlink
                    LEFT JOIN client c ON a.client = c.id_client
                    INNER JOIN plan p ON a.plan = p.id_plan
                    INNER JOIN country co ON a.country = co.id_country";

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
            $sql .= " GROUP BY pay.id_payment, pay.payment_date, pay.amount, pay.currency, pay.created_at, pay.receipt_path, pay.status, pay.reviewed_at";
            $sql .= " ORDER BY pay.payment_date DESC, pay.id_payment DESC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en PaymentModel::getAll -> " . $e->getMessage());
            return [];
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
                        a.pay,
                        CONCAT(c.name, ' ', c.surname) AS cliente,
                        a.client AS client_id,
                        p.plan AS nombre_plan,
                        p.price AS plan_price,
                        co.country AS pais,
                        CONCAT(acc.owner, ' • ', acc.acc) AS cuenta_starlink,
                        lp.last_payment_date,
                        lp.last_amount,
                        lp.last_currency,
                        lp.last_status
                    FROM antenas a
                    LEFT JOIN client c ON a.client = c.id_client
                    INNER JOIN plan p ON a.plan = p.id_plan
                    INNER JOIN country co ON a.country = co.id_country
                    LEFT JOIN accounts acc ON a.account_id = acc.id_accounts
                    LEFT JOIN (
                        SELECT pa1.antenna_id, p1.payment_date AS last_payment_date, p1.amount AS last_amount, p1.currency AS last_currency{$lastStatusSelect}
                        FROM payments p1
                        INNER JOIN payment_antennas pa1 ON p1.id_payment = pa1.payment_id
                        INNER JOIN (
                            SELECT pa2.antenna_id, MAX(p2.payment_date) AS max_date
                            FROM payments p2
                            INNER JOIN payment_antennas pa2 ON p2.id_payment = pa2.payment_id
                            {$approvedFilter}
                            GROUP BY pa2.antenna_id
                        ) p2 ON pa1.antenna_id = p2.antenna_id AND p1.payment_date = p2.max_date
                    ) lp ON a.id_starlink = lp.antenna_id";

            $params = [];
            if ($userRole === 'Expectador' && $ci !== null) {
                $sql .= " WHERE c.ci = :ci";
                $params[':ci'] = $ci;
            }

            $sql .= " ORDER BY a.id_starlink DESC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
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
    public function register($antenna_id, $amount, $currency, $payment_date, $submittedBy, $status, $receiptPath = null) {
        try {
            $stmt = $this->db->prepare("SELECT client FROM antenas WHERE id_starlink = :id");
            $stmt->bindParam(':id', $antenna_id, PDO::PARAM_INT);
            $stmt->execute();
            $antenna = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$antenna) {
                return false;
            }

            $client_id = $antenna['client'] ?? null;

                $sql = "INSERT INTO payments (antenna_id, client_id, amount, currency, payment_date, submitted_by, status, receipt_path)
                    VALUES (:antenna_id, :client_id, :amount, :currency, :payment_date, :submitted_by, :status, :receipt_path)";
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

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error en PaymentModel::register -> " . $e->getMessage());
            return false;
        }
    }

    public function registerMany($antennaIds, $amounts, $currency, $paymentDate, $submittedBy, $status, $receiptPath = null) {
        try {
            $this->db->beginTransaction();

            $firstAntennaId = (int) $antennaIds[0];
            $antennaStmt = $this->db->prepare('SELECT client FROM antenas WHERE id_starlink = :id');
            $antennaStmt->execute([':id' => $firstAntennaId]);
            $antenna = $antennaStmt->fetch(PDO::FETCH_ASSOC);
            if (!$antenna) {
                $this->db->rollBack();
                return false;
            }

            $totalAmount = array_sum(array_map('floatval', $amounts));
            $stmt = $this->db->prepare(
                'INSERT INTO payments (antenna_id, client_id, amount, currency, payment_date, submitted_by, status, receipt_path)
                 VALUES (:antenna_id, :client_id, :amount, :currency, :payment_date, :submitted_by, :status, :receipt_path)'
            );
            $stmt->execute([
                ':antenna_id' => $firstAntennaId,
                ':client_id' => $antenna['client'],
                ':amount' => $totalAmount,
                ':currency' => $currency,
                ':payment_date' => $paymentDate,
                ':submitted_by' => $submittedBy,
                ':status' => $status,
                ':receipt_path' => $receiptPath
            ]);

            $paymentId = $this->db->lastInsertId();
            $linkStmt = $this->db->prepare('INSERT INTO payment_antennas (payment_id, antenna_id) VALUES (:payment_id, :antenna_id)');
            foreach ($antennaIds as $antennaId) {
                $linkStmt->execute([':payment_id' => $paymentId, ':antenna_id' => $antennaId]);
            }

            $this->db->commit();
            return true;
        } catch (PDOException $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
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
                'SELECT a.id_starlink, p.price
                 FROM antenas a
                 INNER JOIN plan p ON a.plan = p.id_plan
                 WHERE a.id_starlink IN (' . implode(', ', $placeholders) . ')'
            );
            $stmt->execute($params);

            $amounts = [];
            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
                $amounts[(int) $row['id_starlink']] = $row['price'];
            }
            return $amounts;
        } catch (PDOException $e) {
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
}
