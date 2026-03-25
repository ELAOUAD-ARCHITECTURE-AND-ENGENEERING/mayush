<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use App\Notifications\SecurityAlertNotification;
use App\Models\User;

class ClamavService
{
    protected $socket;
    protected $host;
    protected $port;

    public function __construct()
    {
        $this->host = env('CLAMAV_HOST', '127.0.0.1');
        $this->port = env('CLAMAV_PORT', 3310);
    }

    /**
     * Scan a file for viruses.
     * 
     * @param \Illuminate\Http\UploadedFile $file
     * @return bool True if clean, false if infected
     */
    public function scan($file)
    {
        if (env('DISABLE_CLAMAV', true)) {
            return true;
        }

        try {
            $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
            if (!$socket) {
                Log::warning('ClamAV: Could not create socket.');
                return true; // Fail-safe
            }

            if (!@socket_connect($socket, $this->host, $this->port)) {
                Log::warning('ClamAV: Could not connect to daemon at ' . $this->host . ':' . $this->port);
                socket_close($socket);
                return true; // Fail-safe
            }

            // Send INSTREAM command
            socket_send($socket, "nINSTREAM\n", strlen("nINSTREAM\n"), 0);

            $handle = fopen($file->getRealPath(), 'rb');
            while (!feof($handle)) {
                $chunk = fread($handle, 8192);
                $size = pack('N', strlen($chunk));
                socket_send($socket, $size, 4, 0);
                socket_send($socket, $chunk, strlen($chunk), 0);
            }
            fclose($handle);

            // Send end of stream
            socket_send($socket, pack('N', 0), 4, 0);

            $response = "";
            while ($out = socket_read($socket, 1024)) {
                $response .= $out;
            }

            socket_close($socket);

            if (strpos($response, 'FOUND') !== false) {
                Log::error('ClamAV: Virus found in file: ' . $file->getClientOriginalName() . ' - Response: ' . $response);
                
                // Security Evolution: Real-time Alerting
                $admin = User::where('user_type', 'admin')->first();
                if ($admin) {
                    Notification::send($admin, new SecurityAlertNotification([
                        'type' => 'Virus Detected',
                        'message' => "A virus was found in the uploaded file: {$file->getClientOriginalName()}. The upload was blocked.",
                        'level' => 'critical'
                    ]));
                }
                
                return false;
            }

            return true;
        } catch (\Exception $e) {
            Log::error('ClamAV Error: ' . $e->getMessage());
            return true; // Fail-safe
        }
    }
}
