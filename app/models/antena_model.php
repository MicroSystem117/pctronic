<?php

class AntenaModel {
    private $db;

    public function __construct() {
        // Conexión nativa a tu base de datos
        $this->db = Database::connect();
    }

    /**
     * Obtener todas las antenas con la información cruzada de clientes, planes, países y cuentas
     */
    public function getAll($userRole = 'Administrador', $ci = null) {
        try {
            $sql = "SELECT 
                        a.id_starlink, 
                        a.serial, 
                        a.nickname,
                        a.kit, 
                        a.date,
                        a.pay,
                        a.client AS client_id,
                        a.account_id AS account_id,
                        a.plan AS plan_id,
                        a.country AS country_id,
                        CONCAT(c.name, ' ', c.surname) AS cliente,
                        p.plan AS nombre_plan,
                        co.country AS pais,
                        CONCAT(acc.owner, ' • ', acc.acc) AS cuenta_starlink,
                        lp.last_payment_date,
                        lp.last_amount,
                        lp.last_currency
                    FROM antenas a 
                    LEFT JOIN client c ON a.client = c.id_client
                    INNER JOIN plan p ON a.plan = p.id_plan
                    INNER JOIN country co ON a.country = co.id_country
                    LEFT JOIN accounts acc ON a.account_id = acc.id_accounts
                    LEFT JOIN (
                        SELECT p1.antenna_id, p1.payment_date AS last_payment_date, p1.amount AS last_amount, p1.currency AS last_currency
                        FROM payments p1
                        INNER JOIN (
                            SELECT antenna_id, MAX(payment_date) AS max_date
                            FROM payments
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
            error_log("Error en AntenaModel::getAll -> " . $e->getMessage());
            return [];
        }
    }

    /**
     * Insertar una nueva antena en la base de datos
     */
    public function register($client_id, $account_id, $serial, $nickname, $kit, $date, $pay, $plan_id, $country_id) {
        try {
            $sql = "INSERT INTO antenas (client, account_id, serial, nickname, kit, date, pay, plan, country) 
                    VALUES (:client, :account_id, :serial, :nickname, :kit, :date, :pay, :plan, :country)";
            
            $stmt = $this->db->prepare($sql);

            // Vinculamos de forma segura con los tipos de datos correctos de tu SQL
            $stmt->bindParam(':client', $client_id, PDO::PARAM_INT);
            // Si no se selecciona cuenta, pasamos NULL explícito
            if (empty($account_id)) {
                $stmt->bindValue(':account_id', null, PDO::PARAM_NULL);
            } else {
                $stmt->bindParam(':account_id', $account_id, PDO::PARAM_INT);
            }
            $stmt->bindParam(':serial', $serial, PDO::PARAM_STR);
            $stmt->bindParam(':nickname', $nickname, PDO::PARAM_STR);
            $stmt->bindParam(':kit', $kit, PDO::PARAM_STR);
            $stmt->bindParam(':date', $date, PDO::PARAM_STR);
            if ($pay === null || $pay === '' ) {
                $stmt->bindValue(':pay', null, PDO::PARAM_NULL);
            } else {
                $payInt = intval($pay);
                $stmt->bindParam(':pay', $payInt, PDO::PARAM_INT);
            }
            $stmt->bindParam(':plan', $plan_id, PDO::PARAM_INT);
            $stmt->bindParam(':country', $country_id, PDO::PARAM_INT);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error en AntenaModel::register -> " . $e->getMessage());
            return false;
        }
    }

    /**
     * Actualizar una antena existente
     */
    public function update($id, $client_id, $account_id, $serial, $nickname, $kit, $date, $pay, $plan_id, $country_id) {
        try {
            $sql = "UPDATE antenas SET client = :client, account_id = :account_id, serial = :serial, nickname = :nickname, kit = :kit, date = :date, pay = :pay, plan = :plan, country = :country WHERE id_starlink = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->bindParam(':client', $client_id, PDO::PARAM_INT);
            if (empty($account_id)) {
                $stmt->bindValue(':account_id', null, PDO::PARAM_NULL);
            } else {
                $stmt->bindParam(':account_id', $account_id, PDO::PARAM_INT);
            }
            $stmt->bindParam(':serial', $serial, PDO::PARAM_STR);
            $stmt->bindParam(':nickname', $nickname, PDO::PARAM_STR);
            $stmt->bindParam(':kit', $kit, PDO::PARAM_STR);
            $stmt->bindParam(':date', $date, PDO::PARAM_STR);
            if ($pay === null || $pay === '' ) {
                $stmt->bindValue(':pay', null, PDO::PARAM_NULL);
            } else {
                $payInt = intval($pay);
                $stmt->bindParam(':pay', $payInt, PDO::PARAM_INT);
            }
            $stmt->bindParam(':plan', $plan_id, PDO::PARAM_INT);
            $stmt->bindParam(':country', $country_id, PDO::PARAM_INT);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error en AntenaModel::update -> " . $e->getMessage());
            return false;
        }
    }

    /**
     * Eliminar una antena por ID
     */
    public function delete($id) {
        try {
            $sql = "DELETE FROM antenas WHERE id_starlink = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error en AntenaModel::delete -> " . $e->getMessage());
            return false;
        }
    }
}