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
                        a.countries AS country_id,
                        co.country AS pais,
                        COUNT(s.id_starlink) AS starlink_count,
                        GROUP_CONCAT(DISTINCT CONCAT('Cliente: ', COALESCE(CONCAT(c.name, ' ', c.surname), 'Sin cliente'), ' | Serial: ', s.serial) ORDER BY s.id_starlink DESC SEPARATOR ' || ') AS starlinks
                    FROM accounts a
                    LEFT JOIN antenas s ON s.account_id = a.id_accounts
                    LEFT JOIN client c ON c.id_client = s.client
                    LEFT JOIN country co ON co.id_country = a.countries
                    GROUP BY a.id_accounts, a.owner, a.acc, a.email, a.create_date, a.countries, co.country
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
    public function register($owner, $acc, $email, $create_date, $countryId = null) {
        try {
            $sql = "INSERT INTO accounts (owner, acc, email, create_date, countries) VALUES (:owner, :acc, :email, :create_date, :countries)";
            $stmt = $this->db->prepare($sql);

            $stmt->bindParam(':owner', $owner, PDO::PARAM_STR);
            $stmt->bindParam(':acc', $acc, PDO::PARAM_STR);
            $stmt->bindParam(':email', $email, PDO::PARAM_STR);
            $stmt->bindParam(':create_date', $create_date, PDO::PARAM_STR);
            $this->bindCountry($stmt, $countryId);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error en AccountModel::register -> " . $e->getMessage());
            return false;
        }
    }

    /**
     * Actualizar una cuenta existente
     */
    public function update($id, $owner, $acc, $email, $create_date, $countryId = null) {
        try {
            $sql = "UPDATE accounts SET owner = :owner, acc = :acc, email = :email, create_date = :create_date, countries = :countries WHERE id_accounts = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->bindParam(':owner', $owner, PDO::PARAM_STR);
            $stmt->bindParam(':acc', $acc, PDO::PARAM_STR);
            $stmt->bindParam(':email', $email, PDO::PARAM_STR);
            $stmt->bindParam(':create_date', $create_date, PDO::PARAM_STR);
            $this->bindCountry($stmt, $countryId);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error en AccountModel::update -> " . $e->getMessage());
            return false;
        }
    }

    private function bindCountry($stmt, $countryId) {
        if ($countryId === null || $countryId <= 0) {
            $stmt->bindValue(':countries', null, PDO::PARAM_NULL);
        } else {
            $stmt->bindValue(':countries', $countryId, PDO::PARAM_INT);
        }
    }

    /**
     * Eliminar una cuenta por ID
     */
    public function delete($id, $preserveAntennas = true) {
        $db = $this->db;
        try {
            $db->beginTransaction();

            if (!$preserveAntennas) {
                $stmt = $db->prepare("DELETE FROM antenas WHERE account_id = :id");
                $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                $stmt->execute();
            }

            $sql = "DELETE FROM accounts WHERE id_accounts = :id";
            $stmt = $db->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $deleted = $stmt->execute();

            if ($deleted) {
                $db->commit();
            } else {
                $db->rollBack();
            }

            return $deleted;
        } catch (PDOException $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            error_log("Error en AccountModel::delete -> " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtener el país asociado a una cuenta administrativa
     */
    public function getCountryId($accountId) {
        try {
            $stmt = $this->db->prepare('SELECT countries FROM accounts WHERE id_accounts = :id');
            $stmt->execute([':id' => intval($accountId)]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row ? intval($row['countries']) : 0;
        } catch (PDOException $e) {
            error_log("Error en AccountModel::getCountryId -> " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Buscar duplicado por número de cuenta o email excluyendo un ID
     */
    public function findDuplicate($acc, $email, $excludeId = 0) {
        try {
            $currentAccount = null;
            if ($excludeId > 0) {
                $stmt = $this->db->prepare("SELECT acc, email FROM accounts WHERE id_accounts = :id");
                $stmt->bindValue(':id', $excludeId, PDO::PARAM_INT);
                $stmt->execute();
                $currentAccount = $stmt->fetch(PDO::FETCH_ASSOC);
            }

            $accChanged = !$currentAccount || trim($currentAccount['acc']) !== trim($acc);
            $emailChanged = !$currentAccount || trim($currentAccount['email']) !== trim($email);

            if (!$accChanged && !$emailChanged) {
                return null;
            }

            $conditions = [];
            $params = [':id' => intval($excludeId)];
            if ($accChanged) {
                $conditions[] = 'TRIM(acc) = TRIM(:acc)';
                $params[':acc'] = trim($acc);
            }
            if ($emailChanged) {
                $conditions[] = 'TRIM(email) = TRIM(:email)';
                $params[':email'] = trim($email);
            }

            $sql = "SELECT id_accounts, acc, email FROM accounts WHERE (" . implode(' OR ', $conditions) . ") AND id_accounts <> :id LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (PDOException $e) {
            error_log("Error en AccountModel::findDuplicate -> " . $e->getMessage());
            return null;
        }
    }

    /**
     * Obtener lista de cuentas formateada para select
     */
    public function getSelectOptions() {
        try {
            $sql = "SELECT a.id_accounts, CONCAT(a.owner, ' • ', a.acc) AS info_cuenta, a.countries AS country_id, c.country AS country_name 
                    FROM accounts a 
                    LEFT JOIN country c ON c.id_country = a.countries 
                    ORDER BY a.owner ASC";
            $stmt = $this->db->query($sql);
            return $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
        } catch (PDOException $e) {
            error_log("Error en AccountModel::getSelectOptions -> " . $e->getMessage());
            return [];
        }
    }

    /**
     * Contar total de cuentas
     */
    public function count() {
        try {
            $stmt = $this->db->query("SELECT COUNT(*) AS total FROM accounts");
            return $stmt ? (int)$stmt->fetchColumn() : 0;
        } catch (PDOException $e) {
            error_log("Error en AccountModel::count -> " . $e->getMessage());
            return 0;
        }
    }
}