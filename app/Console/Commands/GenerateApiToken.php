<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class GenerateApiToken extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'api:token {email?} {--name=conduit}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate an API token for a user';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $emailArg = $this->argument('email');
        $email = is_string($emailArg) && $emailArg !== '' ? $emailArg : $this->ask('User email address');
        assert(is_string($email));
        $tokenName = (string) $this->option('name');

        $user = User::where('email', $email)->first();

        if (! $user) {
            $this->error("User with email {$email} not found.");

            $create = $this->confirm('Would you like to create this user?');

            if (! $create) {
                return self::FAILURE;
            }

            $name = $this->ask('User name');
            assert(is_string($name));
            $password = $this->secret('User password');
            assert(is_string($password));

            $user = User::create([
                'name' => $name,
                'email' => $email,
                'password' => bcrypt($password),
            ]);

            $this->info('User created successfully.');
        }

        $token = $user->createToken($tokenName);

        $this->newLine();
        $this->line('API Token Generated:');
        $this->newLine();
        $this->line('  <fg=yellow>'.$token->plainTextToken.'</>');
        $this->newLine();
        $this->line('Add this to your Conduit .env file:');
        $this->line('  <fg=green>FINANCES_API_TOKEN='.$token->plainTextToken.'</>');
        $this->newLine();

        return self::SUCCESS;
    }
}
