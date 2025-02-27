<?php
session_start();
require_once 'includes/functions.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: index.php');
    exit;
}

$hostname = gethostname();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Terminal - Administration Serveur</title>
    <link rel="icon" href="favicon.svg" type="image/svg+xml">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
    <link href="css/terminal.css" rel="stylesheet">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container-fluid">
        <div class="row">
            <?php include 'includes/sidebar.php'; ?>
            
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2"><i class="bi bi-terminal"></i> Terminal</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <button type="button" class="btn btn-sm btn-outline-secondary" id="clearTerminal">
                                <i class="bi bi-trash"></i> Effacer
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" id="downloadOutput">
                                <i class="bi bi-download"></i> Télécharger
                            </button>
                        </div>
                    </div>
                </div>
                
                <div class="card mb-4">
                    <div class="card-body p-0">
                        <div class="terminal-container">
                            <div class="terminal-header">
                                <div class="terminal-title">Terminal - <?php echo $hostname; ?></div>
                                <div class="terminal-controls">
                                    <span class="terminal-control terminal-control-minimize"></span>
                                    <span class="terminal-control terminal-control-maximize"></span>
                                    <span class="terminal-control terminal-control-close"></span>
                                </div>
                            </div>
                            <div class="terminal-body" id="terminal">
                                <div class="terminal-output" id="terminalOutput">
                                    <div class="terminal-line">
                                        <span class="text-success">root@<?php echo $hostname; ?>:~$</span> 
                                        <span class="terminal-welcome">Bienvenue dans le terminal d'administration. Tapez 'help' pour voir les commandes disponibles.</span>
                                    </div>
                                </div>
                                <div class="terminal-input-line">
                                    <span class="text-success">root@<?php echo $hostname; ?>:~$</span> 
                                    <input type="text" id="terminalInput" class="terminal-input" autofocus>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <i class="bi bi-info-circle"></i> Commandes Utiles
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Commande</th>
                                                <th>Description</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td><code>help</code></td>
                                                <td>Affiche la liste des commandes disponibles</td>
                                            </tr>
                                            <tr>
                                                <td><code>clear</code></td>
                                                <td>Efface le terminal</td>
                                            </tr>
                                            <tr>
                                                <td><code>ls [chemin]</code></td>
                                                <td>Liste les fichiers et dossiers</td>
                                            </tr>
                                            <tr>
                                                <td><code>cat [fichier]</code></td>
                                                <td>Affiche le contenu d'un fichier</td>
                                            </tr>
                                            <tr>
                                                <td><code>sysinfo</code></td>
                                                <td>Affiche les informations système</td>
                                            </tr>
                                            <tr>
                                                <td><code>netinfo</code></td>
                                                <td>Affiche les informations réseau</td>
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
                                <i class="bi bi-clock-history"></i> Historique des Commandes
                            </div>
                            <div class="card-body">
                                <ul class="list-group" id="commandHistory">
                                    <!-- L'historique des commandes sera ajouté ici par JavaScript -->
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
    
    <?php include 'includes/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/script.js"></script>
    <script src="js/terminal.js"></script>
</body>
</html>

