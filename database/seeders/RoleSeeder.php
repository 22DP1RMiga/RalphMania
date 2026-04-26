<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            ['name' => 'guest',         'display_name_lv' => 'Viesis',         'display_name_en' => 'Guest',
                'description_lv' => 'Neautorizēts lietotājs', 'description_en' => 'Unauthenticated user'],
            ['name' => 'user',          'display_name_lv' => 'Lietotājs',      'display_name_en' => 'User',
                'description_lv' => 'Reģistrēts lietotājs',  'description_en' => 'Registered user'],
            ['name' => 'administrator', 'display_name_lv' => 'Administrators', 'display_name_en' => 'Administrator',
                'description_lv' => 'Administrators',         'description_en' => 'Administrator'],
            ['name' => 'courier',       'display_name_lv' => 'Kurjers',        'display_name_en' => 'Courier',
                'description_lv' => 'Kurjers',                'description_en' => 'Courier'],
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role['name']], $role);
        }
    }
}
