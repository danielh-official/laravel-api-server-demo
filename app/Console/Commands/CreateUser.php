<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

use function Laravel\Prompts\password;
use function Laravel\Prompts\text;

class CreateUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:create-user
                            {--name= : The name of the user}
                            {--email= : The email address of the user}
                            {--password= : The password for the user}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new user';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $name = $this->option('name') ?? text(
            label: 'What is the user\'s name?',
            required: true
        );

        $email = $this->option('email') ?? text(
            label: 'What is the user\'s email?',
            required: true,
            validate: fn (string $value) => match (true) {
                ! filter_var($value, FILTER_VALIDATE_EMAIL) => 'The email must be a valid email address.',
                User::where('email', $value)->exists() => 'A user with this email already exists.',
                default => null
            }
        );

        $userPassword = $this->option('password') ?? password(
            label: 'What is the user\'s password?',
            required: true
        );

        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => $userPassword,
        ]);

        $this->components->info("User [{$user->name}] created successfully with ID: {$user->id}");

        return self::SUCCESS;
    }
}
