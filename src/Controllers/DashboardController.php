<?php

require_once __DIR__ . '/../Services/AuthService.php';

class DashboardController {
    public function index() {
        // si l'utilisateur n'est pas connecté, on le tèje 
        AuthService::requireLogin();

        $user = AuthService::getCurrentUser();

        require_once __DIR__ . '/../Views/dashboard/index.php';
    }
}