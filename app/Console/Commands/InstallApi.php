<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class InstallApi extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'install:api';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Updating composer.json...');

        // Run a composer update for Sanctum or other required packages
        shell_exec('composer require laravel/sanctum');

        $this->info('Running migrations...');
        $this->call('migrate');

        $this->info('Publishing Sanctum configuration...');
        $this->call('vendor:publish', ['--provider' => 'Laravel\Sanctum\SanctumServiceProvider']);

        $this->info('Installing API setup completed successfully!');
    }


}
