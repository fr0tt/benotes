<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class ResetPassword extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reset:password';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset a password for an user';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {

        $this->info('Type in the email of the user you would like to reset');

        $email = $this->ask('Email');

        $validator = Validator::make([
            'email' => $email
        ], [
            'email' => 'email',
        ]);

        if ($validator->fails()) {
            $this->error($validator->errors()->first());
            return Command::FAILURE;
        }

        $user = User::where('email', $email)->first();

        if ($user === null) {
            $this->error('User does not exist.');
            return Command::FAILURE;
        }

        $this->info('Type in a new password');
        $password = $this->secret('Password');
        $password2 = $this->secret('Re-entered password');

        if ($password !== $password2) {
            $this->error('Re-entered password does not match password');
            return Command::FAILURE;
        }

        $user->password = Hash::make($password);
        $user->save();

        return Command::SUCCESS;
    }

}
