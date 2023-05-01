<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use DB;

class RetryMigrationCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migrate:retry';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Retry the migration command if it fails the first time';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {

        $this->info('Migrate database...');

        for ($i = 0; $i < 3; $i++) {

            try {
                DB::connection()->getPDO();
                Artisan::call('migrate --force');
                $this->info('Database migration completed.');
                return 0;
            } catch(\Exception $e) {
                sleep(5 + $i);
                $this->info('Database migration failed. Try again...');
            }
        }

        try {
            DB::connection()->getPDO();
            Artisan::call('migrate --force');
            $this->info('Database migration completed.');
            return 0;
        } catch(\Exception $e) {
            $this->error('Database migration failed.');
            Log::error('Database migration failed.');
        }


        return 0;
    }
}
