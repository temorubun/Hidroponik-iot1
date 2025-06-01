<?php

namespace App\Http\Controllers;

use App\Services\TelegramService;
use Illuminate\Http\Request;

class TelegramController extends Controller
{
    protected $telegramService;

    public function __construct(TelegramService $telegramService)
    {
        $this->telegramService = $telegramService;
    }

    public function testConnection($chatId)
    {
        $result = $this->telegramService->testConnection($chatId);

        return response()->json([
            'success' => $result
        ]);
    }
} 