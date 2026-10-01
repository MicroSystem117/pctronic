<?php

class AntenaModel {
    private $db;

    public function __construct() {
        // Conexión nativa a tu base de datos
        $this->db = Database::connect();
        $this->ensureSchema();
    }

    /**
     * Asegurar que las columnas para exoneración de deuda existan en la tabla antenas
     */
    private function ensureSchema() {
        static $checked = false;
        if ($checked) return;
        $checked = true;

        try {
            $columns = $this->db->query("SHOW COLUMNS FROM antenas")->fetchAll(PDO::FETCH_COLUMN);
            if (!in_array('debt_exempt_until', $columns, true)) {
                $this->db->exec("ALTER TABLE antenas ADD COLUMN debt_exempt_until DATE NULL DEFAULT NULL");
            }
            if (!in_array('debt_exempt_note', $columns, true)) {
                $this->db->exec("ALTER TABLE antenas ADD COLUMN debt_exempt_note VARCHAR(255) NULL DEFAULT NULL");
            }
            if (!in_array('debt_exempt_at', $columns, true)) {
                $this->db->exec("ALTER TABLE antenas ADD COLUMN debt_exempt_at DATETIME NULL DEFAULT NULL");
            }
        } catch (Throwable $e) {
            error_log("AntenaModel::ensureSchema warning: " . $e->getMessage());
        }
    }

    /**
     * Calcula de forma precisa los meses devengados, pagados, atrasados y estado de cuenta de una antena
     */
    public static function calculateDebtInfo($antena, $payments = [], $today = null) {
        if (!$today) {
            $today = new DateTime('today');
        } elseif (is_string($today)) {
            $today = new DateTime($today);
        }

        $planPrice = !empty($antena['plan_price']) ? floatval($antena['plan_price']) : 0.0;
        $isExempt = !empty($antena['debt_exempt_until']);
        $effectiveStartStr = $isExempt ? $antena['debt_exempt_until'] : (!empty($antena['date']) ? $antena['date'] : $today->format('Y-m-d'));
        $effectiveStartDate = new DateTime($effectiveStartStr);

        // Día de cobro (1 - 31)
        if (!empty($antena['pay']) && intval($antena['pay']) >= 1 && intval($antena['pay']) <= 31) {
            $dueDay = intval($antena['pay']);
        } else {
            $dueDay = intval($effectiveStartDate->format('j'));
            if ($dueDay < 1) $dueDay = 1;
            if ($dueDay > 31) $dueDay = 31;
        }

        // Iterar mes a mes desde el mes de inicio efectivo hasta el mes de hoy
        $curr = new DateTime($effectiveStartDate->format('Y-m-01'));
        $end = new DateTime($today->format('Y-m-01'));
        $maturedCycles = 0;
        $currentMonthMatured = false;
        $currentMonthDueDate = null;

        while ($curr <= $end) {
            $year = (int)$curr->format('Y');
            $month = (int)$curr->format('n');
            $maxDays = (int)$curr->format('t');
            $actualDay = min($dueDay, $maxDays);
            $cycleDueDate = new DateTime(sprintf('%04d-%02d-%02d', $year, $month, $actualDay));

            // Si el ciclo cae antes de la fecha de inicio/exoneración, ignorarlo
            if ($cycleDueDate < $effectiveStartDate) {
                $curr->modify('+1 month');
                continue;
            }

            // Si es el mes en curso, guardar la fecha de vencimiento
            if ($curr->format('Y-m') === $today->format('Y-m')) {
                $currentMonthDueDate = $cycleDueDate;
            }

            // Si ya venció a la fecha de hoy
            if ($cycleDueDate <= $today) {
                $maturedCycles++;
                if ($curr->format('Y-m') === $today->format('Y-m')) {
                    $currentMonthMatured = true;
                }
            }

            $curr->modify('+1 month');
        }

        if (!$currentMonthDueDate) {
            $year = (int)$today->format('Y');
            $month = (int)$today->format('n');
            $maxDays = (int)$today->format('t');
            $actualDay = min($dueDay, $maxDays);
            $currentMonthDueDate = new DateTime(sprintf('%04d-%02d-%02d', $year, $month, $actualDay));
        }

        // Filtrar pagos aprobados posteriores o iguales a la fecha de inicio efectivo
        $applicablePayments = [];
        $lastApprovedDate = null;
        $lastPaymentAmount = null;
        $lastPaymentCurrency = null;

        foreach ($payments as $p) {
            $pDate = $p['payment_date'] ?? null;
            if (!$pDate) continue;

            if (!$lastApprovedDate || $pDate > $lastApprovedDate) {
                $lastApprovedDate = $pDate;
                $lastPaymentAmount = $p['amount'] ?? null;
                $lastPaymentCurrency = $p['currency'] ?? 'USD';
            }

            if ($isExempt && $pDate < $effectiveStartStr) {
                continue;
            }

            $applicablePayments[] = $p;
        }

        $paidCount = count($applicablePayments);
        $overdueMonths = max(0, $maturedCycles - $paidCount);
        $overdueAmount = $overdueMonths * $planPrice;

        if ($overdueMonths > 0) {
            $statusLabel = 'Atrasado';
            $statusBadgeClass = 'bg-danger text-white';
            $statusBadgeText = $overdueMonths . ($overdueMonths === 1 ? ' mes atrasado' : ' meses atrasados');
        } elseif ($paidCount > $maturedCycles) {
            $statusLabel = 'Pagado';
            $statusBadgeClass = 'bg-success text-dark';
            $statusBadgeText = 'Al día / Pagado';
        } else {
            if ($currentMonthMatured) {
                $statusLabel = 'Pagado';
                $statusBadgeClass = 'bg-success text-dark';
                $statusBadgeText = 'Al día';
            } else {
                $statusLabel = 'Pendiente';
                $statusBadgeClass = 'bg-warning text-dark';
                $statusBadgeText = 'Pendiente';
            }
        }

        return [
            'is_exempt' => $isExempt,
            'debt_exempt_until' => $antena['debt_exempt_until'] ?? null,
            'debt_exempt_note' => $antena['debt_exempt_note'] ?? null,
            'debt_exempt_at' => $antena['debt_exempt_at'] ?? null,
            'effective_start_date' => $effectiveStartStr,
            'due_day' => $dueDay,
            'matured_cycles' => $maturedCycles,
            'paid_cycles' => $paidCount,
            'overdue_months' => $overdueMonths,
            'overdue_amount' => $overdueAmount,
            'status_label' => $statusLabel,
            'status_badge_class' => $statusBadgeClass,
            'status_badge_text' => $statusBadgeText,
            'current_month_matured' => $currentMonthMatured,
            'current_month_due_date' => $currentMonthDueDate ? $currentMonthDueDate->format('Y-m-d') : null,
            'last_payment_date' => $lastApprovedDate ?: ($antena['last_payment_date'] ?? null),
            'last_amount' => $lastPaymentAmount ?: ($antena['last_amount'] ?? null),
            'last_currency' => $lastPaymentCurrency ?: ($antena['last_currency'] ?? 'USD')
        ];
    }

