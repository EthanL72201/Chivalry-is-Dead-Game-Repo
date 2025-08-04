<?php
function benchmark_heavy_task($maxNumber = 10000) {
    $start = microtime(true);
    
    // Heavy computation: find primes up to $maxNumber using a simple sieve
    $primes = [];
    for ($i = 2; $i <= $maxNumber; $i++) {
        $isPrime = true;
        for ($j = 2; $j <= sqrt($i); $j++) {
            if ($i % $j === 0) {
                $isPrime = false;
                break;
            }
        }
        if ($isPrime) {
            $primes[] = $i;
        }
    }
    
    $end = microtime(true);
    $elapsed = $end - $start;
    
    // Assign score inversely proportional to time taken
    // The faster, the higher the score.
    // This formula can be adjusted for your needs.
    $score = round(1000 / max($elapsed, 0.001), 2); // Avoid division by zero
    
    return [
        'time' => $elapsed,
        'score' => $score,
        'primes_found' => count($primes)
    ];
}

function get_cpu_info() {
    $cpu = [
        'name' => 'Unknown',
        'cores' => 0,
        'speed_mhz' => 0
    ];
    
    if (stristr(PHP_OS, 'Linux')) {
        $cpuinfo = @file_get_contents('/proc/cpuinfo');
        $lines = explode("\n", $cpuinfo);
        $props = [];
        $processorCount = 0;
        
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '') continue;
            if (strpos($line, ':') !== false) {
                list($key, $value) = array_map('trim', explode(':', $line, 2));
                $props[$key] = $value;
                if (strtolower($key) === 'processor') {
                    $processorCount++;
                }
            }
        }
        
        $cpu['cores'] = $processorCount ?: 1;
        
        // Use Raspberry Pi Model if present
        if (!empty($props['Model'])) {
            $cpu['name'] = $props['Model'];
            if (!empty($props['Revision'])) {
                $cpu['name'] .= ' Rev ' . $props['Revision'];
            }
        } elseif (!empty($props['model name'])) {
            $cpu['name'] = $props['model name'];
        } elseif (!empty($props['Processor'])) {
            $cpu['name'] = $props['Processor'];
        } elseif (!empty($props['Hardware'])) {
            $cpu['name'] = $props['Hardware'];
            if (!empty($props['Revision'])) {
                $cpu['name'] .= ' Rev ' . $props['Revision'];
            }
        } else {
            $cpu['name'] = 'Linux CPU';
        }
        
        // CPU speed (in MHz), often missing on Raspberry Pi
        if (!empty($props['cpu MHz'])) {
            $cpu['speed_mhz'] = round(floatval($props['cpu MHz']));
        } elseif (file_exists('/sys/devices/system/cpu/cpu0/cpufreq/cpuinfo_max_freq')) {
            $freq = @file_get_contents('/sys/devices/system/cpu/cpu0/cpufreq/cpuinfo_max_freq');
            if ($freq !== false) {
                $cpu['speed_mhz'] = round(intval($freq) / 1000);
            }
        } else {
            // As fallback for Raspberry Pi 3, known CPU speed ~1200 MHz
            if (!empty($props['Model']) && strpos($props['Model'], 'Raspberry Pi 3') !== false) {
                $cpu['speed_mhz'] = 1200;
            }
        }
    } elseif (stristr(PHP_OS, 'WIN')) {
        // Windows unchanged
        $output = [];
        exec('wmic cpu get Name,NumberOfCores,MaxClockSpeed /format:list', $output);
        foreach ($output as $line) {
            if (stripos($line, 'Name=') === 0) {
                $cpu['name'] = trim(substr($line, 5));
            } elseif (stripos($line, 'NumberOfCores=') === 0) {
                $cpu['cores'] = intval(substr($line, 14));
            } elseif (stripos($line, 'MaxClockSpeed=') === 0) {
                $cpu['speed_mhz'] = intval(substr($line, 14));
            }
        }
        if (empty($cpu['speed_mhz'])) {
            $speedOutput = [];
            exec('powershell -command "Get-WmiObject Win32_Processor | Select-Object -ExpandProperty MaxClockSpeed"', $speedOutput);
            if (isset($speedOutput[0]) && is_numeric($speedOutput[0])) {
                $cpu['speed_mhz'] = intval($speedOutput[0]);
            }
        }
    } elseif (stristr(PHP_OS, 'Darwin')) {
        // macOS unchanged
        $name = trim(shell_exec("sysctl -n machdep.cpu.brand_string"));
        $cores = intval(trim(shell_exec("sysctl -n hw.physicalcpu")));
        $speed = floatval(trim(shell_exec("sysctl -n hw.cpufrequency"))) / 1e6;
        if ($name) $cpu['name'] = $name;
        if ($cores) $cpu['cores'] = $cores;
        if ($speed) $cpu['speed_mhz'] = round($speed);
    }
    
    return $cpu;
}

