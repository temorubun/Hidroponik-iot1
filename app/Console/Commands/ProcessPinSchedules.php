<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Pin;
use App\Services\TelegramService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ProcessPinSchedules extends Command
{
    protected $signature = 'schedule:process-pins';
    protected $description = 'Process scheduled pin operations';

    protected $telegramService;

    public function __construct(TelegramService $telegramService)
    {
        parent::__construct();
        $this->telegramService = $telegramService;
    }

    public function handle()
    {
        try {
            // Ambil semua pin digital output
            $pins = Pin::where('type', 'digital_output')->get();
            
            foreach ($pins as $pin) {
                $this->cekDanUpdatePin($pin);
            }
            
        } catch (\Exception $e) {
            Log::error('Error: ' . $e->getMessage());
        }
    }

    protected function cekDanUpdatePin(Pin $pin)
    {
        // Skip jika tidak ada jadwal atau tidak aktif
        if (!isset($pin->settings['schedule']) || !$pin->settings['schedule']['enabled']) {
            return;
        }

        $jadwal = $pin->settings['schedule'];
        $sekarang = now();
        
        // Konversi waktu mulai ke timestamp hari ini
        $waktuMulai = Carbon::parse($jadwal['on'])->setDate(
            $sekarang->year, 
            $sekarang->month, 
            $sekarang->day
        );
        
        // Jika menggunakan pengulangan
        if (isset($jadwal['repeat_hourly']) && $jadwal['repeat_hourly']) {
            $interval = (int)$jadwal['hourly_interval']; // dalam menit
            
            // Hitung selisih dari waktu mulai awal sampai sekarang dalam menit
            $selisihMenit = $sekarang->diffInMinutes($waktuMulai);
            
            // Jika sudah melewati waktu mulai, cari waktu mulai berikutnya
            if ($sekarang > $waktuMulai) {
                // Hitung berapa kali interval telah berlalu
                $siklusKe = ceil($selisihMenit / $interval);
                // Set waktu mulai ke siklus berikutnya
                $waktuMulai->addMinutes($siklusKe * $interval);
            }
        }
        
        // Hitung waktu selesai
        $waktuSelesai = (clone $waktuMulai)->addMinutes($jadwal['duration']);
        
        // Cek apakah sekarang waktunya nyala
        $harusNyala = $sekarang->between($waktuMulai, $waktuSelesai);
        
        Log::info('Status Jadwal Pin: ' . $pin->name, [
            'waktu_sekarang' => $sekarang->format('Y-m-d H:i:s'),
            'waktu_mulai' => $waktuMulai->format('Y-m-d H:i:s'),
            'waktu_selesai' => $waktuSelesai->format('Y-m-d H:i:s'),
            'harus_nyala' => $harusNyala,
            'nilai_sekarang' => $pin->value
        ]);
        
        // Update status pin jika perlu
        if ($harusNyala && $pin->value == 0) {
            $pin->update(['value' => 1, 'last_update' => $sekarang]);
            $this->kirimNotifikasi($pin, 'NYALA');
            
        } elseif (!$harusNyala && $pin->value == 1) {
            $pin->update(['value' => 0, 'last_update' => $sekarang]);
            $this->kirimNotifikasi($pin, 'MATI');
        }
    }

    protected function kirimNotifikasi(Pin $pin, $status)
    {
        if (!isset($pin->settings['alerts']['enabled']) || !$pin->settings['alerts']['enabled']) {
            return;
        }

        $pesan = "⏰ Jadwal Otomatis:\n"
               . "Perangkat: {$pin->device->name}\n"
               . "Pin: {$pin->name}\n"
               . "Status: {$status}\n"
               . "Jam: " . now()->format('H:i');
        
        $this->telegramService->sendMessage(
            $pin->settings['alerts']['telegram_chat_id'],
            $pesan
        );
    }
} 