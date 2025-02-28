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
    } else {
        // Fallback pour les systèmes qui ne supportent pas sys_getloadavg
        $cmd = "top -bn1 | grep 'Cpu(s)' | sed 's/.*, *\$$[0-9.]*\$$%* id.*/\\1/' | awk '{print 100 - $1}'";
        @exec($cmd, $output);
        if (isset($output[0])) {
            $cpu_usage = round(floatval($output[0]));
        }
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
    } else {
        // Fallback pour les systèmes qui n'ont pas /proc/meminfo
        $cmd = "free | grep Mem | awk '{print $3/$2 * 100.0}'";
        @exec($cmd, $output);
        if (isset($output[0])) {
            $mem_usage = round(floatval($output[0]));
        }
        
        $cmd = "free -m | grep Mem | awk '{print $2}'";
        @exec($cmd, $output);
        if (isset($output[0])) {
            $mem_total = intval($output[0]);
        }
        
        $cmd = "free -m | grep Mem | awk '{print $3}'";
        @exec($cmd, $output);
        if (isset($output[0])) {
            $mem_used = intval($output[0]);
        }
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
    } else {
        // Fallback pour les systèmes qui n'ont pas ce fichier
        $cmd = "vcgencmd measure_temp | cut -d= -f2 | cut -d\"'\" -f1";
        @exec($cmd, $output);
        if (isset($output[0])) {
            $temp = floatval($output[0]);
        }
    }
    
    // Hostname
    $hostname = gethostname();
    
    return [
        'hostname' => $hostname,
        'cpu' => $cpu_usage,
        'memory' => $mem_usage,
        'memory_total' => formatBytes($mem_total * 1024 * 1024),
        'memory_used' => formatBytes($mem_used * 1024 * 1024),
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
    } else {
        // Fallback pour les systèmes qui n'ont pas /proc/uptime
        $cmd = "uptime -p";
        @exec($cmd, $output);
        if (isset($output[0])) {
            $uptime = $output[0];
        } else {
            $uptime = "Information non disponible";
        }
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
    } else {
        // Fallback pour les systèmes qui ne supportent pas sys_getloadavg
        $cmd = "cat /proc/loadavg | awk '{print $1, $2, $3}'";
        @exec($cmd, $output);
        if (isset($output[0])) {
            return $output[0];
        }
        return '0, 0, 0';
    }
}

/**
 * Récupère l'adresse IPv4 du serveur
 * @return string Adresse IPv4
 */
function getIPv4() {
    $ipv4 = '0.0.0.0';
    
    // Méthode 1: hostname -I
    $command = 'hostname -I | awk \'{print $1}\'';
    @exec($command, $output, $return_var);
    if ($return_var === 0 && !empty($output[0])) {
        $ipv4 = $output[0];
    } else {
        // Méthode 2: ifconfig
        $command = 'ifconfig | grep -Eo \'inet (addr:)?([0-9]*\\.){3}[0-9]*\' | grep -Eo \'([0-9]*\\.){3}[0-9]*\' | grep -v \'127.0.0.1\'';
        @exec($command, $output, $return_var);
        if ($return_var === 0 && !empty($output[0])) {
            $ipv4 = $output[0];
        } else {
            // Méthode 3: ip addr
            $command = 'ip addr | grep -Eo \'inet (addr:)?([0-9]*\\.){3}[0-9]*\' | grep -Eo \'([0-9]*\\.){3}[0-9]*\' | grep -v \'127.0.0.1\'';
            @exec($command, $output, $return_var);
            if ($return_var === 0 && !empty($output[0])) {
                $ipv4 = $output[0];
            }
        }
    }
    
    return $ipv4;
}

/**
 * Récupère l'adresse IPv6 du serveur
 * @return string Adresse IPv6
 */
