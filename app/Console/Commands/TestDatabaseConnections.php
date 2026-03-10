<?php

namespace App\Console\Commands;

use App\Services\DataQueryService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class TestDatabaseConnections extends Command
{
    protected $signature = 'db:test-connections';
    protected $description = 'Test all database connections';

    public function handle(DataQueryService $queryService): int
    {
        $this->info('Testing database connections...');
        $this->newLine();

        $results = $queryService->testConnections();

        $tableData = [];
        foreach ($results as $connection => $result) {
            $tableData[] = [
                'connection' => $connection,
                'status' => $result['status'],
                'message' => $result['message'],
            ];
        }

        $this->table(['Connection', 'Status', 'Message'], $tableData);

        // Additional info for each connection
        $this->newLine();
        $this->info('Connection Details:');
        
        $connections = [
            'mysql' => [
                'host' => config('database.connections.mysql.host'),
                'database' => config('database.connections.mysql.database'),
                'driver' => 'MySQL',
            ],
            'policy_db' => [
                'host' => config('database.connections.policy_db.host'),
                'database' => config('database.connections.policy_db.database'),
                'driver' => 'SQL Server',
            ],
            'customer_db' => [
                'host' => config('database.connections.customer_db.host'),
                'database' => config('database.connections.customer_db.database'),
                'driver' => 'SQL Server',
            ],
        ];

        foreach ($connections as $name => $config) {
            $this->line("  [{$name}] {$config['driver']} - {$config['host']}/{$config['database']}");
        }

        $this->newLine();
        
        $allOk = collect($results)->every(fn($r) => $r['status'] === 'OK');
        
        if ($allOk) {
            $this->info('✓ All database connections are working!');
            return Command::SUCCESS;
        } else {
            $this->error('✗ Some database connections failed. Please check your .env file.');
            return Command::FAILURE;
        }
    }
}
