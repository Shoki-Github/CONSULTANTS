<?php

namespace Database\Seeders;

use App\Models\ForumCategory;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $permissions = collect([
            'manage_users',
            'manage_roles',
            'manage_content',
            'manage_consultations',
            'moderate_forum',
            'create_forum_posts',
            'view_audit_logs',
        ])->mapWithKeys(fn (string $name) => [
            $name => Permission::query()->firstOrCreate(['name' => $name]),
        ]);

        $rolePermissions = [
            'super_administrator' => $permissions->keys()->all(),
            'administrator' => ['manage_users', 'manage_content', 'manage_consultations', 'moderate_forum', 'create_forum_posts'],
            'content_manager' => ['manage_content'],
            'forum_moderator' => ['moderate_forum', 'create_forum_posts'],
            'registered_member' => ['create_forum_posts'],
        ];

        foreach ($rolePermissions as $name => $rolePermissionNames) {
            $role = Role::query()->firstOrCreate(['name' => $name]);
            $role->permissions()->sync(
                collect($rolePermissionNames)->map(fn (string $permission) => $permissions[$permission]->id)->all(),
            );
        }

        ForumCategory::query()->firstOrCreate(
            ['slug' => 'general-discussion'],
            [
                'name' => 'General discussion',
                'description' => 'Share questions and ideas with the community.',
                'is_active' => true,
                'sort_order' => 1,
            ],
        );
    }
}
