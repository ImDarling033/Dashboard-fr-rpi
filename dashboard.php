<?php
session_start();
require_once 'includes/functions.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: index.php');
    exit;
}

// Récupérer les statistiques du serveur
$stats = getServerStats();
$hostname = gethostname();
$uptime = getUptime();
$diskUsage = getDiskUsage();
$loadAverage = getLoadAverage();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de Bord - Administration Serveur</title>
    <link rel="icon" href="favicon.svg" type="image/svg+xml">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container-fluid">
        <div class="row">
            <?php include 'includes/sidebar.php'; ?>
            
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2"><i class="bi bi-speedometer2"></i> Tableau de Bord</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <button type="button" class="btn btn-sm btn-outline-secondary" id="refreshStats">
                                <i class="bi bi-arrow-repeat"></i> Actualiser
                            </button>
                        </div>
                    </div>
                </div>
                
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card text-white bg-primary mb-3">
                            <div class="card-body">
                                <h5 class="card-title"><i class="bi bi-cpu"></i> CPU</h5>
                                <div class="progress">
                                    <div class="progress-bar" role="progressbar" style="width: <?php echo $stats['cpu']; ?>%;" 
                                         aria-valuenow="<?php echo $stats['cpu']; ?>" aria-valuemin="0" aria-valuemax="100">
                                        <?php echo $stats['cpu']; ?>%
                                    </div>
                                </div>
                                <p class="card-text mt-2">Charge: <?php echo $loadAverage; ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-white bg-success mb-3">
                            <div class="card-body">
                                <h5 class="card-title"><i class="bi bi-memory"></i> RAM</h5>
                                <div class="progress">
                                    <div class="progress-bar bg-success" role="progressbar" style="width: <?php echo $stats['memory']; ?>%;" 
                                         aria-valuenow="<?php echo $stats['memory']; ?>" aria-valuemin="0" aria-valuemax="100">
                                        <?php echo $stats['memory']; ?>%
                                    </div>
                                </div>
                                <p class="card-text mt-2"><?php echo $stats['memory_used']; ?> / <?php echo $stats['memory_total']; ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-white bg-info mb-3">
                            <div class="card-body">
                                <h5 class="card-title"><i class="bi bi-hdd"></i> Stockage</h5>
                                <div class="progress">
                                    <div class="progress-bar bg-info" role="progressbar" style="width: <?php echo $stats['disk']; ?>%;" 
                                         aria-valuenow="<?php echo $stats['disk']; ?>" aria-valuemin="0" aria-valuemax="100">
                                        <?php echo $stats['disk']; ?>%
                                    </div>
                                </div>
                                <p class="card-text mt-2"><?php echo $diskUsage; ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-white bg-warning mb-3">
                            <div class="card-body">
                                <h5 class="card-title"><i class="bi bi-thermometer-half"></i> Température</h5>
                                <div class="progress">
                                    <div class="progress-bar bg-warning" role="progressbar" style="width: <?php echo min(100, $stats['temp'] * 2); ?>%;" 
                                         aria-valuenow="<?php echo $stats['temp']; ?>" aria-valuemin="0" aria-valuemax="100">
                                        <?php echo $stats['temp']; ?>°C
                                    </div>
                                </div>
                                <p class="card-text mt-2">Uptime: <?php echo $uptime; ?></p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <i class="bi bi-activity"></i> Activité Système
                            </div>
                            <div class="card-body">
                                <canvas id="systemActivityChart" width="400" height="200"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <i class="bi bi-globe"></i> Informations Réseau
                            </div>
                            <div class="card-body">
                                <ul class="list-group">
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <span><i class="bi bi-hdd-network"></i> Nom d'hôte</span>
                                        <span class="badge bg-primary rounded-pill"><?php echo $hostname; ?></span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <span><i class="bi bi-ethernet"></i> Adresse IPv4</span>
                                        <span class="badge bg-primary rounded-pill"><?php echo getIPv4(); ?></span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <span><i class="bi bi-globe2"></i> Adresse IPv6</span>
                                        <span class="badge bg-primary rounded-pill"><?php echo getIPv6(); ?></span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <span><i class="bi bi-people"></i> Utilisateurs connectés</span>
                                        <span class="badge bg-primary rounded-pill"><?php echo getConnectedUsers(); ?></span>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row mb-4">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <i class="bi bi-terminal"></i> Terminal Rapide
                            </div>
                            <div class="card-body">
                                <div class="input-group mb-3">
                                    <input type="text" class="form-control" id="quickCommand" placeholder="Entrez une commande...">
                                    <button class="btn btn-primary" type="button" id="executeCommand">
                                        <i class="bi bi-play-fill"></i> Exécuter
                                    </button>
                                </div>
                                <div class="terminal-output" id="quickTerminalOutput">
                                    <div class="terminal-line">
                                        <span class="text-success">root@<?php echo $hostname; ?>:~$</span> 
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
    
    <?php include 'includes/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="js/script.js"></script>
    <script src="js/stats.js"></script>
    <script>
        // Initialiser les graphiques
        initSystemActivityChart();
        
        // Actualiser les statistiques toutes les 10 secondes
        setInterval(updateStats, 10000);
        
        // Gestionnaire pour le terminal rapide
        document.getElementById('executeCommand').addEventListener('click', function() {
            executeQuickCommand();
        });
        
        document.getElementById('quickCommand').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                executeQuickCommand();
            }
        });
        
        document.getElementById('refreshStats').addEventListener('click', function() {
            updateStats();
        });
    </script>
</body>
</html>

