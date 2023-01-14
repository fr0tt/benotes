<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Post;

class InstallCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'install {--only-user}';

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

        $createOnlyUser = $this->option('only-user');

        $this->line('Initiate installation...');
        $this->line(PHP_EOL);

        if (!$createOnlyUser) {

            $bar = $this->output->createProgressBar(4);
            $bar->start();
            $this->line(PHP_EOL);

            // APP_KEY
            $this->call('key:generate');
            $bar->advance();

            // jwt secret
            $this->call('jwt:secret');
            $this->line(PHP_EOL);
            $bar->advance();
            $this->line(PHP_EOL);

            // database migration
            $this->call('migrate');
            $this->line(PHP_EOL);
            $bar->advance();
            $this->line(PHP_EOL);
        }

        // database seeding
        $this->info('Create your Admin account:');
        $username = $this->ask('Username', 'Admin');
        $email = $this->ask('Email');
        $password = $this->secret('Password');

        $validator = Validator::make([
            'username' => $username,
            'email' => $email
        ], [
            'username' => 'string',
            'email' => 'email',
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }
            return;
        }

        $user = new User;
        $user->name = $username;
        $user->email = $email;
        $user->password = Hash::make($password);
        $user->permission = User::ADMIN;
        $user->save();

        (new Post())->seedIntroData($user->id);

        if (!$createOnlyUser) {

            $bar->finish();
        }

        $this->line(PHP_EOL);
        $this->info('Installation complete.');
    }
}
