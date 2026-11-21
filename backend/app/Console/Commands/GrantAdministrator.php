<?php

namespace App\Console\Commands;

use App\Models\Role;
use App\Models\User;
use Illuminate\Console\Command;

class GrantAdministrator extends Command
{
    protected $signature = 'app:grant-administrator {email : Email address of the existing account}';

    protected $description = 'Grant the administrator role to an existing account';

    public function handle(): int
    {
        $user = User::query()->where('email', $this->argument('email'))->first();

        if (! $user) {
            $this->error('No account was found with that email address.');

            return self::FAILURE;
        }

        if ($user->status !== 'active') {
            $this->error('The account must be active before it can be granted administrator access.');

            return self::FAILURE;
        }

        if (! $this->confirm("Grant administrator access to {$user->email}?")) {
            $this->comment('No changes were made.');

            return self::SUCCESS;
        }

        $role = Role::query()->where('name', 'administrator')->firstOrFail();
        $user->roles()->syncWithoutDetaching([$role->id]);

        $this->info('Administrator role granted.');

        return self::SUCCESS;
    }
}
