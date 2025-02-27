<nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse">
    <div class="position-sticky pt-3">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'dashboard.php') ? 'active' : ''; ?>" href="dashboard.php">
                    <i class="bi bi-speedometer2"></i> Tableau de Bord
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'terminal.php') ? 'active' : ''; ?>" href="terminal.php">
                    <i class="bi bi-terminal"></i> Terminal
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'files.php') ? 'active' : ''; ?>" href="files.php">
                    <i class="bi bi-folder2-open"></i> Explorateur de Fichiers
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'network.php') ? 'active' : ''; ?>" href="network.php">
                    <i class="bi bi-globe"></i> Réseau
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'settings.php') ? 'active' : ''; ?>" href="settings.php">
                    <i class="bi bi-gear"></i> Paramètres
                </a>
            </li>
        </ul>
        
        <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted">
            <span>Système</span>
        </h6>
        <ul class="nav flex-column mb-2">
            <li class="nav-item">
                <a class="nav-link" href="#" id="sidebarCPUInfo">
                    <i class="bi bi-cpu"></i> CPU: <span class="cpu-usage">0%</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#" id="sidebarRAMInfo">
                    <i class="bi bi-memory"></i> RAM: <span class="ram-usage">0%</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#" id="sidebarDiskInfo">
                    <i class="bi bi-hdd"></i> Disque: <span class="disk-usage">0%</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#" id="sidebarTempInfo">
                    <i class="bi bi-thermometer-half"></i> Temp: <span class="temp">0°C</span>
                </a>
            </li>
        </ul>
    </div>
</nav>

