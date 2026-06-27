<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Mengirim event ke Pusher...\n";

try {
    // Override antrean agar langsung dikirim (sync) tanpa masuk database
    config(['queue.default' => 'sync']);
    
    event(new App\Events\PesertaSubmitKuis(
        guruId: 1, 
        kuisId: 5, 
        judulKuis: 'Ujian Uji Coba Pusher', 
        namaPeserta: 'Siswa Test Pusher', 
        totalSkor: 100, 
        durasi: '10:05', 
        waktuSelesai: now()->toDateTimeString()
    ));
    echo "✅ Event berhasil dikirim!\n";
    echo "👉 Silakan cek tab 'Debug Console' di Pusher Dashboard kamu.\n";
} catch (Exception $e) {
    echo "❌ Gagal mengirim event: " . $e->getMessage() . "\n";
}