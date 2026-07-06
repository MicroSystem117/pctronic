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
            ':a1' => $a1,
            ':q2' => $q2,
            ':a2' => $a2,
            ':q3' => $q3,
            ':a3' => $a3
        ]);
    }
}
