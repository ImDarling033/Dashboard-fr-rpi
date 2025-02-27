<?php
session_start();
require_once '../includes/functions.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('HTTP/1.1 401 Unauthorized');
    echo json_encode(['error' => 'Non autorisé']);
    exit;
}

// Récupérer l'action demandée
$action = isset($_POST['action']) ? $_POST['action'] : '';

// Traiter l'action
switch ($action) {
    case 'create_folder':
        createFolder();
        break;
    case 'delete_file':
        deleteFile();
        break;
    case 'view_file':
        viewFile();
        break;
    case 'upload_file':
        uploadFile();
        break;
    default:
        header('HTTP/1.1 400 Bad Request');
        echo json_encode(['error' => 'Action non valide']);
        break;
}

/**
 * Crée un nouveau dossier
 */
function createFolder() {
    $folderName = isset($_POST['folderName']) ? $_POST['folderName'] : '';
    $currentPath = isset($_POST['currentPath']) ? $_POST['currentPath'] : '/';
    
    // Valider le chemin
    $currentPath = validatePath($currentPath);
    $newFolderPath = $_SERVER['DOCUMENT_ROOT'] . $currentPath . $folderName;
    
    // Créer le dossier
    if (mkdir($newFolderPath, 0755)) {
        echo json_encode(['success' => true, 'message' => 'Dossier créé avec succès']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erreur lors de la création du dossier']);
    }
}

/**
 * Supprime un fichier ou un dossier
 */
function deleteFile() {
    $path = isset($_POST['path']) ? $_POST['path'] : '';
    
    // Valider le chemin
    $path = str_replace(['..', '\\'], '', $path);
    $fullPath = $_SERVER['DOCUMENT_ROOT'] . $path;
    
    // Supprimer le fichier ou dossier
    if (is_dir($fullPath)) {
        // Supprimer récursivement le dossier
        if (deleteDirectory($fullPath)) {
            echo json_encode(['success' => true, 'message' => 'Dossier supprimé avec succès']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erreur lors de la suppression du dossier']);
        }
    } else {
        // Supprimer le fichier
        if (unlink($fullPath)) {
            echo json_encode(['success' => true, 'message' => 'Fichier supprimé avec succès']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erreur lors de la suppression du fichier']);
        }
    }
}

/**
 * Supprime récursivement un dossier et son contenu
 * @param string $dir Chemin du dossier
 * @return bool Succès de la suppression
 */
function deleteDirectory($dir) {
    if (!file_exists($dir)) {
        return true;
    }
    
    if (!is_dir($dir)) {
        return unlink($dir);
    }
    
    foreach (scandir($dir) as $item) {
        if ($item == '.' || $item == '..') {
            continue;
        }
        
        if (!deleteDirectory($dir . DIRECTORY_SEPARATOR . $item)) {
            return false;
        }
    }
    
    return rmdir($dir);
}

/**
 * Affiche le contenu d'un fichier
 */
function viewFile() {
    $path = isset($_POST['path']) ? $_POST['path'] : '';
    
    // Valider le chemin
    $path = str_replace(['..', '\\'], '', $path);
    $fullPath = $_SERVER['DOCUMENT_ROOT'] . $path;
    
    // Vérifier si le fichier existe
    if (!file_exists($fullPath) || !is_file($fullPath)) {
        echo json_encode(['success' => false, 'message' => 'Fichier non trouvé']);
        return;
    }
    
    // Lire le contenu du fichier
    $content = file_get_contents($fullPath);
    $mime = function_exists('mime_content_type') ? mime_content_type($fullPath) : 'text/plain';
    
    echo json_encode([
        'success' => true,
        'content' => base64_encode($content),
        'mime' => $mime,
        'filename' => basename($path)
    ]);
}

/**
 * Téléverse un fichier
 */
function uploadFile() {
    $uploadPath = isset($_POST['uploadPath']) ? $_POST['uploadPath'] : '/';
    
    // Valider le chemin
    $uploadPath = validatePath($uploadPath);
    $targetDir = $_SERVER['DOCUMENT_ROOT'] . $uploadPath;
    
    // Vérifier si un fichier a été téléversé
    if (!isset($_FILES['fileToUpload']) || $_FILES['fileToUpload']['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(['success' => false, 'message' => 'Erreur lors du téléversement']);
        return;
    }
    
    $targetFile = $targetDir . basename($_FILES['fileToUpload']['name']);
    
    // Déplacer le fichier téléversé
    if (move_uploaded_file($_FILES['fileToUpload']['tmp_name'], $targetFile)) {
        echo json_encode(['success' => true, 'message' => 'Fichier téléversé avec succès']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erreur lors du déplacement du fichier']);
    }
}

