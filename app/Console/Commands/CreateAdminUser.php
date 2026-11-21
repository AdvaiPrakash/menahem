<?php

namespace App\Console\Commands;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class CreateAdminUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'admin:create
                            {--name= : The name of the user}
                            {--email= : The email address}
                            {--password= : The account password}
                            {--role=admin : The role: admin, editor, or viewer}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Bootstrap or create an administrator or staff user for the admin panel';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $name = $this->option('name') ?: $this->ask('Enter user name', 'Admin');
        $email = $this->option('email') ?: $this->ask('Enter user email');
        $roleInput = strtolower($this->option('role') ?: $this->choice('Select role', ['admin', 'editor', 'viewer'], 0));
        $password = $this->option('password') ?: $this->secret('Enter password (min 8 chars)');

        $validator = Validator::make([
            'name' => $name,
            'email' => $email,
            'password' => $password,
            'role' => $roleInput,
        ], [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'password' => ['required', 'string', 'min:8'],
            'role' => ['required', 'in:admin,editor,viewer'],
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        $roleEnum = match ($roleInput) {
            'admin' => UserRole::Admin,
            'editor' => UserRole::Editor,
            default => UserRole::Viewer,
        };

        $user = User::updateOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => Hash::make($password),
                'role' => $roleEnum,
            ]
        );

        $this->info("User [{$user->email}] successfully configured with role [{$user->role->value}].");

        return self::SUCCESS;
    }
}
