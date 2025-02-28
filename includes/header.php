<header class="navbar navbar-dark sticky-top bg-dark flex-md-nowrap p-0 shadow">
    <a class="navbar-brand col-md-3 col-lg-2 me-0 px-3" href="dashboard.php">
        <img src="favicon.svg" alt="Logo" width="30" height="30" class="d-inline-block align-text-top me-2">
        Admin Serveur
    </a>
    <button class="navbar-toggler position-absolute d-md-none collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#sidebarMenu" aria-controls="sidebarMenu" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>
    <div class="w-100"></div>
    <div class="navbar-nav">
        <div class="nav-item text-nowrap d-flex align-items-center">
            <div class="dropdown me-3">
                <a href="#" class="nav-link px-3 dropdown-toggle" id="themeDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-palette"></i>
                </a>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="themeDropdown">
                    <li><h6 class="dropdown-header">Thème</h6></li>
                    <li>
                        <a class="dropdown-item d-flex align-items-center" href="#" id="lightTheme">
                            <i class="bi bi-sun-fill me-2"></i> Mode Clair
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item d-flex align-items-center" href="#" id="darkTheme">
                            <i class="bi bi-moon-fill me-2"></i> Mode Sombre
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item d-flex align-items-center" href="#" id="autoTheme">
                            <i class="bi bi-circle-half me-2"></i> Auto
                        </a>
                    </li>
                </ul>
            </div>
            <a href="settings.php" class="nav-link px-3">
                <i class="bi bi-gear"></i>
            </a>
            <a href="logout.php" class="nav-link px-3">
                <i class="bi bi-box-arrow-right"></i> Déconnexion
            </a>
        </div>
    </div>
</header>

