<?php

class DashboardController extends Controller {
    
    public function index() {
        $userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
        $isExpectador = $this->isExpectador();
        $totalClients = 0;
        $totalAntenas = 0;
        $totalAccounts = 0;
        $antenasByPlan = [];
        $activeAntennas = 0;

        // Soporte de mes/año desde querystring para navegación
        $month = isset($_GET['month']) ? intval($_GET['month']) : intval(date('n'));
        $year  = isset($_GET['year']) ? intval($_GET['year']) : intval(date('Y'));

        try {
            $antenaModel = new AntenaModel();
            $clientModel = new ClientModel();
            $accountModel = new AccountModel();
            $paymentModel = new PaymentModel();

            $mrr = 0.0;
            $debtOverview = ['total_activas' => 0, 'al_dia' => 0, 'con_deuda' => 0, 'total_deuda' => 0.0];
            $collectedThisMonth = 0.0;
            $collectionTrend = [];
            $paymentStatusSummary = [];
            $upcomingExpirations = [];
            $clientSummary = null;

            if (!$isExpectador) {
                // 1. Contar total de clientes registrados
                $totalClients = $clientModel->count();

                // 2. Contar total de antenas Starlink mapeadas
                $totalAntenas = $antenaModel->count();

                // 3. Obtener antenas agrupadas por plan
                $antenasByPlan = $antenaModel->getCountsByPlan();

                $activePlanNames = ['Itinerante 100GB', 'Residencial', 'Itinerante Ilimitado'];
                $activeAntennas = 0;
                foreach ($antenasByPlan as $plan) {
                    if (in_array($plan['nombre_plan'], $activePlanNames, true)) {
                        $activeAntennas += intval($plan['total_antenas']);
                    }
                }

                // 4. Contar total de cuentas administrativas registradas
                $totalAccounts = $accountModel->count();

                // 5. Métricas financieras avanzadas (MRR, Morosidad, Recaudación)
                $mrr = $antenaModel->getMRR();
                $debtOverview = $antenaModel->getDebtOverview();
                $collectedThisMonth = $paymentModel->getMonthlyApprovedSum($year, $month);
                $collectionTrend = $paymentModel->getCollectionTrend(6);
                $paymentStatusSummary = $paymentModel->getPaymentStatusCounts($month, $year);
                $upcomingExpirations = $paymentModel->getUpcomingExpirations(7);
            } else {
                // Portal de autogestión de cliente
                $clientSummary = $paymentModel->getClientSummary($this->getUserCi());
            }

            // 6. Verificar si el usuario tiene preguntas de seguridad
            $hasSecQuestions = false;
            if ($userId) {
                $secModel = new SecQuestionModel();
                $hasSecQuestions = $secModel->hasSecurityQuestions($userId);
            }

        } catch (Throwable $e) {
            error_log("Error en DashboardController -> " . $e->getMessage());
            $totalClients = 0;
            $totalAntenas = 0;
            $totalAccounts = 0;
            $antenasByPlan = [];
            $hasSecQuestions = false;
            $mrr = 0.0;
            $debtOverview = ['total_activas' => 0, 'al_dia' => 0, 'con_deuda' => 0, 'total_deuda' => 0.0];
            $collectedThisMonth = 0.0;
            $collectionTrend = [];
            $paymentStatusSummary = [];
            $upcomingExpirations = [];
            $clientSummary = null;
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
            'active_antennas' => isset($activeAntennas) ? $activeAntennas : 0,
            'pay_days'        => $payDays,
            'calendar_month'  => $month,
            'calendar_year'   => $year,
            'has_sec_questions' => isset($hasSecQuestions) ? $hasSecQuestions : false,
            'isExpectador'    => $isExpectador,
            'mrr'             => $mrr ?? 0.0,
            'debt_overview'   => $debtOverview ?? [],
            'collected_this_month' => $collectedThisMonth ?? 0.0,
            'collection_trend'=> $collectionTrend ?? [],
            'status_summary'  => $paymentStatusSummary ?? [],
            'upcoming_expirations' => $upcomingExpirations ?? [],
            'client_summary'  => $clientSummary ?? null
        ];

        // Renderizamos la vista del inicio (puedes llamarla dashboard o home según tu enrutador)
        $this->render('modules/dashboard', $data);
    }
}