function get_machine_uuid() {
    $uuid = null;
    
    if (stristr(PHP_OS, 'Linux')) {
        // Try /etc/machine-id
        if (file_exists('/etc/machine-id')) {
            $uuid = trim(file_get_contents('/etc/machine-id'));
        }
        
        // Try Raspberry Pi CPU Serial
        if (!$uuid && file_exists('/proc/cpuinfo')) {
            $cpuinfo = file_get_contents('/proc/cpuinfo');
            if (preg_match('/^Serial\s+:\s+([a-fA-F0-9]+)/mi', $cpuinfo, $matches)) {
                $uuid = $matches[1];
            }
        }
        
        // Try MAC address fallback
        if (!$uuid) {
            foreach (['eth0', 'wlan0'] as $iface) {
                $macFile = "/sys/class/net/{$iface}/address";
                if (file_exists($macFile)) {
                    $mac = trim(file_get_contents($macFile));
                    if ($mac && $mac !== '00:00:00:00:00:00') {
                        $uuid = $mac;
                        break;
                    }
                }
            }
        }
    } elseif (stristr(PHP_OS, 'WIN')) {
        // Windows: use wmic or powershell
        $output = [];
        @exec('wmic csproduct get UUID', $output);
        foreach ($output as $line) {
            if (preg_match('/^[a-f0-9\-]{36}$/i', trim($line))) {
                $uuid = trim($line);
                break;
            }
        }
        
        // Fallback to BIOS serial
        if (!$uuid) {
            $output = [];
            @exec('wmic bios get serialnumber', $output);
            foreach ($output as $line) {
                if (preg_match('/^[a-z0-9\-]+$/i', trim($line))) {
                    $uuid = trim($line);
                    break;
                }
            }
        }
    } elseif (stristr(PHP_OS, 'Darwin')) {
        // macOS: use IOPlatformUUID
        $uuid = trim(shell_exec("ioreg -rd1 -c IOPlatformExpertDevice | awk '/IOPlatformUUID/ { print $3; }'"));
        $uuid = trim($uuid, "\"");
    } elseif (file_exists('/system/build.prop') || shell_exec('getprop')) {
        // Android detection
        $props = [
            'ro.boot.serialno',
            'ro.serialno',
            'ro.product.device',
            'ro.hardware',
            'ro.build.id'
        ];
        $values = [];
        foreach ($props as $key) {
            $val = trim(shell_exec("getprop $key"));
            if ($val) $values[] = $val;
        }
        if (!empty($values)) {
            $uuid = implode('-', array_unique($values));
        }
    }
    
    // Fallback to random UUID if nothing found
    if (!$uuid) {
        $uuid = bin2hex(random_bytes(16));
    }
    
    // Normalize to SHA-256 fingerprint
    return hash('sha256', $uuid);
}



// Example usage:
$phpVersion = PHP_VERSION;
$os = PHP_OS;

$cpu = get_cpu_info();
$uuid = get_machine_uuid();
// Example usage:
$result = benchmark_heavy_task(15000);
echo "<b>Time taken:</b> " . $result['time'] . " seconds<br />";
echo "<b>Benchmark score:</b> " . $result['score'] . "<br />";
echo "<b>Primes found:</b> " . $result['primes_found'] . "<br />";
echo "<b>PHP:</b> " . $phpVersion . "<br />";
echo "<b>OS:</b> " . $os . "<br />";
echo "<b>CPU:</b> {$cpu['cores']} x {$cpu['name']} @ {$cpu['speed_mhz']}<br />";
echo "<b>UUID:</b> {$uuid}<br />";