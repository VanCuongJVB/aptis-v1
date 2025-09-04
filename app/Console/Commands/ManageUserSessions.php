<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\UserSession;
use Carbon\Carbon;

class ManageUserSessions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:sessions {action : Action to perform (list, clear-all, clear-user)}
                           {--user= : User ID or email when using clear-user}
                           {--days= : Clear sessions older than specified days}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Manage user device sessions';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $action = $this->argument('action');
        
        switch ($action) {
            case 'list':
                $this->listSessions();
                break;
            case 'clear-all':
                $this->clearAllSessions();
                break;
            case 'clear-user':
                $userId = $this->option('user');
                if (!$userId) {
                    $this->error('User ID or email is required for clear-user action');
                    return 1;
                }
                $this->clearUserSessions($userId);
                break;
            default:
                $this->error('Invalid action. Available actions: list, clear-all, clear-user');
                return 1;
        }
        
        return 0;
    }
    
    /**
     * List all active sessions
     */
    private function listSessions()
    {
        $sessions = UserSession::with('user')
            ->where('is_active', true)
            ->where('last_active_at', '>=', Carbon::now()->subDays(30))
            ->orderBy('user_id')
            ->orderBy('last_active_at', 'desc')
            ->get();
            
        if ($sessions->isEmpty()) {
            $this->info('No active sessions found.');
            return;
        }
        
        $rows = [];
        foreach ($sessions as $session) {
            $rows[] = [
                'user_id' => $session->user_id,
                'user_name' => $session->user->name ?? 'Unknown',
                'user_email' => $session->user->email ?? 'Unknown',
                'device' => $session->device_name,
                'ip_address' => $session->ip_address,
                'last_active' => $session->last_active_at->diffForHumans(),
            ];
        }
        
        $this->table(
            ['User ID', 'Name', 'Email', 'Device', 'IP Address', 'Last Active'],
            $rows
        );
        
        $this->info('Total active sessions: ' . count($rows));
    }
    
    /**
     * Clear all inactive sessions
     */
    private function clearAllSessions()
    {
        $days = $this->option('days') ?? 30;
        
        $count = UserSession::where('last_active_at', '<', Carbon::now()->subDays($days))
            ->delete();
            
        $this->info("Cleared {$count} sessions older than {$days} days.");
    }
    
    /**
     * Clear sessions for a specific user
     */
    private function clearUserSessions($userIdentifier)
    {
        // Find user by ID or email
        $user = is_numeric($userIdentifier) 
            ? User::find($userIdentifier)
            : User::where('email', $userIdentifier)->first();
            
        if (!$user) {
            $this->error("User not found with ID/email: {$userIdentifier}");
            return;
        }
        
        $count = UserSession::where('user_id', $user->id)->delete();
        
        $this->info("Cleared {$count} sessions for user {$user->name} ({$user->email}).");
    }
}
