<?php

class UsersController extends Controller {
    private $userModel;

    public function __construct() {
        $this->userModel = new UserModel();
    }

    public function index() {
        if (!$this->isAdmin() && !$this->isModerator()) {
            header('Location: index.php?url=dashboard&status=access_denied');
            exit();
        }

        if (isset($_GET['action']) && $_GET['action'] === 'delete') {
            if (!$this->isAdmin()) {
                header('Location: index.php?url=users&status=access_denied');
                exit();
            }

            $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
            if ($id > 0 && $id !== intval($_SESSION['user_id'])) {
                $deleted = $this->userModel->deleteUser($id);
                $status = $deleted ? 'user_deleted' : 'error';
            } else {
                $status = 'user_delete_denied';
            }
            header('Location: index.php?url=users&status=' . $status);
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!$this->isAdmin() && !$this->isModerator()) {
                header('Location: index.php?url=users&status=access_denied');
                exit();
            }

            $id = isset($_POST['id_user']) ? intval($_POST['id_user']) : 0;
            if ($this->isModerator() && $id !== intval($_SESSION['user_id'])) {
                header('Location: index.php?url=users&status=access_denied');
                exit();
            }

            $name = isset($_POST['name']) ? trim($_POST['name']) : '';
            $surname = isset($_POST['surname']) ? trim($_POST['surname']) : '';
            $ci = isset($_POST['ci']) ? trim($_POST['ci']) : '';
            $birth = isset($_POST['birth']) && $_POST['birth'] !== '' ? $_POST['birth'] : null;
            $password = isset($_POST['password']) ? $_POST['password'] : '';
            $idLevel = isset($_POST['id_level']) ? intval($_POST['id_level']) : 0;

            if ($this->isModerator()) {
                $currentUser = $this->userModel->findById($id);
                $idLevel = $currentUser ? intval($currentUser['id_level']) : 0;
            }

            if ($name === '' || $surname === '' || $ci === '' || $idLevel <= 0 || ($id === 0 && $password === '')) {
                header('Location: index.php?url=users&status=user_empty');
                exit();
            }

            $existing = $this->userModel->findByCi($ci);
            if ($existing && intval($existing['id_user']) !== $id) {
                header('Location: index.php?url=users&status=user_ci_exists');
                exit();
            }

            if ($id > 0) {
                $saved = $this->userModel->update($id, $name, $surname, $ci, $birth, $idLevel, $password);
                $status = $saved ? 'user_updated' : 'error';
            } else {
                $saved = $this->userModel->create($name, $surname, $ci, $birth, $password, $idLevel);
                $status = $saved ? 'user_created' : 'error';
            }

            header('Location: index.php?url=users&status=' . $status);
            exit();
        }

        $users = $this->isModerator()
            ? [$this->userModel->findById(intval($_SESSION['user_id']))]
            : $this->userModel->getAll();
        $users = array_values(array_filter($users));

        $data = [
            'page_title' => 'Gestión de Usuarios - Starlink Control',
            'users' => $users,
            'levels' => $this->userModel->getLevels(),
            'can_manage' => $this->isAdmin()
        ];
        $this->render('modules/users', $data);
    }
}