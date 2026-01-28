<?php

namespace App\Http\Controllers;

use App\Models\PAEI\ErplyRequest; 
use Exception;
use Illuminate\Http\Request; 
use Illuminate\Support\Facades\File;

class LogsController extends Controller
{
    public function clearLogs(Request $req)
    {
        try {
            // DB::beginTransaction();
            #Clear Erply Request Logs
            ErplyRequest::truncate();

            #Clear User Logs
            // UserLog::truncate();

            #laravel log file
            $logFilePath = storage_path('logs/laravel.log');
            if (File::exists($logFilePath)) {
                File::delete($logFilePath);
            }
            // DB::commit();
        } catch (Exception $e) {
            // DB::rollBack();
            dd($e);
        }

        return response("Logs Data Cleared Successfully.");
    }
}
