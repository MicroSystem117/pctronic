<?php

class UserModel {
    public function register($name, $surname, $ci, $birth, $password, $id_level = 3) {
        $db = Database::connect();

        if ($this->findByCi($ci)) {
            return false;
        }

        $hash = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $db->prepare('INSERT INTO `user` (name, surname, ci, birth, pass, id_level) VALUES (:name, :surname, :ci, :birth, :pass, :id_level)');
        return $stmt->execute([
            ':name' => $name,
            ':surname' => $surname,
            ':ci' => $ci,
            ':birth' => $birth,
            ':pass' => $hash,
            ':id_level' => $id_level
        ]);
    }

    public function login($ci, $password) {
        $db = Database::connect();
        $stmt = $db->prepare('SELECT u.*, l.user_role FROM `user` u LEFT JOIN `level` l ON u.id_level = l.id_level WHERE u.ci = :ci');
        $stmt->execute([':ci' => $ci]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            return false;
        }

        if (!password_verify($password, $user['pass'])) {
            return false;
        }

        return $user;
    }

    public function findByCi($ci) {
        $db = Database::connect();
        $stmt = $db->prepare('SELECT * FROM `user` WHERE ci = :ci');
        $stmt->execute([':ci' => $ci]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function findById($id) {
        $db = Database::connect();
        $stmt = $db->prepare('SELECT u.*, l.user_role FROM `user` u LEFT JOIN `level` l ON u.id_level = l.id_level WHERE u.id_user = :id');
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
