<?php

class UserModel {
    public function getAll($levelIds = []) {
        $db = Database::connect();
        $sql = 'SELECT u.id_user, u.name, u.surname, u.ci, u.birth, u.id_level, l.user_role FROM `user` u LEFT JOIN `level` l ON u.id_level = l.id_level';
        $params = [];
        if (!empty($levelIds)) {
            $placeholders = [];
            foreach (array_values($levelIds) as $index => $levelId) {
                $placeholder = ':level' . $index;
                $placeholders[] = $placeholder;
                $params[$placeholder] = intval($levelId);
            }
            $sql .= ' WHERE u.id_level IN (' . implode(', ', $placeholders) . ')';
        }
        $sql .= ' ORDER BY u.id_user DESC';
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getLevels() {
        $db = Database::connect();
        $stmt = $db->query('SELECT id_level, user_role FROM `level` ORDER BY id_level ASC');
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create($name, $surname, $ci, $birth, $password, $idLevel) {
        return $this->register($name, $surname, $ci, $birth, $password, $idLevel);
    }

    public function update($id, $name, $surname, $ci, $birth, $idLevel, $password = '') {
        $db = Database::connect();
        $fields = 'name = :name, surname = :surname, ci = :ci, birth = :birth, id_level = :id_level';
        $params = [
            ':id' => $id,
            ':name' => $name,
            ':surname' => $surname,
            ':ci' => $ci,
            ':birth' => $birth,
            ':id_level' => $idLevel
        ];

        if ($password !== '') {
            $fields .= ', pass = :pass';
            $params[':pass'] = password_hash($password, PASSWORD_DEFAULT);
        }

        $stmt = $db->prepare("UPDATE `user` SET {$fields} WHERE id_user = :id");
        return $stmt->execute($params);
    }

    public function deleteUser($id) {
        $db = Database::connect();
        $stmt = $db->prepare('DELETE FROM `user` WHERE id_user = :id');
        return $stmt->execute([':id' => $id]);
    }

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

    public function hasActiveSession($userId, $sessionId) {
        $db = Database::connect();
        $stmt = $db->prepare('SELECT id_session FROM user_sessions WHERE user_id = :user_id AND session_id <> :session_id AND active = 1 LIMIT 1');
        $stmt->execute([':user_id' => $userId, ':session_id' => $sessionId]);
        return (bool) $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function createSession($userId, $sessionId, $justification = null) {
        $db = Database::connect();
        $stmt = $db->prepare('INSERT INTO user_sessions (user_id, session_id, justification, ip_address, user_agent) VALUES (:user_id, :session_id, :justification, :ip_address, :user_agent)');
        return $stmt->execute([
            ':user_id' => $userId,
            ':session_id' => $sessionId,
            ':justification' => $justification,
            ':ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
            ':user_agent' => substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 500)
        ]);
    }

    public function touchSession($sessionId) {
        $db = Database::connect();
        $stmt = $db->prepare('UPDATE user_sessions SET last_seen = CURRENT_TIMESTAMP WHERE session_id = :session_id AND active = 1');
        return $stmt->execute([':session_id' => $sessionId]);
    }

    public function deactivateSession($sessionId) {
        $db = Database::connect();
        $stmt = $db->prepare('UPDATE user_sessions SET active = 0, ended_at = CURRENT_TIMESTAMP WHERE session_id = :session_id AND active = 1');
        return $stmt->execute([':session_id' => $sessionId]);
    }
}