function getIPv6() {
    $ipv6 = '::';
    
    // Méthode 1: hostname -I
    $command = 'hostname -I | awk \'{print $2}\'';
    @exec($command, $output, $return_var);
    if ($return_var === 0 && !empty($output[0]) && filter_var($output[0], FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
        $ipv6 = $output[0];
    } else {
        // Méthode 2: ifconfig
        $command = 'ifconfig | grep -Eo \'inet6 (addr:)?([0-9a-f]*:){7}[0-9a-f]*\' | grep -Eo \'([0-9a-f]*:){7}[0-9a-f]*\' | grep -v \'::1\'';
        @exec($command, $output, $return_var);
        if ($return_var === 0 && !empty($output[0])) {
            $ipv6 = $output[0];
        } else {
            // Méthode 3: ip addr
            $command = 'ip -6 addr | grep -Eo \'inet6 (addr:)?([0-9a-f]*:){7}[0-9a-f]*\' | grep -Eo \'([0-9a-f]*:){7}[0-9a-f]*\' | grep -v \'::1\'';
            @exec($command, $output, $return_var);
            if ($return_var === 0 && !empty($output[0])) {
                $ipv6 = $output[0];
            }
        }
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
    @exec($command, $output, $return_var);
    if ($return_var === 0 && !empty($output[0])) {
        $users = intval($output[0]);
    } else {
        // Fallback
        $command = 'w -h | wc -l';
        @exec($command, $output, $return_var);
        if ($return_var === 0 && !empty($output[0])) {
            $users = intval($output[0]);
        }
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
    @exec($command, $output, $return_var);
    if ($return_var === 0 && !empty($output[0])) {
        $public_ip = $output[0];
    } else {
        // Fallback
        $command = 'curl -s https://ifconfig.me';
        @exec($command, $output, $return_var);
        if ($return_var === 0 && !empty($output[0])) {
            $public_ip = $output[0];
        } else {
            // Autre fallback
            $command = 'curl -s https://ipinfo.io/ip';
            @exec($command, $output, $return_var);
            if ($return_var === 0 && !empty($output[0])) {
                $public_ip = $output[0];
            }
        }
    }
    
    // Adresse MAC
    $mac = '00:00:00:00:00:00';
    $command = 'cat /sys/class/net/eth0/address';
    @exec($command, $output, $return_var);
    if ($return_var === 0 && !empty($output[0])) {
        $mac = $output[0];
    } else {
        // Fallback
        $command = 'ifconfig eth0 | grep -o -E "([0-9a-f]{2}:){5}([0-9a-f]{2})"';
        @exec($command, $output, $return_var);
        if ($return_var === 0 && !empty($output[0])) {
            $mac = $output[0];
        } else {
            // Autre fallback
            $command = 'ip link show eth0 | grep -o -E "([0-9a-f]{2}:){5}([0-9a-f]{2})"';
            @exec($command, $output, $return_var);
            if ($return_var === 0 && !empty($output[0])) {
                $mac = $output[0];
            }
        }
    }
    
    // Passerelle
    $gateway = '0.0.0.0';
    $command = 'ip route | grep default | awk \'{print $3}\'';
    @exec($command, $output, $return_var);
    if ($return_var === 0 && !empty($output[0])) {
        $gateway = $output[0];
    } else {
        // Fallback
        $command = 'route -n | grep "^0.0.0.0" | awk \'{print $2}\'';
        @exec($command, $output, $return_var);
        if ($return_var === 0 && !empty($output[0])) {
            $gateway = $output[0];
        }
    }
    
    // Masque de sous-réseau
    $netmask = '255.255.255.0';
    $command = 'ifconfig eth0 | grep netmask | awk \'{print $4}\'';
    @exec($command, $output, $return_var);
    if ($return_var === 0 && !empty($output[0])) {
        $netmask = $output[0];
    } else {
        // Fallback
        $command =   {
        $netmask = $output[0];
    } else {
        // Fallback
        $command = 'ip addr show eth0 | grep -w inet | awk \'{print $4}\'';
        @exec($command, $output, $return_var);
        if ($return_var === 0 && !empty($output[0])) {
            $netmask = $output[0];
        }
    }
    
    // DNS
    $dns = '8.8.8.8, 8.8.4.4';
    $command = 'cat /etc/resolv.conf | grep nameserver | awk \'{print $2}\'';
    @exec($command, $output, $return_var);
    if ($return_var === 0 && !empty($output)) {
        $dns = implode(', ', $output);
    } else {
        // Fallback
        $command = 'nmcli dev show | grep DNS | awk \'{print $2}\'';
        @exec($command, $output, $return_var);
        if ($return_var === 0 && !empty($output)) {
            $dns = implode(', ', $output);
        }
    }
    
    // Interfaces réseau
    $interfaces = [];
    $command = 'ip -o addr show | grep -v "lo" | awk \'{print $2, $4, $9}\'';
    @exec($command, $output, $return_var);
    if ($return_var === 0) {
        foreach ($output as $line) {
            $parts = explode(' ', $line . ' up');
            $name = isset($parts[0]) ? $parts[0] : '';
            $ip = isset($parts[1]) ? str_replace('/', '', $parts[1]) : '';
            $status = isset($parts[2]) ? $parts[2] : '';
            
            $interfaces[] = [
                'name' => $name,
                'ip' => $ip,
                'status' => $status == 'UP' ? 'up' : 'down',
                'speed' => getInterfaceSpeed($name)
            ];
        }
    } else {
        // Fallback
        $command = 'ifconfig | grep -E "^[a-z]" | awk \'{print $1}\'';
        @exec($command, $output, $return_var);
        if ($return_var === 0) {
            foreach ($output as $iface) {
                $iface = str_replace(':', '', $iface);
                if ($iface == 'lo') continue;
                
                $ip_cmd = 'ifconfig ' . $iface . ' | grep -Eo \'inet (addr:)?([0-9]*\\.){3}[0-9]*\' | grep -Eo \'([0-9]*\\.){3}[0-9]*\'';
                @exec($ip_cmd, $ip_output, $ip_return_var);
                $ip = ($ip_return_var === 0 && !empty($ip_output[0])) ? $ip_output[0] : '';
                
                $status_cmd = 'ifconfig ' . $iface . ' | grep -o "UP"';
                @exec($status_cmd, $status_output, $status_return_var);
                $status = ($status_return_var === 0 && !empty($status_output[0])) ? 'up' : 'down';
                
                $interfaces[] = [
                    'name' => $iface,
                    'ip' => $ip,
                    'status' => $status,
                    'speed' => getInterfaceSpeed($iface)
                ];
            }
        }
    }
    
    // Connexions actives
    $connections = [];
    $command = 'netstat -tn | grep ESTABLISHED | head -5';
    @exec($command, $output, $return_var);
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
    } else {
        // Fallback
        $command = 'ss -tn | grep ESTAB | head -5';
        @exec($command, $output, $return_var);
        if ($return_var === 0) {
            foreach ($output as $line) {
                $parts = preg_split('/\s+/', trim($line));
                if (count($parts) >= 5) {
                    $connections[] = [
                        'protocol' => 'tcp',
                        'local' => $parts[3],
                        'remote' => $parts[4],
                        'state' => $parts[1]
                    ];
                }
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
 * Récupère la vitesse d'une interface réseau
 * @param string $interface Nom de l'interface
 * @return string Vitesse de l'interface
 */
function getInterfaceSpeed($interface) {
    $speed = '100 Mbps'; // Valeur par défaut
    
    $command = 'ethtool ' . $interface . ' | grep Speed | awk \'{print $2}\'';
    @exec($command, $output, $return_var);
    if ($return_var === 0 && !empty($output[0])) {
        $speed = $output[0];
    }
    
    return $speed;
}

/**
 * Récupère la liste des périphériques connectés
 * @return array Liste des périphériques
 */
function getConnectedDevices() {
    $devices = [];
    
    // Essayer d'obtenir les périphériques via arp
    $command = 'arp -a';
    @exec($command, $output, $return_var);
    if ($return_var === 0) {
        foreach ($output as $line) {
            if (preg_match('/$$([0-9.]+)$$ at ([0-9a-f:]+)/', $line, $matches)) {
                $ip = $matches[1];
                $mac = $matches[2];
                
                // Essayer d'obtenir le nom d'hôte
                $hostname_cmd = 'host ' . $ip . ' | grep "domain name pointer" | cut -d " " -f 5';
                @exec($hostname_cmd, $hostname_output, $hostname_return_var);
                $name = ($hostname_return_var === 0 && !empty($hostname_output[0])) ? 
                    rtrim($hostname_output[0], '.') : 'Périphérique-' . str_replace('.', '-', $ip);
                
                // Déterminer le type de périphérique (basé sur le préfixe MAC)
                $type = determineDeviceType($mac);
                
                // Vérifier si le périphérique est en ligne
                $ping_cmd = 'ping -c 1 -W 1 ' . $ip . ' > /dev/null 2>&1 && echo online || echo offline';
                @exec($ping_cmd, $ping_output, $ping_return_var);
                $status = ($ping_return_var === 0 && isset($ping_output[0]) && $ping_output[0] === 'online') ? 'online' : 'offline';
                
                $devices[] = [
                    'name' => $name,
                    'ip' => $ip,
                    'mac' => $mac,
                    'type' => $type,
                    'status' => $status,
                    'last_seen' => date('Y-m-d H:i:s')
                ];
            }
        }
    }
    
    // Si aucun périphérique n'est trouvé, ajouter des exemples
    if (empty($devices)) {
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
    }
    
    return $devices;
}

/**
 * Détermine le type de périphérique à partir de l'adresse MAC
 * @param string $mac Adresse MAC
 * @return string Type de périphérique
 */
function determineDeviceType($mac) {
    // Préfixes MAC connus pour différents fabricants
    $prefixes = [
        'Apple' => ['00:03:93', '00:05:02', '00:0A:27', '00:0A:95', '00:0D:93', '00:1B:63', '00:1C:B3', '00:1D:4F', '00:1E:52', '00:1E:C2', '00:1F:5B', '00:1F:F3', '00:21:E9', '00:22:41', '00:23:12', '00:23:32', '00:23:6C', '00:23:DF', '00:24:36', '00:25:00', '00:25:4B', '00:25:BC', '00:26:08', '00:26:4A', '00:26:B0', '00:26:BB', '00:30:65', '00:3E:E1', '00:50:E4', '00:56:CD', '00:61:71', '00:6D:52', '00:88:65', '00:B3:62', '00:C6:10', '00:DB:70', '00:F4:B9', '04:0C:CE', '04:15:52', '04:1E:64', '04:26:65', '04:52:F3', '04:54:53', '04:69:F8', '04:D3:CF', '04:DB:56', '04:E5:36', '04:F1:3E', '04:F7:E4', '08:00:07', '08:66:98', '08:70:45', '08:74:02', '08:86:3B', '08:F4:AB', '0C:15:39', '0C:30:21', '0C:3E:9F', '0C:4D:E9', '0C:51:01', '0C:74:C2', '0C:77:1A', '0C:BC:9F', '0C:D7:46', '10:1C:0C', '10:40:F3', '10:41:7F', '10:93:E9', '10:9A:DD', '10:DD:B1', '14:10:9F', '14:5A:05', '14:8F:C6', '14:99:E2', '14:BD:61', '18:20:32', '18:34:51', '18:65:90', '18:81:0E', '18:9E:FC', '18:AF:61', '18:E7:F4', '18:EE:69', '18:F6:43', '1C:1A:C0', '1C:36:BB', '1C:5C:F2', '1C:91:48', '1C:9E:46', '1C:AB:A7', '1C:E6:2B', '20:3C:AE', '20:78:F0', '20:7D:74', '20:9B:CD', '20:A2:E4', '20:AB:37', '20:C9:D0', '20:EE:28', '24:1E:EB', '24:24:0E', '24:5B:A7', '24:A0:74', '24:A2:E1', '24:AB:81', '24:E3:14', '24:F0:94', '24:F6:77', '28:37:37', '28:5A:EB', '28:6A:B8', '28:6A:BA', '28:CF:DA', '28:CF:E9', '28:E0:2C', '28:E1:4C', '28:E7:CF', '28:F0:76', '28:FF:3C', '2C:1F:23', '2C:20:0B', '2C:33:61', '2C:B4:3A', '2C:BE:08', '2C:F0:A2', '2C:F0:EE', '30:10:E4', '30:35:AD', '30:63:6B', '30:90:AB', '30:F7:C5', '34:08:BC', '34:12:98', '34:15:9E', '34:36:3B', '34:51:C9', '34:A3:95', '34:AB:37', '34:C0:59', '34:E2:FD', '38:0F:4A', '38:48:4C', '38:66:F0', '38:71:DE', '38:B5:4D', '38:C9:86', '38:CA:DA', '3C:07:54', '3C:15:C2', '3C:2E:F9', '3C:2E:FF', '3C:AB:8E', '3C:D0:F8', '3C:E0:72', '40:30:04', '40:33:1A', '40:3C:FC', '40:4D:7F', '40:6C:8F', '40:9C:28', '40:A6:D9', '40:B3:95', '40:D3:2D', '44:00:10', '44:2A:60', '44:4C:0C', '44:D8:84', '44:FB:42', '48:3B:38', '48:43:7C', '48:4B:AA', '48:60:BC', '48:74:6E', '48:A1:95', '48:BF:6B', '48:D7:05', '48:E9:F1', '4C:32:75', '4C:57:CA', '4C:74:BF', '4C:8D:79', '4C:B1:99', '50:32:37', '50:7A:55', '50:82:D5', '50:EA:D6', '54:26:96', '54:33:CB', '54:4E:90', '54:72:4F', '54:99:63', '54:9F:13', '54:AE:27', '54:E4:3A', '54:EA:A8', '58:1F:AA', '58:40:4E', '58:55:CA', '58:7F:57', '58:B0:35', '58:E2:8F', '5C:59:48', '5C:8D:4E', '5C:95:AE', '5C:96:9D', '5C:97:F3', '5C:AD:CF', '5C:F5:DA', '5C:F7:E6', '5C:F9:38', '60:03:08', '60:33:4B', '60:69:44', '60:92:17', '60:9A:C1', '60:A3:7D', '60:C5:47', '60:D9:C7', '60:F4:45', '60:F8:1D', '60:FA:CD', '60:FB:42', '60:FE:C5', '64:20:0C', '64:76:BA', '64:9A:BE', '64:A3:CB', '64:A5:C3', '64:B0:A6', '64:B9:E8', '64:E6:82', '68:09:27', '68:5B:35', '68:64:4B', '68:96:7B', '68:9C:70', '68:A8:6D', '68:AE:20', '68:D9:3C', '68:DB:CA', '68:FB:7E', '6C:19:C0', '6C:3E:6D', '6C:40:08', '6C:70:9F', '6C:72:E7', '6C:8D:C1', '6C:94:F8', '6C:96:CF', '6C:AB:31', '6C:C2:6B', '70:11:24', '70:14:A6', '70:3E:AC', '70:48:0F', '70:56:81', '70:70:0D', '70:73:CB', '70:81:EB', '70:A2:B3', '70:CD:60', '70:DE:E2', '70:E7:2C', '70:EC:E4', '70:F0:87', '74:1B:B2', '74:81:14', '74:8D:08', '74:E1:B6', '74:E2:F5', '78:31:C1', '78:32:1B', '78:3A:84', '78:4F:43', '78:67:D7', '78:6C:1C', '78:7B:8A', '78:7E:61', '78:88:6D', '78:9F:70', '78:A3:E4', '78:CA:39', '7C:01:91', '7C:04:D0', '7C:11:BE', '7C:50:49', '7C:6D:62', '7C:6D:F8', '7C:C3:A1', '7C:C5:37', '7C:D1:C3', '7C:F0:5F', '7C:FA:DF', '80:00:6E', '80:49:71', '80:92:9F', '80:B0:3D', '80:BE:05', '80:D6:05', '80:E6:50', '80:EA:96', '80:ED:2C', '84:29:99', '84:38:35', '84:41:67', '84:78:8B', '84:85:06', '84:89:AD', '84:8E:0C', '84:A1:34', '84:A4:23', '84:FC:AC', '84:FC:FE', '88:19:08', '88:1F:A1', '88:53:95', '88:63:DF', '88:66:A5', '88:6B:6E', '88:AE:07', '88:C6:63', '88:CB:87', '88:E8:7F', '8C:00:6D', '8C:29:37', '8C:2D:AA', '8C:58:77', '8C:7B:9D', '8C:7C:92', '8C:85:90', '8C:8E:F2', '8C:8F:E9', '8C:FA:BA', '90:27:E4', '90:3C:92', '90:60:F1', '90:72:40', '90:84:0D', '90:8D:6C', '90:B0:ED', '90:B2:1F', '90:B9:31', '90:C1:C6', '90:DD:5D', '90:FD:61', '94:94:26', '94:BF:2D', '94:E9:6A', '94:F6:A3', '98:00:C6', '98:01:A7', '98:03:D8', '98:10:E8', '98:5A:EB', '98:9E:63', '98:B8:E3', '98:D6:BB', '98:E0:D9', '98:F0:AB', '98:FE:94', '9C:04:EB', '9C:20:7B', '9C:29:3F', '9C:35:EB', '9C:4F:DA', '9C:84:BF', '9C:8B:A0', '9C:E3:3F', '9C:F3:87', '9C:F4:8E', 'A0:18:28', 'A0:3B:E3', 'A0:4E:A7', 'A0:56:F3', 'A0:99:9B', 'A0:D7:95', 'A0:ED:CD', 'A4:31:35', 'A4:5E:60', 'A4:67:06', 'A4:B1:97', 'A4:B8:05', 'A4:C3:61', 'A4:D1:8C', 'A4:D1:D2', 'A4:D9:31', 'A4:E9:75', 'A4:F1:E8', 'A8:20:66', 'A8:5B:78', 'A8:5C:2C', 'A8:66:7F', 'A8:86:DD', 'A8:88:08', 'A8:8E:24', 'A8:96:8A', 'A8:BB:CF', 'A8:BE:27', 'A8:FA:D8', 'AC:29:3A', 'AC:3C:0B', 'AC:61:EA', 'AC:7F:3E', 'AC:87:A3', 'AC:BC:32', 'AC:CF:5C', 'AC:E4:B5', 'AC:FD:EC', 'B0:19:C6', 'B0:34:95', 'B0:48:1A', 'B0:65:BD', 'B0:70:2D', 'B0:9F:BA', 'B0:CA:68', 'B4:18:D1', 'B4:4B:D2', 'B4:8B:19', 'B4:9C:DF', 'B4:F0:AB', 'B4:F6:1C', 'B8:09:8A', 'B8:17:C2', 'B8:41:A4', 'B8:44:D9', 'B8:53:AC', 'B8:63:4D', 'B8:78:2E', 'B8:8D:12', 'B8:C1:11', 'B8:C7:5D', 'B8:E8:56', 'B8:F6:B1', 'B8:FF:61', 'BC:3B:AF', 'BC:4C:C4', 'BC:52:B7', 'BC:54:36', 'BC:67:78', 'BC:6C:21', 'BC:92:6B', 'BC:9F:EF', 'BC:A9:20', 'BC:EC:5D', 'C0:1A:DA', 'C0:63:94', 'C0:84:7A', 'C0:9F:42', 'C0:A5:3E', 'C0:CC:F8', 'C0:CE:CD', 'C0:D0:12', 'C0:F2:FB', 'C4:2C:03', 'C4:61:8B', 'C4:84:66', 'C4:B3:01', 'C8:1E:E7', 'C8:2A:14', 'C8:33:4B', 'C8:3C:85', 'C8:69:CD', 'C8:6F:1D', 'C8:85:50', 'C8:B5:B7', 'C8:BC:C8', 'C8:E0:EB', 'C8:F6:50', 'CC:08:8D', 'CC:08:E0', 'CC:20:E8', 'CC:25:EF', 'CC:29:F5', 'CC:44:63', 'CC:78:5F', 'CC:C7:60', 'D0:03:4B', 'D0:23:DB', 'D0:25:98', 'D0:33:11', 'D0:4B:1A', 'D0:81:7A', 'D0:A6:37', 'D0:C5:F3', 'D0:D2:B0', 'D0:E1:40', 'D4:61:9D', 'D4:9A:20', 'D4:DC:CD', 'D4:F4:6F', 'D8:00:4D', 'D8:1D:72', 'D8:30:62', 'D8:8F:76', 'D8:96:95', 'D8:9E:3F', 'D8:A2:5E', 'D8:BB:2C', 'D8:CF:9C', 'D8:D1:CB', 'DC:0C:5C', 'DC:2B:2A', 'DC:2B:61', 'DC:37:14', 'DC:41:5F', 'DC:86:D8', 'DC:9B:9C', 'DC:A4:CA', 'DC:A9:04', 'E0:5F:45', 'E0:66:78', 'E0:AC:CB', 'E0:B5:2D', 'E0:B9:BA', 'E0:C7:67', 'E0:C9:7A', 'E0:F5:C6', 'E0:F8:47', 'E4:25:E7', 'E4:2B:34', 'E4:8B:7F', 'E4:98:D1', 'E4:9A:79', 'E4:9A:DC', 'E4:C6:3D', 'E4:CE:8F', 'E4:E0:A6', 'E4:E4:AB', 'E8:04:0B', 'E8:06:88', 'E8:8D:28', 'E8:B2:AC', 'EC:35:86', 'EC:85:2F', 'EC:AD:B8', 'F0:18:98', 'F0:24:75', 'F0:79:60', 'F0:98:9D', 'F0:99:BF', 'F0:B0:E7', 'F0:C1:F1', 'F0:CB:A1', 'F0:D1:A9', 'F0:DB:E2', 'F0:DB:F8', 'F0:DC:E2', 'F0:F6:1C', 'F4:0F:24', 'F4:1B:A1', 'F4:31:C3', 'F4:5C:89', 'F4:F1:5A', 'F4:F9:51', 'F8:03:77', 'F8:1E:DF', 'F8:27:93', 'F8:62:AA', 'F8:95:C7', 'FC:25:3F', 'FC:D8:48', 'FC:E9:98', 'FC:FC:48'],
        'Samsung' => ['00:00:F0', '00:07:AB', '00:12:47', '00:12:FB', '00:13:77', '00:15:99', '00:15:B9', '00:16:32', '00:16:6B', '00:16:6C', '00:16:DB', '00:17:C9', '00:17:D5', '00:18:AF', '00:1A:8A', '00:1B:98', '00:1C:43', '00:1D:25', '00:1D:F6', '00:1E:7D', '00:1F:CC', '00:1F:CD', '00:21:19', '00:21:4C', '00:21:D1', '00:21:D2', '00:23:39', '00:23:3A', '00:23:99', '00:23:C2', '00:23:D6', '00:23:D7', '00:24:54', '00:24:90', '00:24:91', '00:24:E9', '00:25:38', '00:25:66', '00:25:67', '00:26:37', '00:26:5D', '00:26:5F', '00:E0:64', '00:E3:B2', '04:18:0F', '04:1B:BA', '04:FE:31', '08:08:C2', '08:37:3D', '08:3D:88', '08:78:08', '08:8C:2C', '08:C6:B3', '08:EE:8B', '08:FC:88', '08:FD:0E', '0C:14:20', '0C:71:5D', '0C:89:10', '0C:A8:A7', '0C:B3:19', '0C:DF:A4', '10:07:B6', '10:1D:C0', '10:30:47', '10:3B:59', '10:77:B1', '10:92:66', '10:D3:8A', '10:D5:42', '14:1F:78', '14:32:D1', '  '10:30:47', '10:3B:59', '10:77:B1', '10:92:66', '10:D3:8A', '10:D5:42', '14:1F:78', '14:32:D1', '14:49:E0', '14:56:8E', '14:5A:83', '14:7D:C5', '14:89:FD', '14:96:E5', '14:9F:3C', '14:A3:64', '18:16:C9', '18:1E:B0', '18:21:95', '18:22:7E', '18:26:66', '18:3A:2D', '18:3F:47', '18:46:17', '18:67:B0', '18:83:31', '18:89:5B', '18:E2:C2', '1C:23:2C', '1C:3A:DE', '1C:5A:3E', '1C:62:B8', '1C:66:AA', '1C:AF:05', '20:13:E0', '20:2D:07', '20:55:31', '20:64:32', '20:6E:9C', '20:D3:90', '20:D5:BF', '24:4B:03', '24:4B:81', '24:92:0E', '24:C6:96', '24:DB:AC', '24:F5:AA', '28:27:BF', '28:39:5E', '28:83:35', '28:98:7B', '28:BA:B5', '28:CC:01', '2C:0E:3D', '2C:44:01', '2C:AE:2B', '2C:BA:BA', '30:19:66', '30:C7:AE', '30:CB:F8', '30:CD:A7', '30:D6:C9', '34:14:5F', '34:23:BA', '34:2D:0D', '34:31:11', '34:8A:7B', '34:AA:8B', '34:BE:00', '34:C3:AC', '38:01:95', '38:0A:94', '38:0B:40', '38:16:D1', '38:2D:D1', '38:2D:E8', '38:94:96', '38:AA:3C', '38:D4:0B', '38:EC:E4', '3C:5A:37', '3C:62:00', '3C:8B:FE', '3C:A1:0D', '3C:BB:FD', '40:0E:85', '40:16:3B', '40:D3:AE', '44:4E:1A', '44:6D:6C', '44:78:3E', '44:F4:59', '48:13:7E', '48:27:EA', '48:44:F7', '48:49:C7', '4C:3C:16', '4C:A5:6D', '4C:BC:A5', '50:01:BB', '50:32:75', '50:3D:A1', '50:85:69', '50:92:B9', '50:9E:A7', '50:A4:C8', '50:B7:C3', '50:C8:E5', '50:F0:D3', '50:F5:20', '50:FC:9F', '54:40:AD', '54:88:0E', '54:92:BE', '54:9B:12', '54:BD:79', '54:F2:01', '54:FA:3E', '58:B1:0F', '58:C3:8B', '5C:0A:5B', '5C:2E:59', '5C:3C:27', '5C:49:7D', '5C:51:88', '5C:99:60', '5C:A3:9D', '5C:E8:EB', '5C:F6:DC', '60:6B:BD', '60:77:71', '60:8F:5C', '60:A1:0A', '60:A4:D0', '60:AF:6D', '60:C5:AD', '60:D0:A9', '64:1C:AE', '64:6C:B2', '64:77:91', '64:B3:10', '64:B8:53', '68:05:71', '68:27:37', '68:48:98', '68:EB:AE', '6C:2F:2C', '6C:83:36', '6C:B7:49', '6C:F3:73', '70:28:8B', '70:5A:AC', '70:F9:27', '74:45:8A', '74:5F:00', '78:00:9E', '78:1F:DB', '78:25:AD', '78:40:E4', '78:47:1D', '78:52:1A', '78:59:5E', '78:9E:D0', '78:A8:73', '78:AB:BB', '78:BD:BC', '78:C3:E9', '78:F7:BE', '7C:0B:C6', '7C:1C:68', '7C:2E:DD', '7C:64:56', '7C:78:7E', '7C:91:22', '7C:F8:54', '7C:F9:0E', '80:18:A7', '80:4E:81', '80:57:19', '80:65:6D', '84:0B:2D', '84:11:9E', '84:25:DB', '84:2E:27', '84:51:81', '84:55:A5', '84:98:66', '88:32:9B', '88:75:98', '88:9B:39', '8C:71:F8', '8C:77:12', '8C:BF:A6', '8C:C8:CD', '90:06:28', '90:18:7C', '94:01:C2', '94:35:0A', '94:51:03', '94:63:D1', '94:76:B7', '94:7B:E7', '94:D7:71', '98:0C:82', '98:1D:FA', '98:39:8E', '98:52:B1', '98:83:89', '9C:02:98', '9C:3A:AF', '9C:65:B0', '9C:D3:5B', 'A0:07:98', 'A0:0B:BA', 'A0:21:95', 'A0:60:90', 'A0:75:91', 'A0:82:1F', 'A0:B4:A5', 'A0:CB:FD', 'A4:07:B6', 'A4:84:31', 'A8:06:00', 'A8:16:D0', 'A8:51:5B', 'A8:7C:01', 'A8:9F:BA', 'A8:F2:74', 'AC:36:13', 'AC:5A:14', 'AC:5F:3E', 'AC:C3:3A', 'AC:EE:9E', 'B0:C4:E7', 'B0:D0:9C', 'B0:DF:3A', 'B0:EC:71', 'B4:3A:28', 'B4:62:93', 'B4:74:43', 'B4:79:A7', 'B4:BF:F6', 'B8:57:D8', 'B8:5A:73', 'B8:5E:7B', 'B8:6C:E8', 'B8:BB:AF', 'B8:C6:8E', 'BC:14:85', 'BC:20:A4', 'BC:44:86', 'BC:47:60', 'BC:72:B1', 'BC:76:5E', 'BC:79:AD', 'BC:85:1F', 'BC:B1:F3', 'BC:E6:3F', 'C0:11:73', 'C0:65:99', 'C0:89:97', 'C0:BD:D1', 'C0:D3:C0', 'C4:42:02', 'C4:50:06', 'C4:57:6E', 'C4:62:EA', 'C4:73:1E', 'C4:88:E5', 'C4:AE:12', 'C8:14:79', 'C8:19:F7', 'C8:38:70', 'C8:7E:75', 'C8:A8:23', 'C8:BA:94', 'CC:05:1B', 'CC:07:AB', 'CC:3A:61', 'CC:6E:A4', 'CC:F9:E8', 'CC:FE:3C', 'D0:17:6A', 'D0:22:BE', 'D0:25:44', 'D0:59:E4', 'D0:66:7B', 'D0:87:E2', 'D0:B1:28', 'D0:C1:B1', 'D0:DF:C7', 'D0:FC:CC', 'D4:7A:E2', 'D4:87:D8', 'D4:88:90', 'D4:AE:05', 'D4:E6:B7', 'D4:E8:DB', 'D8:08:31', 'D8:31:CF', 'D8:57:EF', 'D8:58:D7', 'D8:68:C3', 'D8:90:E8', 'D8:C4:E9', 'DC:44:B6', 'DC:66:72', 'DC:74:A8', 'DC:CF:96', 'E0:99:71', 'E0:AA:96', 'E0:CB:EE', 'E0:DB:10', 'E4:12:1D', 'E4:32:CB', 'E4:40:E2', 'E4:58:B8', 'E4:5D:75', 'E4:7C:F9', 'E4:92:FB', 'E4:B0:21', 'E4:E0:C5', 'E4:F8:EF', 'E8:03:9A', 'E8:11:32', 'E8:3A:12', 'E8:4E:84', 'E8:93:09', 'E8:B4:C8', 'E8:E5:D6', 'EC:10:7B', 'EC:1F:72', 'EC:9B:F3', 'EC:E0:9B', 'F0:08:F1', 'F0:5A:09', 'F0:5B:7B', 'F0:5F:B7', 'F0:6B:CA', 'F0:72:8C', 'F0:E7:7E', 'F4:0E:22', 'F4:42:8F', 'F4:7B:5E', 'F4:9F:54', 'F4:D9:FB', 'F8:04:2E', 'F8:3F:51', 'F8:77:B8', 'F8:84:F2', 'F8:D0:BD', 'FC:00:12', 'FC:19:10', 'FC:42:03', 'FC:8F:90', 'FC:A1:3E', 'FC:C7:34'],
        'Microsoft' => ['00:03:FF', '00:0D:3A', '00:12:5A', '00:15:5D', '00:17:FA', '00:1D:D8', '00:22:48', '00:25:AE', '00:50:F2', '28:18:78', '30:59:B7', '58:82:A8', '60:45:BD', '7C:1E:52', '7C:ED:8D', '98:5F:D3', 'B4:AE:2B', 'B8:3E:59', 'C8:3F:26', 'D8:D3:85', 'E4:98:D6', 'EC:59:E7']
    ];
    
    // Vérifier si l'adresse MAC correspond à un fabricant connu
    $mac_prefix = substr($mac, 0, 8);
    foreach ($prefixes as $manufacturer => $prefix_list) {
        if (in_array($mac_prefix, $prefix_list)) {
            switch ($manufacturer) {
                case 'Apple':
                    return 'tablet'; // Supposons que c'est un iPad
                case 'Samsung':
                    return 'smartphone'; // Supposons que c'est un smartphone Samsung
                case 'Microsoft':
                    return 'laptop'; // Supposons que c'est un ordinateur portable
                default:
                    return 'desktop';
            }
        }
    }
    
    // Par défaut, considérer comme un ordinateur de bureau
    return 'desktop';
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

