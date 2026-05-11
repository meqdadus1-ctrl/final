<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class BackupController extends Controller
{
    private string $disk = 'backups';

    public function index()
    {
        $files = [];

        if (Storage::disk($this->disk)->exists('/')) {
            $rawFiles = Storage::disk($this->disk)->files('/');
            foreach ($rawFiles as $file) {
                $name  = basename($file);
                $size  = Storage::disk($this->disk)->size($file);
                $mtime = Storage::disk($this->disk)->lastModified($file);
                $files[] = [
                    'name'     => $name,
                    'size'     => $size,
                    'modified' => $mtime,
                ];
            }
            usort($files, fn($a, $b) => $b['modified'] - $a['modified']);
        }

        return view('backup.index', compact('files'));
    }

    public function run(Request $request)
    {
        try {
            $filename = $this->createBackup();
            return back()->with('success', 'تم إنشاء النسخة الاحتياطية بنجاح: ' . $filename);
        } catch (\Exception $e) {
            return back()->with('error', 'فشل إنشاء النسخة: ' . $e->getMessage());
        }
    }

    public function download(string $file)
    {
        $safeName = basename($file);

        if (!Storage::disk($this->disk)->exists($safeName)) {
            abort(404);
        }

        $path = Storage::disk($this->disk)->path($safeName);
        return response()->download($path, $safeName);
    }

    public function destroy(string $file)
    {
        $safeName = basename($file);

        if (Storage::disk($this->disk)->exists($safeName)) {
            Storage::disk($this->disk)->delete($safeName);
        }

        return back()->with('success', 'تم حذف النسخة الاحتياطية.');
    }

    public static function createBackup(): string
    {
        $dbHost     = config('database.connections.mysql.host', '127.0.0.1');
        $dbPort     = config('database.connections.mysql.port', '3306');
        $dbName     = config('database.connections.mysql.database');
        $dbUser     = config('database.connections.mysql.username');
        $dbPass     = config('database.connections.mysql.password');

        $filename   = 'backup_' . date('Y-m-d_H-i-s') . '.sql.gz';
        $backupDir  = storage_path('app/backups');

        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
        }

        $filePath = $backupDir . '/' . $filename;

        $passArg = $dbPass ? '-p' . escapeshellarg($dbPass) : '';
        $errFile = tempnam(sys_get_temp_dir(), 'mysqldump_err_');

        $cmd = sprintf(
            'bash -c \'set -o pipefail; mysqldump -h %s -P %s -u %s %s --single-transaction --routines --triggers %s 2>%s | gzip > %s\'',
            escapeshellarg($dbHost),
            escapeshellarg($dbPort),
            escapeshellarg($dbUser),
            $passArg,
            escapeshellarg($dbName),
            escapeshellarg($errFile),
            escapeshellarg($filePath)
        );

        exec($cmd, $output, $exitCode);

        $errOutput = $errFile && file_exists($errFile) ? trim(file_get_contents($errFile)) : '';
        @unlink($errFile);

        if ($exitCode !== 0 || !file_exists($filePath) || filesize($filePath) < 10) {
            @unlink($filePath);
            $detail = $errOutput ?: implode(' ', $output) ?: "Exit code: $exitCode";
            throw new \RuntimeException('mysqldump failed: ' . $detail);
        }

        return $filename;
    }
}
