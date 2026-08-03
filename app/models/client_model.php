<?php

class ClientModel {
    private $db;

    public function __construct() {
        $this->db = Database::connect();
    }

    /**
     * Obtener el listado completo de clientes
     */
    public function getAll($ci = null) {
        try {
            $sql = "SELECT
                        c.id_client,
                        c.name,
                        c.surname,
                        c.ci,
                        c.phone,
                        COUNT(a.id_starlink) AS starlink_count,
                        GROUP_CONCAT(DISTINCT a.serial ORDER BY a.id_starlink DESC SEPARATOR ', ') AS starlinks
                    FROM client c
                    LEFT JOIN antenas a ON a.client = c.id_client";

            $params = [];

            if ($ci !== null) {
                $sql .= " WHERE c.ci = :ci";
                $params[':ci'] = $ci;
            }

            $sql .= " GROUP BY c.id_client, c.name, c.surname, c.ci, c.phone ORDER BY c.id_client DESC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en ClientModel::getAll -> " . $e->getMessage());
            return [];
        }
    }

    /**
     * Registrar un nuevo cliente
     */
    public function register($name, $surname, $ci, $phone) {
        try {
            $sql = "INSERT INTO client (name, surname, ci, phone) VALUES (:name, :surname, :ci, :phone)";
            $stmt = $this->db->prepare($sql);

            $stmt->bindParam(':name', $name, PDO::PARAM_STR);
            $stmt->bindParam(':surname', $surname, PDO::PARAM_STR);
            
            if (empty($ci)) {
                $stmt->bindValue(':ci', null, PDO::PARAM_NULL);
            } else {
                $stmt->bindParam(':ci', $ci, PDO::PARAM_INT);
            }
            
            $stmt->bindParam(':phone', $phone, PDO::PARAM_STR);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error en ClientModel::register -> " . $e->getMessage());
            return false;
        }
    }

    /**
     * Actualizar datos de un cliente existente
     */
    public function update($id, $name, $surname, $ci, $phone) {
        try {
            $sql = "UPDATE client 
                    SET name = :name, surname = :surname, ci = :ci, phone = :phone 
                    WHERE id_client = :id";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->bindParam(':name', $name, PDO::PARAM_STR);
            $stmt->bindParam(':surname', $surname, PDO::PARAM_STR);
            
            if (empty($ci)) {
                $stmt->bindValue(':ci', null, PDO::PARAM_NULL);
            } else {
                $stmt->bindParam(':ci', $ci, PDO::PARAM_INT);
            }
            
            $stmt->bindParam(':phone', $phone, PDO::PARAM_STR);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error en ClientModel::update -> " . $e->getMessage());
            return false;
        }
    }

    /**
     * Eliminar un cliente por ID
     */
    public function delete($id) {
        try {
            $sql = "DELETE FROM client WHERE id_client = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error en ClientModel::delete -> " . $e->getMessage());
            return false;
        }
    }
}