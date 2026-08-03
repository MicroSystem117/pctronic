<?php

class PlanModel {
    private $db;

    public function __construct() {
        $this->db = Database::connect();
    }

    public function getAll() {
        $stmt = $this->db->prepare(
            "SELECT p.*, COALESCE(COUNT(a.id_starlink), 0) AS antenna_count " .
            "FROM plan p " .
            "LEFT JOIN antenas a ON a.plan = p.id_plan " .
            "GROUP BY p.id_plan, p.plan, p.price " .
            "ORDER BY p.id_plan ASC"
        );
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function register($planName, $price) {
        try {
            $stmt = $this->db->prepare("INSERT INTO plan (plan, price) VALUES (:plan, :price)");
            $stmt->bindParam(':plan', $planName, PDO::PARAM_STR);
            $stmt->bindParam(':price', $price, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error en PlanModel::register -> " . $e->getMessage());
            return false;
        }
    }

    public function update($id, $planName, $price) {
        try {
            $stmt = $this->db->prepare("UPDATE plan SET plan = :plan, price = :price WHERE id_plan = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->bindParam(':plan', $planName, PDO::PARAM_STR);
            $stmt->bindParam(':price', $price, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error en PlanModel::update -> " . $e->getMessage());
            return false;
        }
    }

    public function delete($id) {
        try {
            $stmt = $this->db->prepare("DELETE FROM plan WHERE id_plan = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error en PlanModel::delete -> " . $e->getMessage());
            return false;
        }
    }
}