    /**
     * Obtener todas las antenas con la información cruzada de clientes, planes, cuentas y cálculo de deuda
     */
    public function getAll($userRole = 'Administrador', $ci = null) {
        try {
            $sql = "SELECT 
                        a.id_starlink, 
                        a.serial, 
                        a.nickname,
                        a.kit, 
                        a.date,
                        a.pay,
                        a.debt_exempt_until,
                        a.debt_exempt_note,
                        a.debt_exempt_at,
                        a.client AS client_id,
                        a.account_id AS account_id,
                        a.plan AS plan_id,
                        a.country AS country_id,
                        CONCAT(c.name, ' ', c.surname) AS cliente,
                        c.phone AS client_phone,
                        p.plan AS nombre_plan,
                        p.price AS plan_price,
                        co.country AS pais,
                        CONCAT(acc.owner, ' • ', acc.acc) AS cuenta_starlink
                    FROM antenas a 
                    LEFT JOIN client c ON a.client = c.id_client
                    INNER JOIN plan p ON a.plan = p.id_plan
                    INNER JOIN country co ON a.country = co.id_country
                    LEFT JOIN accounts acc ON a.account_id = acc.id_accounts";

            $params = [];
            if ($userRole === 'Expectador' && $ci !== null) {
                $sql .= " WHERE c.ci = :ci";
                $params[':ci'] = $ci;
            }

            $sql .= " ORDER BY a.id_starlink DESC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $antenas = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Obtener todos los pagos aprobados para cruzarlos con las antenas
            $paymentsSql = "SELECT antenna_id, payment_date, amount, currency, status 
                            FROM (
                                SELECT pa.antenna_id, p.payment_date, p.amount, p.currency, p.status
                                FROM payments p
                                INNER JOIN payment_antennas pa ON p.id_payment = pa.payment_id
                                WHERE p.status = 'Aprobado'
                                UNION ALL
                                SELECT p.antenna_id, p.payment_date, p.amount, p.currency, p.status
                                FROM payments p
                                LEFT JOIN payment_antennas pa ON p.id_payment = pa.payment_id
                                WHERE p.status = 'Aprobado' AND pa.payment_id IS NULL AND p.antenna_id IS NOT NULL
                            ) ap
                            ORDER BY payment_date ASC";
            $paymentsStmt = $this->db->query($paymentsSql);
            $paymentsRows = $paymentsStmt ? $paymentsStmt->fetchAll(PDO::FETCH_ASSOC) : [];

            $paymentsByAntenna = [];
            foreach ($paymentsRows as $pRow) {
                $antId = (int)$pRow['antenna_id'];
                if (!isset($paymentsByAntenna[$antId])) {
                    $paymentsByAntenna[$antId] = [];
                }
                $paymentsByAntenna[$antId][] = $pRow;
            }

            // Calcular las estadísticas de deuda y estado para cada antena
            foreach ($antenas as &$antena) {
                $antId = (int)$antena['id_starlink'];
                $antPayments = $paymentsByAntenna[$antId] ?? [];
                $debtStats = self::calculateDebtInfo($antena, $antPayments);
                $antena = array_merge($antena, $debtStats);
            }
            unset($antena);

            return $antenas;
        } catch (PDOException $e) {
            error_log("Error en AntenaModel::getAll -> " . $e->getMessage());
            return [];
        }
    }

