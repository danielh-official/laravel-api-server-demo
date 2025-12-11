<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

use function Laravel\Prompts\multiselect;
use function Laravel\Prompts\text;

class GiveUserToken extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:give-user-token
                            {user : The ID of the user to give a token to}
                            {--abilities=* : The abilities to grant to the token}
                            {--name= : The name of the token}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Give a user a Sanctum API token';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $userId = $this->argument('user');

        $user = User::find($userId);

        if (! $user) {
            $this->components->error("User with ID {$userId} not found.");

            return self::FAILURE;
        }

        $abilities = $this->option('abilities');

        if (empty($abilities) && $this->input->isInteractive()) {
            $abilities = multiselect(
                label: 'What abilities should this token have?',
                options: ['view-partners', 'edit-partners'],
                required: true
            );
        }

        if (empty($abilities) && ! $this->input->isInteractive()) {
            $this->components->error('The --abilities option is required when running in non-interactive mode.');

            return self::FAILURE;
        }

        $tokenName = $this->option('name') ?? text(
            label: 'What should this token be named?',
            default: 'API Token',
            required: true
        );

        $token = $user->createToken($tokenName, $abilities);

        $this->components->info("Token created successfully for user [{$user->name}]");
        $this->newLine();
        $this->components->twoColumnDetail('Token Name', $tokenName);
        $this->components->twoColumnDetail('Abilities', implode(', ', $abilities));
        $this->newLine();
        $this->components->warn('Please save this token - it will not be shown again:');
        $this->line($token->plainTextToken);

        return self::SUCCESS;
    }
}
