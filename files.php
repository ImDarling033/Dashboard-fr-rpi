<?php
session_start();
require_once 'includes/functions.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: index.php');
    exit;
}

// Récupérer le chemin actuel
$currentPath = isset($_GET['path']) ? $_GET['path'] : '/';
$currentPath = validatePath($currentPath);

// Récupérer la liste des fichiers et dossiers
$fileList = getFileList($currentPath);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Explorateur de Fichiers - Administration Serveur</title>
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
                    <h1 class="h2"><i class="bi bi-folder2-open"></i> Explorateur de Fichiers</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <button type="button" class="btn btn-sm btn-outline-secondary" id="refreshFiles">
                                <i class="bi bi-arrow-repeat"></i> Actualiser
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#newFolderModal">
                                <i class="bi bi-folder-plus"></i> Nouveau Dossier
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-success" data-bs-toggle="modal" data-bs-target="#uploadFileModal">
                                <i class="bi bi-upload"></i> Téléverser
                            </button>
                        </div>
                    </div>
                </div>
                
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="?path=/"><i class="bi bi-house-door"></i> Racine</a></li>
                        <?php
                        $pathParts = explode('/', trim($currentPath, '/'));
                        $buildPath = '';
                        foreach ($pathParts as $part) {
                            if (empty($part)) continue;
                            $buildPath .= '/' . $part;
                            echo '<li class="breadcrumb-item"><a href="?path=' . urlencode($buildPath) . '">' . htmlspecialchars($part) . '</a></li>';
                        }
                        ?>
                    </ol>
                </nav>
                
                <div class="card mb-4">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover table-striped mb-0">
                                <thead>
                                    <tr>
                                        <th width="5%"></th>
                                        <th width="40%">Nom</th>
                                        <th width="15%">Taille</th>
                                        <th width="15%">Type</th>
                                        <th width="15%">Modifié</th>
                                        <th width="10%">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($currentPath != '/'): ?>
                                    <tr>
                                        <td><i class="bi bi-arrow-up-circle text-primary"></i></td>
                                        <td colspan="4">
                                            <a href="?path=<?php echo urlencode(dirname($currentPath)); ?>" class="text-decoration-none">
                                                ..
                                            </a>
                                        </td>
                                        <td></td>
                                    </tr>
                                    <?php endif; ?>
                                    
                                    <?php foreach ($fileList as $file): ?>
                                    <tr>
                                        <td>
                                            <?php if ($file['type'] == 'dir'): ?>
                                            <i class="bi bi-folder-fill text-warning"></i>
                                            <?php else: ?>
                                            <i class="bi bi-file-earmark text-primary"></i>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($file['type'] == 'dir'): ?>
                                            <a href="?path=<?php echo urlencode($currentPath . '/' . $file['name']); ?>" class="text-decoration-none">
                                                <?php echo htmlspecialchars($file['name']); ?>
                                            </a>
                                            <?php else: ?>
                                            <?php echo htmlspecialchars($file['name']); ?>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo $file['size']; ?></td>
                                        <td><?php echo $file['mime']; ?></td>
                                        <td><?php echo $file['modified']; ?></td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <?php if ($file['type'] != 'dir'): ?>
                                                <button type="button" class="btn btn-outline-primary view-file" data-path="<?php echo htmlspecialchars($currentPath . '/' . $file['name']); ?>">
                                                    <i class="bi bi-eye"></i>
                                                </button>
                                                <?php endif; ?>
                                                <button type="button" class="btn btn-outline-danger delete-file" data-path="<?php echo htmlspecialchars($currentPath . '/' . $file['name']); ?>" data-name="<?php echo htmlspecialchars($file['name']); ?>">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                    
                                    <?php if (empty($fileList)): ?>
                                    <tr>
                                        <td colspan="6" class="text-center py-4">
                                            <i class="bi bi-folder2-open display-4 d-block mb-2 text-muted"></i>
                                            <p class="text-muted">Ce dossier est vide</p>
                                        </td>
                                    </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
    
    <!-- Modal pour nouveau dossier -->
    <div class="modal fade" id="newFolderModal" tabindex="-1" aria-labelledby="newFolderModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="newFolderModalLabel"><i class="bi bi-folder-plus"></i> Nouveau Dossier</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                </div>
                <div class="modal-body">
                    <form id="newFolderForm">
                        <div class="mb-3">
                            <label for="folderName" class="form-label">Nom du dossier</label>
                            <input type="text" class="form-control" id="folderName" name="folderName" required>
                            <input type="hidden" id="currentPath" name="currentPath" value="<?php echo htmlspecialchars($currentPath); ?>">
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="button" class="btn btn-primary" id="createFolderBtn">Créer</button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Modal pour téléverser un fichier -->
    <div class="modal fade" id="uploadFileModal" tabindex="-1" aria-labelledby="uploadFileModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="uploadFileModalLabel"><i class="bi bi-upload"></i> Téléverser un Fichier</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                </div>
                <div class="modal-body">
                    <form id="uploadFileForm" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="fileToUpload" class="form-label">Sélectionner un fichier</label>
                            <input type="file" class="form-control" id="fileToUpload" name="fileToUpload" required>
                            <input type="hidden" id="uploadPath" name="uploadPath" value="<?php echo htmlspecialchars($currentPath); ?>">
                        </div>
                    </form>
                    <div class="progress d-none" id="uploadProgress">
                        <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="button" class="btn btn-primary" id="uploadFileBtn">Téléverser</button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Modal pour visualiser un fichier -->
    <div class="modal fade" id="viewFileModal" tabindex="-1" aria-labelledby="viewFileModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewFileModalLabel"><i class="bi bi-eye"></i> Visualiser le Fichier</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                </div>
                <div class="modal-body">
                    <div id="fileContent" class="p-3 border rounded bg-light">
                        <div class="text-center py-5">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Chargement...</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                    <a href="#" class="btn btn-primary" id="downloadFileBtn" download>Télécharger</a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Modal de confirmation de suppression -->
    <div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-labelledby="deleteConfirmModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteConfirmModalLabel"><i class="bi bi-exclamation-triangle-fill text-danger"></i> Confirmation</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                </div>
                <div class="modal-body">
                    <p>Êtes-vous sûr de vouloir supprimer <strong id="deleteFileName"></strong> ?</p>
                    <p class="text-danger">Cette action est irréversible !</p>
                    <input type="hidden" id="deleteFilePath">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Supprimer</button>
                </div>
            </div>
        </div>
    </div>
    
    <?php include 'includes/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/script.js"></script>
    <script src="js/files.js"></script>
</body>
</html>

