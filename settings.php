<?php
session_start();
require_once 'includes/functions.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: index.php');
    exit;
}

// Récupérer les paramètres du serveur
$serverSettings = getServerSettings();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Paramètres - Administration Serveur</title>
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
                    <h1 class="h2"><i class="bi bi-gear"></i> Paramètres</h1>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <i class="bi bi-server"></i> Paramètres du Serveur
                            </div>
                            <div class="card-body">
                                <form id="serverSettingsForm">
                                    <div class="mb-3">
                                        <label for="serverName" class="form-label">Nom du serveur</label>
                                        <input type="text" class="form-control" id="serverName" name="serverName" value="<?php echo htmlspecialchars($serverSettings['server_name']); ?>">
                                    </div>
                                    <div class="mb-3">
                                        <label for="timeZone" class="form-label">Fuseau horaire</label>
                                        <select class="form-select" id="timeZone" name="timeZone">
                                            <?php foreach (getTimeZones() as $tz): ?>
                                            <option value="<?php echo $tz; ?>" <?php echo ($serverSettings['timezone'] == $tz) ? 'selected' : ''; ?>>
                                                <?php echo $tz; ?>
                                            </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label for="maxUploadSize" class="form-label">Taille maximale de téléversement (MB)</label>
                                        <input type="number" class="form-control" id="maxUploadSize" name="maxUploadSize" value="<?php echo htmlspecialchars($serverSettings['max_upload_size']); ?>">
                                    </div>
                                    <div class="mb-3">
                                        <label for="maxExecutionTime" class="form-label">Temps d'exécution maximal (secondes)</label>
                                        <input type="number" class="form-control" id="maxExecutionTime" name="maxExecutionTime" value="<?php echo htmlspecialchars($serverSettings['max_execution_time']); ?>">
                                    </div>
                                    <div class="mb-3">
                                        <label for="memoryLimit" class="form-label">Limite de mémoire (MB)</label>
                                        <input type="number" class="form-control" id="memoryLimit" name="memoryLimit" value="<?php echo htmlspecialchars($serverSettings['memory_limit']); ?>">
                                    </div>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-save"></i> Enregistrer
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <i class="bi bi-palette"></i> Personnalisation
                            </div>
                            <div class="card-body">
                                <form id="uiSettingsForm">
                                    <div class="mb-3">
                                        <label class="form-label d-block">Thème</label>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="theme" id="themeLight" value="light" checked>
                                            <label class="form-check-label" for="themeLight">
                                                <i class="bi bi-sun-fill"></i> Clair
                                            </label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="theme" id="themeDark" value="dark">
                                            <label class="form-check-label" for="themeDark">
                                                <i class="bi bi-moon-fill"></i> Sombre
                                            </label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="theme" id="themeAuto" value="auto">
                                            <label class="form-check-label" for="themeAuto">
                                                <i class="bi bi-circle-half"></i> Auto
                                            </label>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="primaryColor" class="form-label">Couleur principale</label>
                                        <input type="color" class="form-control form-control-color" id="primaryColor" name="primaryColor" value="#0d6efd">
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="fontSize" class="form-label">Taille de police</label>
                                        <select class="form-select" id="fontSize" name="fontSize">
                                            <option value="small">Petite</option>
                                            <option value="medium" selected>Moyenne</option>
                                            <option value="large">Grande</option>
                                        </select>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="sidebarPosition" class="form-label">Position de la barre latérale</label>
                                        <select class="form-select" id="sidebarPosition" name="sidebarPosition">
                                            <option value="left" selected>Gauche</option>
                                            <option value="right">Droite</option>
                                        </select>
                                    </div>
                                    
                                    <div class="mb-3 form-check">
                                        <input type="checkbox" class="form-check-input" id="compactMode" name="compactMode">
                                        <label class="form-check-label" for="compactMode">Mode compact</label>
                                    </div>
                                    
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-save"></i> Appliquer
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary" id="resetUISettings">
                                        <i class="bi bi-arrow-counterclockwise"></i> Réinitialiser
                                    </button>
                                </form>
                            </div>
                        </div>
                        
                        <div class="card mt-4">
                            <div class="card-header">
                                <i class="bi bi-shield-lock"></i> Sécurité
                            </div>
                            <div class="card-body">
                                <form id="securitySettingsForm">
                                    <div class="mb-3">
                                        <label for="currentUsername" class="form-label">Nom d'utilisateur actuel</label>
                                        <input type="text" class="form-control" id="currentUsername" name="currentUsername" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="newUsername" class="form-label">Nouveau nom d'utilisateur</label>
                                        <input type="text" class="form-control" id="newUsername" name="newUsername">
                                    </div>
                                    <div class="mb-3">
                                        <label for="currentPassword" class="form-label">Mot de passe actuel</label>
                                        <input type="password" class="form-control" id="currentPassword" name="currentPassword" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="newPassword" class="form-label">Nouveau mot de passe</label>
                                        <input type="password" class="form-control" id="newPassword" name="newPassword">
                                    </div>
                                    <div class="mb-3">
                                        <label for="confirmPassword" class="form-label">Confirmer le mot de passe</label>
                                        <input type="password" class="form-control" id="confirmPassword" name="confirmPassword">
                                    </div>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-key"></i> Mettre à jour les informations de sécurité
                                    </button>
                                </form>
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
    <script src="js/settings.js"></script>
</body>
</html>

