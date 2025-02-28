<?php
session_start();
require_once '../includes/functions.php';
require_once '../includes/auth.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('HTTP/1.1 401 Unauthorized');
    echo json_encode(['error' => 'Non autorisé']);
    exit;
}

// Vérifier si la requête est de type POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('HTTP/1.1 405 Method Not Allowed');
    echo json_encode(['error' => 'Méthode non autorisée']);
    exit;
}

$action = isset($_POST['action']) ? $_POST['action'] : '';

switch ($action) {
    case 'update_security_settings':
        updateSecuritySettings();
        break;
    case 'save_server_settings':
        saveServerSettings();
        break;
    case 'save_ui_settings':
        saveUISettings();
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Action non reconnue']);
        break;
}

function updateSecuritySettings() {
    $currentUsername = $_POST['currentUsername'];
    $newUsername = $_POST['newUsername'];
    $currentPassword = $_POST['currentPassword'];
    $newPassword = $_POST['newPassword'];

    if (!authenticate($currentUsername, $currentPassword)) {
        echo json_encode(['success' => false, 'message' => 'Nom d\'utilisateur ou mot de passe actuel incorrect']);
        return;
    }

    $success = true;
    $message = '';

    if (!empty($newUsername)) {
        $success = updateUsername($currentUsername, $newUsername);
        if ($success) {
            $_SESSION['username'] = $newUsername;
            $message .= 'Nom d\'utilisateur mis à jour. ';
        } else {
            $message .= 'Erreur lors de la mise à jour du nom d\'utilisateur. ';
        }
    }

    if (!empty($newPassword)) {
        $success = updatePassword($currentUsername, $newPassword) && $success;
        if ($success) {
            $message .= 'Mot de passe mis à jour. ';
        } else {
            $message .= 'Erreur lors de la mise à jour du mot de passe. ';
        }
    }

    echo json_encode(['success' => $success, 'message' => $message]);
}

function saveServerSettings() {
    $serverName = $_POST['serverName'];
    $timeZone = $_POST['timeZone'];
    $maxUploadSize = $_POST['maxUploadSize'];
    $maxExecutionTime = $_POST['maxExecutionTime'];
    $memoryLimit = $_POST['memoryLimit'];

    $success = updateServerSettings($serverName, $timeZone, $maxUploadSize, $maxExecutionTime, $memoryLimit);

    echo json_encode(['success' => $success, 'message' => $success ? 'Paramètres du serveur mis à jour avec succès' : 'Erreur lors de la mise à jour des paramètres du serveur']);
}

function saveUISettings() {
    $theme = $_POST['theme'];
    $primaryColor = $_POST['primaryColor'];
    $fontSize = $_POST['fontSize'];
    $sidebarPosition = $_POST['sidebarPosition'];
    $compactMode = $_POST['compactMode'] === 'true';

    $settings = [
        'theme' => $theme,
        'primaryColor' => $primaryColor,
        'fontSize' => $fontSize,
        'sidebarPosition' => $sidebarPosition,
        'compactMode' => $compactMode
    ];

    $success = updateUISettings($settings);

    echo json_encode(['success' => $success, 'message' => $success ? 'Paramètres d\'interface mis à jour avec succès' : 'Erreur lors de la mise à jour des paramètres d\'interface', 'settings' => $settings]);
}