    /**
     * Insertar una nueva antena en la base de datos
     */
    public function register($client_id, $account_id, $serial, $nickname, $kit, $date, $pay, $plan_id, $country_id) {
        try {
            $sql = "INSERT INTO antenas (client, account_id, serial, nickname, kit, date, pay, plan, country) 
                    VALUES (:client, :account_id, :serial, :nickname, :kit, :date, :pay, :plan, :country)";
            
            $stmt = $this->db->prepare($sql);

            // Vinculamos de forma segura con los tipos de datos correctos de tu SQL
            $stmt->bindParam(':client', $client_id, PDO::PARAM_INT);
            // Si no se selecciona cuenta, pasamos NULL explícito
            if (empty($account_id)) {
                $stmt->bindValue(':account_id', null, PDO::PARAM_NULL);
            } else {
                $stmt->bindParam(':account_id', $account_id, PDO::PARAM_INT);
            }
            $stmt->bindParam(':serial', $serial, PDO::PARAM_STR);
            $stmt->bindParam(':nickname', $nickname, PDO::PARAM_STR);
            $stmt->bindParam(':kit', $kit, PDO::PARAM_STR);
            $stmt->bindParam(':date', $date, PDO::PARAM_STR);
            if ($pay === null || $pay === '' ) {
                $stmt->bindValue(':pay', null, PDO::PARAM_NULL);
            } else {
                $payInt = intval($pay);
                $stmt->bindParam(':pay', $payInt, PDO::PARAM_INT);
            }
            $stmt->bindParam(':plan', $plan_id, PDO::PARAM_INT);
            $stmt->bindParam(':country', $country_id, PDO::PARAM_INT);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error en AntenaModel::register -> " . $e->getMessage());
            return false;
        }
    }

    /**
     * Actualizar una antena existente
     */
    public function update($id, $client_id, $account_id, $serial, $nickname, $kit, $date, $pay, $plan_id, $country_id) {
        try {
            $sql = "UPDATE antenas SET client = :client, account_id = :account_id, serial = :serial, nickname = :nickname, kit = :kit, date = :date, pay = :pay, plan = :plan, country = :country WHERE id_starlink = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->bindParam(':client', $client_id, PDO::PARAM_INT);
            if (empty($account_id)) {
                $stmt->bindValue(':account_id', null, PDO::PARAM_NULL);
            } else {
                $stmt->bindParam(':account_id', $account_id, PDO::PARAM_INT);
            }
            $stmt->bindParam(':serial', $serial, PDO::PARAM_STR);
            $stmt->bindParam(':nickname', $nickname, PDO::PARAM_STR);
            $stmt->bindParam(':kit', $kit, PDO::PARAM_STR);
            $stmt->bindParam(':date', $date, PDO::PARAM_STR);
            if ($pay === null || $pay === '' ) {
                $stmt->bindValue(':pay', null, PDO::PARAM_NULL);
            } else {
                $payInt = intval($pay);
                $stmt->bindParam(':pay', $payInt, PDO::PARAM_INT);
            }
            $stmt->bindParam(':plan', $plan_id, PDO::PARAM_INT);
            $stmt->bindParam(':country', $country_id, PDO::PARAM_INT);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error en AntenaModel::update -> " . $e->getMessage());
            return false;
        }
    }

    /**
     * Eliminar una antena por ID
     */
    public function delete($id) {
        try {
            $sql = "DELETE FROM antenas WHERE id_starlink = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error en AntenaModel::delete -> " . $e->getMessage());
            return false;
        }
    }

    /**
     * Establecer exoneración de deuda pasada para una antena
     */
    public function setDebtExemption($id, $untilDate, $note = null) {
        try {
            $sql = "UPDATE antenas 
                    SET debt_exempt_until = :exempt_until, 
                        debt_exempt_note = :note, 
                        debt_exempt_at = NOW() 
                    WHERE id_starlink = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->bindParam(':exempt_until', $untilDate, PDO::PARAM_STR);
            if (empty($note)) {
                $stmt->bindValue(':note', null, PDO::PARAM_NULL);
            } else {
                $stmt->bindParam(':note', $note, PDO::PARAM_STR);
            }
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error en AntenaModel::setDebtExemption -> " . $e->getMessage());
            return false;
        }
    }

    /**
     * Quitar exoneración de deuda previa para una antena (restablece al cálculo original)
     */
    public function clearDebtExemption($id) {
        try {
            $sql = "UPDATE antenas 
                    SET debt_exempt_until = NULL, 
                        debt_exempt_note = NULL, 
                        debt_exempt_at = NULL 
                    WHERE id_starlink = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error en AntenaModel::clearDebtExemption -> " . $e->getMessage());
            return false;
        }
    }
}