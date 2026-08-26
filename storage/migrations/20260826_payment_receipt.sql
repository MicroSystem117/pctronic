ALTER TABLE payments
    ADD COLUMN receipt_path VARCHAR(255) NULL AFTER status;