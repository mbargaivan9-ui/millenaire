<?php

namespace App\Console\Commands;

use App\Http\Controllers\Teacher\DashboardController;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;

class TestTeacherControllerMethod extends Command
{
    protected $signature = 'test:teacher-controller';
    protected $description = 'Test the teacher dashboard controller method directly';

    public function handle()
    {
        try {
            $this->info('Testing Teacher Dashboard Controller...');
            $this->info('========================================');
            
            // Get teacher
            $user = User::where('email', 'prof.mathematiques@millenniaire.test')->first();
            if (!$user) {
                $this->error('❌ Teacher user not found');
                return 1;
            }
            
            // Manually set auth
            Auth::guard()->setUser($user);
            
            $this->line('✅ Teacher authenticated: ' . $user->name);
            
            // Create controller instance
            $controller = new DashboardController(
                app('App\Services\ReportCardService')
            );
            
            // Try to call the index method
            $this->line('✅ Calling dashboard index method...');
            $response = $controller->index();
            
            if ($response->getStatusCode && $response->getStatusCode() === 500) {
                $this->error('❌ Dashboard returned 500 error');
                return 1;
            }
            
            $this->info('========================================');
            $this->info('✅ CONTROLLER TEST SUCCESSFUL!');
            $this->line('✅ Teacher dashboard is now working!');
            
            return 0;
            
        } catch (\Exception $e) {
            $this->error('❌ Error: ' . $e->getMessage());
            return 1;
        }
    }
}
