<?php
session_start();
require_once '../includes/functions.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('HTTP/1.1 401 Unauthorized');
    echo json_encode(['error' => 'Non autorisé']);
    exit;
}

// Récupérer les statistiques du serveur
$stats = getServerStats();
$uptime = getUptime();
$diskUsage = getDiskUsage();
$loadAverage = getLoadAverage();
$ipv4 = getIPv4();
$ipv6 = getIPv6();
$connectedUsers = getConnectedUsers();

// Renvoyer les données au format JSON
header('Content-Type: application/json');
echo json_encode([
    'stats' => $stats,
    'uptime' => $uptime,
    'diskUsage' => $diskUsage,
    'loadAverage' => $loadAverage,
    'ipv4' => $ipv4,
    'ipv6' => $ipv6,
    'connectedUsers' => $connectedUsers,
    'timestamp' => time()
]);

