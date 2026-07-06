<?php

class CountryModel {
    private $db;

    public function __construct() {
        $this->db = Database::connect();
    }

    public function getAll() {
        $stmt = $this->db->prepare("SELECT * FROM country ORDER BY country ASC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function register($countryName) {
        try {
            $stmt = $this->db->prepare("INSERT INTO country (country) VALUES (:country)");
            $stmt->bindParam(':country', $countryName, PDO::PARAM_STR);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error en CountryModel::register -> " . $e->getMessage());
            return false;
        }
    }

    public function update($id, $countryName) {
        try {
            $stmt = $this->db->prepare("UPDATE country SET country = :country WHERE id_country = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->bindParam(':country', $countryName, PDO::PARAM_STR);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error en CountryModel::update -> " . $e->getMessage());
            return false;
        }
    }

    public function delete($id) {
        try {
            $stmt = $this->db->prepare("DELETE FROM country WHERE id_country = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error en CountryModel::delete -> " . $e->getMessage());
            return false;
        }
    }
}