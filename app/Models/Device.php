<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\HasUuid;

class Device extends Model
{
    use HasUuid;

    protected $fillable = [
        'name',
        'description',
        'device_key',
        'project_id',
        'wifi_ssid',
        'wifi_password',
        'wifi_qr_code',
        'protocol',
        'protocol_config',
        'is_online',
        'last_online'
    ];

    protected $casts = [
        'is_online' => 'boolean',
        'last_online' => 'datetime',
        'protocol_config' => 'json'
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function pins(): HasMany
    {
        return $this->hasMany(Pin::class);
    }

    public function getProtocolConfigAttribute($value)
    {
        $config = json_decode($value, true) ?? [];
        
        // Mask sensitive information
        if (isset($config['mqtt']['password'])) {
            $config['mqtt']['password'] = str_repeat('*', strlen($config['mqtt']['password']));
        }
        if (isset($config['http']['api_key'])) {
            $config['http']['api_key'] = str_repeat('*', strlen($config['http']['api_key']));
        }
        if (isset($config['websocket']['token'])) {
            $config['websocket']['token'] = str_repeat('*', strlen($config['websocket']['token']));
        }
        if (isset($config['firebase']['api_key'])) {
            $config['firebase']['api_key'] = str_repeat('*', strlen($config['firebase']['api_key']));
        }
        if (isset($config['blynk']['token'])) {
            $config['blynk']['token'] = str_repeat('*', strlen($config['blynk']['token']));
        }
        
        return $config;
    }

    public function setProtocolConfigAttribute($value)
    {
        $this->attributes['protocol_config'] = json_encode($value);
    }

    public function getWebSocketConfig()
    {
        $protocol = request()->isSecure() ? 'wss' : 'ws';
        $host = request()->getHost();
        $port = config('websocket.port', 6001);
        
        return [
            'host' => $host,
            'port' => $port,
            'url' => "{$protocol}://{$host}:{$port}",
            'device_key' => $this->device_key
        ];
    }

    public function getWebSocketUrl()
    {
        $config = $this->getWebSocketConfig();
        return $config['url'];
    }

    public function getLocalIpAddress()
    {
        if (PHP_OS === 'WINNT') {
            // Windows
            $output = [];
            exec('ipconfig', $output);
            foreach ($output as $line) {
                // Cari adapter VirtualBox Host-Only
                if (strpos($line, 'VirtualBox Host-Only') !== false || strpos($line, 'Ethernet adapter VirtualBox Host-Only') !== false) {
                    $inVBoxSection = true;
                    continue;
                }
                
                if (isset($inVBoxSection) && strpos($line, 'IPv4 Address') !== false) {
                    $parts = explode(':', $line);
                    if (count($parts) >= 2) {
                        $ip = trim($parts[1]);
                        if (strpos($ip, '192.168.56.') === 0) {
                            return $ip;
                        }
                    }
                    $inVBoxSection = false;
                }
            }
            
            // Jika tidak ditemukan di VirtualBox, cari IP 192.168.56.x di adapter lain
            foreach ($output as $line) {
                if (strpos($line, 'IPv4 Address') !== false) {
                    $parts = explode(':', $line);
                    if (count($parts) >= 2) {
                        $ip = trim($parts[1]);
                        if (strpos($ip, '192.168.56.') === 0) {
                            return $ip;
                        }
                    }
                }
            }
        } else {
            // Linux/Unix
            $output = shell_exec("ip addr show | grep 'inet ' | grep -v '127.0.0.1'");
            if (preg_match('/inet (192\.168\.56\.[0-9]+)/', $output, $matches)) {
                return $matches[1];
            }
        }

        // Fallback to VirtualBox default host-only adapter IP
        return '192.168.56.1';
    }
} 