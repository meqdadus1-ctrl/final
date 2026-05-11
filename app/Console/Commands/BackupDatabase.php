<?php

namespace App\Console\Commands;

use App\Http\Controllers\BackupController;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class BackupDatabase extends Command
{
    protected $signature   = 'backup:database {--keep=14 : Number of daily backups to retain}';
    protected $description = 'Create a gzipped MySQL backup and prune old files';

    public function handle(): int
    {
        try {
            $filename = BackupController::createBackup();
            $this->info('Backup created: ' . $filename);

            $this->pruneOld((int) $this->option('keep'));

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Backup failed: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }

    private function pruneOld(int $keep): void
    {
        $files = Storage::disk('backups')->files('/');
        if (count($files) <= $keep) return;

        usort($files, fn($a, $b) =>
            Storage::disk('backups')->lastModified($a) - Storage::disk('backups')->lastModified($b)
        );

        $toDelete = array_slice($files, 0, count($files) - $keep);
        foreach ($toDelete as $file) {
            Storage::disk('backups')->delete($file);
            $this->line('Pruned: ' . basename($file));
        }
    }
}
