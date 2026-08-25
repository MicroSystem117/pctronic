ALTER TABLE payments
    ADD COLUMN status ENUM('Pendiente', 'Aprobado', 'Rechazado') NOT NULL DEFAULT 'Pendiente' AFTER created_at,
    ADD COLUMN submitted_by INT NULL AFTER status,
    ADD COLUMN reviewed_by INT NULL AFTER submitted_by,
    ADD COLUMN reviewed_at DATETIME NULL AFTER reviewed_by;

UPDATE payments SET status = 'Aprobado' WHERE status = 'Pendiente';

ALTER TABLE payments
    ADD INDEX idx_payment_status (status),
    ADD CONSTRAINT fk_payments_submitted_by FOREIGN KEY (submitted_by) REFERENCES `user` (id_user) ON DELETE SET NULL,
    ADD CONSTRAINT fk_payments_reviewed_by FOREIGN KEY (reviewed_by) REFERENCES `user` (id_user) ON DELETE SET NULL;