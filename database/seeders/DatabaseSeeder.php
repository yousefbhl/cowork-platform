<?php

namespace Database\Seeders;

use App\Models\Amenity;
use App\Models\Space;
use App\Models\SpaceWorkspace;
use App\Models\User;
use App\Models\WorkspaceType;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ── Fixed users ──────────────────────────────────
        User::factory()->create([
            'name'     => 'Admin User',
            'email'    => 'admin@cowork.test',
            'password' => bcrypt('password'),
            'role'     => 'admin',
        ]);

        $host = User::factory()->create([
            'name'     => 'Host User',
            'email'    => 'host@cowork.test',
            'password' => bcrypt('password'),
            'role'     => 'host',
        ]);

        User::factory()->create([
            'name'     => 'Customer User',
            'email'    => 'customer@cowork.test',
            'password' => bcrypt('password'),
            'role'     => 'customer',
        ]);

        // ── Workspace types ───────────────────────────────
        $types = [
            ['name' => 'hot_desk',       'label' => 'Hot Desk'],
            ['name' => 'dedicated_desk', 'label' => 'Dedicated Desk'],
            ['name' => 'private_office', 'label' => 'Private Office'],
            ['name' => 'meeting_room',   'label' => 'Meeting Room'],
        ];
        foreach ($types as $type) {
            WorkspaceType::create($type);
        }

        // ── Amenities ─────────────────────────────────────
        $amenities = [
            ['name' => 'WiFi',             'category' => 'internet'],
            ['name' => 'Parking',          'category' => 'facilities'],
            ['name' => 'Coffee',           'category' => 'comfort'],
            ['name' => 'Meeting Rooms',    'category' => 'facilities'],
            ['name' => 'Print / Scan',     'category' => 'facilities'],
            ['name' => 'Air Conditioning', 'category' => 'comfort'],
            ['name' => '24/7 Access',      'category' => 'access'],
            ['name' => 'Reception',        'category' => 'facilities'],
            ['name' => 'Phone Booths',     'category' => 'facilities'],
            ['name' => 'Dog Friendly',     'category' => 'comfort'],
        ];
        foreach ($amenities as $amenity) {
            Amenity::create($amenity);
        }

        // ── Spaces + workspaces ───────────────────────────
        Space::factory(20)->create(['host_id' => $host->id])->each(function ($space) {
            $space->amenities()->attach(
                Amenity::inRandomOrder()->limit(rand(3, 7))->pluck('id')
            );
            SpaceWorkspace::factory(rand(1, 3))->create(['space_id' => $space->id]);
        });
    }
}
