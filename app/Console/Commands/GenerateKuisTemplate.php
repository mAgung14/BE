<?php

namespace App\Console\Commands;

use App\Exports\KuisTemplateExport;
use Illuminate\Console\Command;
use Maatwebsite\Excel\Facades\Excel;

class GenerateKuisTemplate extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'kuis:generate-template {--force : Overwrite existing file}';

    /**
     * The console command description.
     */
    protected $description = 'Generate file template Excel untuk import kuis ke storage/public/templates/';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $path = 'templates/template-import-kuis.xlsx';

        if (\Illuminate\Support\Facades\Storage::disk('public')->exists($path) && !$this->option('force')) {
            $this->warn("File template sudah ada di storage/app/public/{$path}");
            $this->info("Gunakan --force untuk menimpa: php artisan kuis:generate-template --force");
            return Command::SUCCESS;
        }

        $this->info('Membuat file template Excel kuis...');

        Excel::store(new KuisTemplateExport(), $path, 'public');

        $size = \Illuminate\Support\Facades\Storage::disk('public')->size($path);
        $this->info("✓ Template berhasil dibuat: storage/app/public/{$path}");
        $this->info("  Ukuran: " . number_format($size / 1024, 1) . " KB");
        $this->info("  URL: " . url("storage/{$path}"));

        return Command::SUCCESS;
    }
}
