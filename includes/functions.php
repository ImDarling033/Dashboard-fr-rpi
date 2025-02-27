<?php
/**
 * Fonctions utilitaires pour l'administration du serveur
 */

/**
 * Récupère les statistiques du serveur
 * @return array Statistiques du serveur
 */
function getServerStats() {
    // CPU
    $cpu_usage = 0;
    if (function_exists('sys_getloadavg')) {
        $load = sys_getloadavg();
        $cpu_usage = $load[0] * 100 / 4; // Supposons 4 cœurs pour un RPi
        $cpu_usage = min(100, round($cpu_usage));
    }
    
    // Mémoire
    $mem_total = 0;
    $mem_used = 0;
    $mem_usage = 0;
    
    if (is_readable('/proc/meminfo')) {
        $meminfo = file_get_contents('/proc/meminfo');
        preg_match('/MemTotal:\s+(\d+)/', $meminfo, $matches);
        $mem_total = isset($matches[1]) ? round($matches[1] / 1024) : 0;
        
        preg_match('/MemAvailable:\s+(\d+)/', $meminfo, $matches);
        $mem_available = isset($matches[1]) ? round($matches[1] / 1024) : 0;
        
        $mem_used = $mem_total - $mem_available;
        $mem_usage = round(($mem_used / $mem_total) * 100);
    }
    
    // Disque
    $disk_total = disk_total_space('/');
    $disk_free = disk_free_space('/');
    $disk_used = $disk_total - $disk_free;
    $disk_usage = round(($disk_used / $disk_total) * 100);
    
    // Température (spécifique à Raspberry Pi)
    $temp = 0;
    if (file_exists('/sys/class/thermal/thermal_zone0/temp')) {
        $temp = intval(file_get_contents('/sys/class/thermal/thermal_zone0/temp')) / 1000;
    }
    
    return [
        'cpu' => $cpu_usage,
        'memory' => $mem_usage,
        'memory_total' => formatBytes($mem_total * 1024),
        'memory_used' => formatBytes($mem_used * 1024),
        'disk' => $disk_usage,
        'temp' => round($temp, 1)
    ];
}

/**
 * Récupère le temps de fonctionnement du serveur
 * @return string Temps de fonctionnement formaté
 */
function getUptime() {
    $uptime = '';
    if (is_readable('/proc/uptime')) {
        $uptime_seconds = floatval(file_get_contents('/proc/uptime'));
        $days = floor($uptime_seconds / 86400);
        $hours = floor(($uptime_seconds % 86400) / 3600);
        $minutes = floor(($uptime_seconds % 3600) / 60);
        
        if ($days > 0) {
            $uptime .= $days . ' jours, ';
        }
        $uptime .= $hours . ' heures, ' . $minutes . ' minutes';
    }
    return $uptime;
}

/**
 * Récupère l'utilisation du disque
 * @return string Utilisation du disque formatée
 */
function getDiskUsage() {
    $disk_total = disk_total_space('/');
    $disk_free = disk_free_space('/');
    $disk_used = $disk_total - $disk_free;
    
    return formatBytes($disk_used) . ' / ' . formatBytes($disk_total);
}

/**
 * Récupère la charge moyenne du système
 * @return string Charge moyenne formatée
 */
function getLoadAverage() {
    if (function_exists('sys_getloadavg')) {
        $load = sys_getloadavg();
        return $load[0] . ', ' . $load[1] . ', ' . $load[2];
    }
    return '0, 0, 0';
}

/**
 * Récupère l'adresse IPv4 du serveur
 * @return string Adresse IPv4
 */
function getIPv4() {
    $ipv4 = '0.0.0.0';
    $command = 'hostname -I | awk \'{print $1}\'';
    exec($command, $output, $return_var);
    if ($return_var === 0 && !empty($output[0])) {
        $ipv4 = $output[0];
    }
    return $ipv4;
}

/**
 * Récupère l'adresse IPv6 du serveur
 * @return string Adresse IPv6
 */
