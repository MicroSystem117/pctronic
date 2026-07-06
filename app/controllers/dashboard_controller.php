<?php

class DashboardController extends Controller {
    
    public function index() {
        $db = Database::connect();

        $userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

        // Soporte de mes/año desde querystring para navegación
        $month = isset($_GET['month']) ? intval($_GET['month']) : intval(date('n'));
        $year  = isset($_GET['year']) ? intval($_GET['year']) : intval(date('Y'));

        try {
            // 1. Contar total de clientes registrados
            $stmtClients = $db->query("SELECT COUNT(*) AS total FROM client");
            $totalClients = $stmtClients->fetch(PDO::FETCH_ASSOC)['total'];

            // 2. Contar total de antenas Starlink mapeadas
            $stmtAntenas = $db->query("SELECT COUNT(*) AS total FROM antenas");
            $totalAntenas = $stmtAntenas->fetch(PDO::FETCH_ASSOC)['total'];

            // 3. Obtener antenas agrupadas por plan
            $stmtAntenasByPlan = $db->query(
                "SELECT p.plan AS nombre_plan, COUNT(a.id_starlink) AS total_antenas " .
                "FROM plan p LEFT JOIN antenas a ON a.plan = p.id_plan " .
                "GROUP BY p.id_plan, p.plan " .
                "ORDER BY total_antenas DESC, p.plan ASC"
            );
            $antenasByPlan = $stmtAntenasByPlan->fetchAll(PDO::FETCH_ASSOC);

            // 4. Contar total de cuentas administrativas registradas
            $stmtAccounts = $db->query("SELECT COUNT(*) AS total FROM accounts");
            $totalAccounts = $stmtAccounts->fetch(PDO::FETCH_ASSOC)['total'];

            // 5. Verificar si el usuario tiene preguntas de seguridad
            $hasSecQuestions = false;
            if ($userId) {
                $secModel = new SecQuestionModel();
                $hasSecQuestions = $secModel->hasSecurityQuestions($userId);
            }

        } catch (PDOException $e) {
            error_log("Error en DashboardController -> " . $e->getMessage());
            $totalClients = 0;
            $totalAntenas = 0;
            $totalAccounts = 0;
            $antenasByPlan = [];
            $hasSecQuestions = false;
        }

        // 5. Obtener días de pago por antena (para calendario)
        $payDays = [];
        try {
            $antenaModel = new AntenaModel();
            // solicitar por rol/ci ya filtrado; getAll ignora mes, ya que pagos son por día del mes
            $antenas = $antenaModel->getAll($this->getUserRole(), $this->getUserCi());
            $today = new DateTime();
            foreach ($antenas as $a) {
                if (isset($a['pay']) && $a['pay'] !== null && $a['pay'] !== '') {
                    $day = intval($a['pay']);
                    if ($day >= 1 && $day <= 31) {
                        // Determinar estado de pago: Pagado / Pendiente / Atrasado
                        $isPaid = false;
                        if (!empty($a['last_payment_date'])) {
                            $lp = DateTime::createFromFormat('Y-m-d', $a['last_payment_date']);
                            if ($lp && $lp->format('Y-m') === $today->format('Y-m')) {
                                $isPaid = true;
                            }
                        }

                        if ($isPaid) {
                            $a['payment_status'] = 'Pagado';
                        } else {
                            if (intval($today->format('j')) <= $day) {
                                $a['payment_status'] = 'Pendiente';
                            } else {
                                $a['payment_status'] = 'Atrasado';
                            }
                        }

                        if (!isset($payDays[$day])) $payDays[$day] = [];
                        $payDays[$day][] = $a;
                    }
                }
            }
        } catch (Exception $e) {
            error_log("Error building payDays in DashboardController -> " . $e->getMessage());
        }

        // Manejar exportación CSV si se solicita
        if (isset($_GET['action']) && $_GET['action'] === 'export_calendar') {
            $filename = sprintf('calendar-%04d-%02d.csv', $year, $month);
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            $out = fopen('php://output', 'w');
            fputcsv($out, ['day','serial','cliente','plan','kit','account']);
            foreach ($payDays as $day => $rows) {
                foreach ($rows as $r) {
                    fputcsv($out, [ $day, $r['serial'] ?? '', $r['cliente'] ?? '', $r['nombre_plan'] ?? '', $r['kit'] ?? '', $r['cuenta_starlink'] ?? '' ]);
                }
            }
            fclose($out);
            exit();
        }

        // Empaquetamos la data para la vista
        $data = [
            'page_title'      => 'Panel de Control - Starlink Control',
            'total_clients'   => $totalClients,
            'total_antenas'   => $totalAntenas,
            'total_accounts'  => $totalAccounts,
            'antenas_by_plan' => $antenasByPlan,
            'pay_days'        => $payDays,
            'calendar_month'  => date('n'),
            'calendar_year'   => date('Y'),
            'has_sec_questions' => isset($hasSecQuestions) ? $hasSecQuestions : false
        ];

        // Renderizamos la vista del inicio (puedes llamarla dashboard o home según tu enrutador)
        $this->render('modules/dashboard', $data);
    }
}