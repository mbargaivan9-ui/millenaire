<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Response;

class DiagnosticController extends Controller
{
    /**
     * Check system health and database connectivity
     */
    public function health()
    {
        $status = [
            'app' => 'running',
            'database' => $this->checkDatabase(),
            'sessions' => session()->getName(),
            'cache' => config('cache.default'),
        ];

        return response()->json($status);
    }

    /**
     * Check database connectivity
     */
    private function checkDatabase(): string
    {
        try {
            DB::connection()->getPdo();
            return 'connected';
        } catch (\Exception $e) {
            return 'unavailable: ' . $e->getMessage();
        }
    }

    /**
     * Show setup guide
     */
    public function setup()
    {
        return view('diagnostic.setup');
    }
}
