<?php

class AuthController extends Controller {
    private $userModel;
    private $secQuestionModel;

    public function __construct() {
        $this->userModel = new UserModel();
        $this->secQuestionModel = new SecQuestionModel();
    }

    public function login() {
        if (isset($_SESSION['user_id'])) {
            header('Location: index.php?url=dashboard');
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCsrfToken();
            if (isset($_POST['security_check']) && $_POST['security_check'] === '1') {
                $pendingUserId = isset($_SESSION['pending_second_session_user']) ? intval($_SESSION['pending_second_session_user']) : 0;
                $pendingAt = isset($_SESSION['pending_second_session_at']) ? intval($_SESSION['pending_second_session_at']) : 0;
                $answers = [
                    trim($_POST['answer1'] ?? ''),
                    trim($_POST['answer2'] ?? ''),
                    trim($_POST['answer3'] ?? '')
                ];

                if (!$pendingUserId || time() - $pendingAt > 300 || in_array('', $answers, true)) {
                    unset($_SESSION['pending_second_session_user'], $_SESSION['pending_second_session_at']);
                    header('Location: index.php?url=login&status=session_questions_invalid');
                    exit();
                }

                $user = $this->userModel->findById($pendingUserId);
                if (!$user) {
                    unset($_SESSION['pending_second_session_user'], $_SESSION['pending_second_session_at']);
                    header('Location: index.php?url=login&status=session_questions_invalid');
                    exit();
                }

                if (!$this->userModel->hasActiveSession($pendingUserId, session_id()) || !$this->secQuestionModel->hasSecurityQuestions($pendingUserId)) {
                    unset($_SESSION['pending_second_session_user'], $_SESSION['pending_second_session_at']);
                    $this->startUserSession($user);
                    header('Location: index.php?url=dashboard&status=login_success');
                    exit();
                }

                $securityData = $this->secQuestionModel->getByUserId($pendingUserId);
                $validAnswers = $securityData &&
                    $this->secQuestionModel->verifyAnswer($answers[0], $securityData['answer1']) &&
                    $this->secQuestionModel->verifyAnswer($answers[1], $securityData['answer2']) &&
                    $this->secQuestionModel->verifyAnswer($answers[2], $securityData['answer3']);

                if (!$validAnswers) {
                    header('Location: index.php?url=login&status=session_questions_wrong');
                    exit();
                }

                unset($_SESSION['pending_second_session_user'], $_SESSION['pending_second_session_at']);
                $this->startUserSession($user);
                header('Location: index.php?url=dashboard&status=login_success');
                exit();
            }

            $ci = isset($_POST['ci']) ? trim($_POST['ci']) : '';
            $password = isset($_POST['password']) ? $_POST['password'] : '';

            if ($ci === '' || $password === '') {
                header('Location: index.php?url=login&status=login_empty');
                exit();
            }

            $user = $this->userModel->login($ci, $password);
            if (!$user) {
                header('Location: index.php?url=login&status=login_failed');
                exit();
            }

            // Verificación de Autenticación en Dos Pasos (2FA)
            $twoFa = $this->userModel->get2FaInfo($user['id_user']);
            if (!empty($twoFa['totp_enabled']) && !empty($twoFa['totp_secret'])) {
                $_SESSION['2fa_pending_user'] = $user;
                header('Location: index.php?url=auth/verify_2fa');
                exit();
            }

            $sessionId = session_id();
            if ($this->userModel->hasActiveSession($user['id_user'], $sessionId)) {
                if ($this->secQuestionModel->hasSecurityQuestions($user['id_user'])) {
                    $_SESSION['pending_second_session_user'] = $user['id_user'];
                    $_SESSION['pending_second_session_at'] = time();
                    header('Location: index.php?url=login&status=session_questions');
                    exit();
                }
            }

            $this->startUserSession($user);

            header('Location: index.php?url=dashboard&status=login_success');
            exit();
        }

        $data = [
            'page_title' => 'Iniciar Sesión - Starlink Control',
            'hideLayout' => true,
            'activeTab' => 'login',
            'questions' => isset($_SESSION['pending_second_session_user']) && (isset($_GET['status']) && $_GET['status'] === 'session_questions')
                ? $this->secQuestionModel->getByUserId($_SESSION['pending_second_session_user'])
                : []
        ];

        $this->render('modules/auth', $data);
    }

