<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\User;

class InstallCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'install';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install the application';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->line('Initiate installation...');
        $bar = $this->output->createProgressBar(3);
        $bar->start();
        
        // jwt secret
        //$this->call('jwt:secret');
        $bar->advance();

        // database migration
        //$this->call('migrate');
        $bar->advance();
        $this->line(PHP_EOL);

        // database seeding 
        $this->info('Create your Admin account:');
        $username = $this->ask('Username', 'Admin');
        $email = $this->ask('Email');
        $password = $this->secret('Password');
        
        User::create([
            'name' => $username,
            'email' => $email,
            'password' => $password
        ]);

        $bar->finish();
        $this->info('Installation complete');
    }

}
