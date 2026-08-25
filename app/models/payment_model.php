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
                        pay.antenna_id,
                        pay.payment_date,
                        pay.amount,
                        pay.currency,
                        pay.created_at,
                        pay.status,
                        pay.reviewed_at,
                        a.serial,
                        a.nickname,
                        CONCAT(c.name, ' ', c.surname) AS cliente,
                        p.plan AS nombre_plan,
                        co.country AS pais
                    FROM payments pay
                    INNER JOIN antenas a ON pay.antenna_id = a.id_starlink
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
            $sql = "SELECT 
                        a.id_starlink,
                        a.serial,
                        a.nickname,
                        a.pay,
                        CONCAT(c.name, ' ', c.surname) AS cliente,
                        a.client AS client_id,
                        p.plan AS nombre_plan,
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
                        SELECT p1.antenna_id, p1.payment_date AS last_payment_date, p1.amount AS last_amount, p1.currency AS last_currency, p1.status AS last_status
                        FROM payments p1
                        INNER JOIN (
                            SELECT antenna_id, MAX(payment_date) AS max_date
                            FROM payments
                            WHERE status = 'Aprobado'
                            GROUP BY antenna_id
                        ) p2 ON p1.antenna_id = p2.antenna_id AND p1.payment_date = p2.max_date
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
        $stmt = $this->db->prepare('SELECT pay.id_payment FROM payments pay INNER JOIN antenas a ON pay.antenna_id = a.id_starlink INNER JOIN client c ON a.client = c.id_client WHERE pay.id_payment = :payment_id AND c.ci = :ci');
        $stmt->execute([':payment_id' => $paymentId, ':ci' => $ci]);
        return (bool) $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Registrar un nuevo pago para una antena.
     */
    public function register($antenna_id, $amount, $currency, $payment_date, $submittedBy, $status) {
        try {
            $stmt = $this->db->prepare("SELECT client FROM antenas WHERE id_starlink = :id");
            $stmt->bindParam(':id', $antenna_id, PDO::PARAM_INT);
            $stmt->execute();
            $antenna = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$antenna) {
                return false;
            }

            $client_id = $antenna['client'] ?? null;

            $sql = "INSERT INTO payments (antenna_id, client_id, amount, currency, payment_date, submitted_by, status)
                    VALUES (:antenna_id, :client_id, :amount, :currency, :payment_date, :submitted_by, :status)";
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

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error en PaymentModel::register -> " . $e->getMessage());
            return false;
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
