<?php
session_start();
require_once 'includes/functions.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: index.php');
    exit;
}

// Récupérer les informations réseau
$networkInfo = getNetworkInfo();
$connectedDevices = getConnectedDevices();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Réseau - Administration Serveur</title>
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
                    <h1 class="h2"><i class="bi bi-globe"></i> Réseau</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <button type="button" class="btn btn-sm btn-outline-secondary" id="refreshNetwork">
                                <i class="bi bi-arrow-repeat"></i> Actualiser
                            </button>
                        </div>
                    </div>
                </div>
                
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <i class="bi bi-info-circle"></i> Informations Réseau
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table">
                                        <tbody>
                                            <tr>
                                                <th><i class="bi bi-hdd-network"></i> Nom d'hôte</th>
                                                <td><?php echo htmlspecialchars($networkInfo['hostname']); ?></td>
                                            </tr>
                                            <tr>
                                                <th><i class="bi bi-ethernet"></i> Adresse IPv4</th>
                                                <td><?php echo htmlspecialchars($networkInfo['ipv4']); ?></td>
                                            </tr>
                                            <tr>
                                                <th><i class="bi bi-globe2"></i> Adresse IPv6</th>
                                                <td><?php echo htmlspecialchars($networkInfo['ipv6']); ?></td>
                                            </tr>
                                            <tr>
                                                <th><i class="bi bi-globe"></i> Adresse IP publique</th>
                                                <td><?php echo htmlspecialchars($networkInfo['public_ip']); ?></td>
                                            </tr>
                                            <tr>
                                                <th><i class="bi bi-upc-scan"></i> Adresse MAC</th>
                                                <td><?php echo htmlspecialchars($networkInfo['mac']); ?></td>
                                            </tr>
                                            <tr>
                                                <th><i class="bi bi-router"></i> Passerelle</th>
                                                <td><?php echo htmlspecialchars($networkInfo['gateway']); ?></td>
                                            </tr>
                                            <tr>
                                                <th><i class="bi bi-mask"></i> Masque de sous-réseau</th>
                                                <td><?php echo htmlspecialchars($networkInfo['netmask']); ?></td>
                                            </tr>
                                            <tr>
                                                <th><i class="bi bi-diagram-3"></i> DNS</th>
                                                <td><?php echo htmlspecialchars($networkInfo['dns']); ?></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <i class="bi bi-speedometer2"></i> Statistiques Réseau
                            </div>
                            <div class="card-body">
                                <canvas id="networkTrafficChart" width="400" height="250"></canvas>
                                <div class="row mt-3">
                                    <div class="col-md-6">
                                        <div class="d-flex justify-content-between">
                                            <span><i class="bi bi-arrow-down"></i> Téléchargement</span>
                                            <span id="downloadSpeed">0 KB/s</span>
                                        </div>
                                        <div class="progress mt-1 mb-3">
                                            <div class="progress-bar bg-success" id="downloadBar" role="progressbar" style="width: 0%"></div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="d-flex justify-content-between">
                                            <span><i class="bi bi-arrow-up"></i> Téléversement</span>
                                            <span id="uploadSpeed">0 KB/s</span>
                                        </div>
                                        <div class="progress mt-1 mb-3">
                                            <div class="progress-bar bg-primary" id="uploadBar" role="progressbar" style="width: 0%"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="card mb-4">
                    <div class="card-header">
                        <i class="bi bi-pc-display"></i> Périphériques Connectés
                        <span class="badge bg-primary rounded-pill ms-2"><?php echo count($connectedDevices); ?></span>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Nom</th>
                                        <th>Adresse IP</th>
                                        <th>Adresse MAC</th>
                                        <th>Type</th>
                                        <th>Statut</th>
                                        <th>Dernière activité</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($connectedDevices as $device): ?>
                                    <tr>
                                        <td>
                                            <?php if ($device['type'] == 'smartphone'): ?>
                                            <i class="bi bi-phone text-primary"></i>
                                            <?php elseif ($device['type'] == 'tablet'): ?>
                                            <i class="bi bi-tablet text-success"></i>
                                            <?php elseif ($device['type'] == 'laptop'): ?>
                                            <i class="bi bi-laptop text-info"></i>
                                            <?php else: ?>
                                            <i class="bi bi-pc-display text-secondary"></i>
                                            <?php endif; ?>
                                            <?php echo htmlspecialchars($device['name']); ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($device['ip']); ?></td>
                                        <td><?php echo htmlspecialchars($device['mac']); ?></td>
                                        <td><?php echo htmlspecialchars($device['type']); ?></td>
                                        <td>
                                            <?php if ($device['status'] == 'online'): ?>
                                            <span class="badge bg-success">En ligne</span>
                                            <?php else: ?>
                                            <span class="badge bg-secondary">Hors ligne</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($device['last_seen']); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                    
                                    <?php if (empty($connectedDevices)): ?>
                                    <tr>
                                        <td colspan="6" class="text-center py-4">
                                            <i class="bi bi-wifi-off display-4 d-block mb-2 text-muted"></i>
                                            <p class="text-muted">Aucun périphérique connecté</p>
                                        </td>
                                    </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <i class="bi bi-hdd-rack"></i> Interfaces Réseau
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Interface</th>
                                                <th>État</th>
                                                <th>Adresse IP</th>
                                                <th>Vitesse</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($networkInfo['interfaces'] as $interface): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($interface['name']); ?></td>
                                                <td>
                                                    <?php if ($interface['status'] == 'up'): ?>
                                                    <span class="badge bg-success">Actif</span>
                                                    <?php else: ?>
                                                    <span class="badge bg-secondary">Inactif</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?php echo htmlspecialchars($interface['ip']); ?></td>
                                                <td><?php echo htmlspecialchars($interface['speed']); ?></td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <i class="bi bi-diagram-3"></i> Connexions Actives
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Protocole</th>
                                                <th>Adresse locale</th>
                                                <th>Adresse distante</th>
                                                <th>État</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($networkInfo['connections'] as $conn): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($conn['protocol']); ?></td>
                                                <td><?php echo htmlspecialchars($conn['local']); ?></td>
                                                <td><?php echo htmlspecialchars($conn['remote']); ?></td>
                                                <td><?php echo htmlspecialchars($conn['state']); ?></td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
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
    <script src="js/network.js"></script>
</body>
</html>

