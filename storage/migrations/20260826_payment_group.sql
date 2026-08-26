CREATE TABLE payment_antennas (
    payment_id INT NOT NULL,
    antenna_id INT NOT NULL,
    PRIMARY KEY (payment_id, antenna_id),
    CONSTRAINT fk_payment_antennas_payment FOREIGN KEY (payment_id) REFERENCES payments (id_payment) ON DELETE CASCADE,
    CONSTRAINT fk_payment_antennas_antenna FOREIGN KEY (antenna_id) REFERENCES antenas (id_starlink) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO payment_antennas (payment_id, antenna_id)
SELECT id_payment, antenna_id FROM payments;