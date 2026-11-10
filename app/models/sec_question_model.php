<?php

class SecQuestionModel {
    public function getByUserId($userId) {
        $db = Database::connect();
        $stmt = $db->prepare('SELECT * FROM `secquestion` WHERE id_user = :id');
        $stmt->execute([':id' => $userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function hasSecurityQuestions($userId) {
        $db = Database::connect();
        $stmt = $db->prepare('SELECT COUNT(*) AS total FROM `secquestion` WHERE id_user = :id AND question1 IS NOT NULL AND question2 IS NOT NULL AND question3 IS NOT NULL');
        $stmt->execute([':id' => $userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return isset($row['total']) ? (int)$row['total'] > 0 : false;
    }

    public function save($userId, $q1, $a1, $q2, $a2, $q3, $a3) {
        $db = Database::connect();
        $stmt = $db->prepare('REPLACE INTO `secquestion` (id_user, question1, answer1, question2, answer2, question3, answer3) VALUES (:id, :q1, :a1, :q2, :a2, :q3, :a3)');
        return $stmt->execute([
            ':id' => $userId,
            ':q1' => $q1,
            ':a1' => password_hash(strtolower(trim($a1)), PASSWORD_DEFAULT),
            ':q2' => $q2,
            ':a2' => password_hash(strtolower(trim($a2)), PASSWORD_DEFAULT),
            ':q3' => $q3,
            ':a3' => password_hash(strtolower(trim($a3)), PASSWORD_DEFAULT)
        ]);
    }

    public function verifyAnswer($inputAnswer, $storedHashOrPlain) {
        if ($storedHashOrPlain === null || $storedHashOrPlain === '') {
            return false;
        }
        $cleanInput = strtolower(trim((string)$inputAnswer));
        $info = password_get_info($storedHashOrPlain);
        if (!empty($info['algo'])) {
            return password_verify($cleanInput, $storedHashOrPlain);
        }
        return strcasecmp($cleanInput, strtolower(trim((string)$storedHashOrPlain))) === 0;
    }
}
