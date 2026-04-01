<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class GenerateApiToken extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'app:generate-api-token {--email= : The email of the user to generate a token for}';

    /**
     * The console command description.
     */
    protected $description = 'Generate a Sanctum API token for n8n integration';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $email = $this->option('email') ?? $this->ask('What is the user email?');

        $user = User::where('email', $email)->first();

        if (! $user) {
            $this->error("User with email [{$email}] not found.");

            return Command::FAILURE;
        }

        $token = $user->createToken('n8n-integration');

        $this->newLine();
        $this->info('API token generated successfully.');
        $this->newLine();
        $this->line("User:  {$user->name} ({$user->email})");
        $this->line("Token: {$token->plainTextToken}");
        $this->newLine();
        $this->warn('Store this token securely — it will not be shown again.');

        return Command::SUCCESS;
    }
}
