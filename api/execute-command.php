<?php
session_start();
require_once '../includes/functions.php';

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

// Récupérer les données JSON
$data = json_decode(file_get_contents('php://input'), true);
$command = isset($data['command']) ? $data['command'] : '';

// Liste des commandes autorisées
$allowed_commands = [
    'ls', 'cat', 'df', 'du', 'free', 'top', 'ps', 'uptime', 'who', 'w',
    'ifconfig', 'ip', 'netstat', 'ping', 'hostname', 'uname', 'date',
    'grep', 'find', 'head', 'tail', 'wc', 'sort', 'uniq', 'echo'
];

// Vérifier si la commande est autorisée
$is_allowed = false;
foreach ($allowed_commands as $allowed) {
    if (strpos($command, $allowed) === 0) {
        $is_allowed = true;
        break;
    }
}

if (!$is_allowed) {
    echo json_encode([
        'output' => "Commande non autorisée: $command\nUtilisez une des commandes suivantes: " . implode(', ', $allowed_commands)
    ]);
    exit;
}

// Exécuter la commande
$output = [];
$return_var = 0;
exec($command . ' 2>&1', $output, $return_var);

// Renvoyer le résultat
echo json_encode([
    'output' => implode("\n", $output),
    'exitCode' => $return_var
]);