function getIPv6() {
    $ipv6 = '::';
    $command = 'hostname -I | awk \'{print $2}\'';
    exec($command, $output, $return_var);
    if ($return_var === 0 && !empty($output[0])) {
        $ipv6 = $output[0];
    }
    return $ipv6;
}

/**
 * Récupère le nombre d'utilisateurs connectés
 * @return int Nombre d'utilisateurs
 */
function getConnectedUsers() {
    $users = 0;
    $command = 'who | wc -l';
    exec($command, $output, $return_var);
    if ($return_var === 0 && !empty($output[0])) {
        $users = intval($output[0]);
    }
    return $users;
}

/**
 * Formate les octets en unités lisibles
 * @param int $bytes Nombre d'octets
 * @param int $precision Précision décimale
 * @return string Taille formatée
 */
function formatBytes($bytes, $precision = 2) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    
    $bytes /= pow(1024, $pow);
    
    return round($bytes, $precision) . ' ' . $units[$pow];
}

/**
 * Récupère les informations réseau
 * @return array Informations réseau
 */
function getNetworkInfo() {
    $hostname = gethostname();
    $ipv4 = getIPv4();
    $ipv6 = getIPv6();
    
    // Adresse IP publique
    $public_ip = '0.0.0.0';
    $command = 'curl -s https://api.ipify.org';
    exec($command, $output, $return_var);
    if ($return_var === 0 && !empty($output[0])) {
        $public_ip = $output[0];
    }
    
    // Adresse MAC
    $mac = '00:00:00:00:00:00';
    $command = 'cat /sys/class/net/eth0/address';
    exec($command, $output, $return_var);
    if ($return_var === 0 && !empty($output[0])) {
        $mac = $output[0];
    }
    
    // Passerelle
    $gateway = '0.0.0.0';
    $command = 'ip route | grep default | awk \'{print $3}\'';
    exec($command, $output, $return_var);
    if ($return_var === 0 && !empty($output[0])) {
        $gateway = $output[0];
    }
    
    // Masque de sous-réseau
    $netmask = '255.255.255.0';
    $command = 'ifconfig eth0 | grep netmask | awk \'{print $4}\'';
    exec($command, $output, $return_var);
    if ($return_var === 0 && !empty($output[0])) {
        $netmask = $output[0];
    }
    
    // DNS
    $dns = '8.8.8.8, 8.8.4.4';
    $command = 'cat /etc/resolv.conf | grep nameserver | awk \'{print $2}\'';
    exec($command, $output, $return_var);
    if ($return_var === 0 && !empty($output)) {
        $dns = implode(', ', $output);
    }
    
    // Interfaces réseau
    $interfaces = [];
    $command = 'ip -o addr show | grep -v "lo" | awk \'{print $2, $4, $9}\'';
    exec($command, $output, $return_var);
    if ($return_var === 0) {
        foreach ($output as $line) {
            list($name, $ip, $status) = explode(' ', $line . ' up');
            $ip = str_replace('/', '', $ip);
            $interfaces[] = [
                'name' => $name,
                'ip' => $ip,
                'status' => $status == 'UP' ? 'up' : 'down',
                'speed' => '100 Mbps' // Valeur par défaut
            ];
        }
    }
    
    // Connexions actives
    $connections = [];
    $command = 'netstat -tn | grep ESTABLISHED | head -5';
    exec($command, $output, $return_var);
    if ($return_var === 0) {
        foreach ($output as $line) {
            $parts = preg_split('/\s+/', trim($line));
            if (count($parts) >= 6) {
                $connections[] = [
                    'protocol' => $parts[0],
                    'local' => $parts[3],
                    'remote' => $parts[4],
                    'state' => $parts[5]
                ];
            }
        }
    }
    
    return [
        'hostname' => $hostname,
        'ipv4' => $ipv4,
        'ipv6' => $ipv6,
        'public_ip' => $public_ip,
        'mac' => $mac,
        'gateway' => $gateway,
        'netmask' => $netmask,
        'dns' => $dns,
        'interfaces' => $interfaces,
        'connections' => $connections
    ];
}

