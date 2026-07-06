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

            $_SESSION['user_id'] = $user['id_user'];
            $_SESSION['user'] = trim($user['name'] . ' ' . $user['surname']);
            $_SESSION['user_ci'] = $user['ci'];
            $_SESSION['user_role'] = $user['user_role'];

            header('Location: index.php?url=dashboard&status=login_success');
            exit();
        }

        $data = [
            'page_title' => 'Iniciar Sesión - Starlink Control',
            'hideLayout' => true,
            'activeTab' => 'login'
        ];

        $this->render('modules/auth', $data);
    }

    public function register() {
        if (isset($_SESSION['user_id'])) {
            header('Location: index.php?url=dashboard');
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
        session_unset();
        session_destroy();
        header('Location: index.php?url=login&status=logout_success');
        exit();
    }

    public function forgot_password() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
            header('Location: index.php?url=login&status=no_sec_questions');
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $a1 = isset($_POST['answer1']) ? trim($_POST['answer1']) : '';
            $a2 = isset($_POST['answer2']) ? trim($_POST['answer2']) : '';
            $a3 = isset($_POST['answer3']) ? trim($_POST['answer3']) : '';

            if ($a1 === '' || $a2 === '' || $a3 === '') {
                header('Location: index.php?url=auth/verify_questions&status=answers_empty');
                exit();
            }

            $match = (strcasecmp($a1, $secData['answer1']) === 0) &&
                     (strcasecmp($a2, $secData['answer2']) === 0) &&
                     (strcasecmp($a3, $secData['answer3']) === 0);

            if (!$match) {
                header('Location: index.php?url=auth/verify_questions&status=wrong_answers');
                exit();
            }

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
        if (empty($_SESSION['reset_user_id'])) {
            header('Location: index.php?url=login');
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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

            unset($_SESSION['reset_user_id']);

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

    public function manage_security() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php?url=login');
            exit();
        }

        $userId = $_SESSION['user_id'];
        $secData = $this->secQuestionModel->getByUserId($userId);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
            header('Location: index.php?url=dashboard&status=security_saved');
            exit();
        }

        $data = [
            'page_title' => 'Preguntas de Seguridad - Starlink Control',
            'activeTab' => 'security',
            'sec' => $secData ?: []
        ];

        $this->render('modules/manage_security', $data);
    }
}
