<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramService
{
    protected $botToken;
    protected $apiBaseUrl = 'https://api.telegram.org/bot';

    public function __construct()
    {
        $this->botToken = env('TELEGRAM_BOT_TOKEN', '8028205316:AAETned3PdmcZd4F05t7XbEKWZJh0nlMcFE');
        
        if (empty($this->botToken)) {
            \Log::error('Telegram bot token not configured');
            throw new \Exception('Telegram bot token not configured');
        }
    }

    public function sendMessage($chatId, $message)
    {
        try {
            \Log::info('Attempting to send Telegram message:', [
                'chat_id' => $chatId,
                'message' => $message
            ]);

            $response = Http::post($this->apiBaseUrl . $this->botToken . '/sendMessage', [
                'chat_id' => $chatId,
                'text' => $message,
                'parse_mode' => 'Markdown'
            ]);

            $result = $response->json();
            
            if ($response->successful() && isset($result['ok']) && $result['ok']) {
                \Log::info('Telegram message sent successfully', [
                    'chat_id' => $chatId,
                    'response' => $result
                ]);
                return true;
            } else {
                \Log::error('Failed to send Telegram message', [
                    'chat_id' => $chatId,
                    'error' => $result['description'] ?? 'Unknown error',
                    'response' => $result
                ]);
                return false;
            }
        } catch (\Exception $e) {
            \Log::error('Error sending Telegram message', [
                'chat_id' => $chatId,
                'message' => $message,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }

    public function formatMessage($template, $data)
    {
        $message = $template;
        
        // Replace all placeholders with actual values
        foreach ($data as $key => $value) {
            $message = str_replace('{' . $key . '}', $value, $message);
        }
        
        return $message;
    }

    public function testConnection($chatId)
    {
        $message = "🔔 Test Connection Successful!\n\n"
                . "Your device is now connected to this Telegram bot.\n"
                . "You will receive notifications here when configured events occur.";
        
        return $this->sendMessage($chatId, $message);
    }

    public function sendAlert($chatId, $deviceName, $pinName, $value, $threshold, $type)
    {
        $message = "⚠️ <b>Alert from {$deviceName}</b>\n\n";
        $message .= "Pin: {$pinName}\n";
        $message .= "Value: {$value}\n";
        $message .= "Threshold: {$threshold}\n";
        $message .= "Type: {$type}";

        return $this->sendMessage($chatId, $message);
    }
} 