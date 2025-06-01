<?php

namespace App\Http\Controllers;

use App\Models\Pin;
use App\Models\PinLog;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ChartController extends Controller
{
    public function index()
    {
        $pins = Pin::all();
        return view('charts.index', compact('pins'));
    }

    public function getData(Request $request, Pin $pin)
    {
        $timeRange = $request->input('range', '24h');
        $startDate = match($timeRange) {
            '24h' => now()->subHours(24),
            '7d' => now()->subDays(7),
            '30d' => now()->subDays(30),
            default => now()->subHours(24)
        };

        $logs = PinLog::where('pin_id', $pin->id)
            ->where('created_at', '>=', $startDate)
            ->orderBy('created_at')
            ->get()
            ->map(function($log) {
                return [
                    'x' => $log->created_at->timestamp * 1000, // Convert to milliseconds for JS
                    'y' => $log->value,
                    'raw' => $log->raw_value
                ];
            });

        return response()->json([
            'name' => $pin->name,
            'type' => $pin->type,
            'data' => $logs
        ]);
    }
} 