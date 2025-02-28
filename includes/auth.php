<?php
/**
 * Fonctions d'authentification
 */

/**
 * Authentifie un utilisateur
 * @param string $username Nom d'utilisateur
 * @param string $password Mot de passe
 * @return bool Succès de l'authentification
 */
function authenticate($username, $password) {
    // Utilisateur admin par défaut (mot de passe en clair comme demandé)
    $admin_username = 'admin';
    $admin_password = 'admin123'; // Mot de passe en clair (non sécurisé)
    
    // Vérifier les identifiants
    if ($username === $admin_username && $password === $admin_password) {
        return true;
    }
    
    return false;
}

/**
 * Change le mot de passe de l'administrateur
 * @param string $current_password Mot de passe actuel
 * @param string $new_password Nouveau mot de passe
 * @return bool Succès du changement
 */
function changePassword($current_password, $new_password) {
    // Vérifier le mot de passe actuel
    if (!authenticate('admin', $current_password)) {
        return false;
    }
    
    // Dans un environnement réel, vous devriez stocker le nouveau mot de passe
    // dans un fichier sécurisé ou une base de données.
    // Pour cet exemple, nous simulons un succès.
    
    return true;
}

