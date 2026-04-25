<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Role;
use App\Models\Administrator;

class DatabaseSeeder extends Seeder
{
    /**
     * Izveido pamata lomas un testēšanas lietotājus.
     *
     * Lietotāji (visi ar paroli: password):
     *   1. superadmin@ralphmania.lv  — Super Admin (RealRoltonsLV)
     *   2. courier@ralphmania.lv     — Kurjers (Courier Master)
     *   3. client@ralphmania.lv      — Klients (TestClient)
     *
     * SVARĪGI: Pēc migrācijas production vidē —
     *   superadmin konta e-pastu un paroli mainīt uz īstajiem datiem!
     */
    public function run(): void
    {
        // ── 1. Lomas ─────────────────────────────────────────────────
        $guestRole = Role::firstOrCreate(
            ['name' => 'guest'],
            [
                'display_name_lv' => 'Viesis',
                'display_name_en' => 'Guest',
                'description_lv'  => 'Neautorizēts lietotājs',
                'description_en'  => 'Unauthenticated user',
            ]
        );

        $userRole = Role::firstOrCreate(
            ['name' => 'user'],
            [
                'display_name_lv' => 'Lietotājs',
                'display_name_en' => 'User',
                'description_lv'  => 'Reģistrēts lietotājs ar pamata tiesībām',
                'description_en'  => 'Registered user with basic permissions',
            ]
        );

        $adminRole = Role::firstOrCreate(
            ['name' => 'administrator'],
            [
                'display_name_lv' => 'Administrators',
                'display_name_en' => 'Administrator',
                'description_lv'  => 'Pilnas sistēmas piekļuves tiesības',
                'description_en'  => 'Full system access permissions',
            ]
        );

        $courierRole = Role::firstOrCreate(
            ['name' => 'courier'],
            [
                'display_name_lv' => 'Kurjers',
                'display_name_en' => 'Courier',
                'description_lv'  => 'Kurjera tiesības piegādēm',
                'description_en'  => 'Courier access for deliveries',
            ]
        );

        // ── 2. Super Admin ────────────────────────────────────────────
        // NOMAINĪT e-pastu uz īsto pēc deployment!
        $superAdmin = User::firstOrCreate(
            ['email' => 'superadmin@ralphmania.lv'],
            [
                'username'          => 'RealRoltonsLV',
                'first_name'        => 'Super',
                'last_name'         => 'Admin',
                'password'          => Hash::make('password'),
                'email_verified_at' => now(),
                'role_id'           => $adminRole->id,
                'is_active'         => true,
                'is_public'         => true,
                'country'           => 'Latvia',
                'city'              => 'Rīga',
            ]
        );

        Administrator::firstOrCreate(
            ['user_id' => $superAdmin->id],
            [
                'full_name'      => 'RealRoltonsLV',
                'permissions'    => null, // null = visas atļaujas (Super Admin)
                'is_super_admin' => true,
            ]
        );

        // ── 3. Kurjers ────────────────────────────────────────────────
        User::firstOrCreate(
            ['email' => 'courier@ralphmania.lv'],
            [
                'username'          => 'CourierMaster',
                'first_name'        => 'Courier',
                'last_name'         => 'Master',
                'password'          => Hash::make('password'),
                'email_verified_at' => now(),
                'role_id'           => $courierRole->id,
                'is_active'         => true,
                'is_public'         => true,
                'country'           => 'Latvia',
                'city'              => 'Rīga',
            ]
        );

        // ── 4. Testa klients ──────────────────────────────────────────
        // Šim kontam var vēlāk piešķirt admin tiesības testēšanai
        User::firstOrCreate(
            ['email' => 'client@ralphmania.lv'],
            [
                'username'          => 'TestClient',
                'first_name'        => 'Test',
                'last_name'         => 'Client',
                'password'          => Hash::make('password'),
                'email_verified_at' => now(),
                'role_id'           => $userRole->id,
                'is_active'         => true,
                'is_public'         => true,
                'country'           => 'Latvia',
                'city'              => 'Rīga',
            ]
        );

        // ── Izvades ziņojums ──────────────────────────────────────────
        $this->command->newLine();
        $this->command->info('✅ Seeding pabeigts! Lietotāji:');
        $this->command->table(
            ['Loma', 'E-pasts', 'Parole', 'Piezīme'],
            [
                ['Super Admin',  'superadmin@ralphmania.lv', 'password', 'NOMAINĪT pirms production!'],
                ['Kurjers',      'courier@ralphmania.lv',    'password', ''],
                ['Klients',      'client@ralphmania.lv',     'password', 'Var piešķirt admin tiesības'],
            ]
        );
        $this->command->newLine();
        $this->command->warn('⚠️  Mainiet superadmin@ralphmania.lv uz īsto e-pastu pirms production deploy!');
        $this->command->newLine();
    }
}
