<?php

namespace Database\Seeders;

use App\Enums\PermissionsEnum;
use App\Models\Permission;
use Illuminate\Database\Seeder;
use Spatie\Permission\PermissionRegistrar;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Reset cached roles and permissions.
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        /**
         * Get all permission names.
         * Add default guard name.
         * Prepare to inserting.
         */
        $permissionNames = collect(PermissionsEnum::values())
            ->map(fn ($item) => ['name' => $item, 'guard_name' => 'web']);

        // Create permissions.
        Permission::insert($permissionNames->all());
    }
}
