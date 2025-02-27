<?php
session_start();
require_once 'includes/functions.php';
require_once 'includes/auth.php';

// Vérifier si l'utilisateur est déjà connecté
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    header('Location: dashboard.php');
    exit;
}

// Traitement du formulaire de connexion
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    
    if (authenticate($username, $password)) {
        $_SESSION['logged_in'] = true;
        $_SESSION['username'] = $username;
        header('Location: dashboard.php');
        exit;
    } else {
        $error = 'Nom d\'utilisateur ou mot de passe incorrect';
    }
}

// Récupérer le nom du serveur
$hostname = gethostname();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - Administration Serveur</title>
    <link rel="icon" href="favicon.svg" type="image/svg+xml">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
</head>
<body>
    <div class="container-fluid vh-100 d-flex align-items-center justify-content-center">
        <div class="card login-card">
            <div class="card-body">
                <div class="text-center mb-4">
                    <img src="favicon.svg" alt="Logo" class="login-logo mb-3">
                    <h2 class="card-title">Administration Serveur</h2>
                    <p class="text-muted"><?php echo $hostname; ?></p>
                </div>
                
                <?php if ($error): ?>
                <div class="alert alert-danger" role="alert">
                    <i class="bi bi-exclamation-triangle-fill"></i> <?php echo $error; ?>
                </div>
                <?php endif; ?>
                
                <form method="post" action="">
                    <div class="mb-3">
                        <label for="username" class="form-label">
                            <i class="bi bi-person-fill"></i> Nom d'utilisateur
                        </label>
                        <input type="text" class="form-control" id="username" name="username" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">
                            <i class="bi bi-key-fill"></i> Mot de passe
                        </label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-box-arrow-in-right"></i> Connexion
                        </button>
                    </div>
                </form>
            </div>
            <div class="card-footer text-center text-muted">
                <small>© <?php echo date('Y'); ?> - Administration Serveur Ubuntu</small>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/script.js"></script>
    <script>
        // Vérifier et appliquer le thème
        applyTheme();
    </script>
</body>
</html>

