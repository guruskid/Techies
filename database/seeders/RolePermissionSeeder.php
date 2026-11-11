<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'members.view', 'members.create', 'members.update', 'members.suspend',
            'hubs.view', 'hubs.approve', 'hubs.reject', 'hubs.update',
            'chapters.manage', 'events.create', 'events.publish', 'events.manage',
            'opportunities.publish', 'content.manage', 'reports.export', 'settings.manage', 'roles.manage',
        ];
        foreach ($permissions as $p) {
            Permission::firstOrCreate(['name' => $p]);
        }
        $roles = [
            'super_admin' => $permissions,
            'admin' => $permissions,
            'content_manager' => ['content.manage'],
            'membership_manager' => ['members.view', 'members.update', 'members.suspend'],
            'events_manager' => ['events.create', 'events.publish', 'events.manage'],
            'hub_manager' => ['hubs.view', 'hubs.approve', 'hubs.reject', 'hubs.update'],
            'chapter_coordinator' => ['chapters.manage'],
            'hub_owner' => [],
            'hub_administrator' => [],
            'hub_editor' => [],
            'member' => [],
        ];
        foreach ($roles as $name => $perms) {
            $role = Role::firstOrCreate(['name' => $name]);
            if ($perms) {
                $role->syncPermissions($perms);
            }
        }
        // super_admin gets all
        Role::findByName('super_admin')->syncPermissions($permissions);
    }
}