    private function startUserSession($user) {
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['id_user'];
        $_SESSION['user'] = trim($user['name'] . ' ' . $user['surname']);
        $_SESSION['user_ci'] = $user['ci'];
        $_SESSION['user_role'] = $user['user_role'] ?? 'Expectador';
        $this->userModel->createSession($user['id_user'], session_id());
        LogModel::log('LOGIN', 'Autenticación', 'Inicio de sesión exitoso', $user['id_user'], trim($user['name'] . ' ' . $user['surname']), $_SESSION['user_role']);
    }

    public function register() {
        if (isset($_SESSION['user_id'])) {
            header('Location: index.php?url=dashboard');
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCsrfToken();
            $name = isset($_POST['name']) ? trim($_POST['name']) : '';
            $surname = isset($_POST['surname']) ? trim($_POST['surname']) : '';
            $ci = isset($_POST['ci']) ? trim($_POST['ci']) : '';
            $birth = isset($_POST['birth']) ? $_POST['birth'] : null;
            $password = isset($_POST['password']) ? $_POST['password'] : '';
            $confirm = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';

            if ($name === '' || $surname === '' || $ci === '' || $password === '' || $confirm === '') {
                header('Location: index.php?url=register&status=register_empty');
                exit();
            }

            if ($password !== $confirm) {
                header('Location: index.php?url=register&status=password_mismatch');
                exit();
            }

            if ($this->userModel->findByCi($ci)) {
                header('Location: index.php?url=register&status=ci_exists');
                exit();
            }

            $created = $this->userModel->register($name, $surname, $ci, $birth, $password);
            if (!$created) {
                header('Location: index.php?url=register&status=register_error');
                exit();
            }

            LogModel::log('REGISTRO', 'Autenticación', "Nuevo usuario registrado: $name $surname (C.I. $ci)");

            header('Location: index.php?url=login&status=register_success');
            exit();
        }

        $data = [
            'page_title' => 'Registro - Starlink Control',
            'hideLayout' => true,
            'activeTab' => 'register'
        ];

        $this->render('modules/auth', $data);
    }

    public function logout() {
        if (isset($_SESSION['user_id'])) {
            LogModel::log('LOGOUT', 'Autenticación', 'Cierre de sesión');
            $this->userModel->deactivateSession(session_id());
        }
        session_unset();
        session_destroy();
        header('Location: index.php?url=login&status=logout_success');
        exit();
    }

    public function forgot_password() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCsrfToken();
            $ci = isset($_POST['ci']) ? trim($_POST['ci']) : '';

            if ($ci === '') {
                header('Location: index.php?url=auth/forgot_password&status=forgot_empty');
                exit();
            }

            $user = $this->userModel->findByCi($ci);
            if (!$user) {
                header('Location: index.php?url=auth/forgot_password&status=forgot_user_not_found');
                exit();
            }

            $hasQuestions = $this->secQuestionModel->hasSecurityQuestions($user['id_user']);
            if (!$hasQuestions) {
                header('Location: index.php?url=login&status=no_sec_questions');
                exit();
            }

