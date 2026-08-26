CREATE TABLE user_sessions (
    id_session INT NOT NULL AUTO_INCREMENT,
    user_id INT NOT NULL,
    session_id VARCHAR(128) NOT NULL,
    justification VARCHAR(500) NULL,
    ip_address VARCHAR(45) NULL,
    user_agent VARCHAR(500) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    last_seen DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    ended_at DATETIME NULL,
    active TINYINT(1) NOT NULL DEFAULT 1,
    PRIMARY KEY (id_session),
    UNIQUE KEY uq_user_session (session_id),
    KEY idx_user_active_session (user_id, active),
    CONSTRAINT fk_user_sessions_user FOREIGN KEY (user_id) REFERENCES `user` (id_user) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;