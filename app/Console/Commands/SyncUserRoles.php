<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class SyncUserRoles extends Command
{
    protected $signature = 'users:sync-roles {--dry-run : Report the changes without saving them}';

    protected $description = 'Align Spatie roles with the canonical users.role column';

    /**
     * Staff roles whose authority comes from users.role. Other roles such as
     * hub_owner are kept as additional capabilities.
     *
     * @var array<int, string>
     */
    private const PRIMARY_ROLES = ['member', 'admin', 'super_admin'];

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $changed = 0;

        User::query()->with('roles')->chunkById(100, function ($users) use ($dryRun, &$changed): void {
            foreach ($users as $user) {
                if (blank($user->role)) {
                    continue;
                }

                $extras = $user->roles
                    ->pluck('name')
                    ->reject(fn (string $role) => in_array($role, self::PRIMARY_ROLES, true))
                    ->values()
                    ->all();

                $wanted = array_values(array_unique([...$extras, (string) $user->role]));
                $current = $user->roles->pluck('name')->sort()->values()->all();
                sort($wanted);

                if ($current === $wanted) {
                    continue;
                }

                $changed++;

                $this->line(sprintf(
                    '#%d %s (%s): %s -> %s',
                    $user->id,
                    $user->name,
                    $user->email,
                    implode(', ', $current) ?: 'none',
                    implode(', ', $wanted),
                ));

                if (! $dryRun) {
                    $user->syncRoles($wanted);
                }
            }
        });

        $this->info($dryRun
            ? "{$changed} account(s) would be updated."
            : "{$changed} account(s) updated.");

        return self::SUCCESS;
    }
}