/**
 * Récupère la liste des périphériques connectés
 * @return array Liste des périphériques
 */
function getConnectedDevices() {
    // Simulation de périphériques connectés
    $devices = [
        [
            'name' => 'Ordinateur-Bureau',
            'ip' => '192.168.1.100',
            'mac' => 'AA:BB:CC:DD:EE:FF',
            'type' => 'desktop',
            'status' => 'online',
            'last_seen' => date('Y-m-d H:i:s')
        ],
        [
            'name' => 'Smartphone-Android',
            'ip' => '192.168.1.101',
            'mac' => 'AA:BB:CC:DD:EE:00',
            'type' => 'smartphone',
            'status' => 'online',
            'last_seen' => date('Y-m-d H:i:s', strtotime('-5 minutes'))
        ],
        [
            'name' => 'Tablette-iPad',
            'ip' => '192.168.1.102',
            'mac' => 'AA:BB:CC:DD:00:FF',
            'type' => 'tablet',
            'status' => 'offline',
            'last_seen' => date('Y-m-d H:i:s', strtotime('-1 hour'))
        ],
        [
            'name' => 'Laptop-Windows',
            'ip' => '192.168.1.103',
            'mac' => 'AA:BB:CC:00:EE:FF',
            'type' => 'laptop',
            'status' => 'online',
            'last_seen' => date('Y-m-d H:i:s', strtotime('-10 minutes'))
        ]
    ];
    
    return $devices;
}

/**
 * Récupère la liste des fichiers et dossiers
 * @param string $path Chemin à explorer
 * @return array Liste des fichiers et dossiers
 */
function getFileList($path) {
    $result = [];
    
    // Sécuriser le chemin
    $path = validatePath($path);
    $realPath = $_SERVER['DOCUMENT_ROOT'] . $path;
    
    if (is_dir($realPath) && is_readable($realPath)) {
        $files = scandir($realPath);
        
        foreach ($files as $file) {
            if ($file == '.' || $file == '..') {
                continue;
            }
            
            $filePath = $realPath . '/' . $file;
            $isDir = is_dir($filePath);
            
            $result[] = [
                'name' => $file,
                'type' => $isDir ? 'dir' : 'file',
                'size' => $isDir ? '-' : formatBytes(filesize($filePath)),
                'mime' => $isDir ? 'Directory' : (function_exists('mime_content_type') ? mime_content_type($filePath) : 'Unknown'),
                'modified' => date('Y-m-d H:i:s', filemtime($filePath))
            ];
        }
    }
    
    return $result;
}

/**
 * Valide et sécurise un chemin
 * @param string $path Chemin à valider
 * @return string Chemin validé
 */
function validatePath($path) {
    // Supprimer les caractères dangereux
    $path = str_replace(['..', '\\'], '', $path);
    
    // S'assurer que le chemin commence par /
    if (substr($path, 0, 1) !== '/') {
        $path = '/' . $path;
    }
    
    // S'assurer que le chemin se termine par /
    if (substr($path, -1) !== '/') {
        $path .= '/';
    }
    
    return $path;
}

/**
 * Récupère les paramètres du serveur
 * @return array Paramètres du serveur
 */
function getServerSettings() {
    return [
        'server_name' => gethostname(),
        'timezone' => date_default_timezone_get(),
        'max_upload_size' => ini_get('upload_max_filesize'),
        'max_execution_time' => ini_get('max_execution_time'),
        'memory_limit' => ini_get('memory_limit')
    ];
}

/**
 * Récupère la liste des fuseaux horaires
 * @return array Liste des fuseaux horaires
 */
function getTimeZones() {
    return [
        'Europe/Paris',
        'Europe/London',
        'America/New_York',
        'America/Los_Angeles',
        'Asia/Tokyo',
        'Australia/Sydney',
        'UTC'
    ];
}

