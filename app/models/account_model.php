<?php

class AccountModel {
    private $db;

    public function __construct() {
        $this->db = Database::connect();
    }

    /**
     * Obtener el listado completo de cuentas administrativas Starlink
     */
    public function getAll() {
        try {
            $sql = "SELECT
                        a.id_accounts,
                        a.owner,
                        a.acc,
                        a.email,
                        a.create_date,
                        COUNT(s.id_starlink) AS starlink_count,
                        GROUP_CONCAT(DISTINCT s.serial ORDER BY s.id_starlink DESC SEPARATOR ', ') AS starlinks
                    FROM accounts a
                    LEFT JOIN antenas s ON s.account_id = a.id_accounts
                    GROUP BY a.id_accounts, a.owner, a.acc, a.email, a.create_date
                    ORDER BY a.id_accounts DESC";

            $stmt = $this->db->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en AccountModel::getAll -> " . $e->getMessage());
            return [];
        }
    }

    /**
     * Registrar una nueva cuenta Starlink en el sistema
     */
    public function register($owner, $acc, $email, $create_date) {
        try {
            $sql = "INSERT INTO accounts (owner, acc, email, create_date) VALUES (:owner, :acc, :email, :create_date)";
            $stmt = $this->db->prepare($sql);

            $stmt->bindParam(':owner', $owner, PDO::PARAM_STR);
            $stmt->bindParam(':acc', $acc, PDO::PARAM_STR);
            $stmt->bindParam(':email', $email, PDO::PARAM_STR);
            $stmt->bindParam(':create_date', $create_date, PDO::PARAM_STR);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error en AccountModel::register -> " . $e->getMessage());
            return false;
        }
    }

    /**
     * Actualizar una cuenta existente
     */
    public function update($id, $owner, $acc, $email, $create_date) {
        try {
            $sql = "UPDATE accounts SET owner = :owner, acc = :acc, email = :email, create_date = :create_date WHERE id_accounts = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->bindParam(':owner', $owner, PDO::PARAM_STR);
            $stmt->bindParam(':acc', $acc, PDO::PARAM_STR);
            $stmt->bindParam(':email', $email, PDO::PARAM_STR);
            $stmt->bindParam(':create_date', $create_date, PDO::PARAM_STR);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error en AccountModel::update -> " . $e->getMessage());
            return false;
        }
    }

    /**
     * Eliminar una cuenta por ID
     */
    public function delete($id) {
        try {
            $sql = "DELETE FROM accounts WHERE id_accounts = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error en AccountModel::delete -> " . $e->getMessage());
            return false;
        }
    }
}