<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Inertia\Inertia;

class SystemLogController extends Controller
{
    public function index()
    {
        // Path to the Laravel log file
        $logPath = storage_path('logs/laravel.log');

        // Check if the log file exists
        if (!File::exists($logPath)) {
            return response()->view('logs', ['logs' => 'Log file does not exist.']);
        }

        // Get the contents of the log file
        $logs = File::get($logPath);

        return Inertia::render('SystemLogs/Index', [
            'logs' => nl2br(e($logs)),
        ]);
    }

    public function clear()
    {
        $logPath = storage_path('logs/laravel.log');

        if (File::exists($logPath)) {
            File::put($logPath, ''); // Clear the file content
        }

        return redirect()->route('system-logs.index')->with('success', 'Log file cleared.');
    }
}