            $_SESSION['reset_user_id'] = $user['id_user'];
            $_SESSION['reset_user_verified'] = false;
            header('Location: index.php?url=auth/verify_questions');
            exit();
        }

        $data = [
            'page_title' => 'Recuperar Contraseña - Starlink Control',
            'hideLayout' => true,
            'activeTab' => 'forgot'
        ];

        $this->render('modules/forgot_password', $data);
    }

    public function verify_questions() {
        if (empty($_SESSION['reset_user_id'])) {
            header('Location: index.php?url=login');
            exit();
        }

        $userId = $_SESSION['reset_user_id'];
        $secData = $this->secQuestionModel->getByUserId($userId);

        if (!$secData || empty($secData['question1']) || empty($secData['question2']) || empty($secData['question3'])) {
            unset($_SESSION['reset_user_id'], $_SESSION['reset_user_verified']);
            header('Location: index.php?url=login&status=no_sec_questions');
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCsrfToken();
            $a1 = isset($_POST['answer1']) ? trim($_POST['answer1']) : '';
            $a2 = isset($_POST['answer2']) ? trim($_POST['answer2']) : '';
            $a3 = isset($_POST['answer3']) ? trim($_POST['answer3']) : '';

            if ($a1 === '' || $a2 === '' || $a3 === '') {
                header('Location: index.php?url=auth/verify_questions&status=answers_empty');
                exit();
            }

            $match = $this->secQuestionModel->verifyAnswer($a1, $secData['answer1']) &&
                     $this->secQuestionModel->verifyAnswer($a2, $secData['answer2']) &&
                     $this->secQuestionModel->verifyAnswer($a3, $secData['answer3']);

            if (!$match) {
                header('Location: index.php?url=auth/verify_questions&status=wrong_answers');
                exit();
            }

            $_SESSION['reset_user_verified'] = true;
            header('Location: index.php?url=auth/reset_password');
            exit();
        }

        $data = [
            'page_title' => 'Verificar Preguntas de Seguridad - Starlink Control',
            'hideLayout' => true,
            'activeTab' => 'verify',
            'questions' => [
                ['key' => 'answer1', 'label' => $secData['question1']],
                ['key' => 'answer2', 'label' => $secData['question2']],
                ['key' => 'answer3', 'label' => $secData['question3']]
            ]
        ];

        $this->render('modules/verify_questions', $data);
    }

    public function reset_password() {
        if (empty($_SESSION['reset_user_id']) || empty($_SESSION['reset_user_verified'])) {
            unset($_SESSION['reset_user_id'], $_SESSION['reset_user_verified']);
            header('Location: index.php?url=login');
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCsrfToken();
            $password = isset($_POST['password']) ? $_POST['password'] : '';
            $confirm = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';

            if ($password === '' || $confirm === '') {
                header('Location: index.php?url=auth/reset_password&status=reset_empty');
                exit();
            }

            if ($password !== $confirm) {
                header('Location: index.php?url=auth/reset_password&status=password_mismatch');
                exit();
            }

            $userId = $_SESSION['reset_user_id'];
            $db = Database::connect();
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $db->prepare('UPDATE `user` SET pass = :pass WHERE id_user = :id');
            $stmt->execute([':pass' => $hash, ':id' => $userId]);

            LogModel::log('PASSWORD_RESET', 'Autenticación', 'Restableció su contraseña mediante preguntas de seguridad', $userId);

            unset($_SESSION['reset_user_id'], $_SESSION['reset_user_verified']);

            header('Location: index.php?url=login&status=password_reset_success');
            exit();
        }

        $data = [
            'page_title' => 'Nueva Contraseña - Starlink Control',
            'hideLayout' => true,
            'activeTab' => 'reset'
        ];

        $this->render('modules/reset_password', $data);
    }

    public function verify_2fa() {
        if (!isset($_SESSION['2fa_pending_user'])) {
            header('Location: index.php?url=login');
            exit();
        }

        $user = $_SESSION['2fa_pending_user'];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCsrfToken();
            $code = isset($_POST['code']) ? trim($_POST['code']) : '';
            $twoFa = $this->userModel->get2FaInfo($user['id_user']);

            if (!empty($twoFa['totp_secret']) && TOTP::verifyCode($twoFa['totp_secret'], $code)) {
                unset($_SESSION['2fa_pending_user']);
                $this->startUserSession($user);
                header('Location: index.php?url=dashboard&status=login_success');
                exit();
            }

            header('Location: index.php?url=auth/verify_2fa&status=2fa_invalid');
            exit();
        }

        $data = [
            'page_title' => 'Verificación 2FA - Starlink Control',
            'hideLayout' => true,
            'user' => $user
        ];

        $this->render('modules/verify_2fa', $data);
    }

    public function manage_security() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php?url=login');
            exit();
        }

        $userId = $_SESSION['user_id'];
        $secData = $this->secQuestionModel->getByUserId($userId);
        $twoFa = $this->userModel->get2FaInfo($userId);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCsrfToken();
            $action = isset($_POST['action']) ? trim($_POST['action']) : 'save_questions';

            if ($action === 'enable_2fa') {
                $code = isset($_POST['code']) ? trim($_POST['code']) : '';
                $secret = $_SESSION['2fa_setup_secret'] ?? '';

                if (empty($secret) || !TOTP::verifyCode($secret, $code)) {
                    header('Location: index.php?url=auth/manage_security&status=2fa_setup_failed');
                    exit();
                }

                $this->userModel->set2FaSecret($userId, $secret);
                $this->userModel->enable2Fa($userId);
                unset($_SESSION['2fa_setup_secret']);
                LogModel::log('SECURITY', 'Autenticación', 'Activó autenticación en dos pasos (2FA)', $userId, $_SESSION['user'], $_SESSION['user_role'] ?? 'Usuario');
                header('Location: index.php?url=auth/manage_security&status=2fa_enabled');
                exit();
            }

            if ($action === 'disable_2fa') {
                $password = isset($_POST['password']) ? $_POST['password'] : '';
                $currentUser = $this->userModel->findById($userId);

                if (!$currentUser || !password_verify($password, $currentUser['pass'])) {
                    header('Location: index.php?url=auth/manage_security&status=2fa_disable_wrong_pass');
                    exit();
                }

                $this->userModel->disable2Fa($userId);
                LogModel::log('SECURITY', 'Autenticación', 'Desactivó autenticación en dos pasos (2FA)', $userId, $_SESSION['user'], $_SESSION['user_role'] ?? 'Usuario');
                header('Location: index.php?url=auth/manage_security&status=2fa_disabled');
                exit();
            }

            $q1 = isset($_POST['question1']) ? trim($_POST['question1']) : '';
            $a1 = isset($_POST['answer1']) ? trim($_POST['answer1']) : '';
            $q2 = isset($_POST['question2']) ? trim($_POST['question2']) : '';
            $a2 = isset($_POST['answer2']) ? trim($_POST['answer2']) : '';
            $q3 = isset($_POST['question3']) ? trim($_POST['question3']) : '';
            $a3 = isset($_POST['answer3']) ? trim($_POST['answer3']) : '';

            if ($q1 === '' || $a1 === '' || $q2 === '' || $a2 === '' || $q3 === '' || $a3 === '') {
                header('Location: index.php?url=auth/manage_security&status=security_empty');
                exit();
            }

            $this->secQuestionModel->save($userId, $q1, $a1, $q2, $a2, $q3, $a3);
            header('Location: index.php?url=auth/manage_security&status=security_saved');
            exit();
        }

        // Si 2FA no está activo, generar un secreto para la vista de configuración
        if (empty($twoFa['totp_enabled'])) {
            if (empty($_SESSION['2fa_setup_secret'])) {
                $_SESSION['2fa_setup_secret'] = TOTP::generateSecret();
            }
            $setupSecret = $_SESSION['2fa_setup_secret'];
            $otpAuthUrl = TOTP::getOtpAuthUrl($_SESSION['user_ci'] ?? 'Usuario', $setupSecret);
            $qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=' . rawurlencode($otpAuthUrl);
        } else {
            $setupSecret = null;
            $qrUrl = null;
        }

        $data = [
            'page_title' => 'Seguridad de la Cuenta - Starlink Control',
            'activeTab' => 'security',
            'sec' => $secData ?: [],
            'two_fa' => $twoFa,
            'setup_secret' => $setupSecret,
            'qr_url' => $qrUrl
        ];

        $this->render('modules/manage_security', $data);
    }
}
