<?php

class ManualController extends Controller {
    public function index() {
        $userRole = $_SESSION['user_role'] ?? 'Expectador';
        $userName = $_SESSION['user'] ?? 'Usuario';

        $data = [
            'page_title' => 'Manual de Operaciones y Usuario • Starlink Control',
            'userRole' => $userRole,
            'userName' => $userName,
            'isAdmin' => $this->isAdmin(),
            'isModerator' => $this->isModerator()
        ];

        $this->render('modules/manual', $data);
    }
}
