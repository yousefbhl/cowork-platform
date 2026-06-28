<?php

namespace Database\Seeders;

use App\Models\Amenity;
use App\Models\Booking;
use App\Models\HostProfile;
use App\Models\Space;
use App\Models\SpacePhoto;
use App\Models\SpaceWorkspace;
use App\Models\User;
use App\Models\WorkspaceType;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DemoSeeder extends Seeder
{
    // ── Public entry point ────────────────────────────────────────────────────

    public function run(): void
    {
        // ── 1. Users ─────────────────────────────────────────────────────────
        User::factory()->create([
            'name'     => 'Admin User',
            'email'    => 'admin@cowork.test',
            'password' => bcrypt('password'),
            'role'     => 'admin',
        ]);

        $host1 = User::factory()->create([
            'name'     => 'Youssef Alami',
            'email'    => 'host@cowork.test',
            'password' => bcrypt('password'),
            'role'     => 'host',
        ]);
        $host1->hostProfile()->create([
            'business_name' => 'Atlas Workspaces',
            'description'   => 'Premium coworking spaces across Casablanca\'s key business districts.',
            'phone'         => '+212 522 100 200',
            'website'       => 'https://atlasworkspaces.ma',
        ]);

        $host2 = User::factory()->create([
            'name'     => 'Nadia Tahiri',
            'email'    => 'host2@cowork.test',
            'password' => bcrypt('password'),
            'role'     => 'host',
        ]);
        $host2->hostProfile()->create([
            'business_name' => 'Medina Coworking',
            'description'   => 'Affordable, community-first workspaces in Casablanca and beyond.',
            'phone'         => '+212 522 300 400',
            'website'       => 'https://medinacoworking.ma',
        ]);

        $customer = User::factory()->create([
            'name'     => 'Karim Mansouri',
            'email'    => 'customer@cowork.test',
            'password' => bcrypt('password'),
            'role'     => 'customer',
        ]);

        $extraCustomers = collect([
            ['name' => 'Sara Benali',    'email' => 'sara@cowork.test'],
            ['name' => 'Mehdi Ouali',    'email' => 'mehdi@cowork.test'],
            ['name' => 'Fatima Zahra',   'email' => 'fatima@cowork.test'],
        ])->map(fn($data) => User::factory()->create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => bcrypt('password'),
            'role'     => 'customer',
        ]));

        [$sara, $mehdi, $fatima] = $extraCustomers;

        // ── 2. Workspace types ────────────────────────────────────────────────
        $hot     = WorkspaceType::create(['name' => 'hot_desk',       'label' => 'Hot Desk']);
        $desk    = WorkspaceType::create(['name' => 'dedicated_desk', 'label' => 'Dedicated Desk']);
        $office  = WorkspaceType::create(['name' => 'private_office', 'label' => 'Private Office']);
        $meeting = WorkspaceType::create(['name' => 'meeting_room',   'label' => 'Meeting Room']);

        // ── 3. Amenities ──────────────────────────────────────────────────────
        $wifi   = Amenity::create(['name' => 'WiFi',             'category' => 'internet']);
        $park   = Amenity::create(['name' => 'Parking',          'category' => 'facilities']);
        $coffee = Amenity::create(['name' => 'Coffee',           'category' => 'comfort']);
        $rooms  = Amenity::create(['name' => 'Meeting Rooms',    'category' => 'facilities']);
        $print  = Amenity::create(['name' => 'Print / Scan',     'category' => 'facilities']);
        $ac     = Amenity::create(['name' => 'Air Conditioning', 'category' => 'comfort']);
        $access = Amenity::create(['name' => '24/7 Access',      'category' => 'access']);
        $recep  = Amenity::create(['name' => 'Reception',        'category' => 'facilities']);
        $phone  = Amenity::create(['name' => 'Phone Booths',     'category' => 'facilities']);
        $dog    = Amenity::create(['name' => 'Dog Friendly',     'category' => 'comfort']);

        // ── 4. Spaces ─────────────────────────────────────────────────────────

        // ────────── HOST 1 — Atlas Workspaces ─────────────────────────────────

        // 1. Atlas Hub Maarif — published
        $atlasHub = $this->createSpace($host1->id, [
            'title'       => 'Atlas Hub Maarif',
            'description' => "Located in the heart of Maarif, Casablanca's most vibrant business district, Atlas Hub offers a premium coworking experience across two bright floors. High-speed fibre internet, ergonomic standing desks, and a rooftop terrace make it a favourite for local startups and remote teams. The building is a five-minute walk from several tram stops and is surrounded by restaurants and cafés.",
            'address'     => '38 Rue Ibnou Majid, Maarif',
            'city'        => 'Casablanca',
            'country'     => 'Morocco',
            'lat'         => 33.5678,
            'lng'         => -7.6281,
            'status'      => 'published',
            'rating_avg'  => 4.80,
            'reviews_count' => 47,
        ], 'https://images.unsplash.com/photo-1497366754035-f200968a6e72?auto=format&fit=crop&w=1200&q=80');

        $atlasHub->amenities()->attach([$wifi->id, $coffee->id, $park->id, $ac->id, $recep->id]);

        $wsAtlasHot    = $atlasHub->workspaces()->create(['workspace_type_id' => $hot->id,    'capacity' => 20, 'price_daily' => 150,  'price_monthly' => 2800,  'currency' => 'MAD', 'is_active' => true]);
        $wsAtlasOffice = $atlasHub->workspaces()->create(['workspace_type_id' => $office->id, 'capacity' => 4,  'price_daily' => 700,  'price_monthly' => 12000, 'currency' => 'MAD', 'is_active' => true]);

        // 2. Casa Nearshore Coworking — published
        $nearshore = $this->createSpace($host1->id, [
            'title'       => 'Casa Nearshore Coworking',
            'description' => "Designed for tech companies and nearshore teams, Casa Nearshore sits inside Casablanca's leading technology park in Sidi Maarouf. Enjoy soundproofed phone booths, six equipped meeting rooms, and direct access to the park's shuttle service. Monthly plans include 24/7 card-key access and a dedicated support team on-site.",
            'address'     => 'Technopark Route de Nouasseur, Sidi Maarouf',
            'city'        => 'Casablanca',
            'country'     => 'Morocco',
            'lat'         => 33.5250,
            'lng'         => -7.6640,
            'status'      => 'published',
            'rating_avg'  => 4.65,
            'reviews_count' => 32,
        ], 'https://images.unsplash.com/photo-1521737604893-d14cc237f11d?auto=format&fit=crop&w=1200&q=80');

        $nearshore->amenities()->attach([$wifi->id, $access->id, $rooms->id, $print->id, $ac->id, $phone->id]);

        $wsNearHot     = $nearshore->workspaces()->create(['workspace_type_id' => $hot->id,     'capacity' => 15, 'price_daily' => 130,  'price_monthly' => 2400,  'currency' => 'MAD', 'is_active' => true]);
        $wsNearDesk    = $nearshore->workspaces()->create(['workspace_type_id' => $desk->id,    'capacity' => 8,  'price_daily' => 220,  'price_monthly' => 4000,  'currency' => 'MAD', 'is_active' => true]);
        $wsNearMeeting = $nearshore->workspaces()->create(['workspace_type_id' => $meeting->id, 'capacity' => 12, 'price_hourly' => 200, 'price_daily' => 1400,   'currency' => 'MAD', 'is_active' => true]);

        // 3. Gauthier Creative Loft — published
        $gauthier = $this->createSpace($host1->id, [
            'title'       => 'Gauthier Creative Loft',
            'description' => "A beautifully converted loft in the leafy Gauthier neighbourhood, this space attracts designers, writers, and creative consultants. Exposed brick walls, natural light from floor-to-ceiling windows, and a curated library set the tone. Two private podcast and call rooms are available on a first-come basis.",
            'address'     => '12 Boulevard de la Résistance, Gauthier',
            'city'        => 'Casablanca',
            'country'     => 'Morocco',
            'lat'         => 33.5894,
            'lng'         => -7.6178,
            'status'      => 'published',
            'rating_avg'  => 4.72,
            'reviews_count' => 28,
        ], 'https://images.unsplash.com/photo-1524758631624-e2822e304c36?auto=format&fit=crop&w=1200&q=80');

        $gauthier->amenities()->attach([$wifi->id, $coffee->id, $ac->id, $phone->id]);

        $wsGauthHot    = $gauthier->workspaces()->create(['workspace_type_id' => $hot->id,    'capacity' => 12, 'price_daily' => 160,  'price_monthly' => 3000,  'currency' => 'MAD', 'is_active' => true]);
        $wsGauthOffice = $gauthier->workspaces()->create(['workspace_type_id' => $office->id, 'capacity' => 2,  'price_daily' => 800,  'price_monthly' => 14000, 'currency' => 'MAD', 'is_active' => true]);

        // 4. Twin Center Executive Suites — published
        $twinCenter = $this->createSpace($host1->id, [
            'title'       => 'Twin Center Executive Suites',
            'description' => "A prestigious address in the iconic Twin Center towers, Casablanca's most recognisable skyline landmark. Executive private offices with panoramic city views, a concierge service, and fully equipped meeting rooms for up to 20 people. Ideal for consulting firms, law offices, and client-facing teams who need to impress.",
            'address'     => 'Tour Zénith, Boulevard Zerktouni, Maarif',
            'city'        => 'Casablanca',
            'country'     => 'Morocco',
            'lat'         => 33.5820,
            'lng'         => -7.6352,
            'status'      => 'published',
            'rating_avg'  => 4.90,
            'reviews_count' => 61,
        ], 'https://images.unsplash.com/photo-1556761175-5973dc0f32e7?auto=format&fit=crop&w=1200&q=80');

        $twinCenter->amenities()->attach([$wifi->id, $recep->id, $ac->id, $park->id, $rooms->id]);

        $wsTwinOffice  = $twinCenter->workspaces()->create(['workspace_type_id' => $office->id,  'capacity' => 6,  'price_daily' => 900,  'price_monthly' => 16000, 'currency' => 'MAD', 'is_active' => true]);
        $wsTwinMeeting = $twinCenter->workspaces()->create(['workspace_type_id' => $meeting->id, 'capacity' => 10, 'price_hourly' => 250, 'price_daily' => 1600,   'currency' => 'MAD', 'is_active' => true]);

        // 5. Anfa Workspace Club — DRAFT (pending review)
        $anfaClub = $this->createSpace($host1->id, [
            'title'       => 'Anfa Workspace Club',
            'description' => "A boutique members-only workspace in the peaceful Anfa district, minutes from Boulevard d'Anfa. Fully air-conditioned open floor with sit-stand desks, a dedicated relaxation lounge, and a dog-friendly policy that makes it unique in Casablanca. Launching Q3 2026 — early members get 20% off the first three months.",
            'address'     => '7 Rue Copernic, Anfa',
            'city'        => 'Casablanca',
            'country'     => 'Morocco',
            'lat'         => 33.5960,
            'lng'         => -7.6524,
            'status'      => 'draft',
            'rating_avg'  => null,
            'reviews_count' => 0,
        ], 'https://images.unsplash.com/photo-1497366216548-37526070297c?auto=format&fit=crop&w=1200&q=80');

        $anfaClub->amenities()->attach([$wifi->id, $coffee->id, $dog->id, $ac->id]);
        $anfaClub->workspaces()->create(['workspace_type_id' => $hot->id, 'capacity' => 10, 'price_daily' => 140, 'price_monthly' => 2600, 'currency' => 'MAD', 'is_active' => true]);

        // ────────── HOST 2 — Medina Coworking ─────────────────────────────────

        // 6. Marina Offices Ain Diab — published
        $marina = $this->createSpace($host2->id, [
            'title'       => 'Marina Offices Ain Diab',
            'description' => "Overlooking the Atlantic from the Ain Diab corniche, Marina Offices combines an unbeatable ocean-front location with top-tier infrastructure. Large windows flood the space with light; the building has its own parking garage, a café on the ground floor, and a rooftop that hosts monthly networking evenings.",
            'address'     => 'Marina Casablanca, Boulevard de la Corniche, Ain Diab',
            'city'        => 'Casablanca',
            'country'     => 'Morocco',
            'lat'         => 33.5959,
            'lng'         => -7.6891,
            'status'      => 'published',
            'rating_avg'  => 4.78,
            'reviews_count' => 53,
        ], 'https://images.unsplash.com/photo-1604328698692-f76ea9498e76?auto=format&fit=crop&w=1200&q=80');

        $marina->amenities()->attach([$wifi->id, $park->id, $ac->id, $access->id, $recep->id]);

        $wsMarinaHot    = $marina->workspaces()->create(['workspace_type_id' => $hot->id,    'capacity' => 25, 'price_daily' => 120,  'price_monthly' => 2200,  'currency' => 'MAD', 'is_active' => true]);
        $wsMarinaOffice = $marina->workspaces()->create(['workspace_type_id' => $office->id, 'capacity' => 3,  'price_daily' => 600,  'price_monthly' => 10500, 'currency' => 'MAD', 'is_active' => true]);

        // 7. Technopark Innovation Hub — published
        $technopark = $this->createSpace($host2->id, [
            'title'       => 'Technopark Innovation Hub',
            'description' => "Embedded within the Casablanca Technopark ecosystem, this hub gives you proximity to Morocco's most vibrant tech scene. On-site incubator support, weekly demo days, a fab lab with 3D printers, and a bustling community of builders and engineers make it far more than just a desk.",
            'address'     => 'Technopark Casablanca, Route de Nouasseur',
            'city'        => 'Casablanca',
            'country'     => 'Morocco',
            'lat'         => 33.5238,
            'lng'         => -7.6648,
            'status'      => 'published',
            'rating_avg'  => 4.55,
            'reviews_count' => 39,
        ], 'https://images.unsplash.com/photo-1600508774634-4e11d34730e2?auto=format&fit=crop&w=1200&q=80');

        $technopark->amenities()->attach([$wifi->id, $access->id, $print->id, $coffee->id, $ac->id]);

        $wsTechHot  = $technopark->workspaces()->create(['workspace_type_id' => $hot->id,  'capacity' => 30, 'price_daily' => 140, 'price_monthly' => 2600, 'currency' => 'MAD', 'is_active' => true]);
        $wsTechDesk = $technopark->workspaces()->create(['workspace_type_id' => $desk->id, 'capacity' => 10, 'price_daily' => 200, 'price_monthly' => 3600, 'currency' => 'MAD', 'is_active' => true]);

        // 8. Médina Collaborative Space — published
        $medina = $this->createSpace($host2->id, [
            'title'       => 'Médina Collaborative Space',
            'description' => "Tucked into a beautifully restored riad in the historic Médina, this collaborative space blends traditional Moroccan architecture with modern amenities. Hand-painted zellige tiles, a central courtyard, and intimate low-capacity work zones create a deeply focused atmosphere unlike any other space in the city.",
            'address'     => '3 Derb El Halfaoui, Médina',
            'city'        => 'Casablanca',
            'country'     => 'Morocco',
            'lat'         => 33.5917,
            'lng'         => -7.6138,
            'status'      => 'published',
            'rating_avg'  => 4.68,
            'reviews_count' => 22,
        ], 'https://images.unsplash.com/photo-1542744173-8e7e53415bb0?auto=format&fit=crop&w=1200&q=80');

        $medina->amenities()->attach([$wifi->id, $coffee->id, $rooms->id, $ac->id]);

        $wsMedHot  = $medina->workspaces()->create(['workspace_type_id' => $hot->id,  'capacity' => 18, 'price_daily' => 110, 'price_monthly' => 2000, 'currency' => 'MAD', 'is_active' => true]);
        $wsMedDesk = $medina->workspaces()->create(['workspace_type_id' => $desk->id, 'capacity' => 6,  'price_daily' => 180, 'price_monthly' => 3200, 'currency' => 'MAD', 'is_active' => true]);

        // 9. Palmier Business Center — DRAFT (pending review)
        $palmier = $this->createSpace($host2->id, [
            'title'       => 'Palmier Business Center',
            'description' => "A premium full-service business centre in the upscale Palmier district, offering furnished private offices and a dedicated reception team. Hot beverage stations, a stocked kitchen, high-end video-conferencing rooms, and a quiet reading lounge are included in all plans. Opening autumn 2026.",
            'address'     => '54 Boulevard Ghandi, Palmier',
            'city'        => 'Casablanca',
            'country'     => 'Morocco',
            'lat'         => 33.5742,
            'lng'         => -7.6230,
            'status'      => 'draft',
            'rating_avg'  => null,
            'reviews_count' => 0,
        ], 'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?auto=format&fit=crop&w=1200&q=80');

        $palmier->amenities()->attach([$wifi->id, $park->id, $recep->id, $ac->id]);
        $palmier->workspaces()->create(['workspace_type_id' => $office->id,  'capacity' => 4, 'price_daily' => 550, 'price_monthly' => 9500,  'currency' => 'MAD', 'is_active' => true]);
        $palmier->workspaces()->create(['workspace_type_id' => $meeting->id, 'capacity' => 8, 'price_hourly' => 180, 'price_daily' => 1200,   'currency' => 'MAD', 'is_active' => true]);

        // ── 5. Bookings ───────────────────────────────────────────────────────

        // ─── customer@cowork.test — 3 bookings ───────────────────────────────

        // Booking 1: Karim — Atlas Hub Hot Desk — June 10-14 (past, completed)
        $b1 = $this->createBooking($customer->id, $wsAtlasHot->id, '2026-06-10', '2026-06-14', 'completed', 600.00);
        $this->insertBookingDays($b1->id, $wsAtlasHot->id, '2026-06-10', '2026-06-14');

        // Booking 2: Karim — Marina Private Office — May 20-24 (past, completed)
        $b2 = $this->createBooking($customer->id, $wsMarinaOffice->id, '2026-05-20', '2026-05-24', 'completed', 2400.00);
        $this->insertBookingDays($b2->id, $wsMarinaOffice->id, '2026-05-20', '2026-05-24');

        // Booking 3: Karim — Gauthier Hot Desk — July 7-10 (upcoming, confirmed)
        $b3 = $this->createBooking($customer->id, $wsGauthHot->id, '2026-07-07', '2026-07-10', 'confirmed', 480.00);
        $this->insertBookingDays($b3->id, $wsGauthHot->id, '2026-07-07', '2026-07-10');

        // ─── Sara Benali — 3 bookings ─────────────────────────────────────────

        // Booking 4: Sara — Nearshore Dedicated Desk — June 2-5 (past, completed)
        $b4 = $this->createBooking($sara->id, $wsNearDesk->id, '2026-06-02', '2026-06-05', 'completed', 660.00);
        $this->insertBookingDays($b4->id, $wsNearDesk->id, '2026-06-02', '2026-06-05');

        // Booking 5: Sara — Technopark Hot Desk — July 15-19 (pending)
        $b5 = $this->createBooking($sara->id, $wsTechHot->id, '2026-07-15', '2026-07-19', 'pending', 560.00);
        $this->insertBookingDays($b5->id, $wsTechHot->id, '2026-07-15', '2026-07-19');

        // Booking 6: Sara — Atlas Private Office — Aug 10-13 (confirmed)
        $b6 = $this->createBooking($sara->id, $wsAtlasOffice->id, '2026-08-10', '2026-08-13', 'confirmed', 2100.00);
        $this->insertBookingDays($b6->id, $wsAtlasOffice->id, '2026-08-10', '2026-08-13');

        // ─── Mehdi Ouali — 3 bookings ────────────────────────────────────────

        // Booking 7: Mehdi — Twin Center Private Office — July 22-25 (confirmed)
        $b7 = $this->createBooking($mehdi->id, $wsTwinOffice->id, '2026-07-22', '2026-07-25', 'confirmed', 2700.00);
        $this->insertBookingDays($b7->id, $wsTwinOffice->id, '2026-07-22', '2026-07-25');

        // Booking 8: Mehdi — Nearshore Hot Desk — Aug 5-8 (pending)
        $b8 = $this->createBooking($mehdi->id, $wsNearHot->id, '2026-08-05', '2026-08-08', 'pending', 390.00);
        $this->insertBookingDays($b8->id, $wsNearHot->id, '2026-08-05', '2026-08-08');

        // Booking 9 (cancelled): Mehdi — Gauthier Private Office — July 1-3
        // Cancelled bookings have no booking_days (they were deleted on cancel)
        $this->createBooking($mehdi->id, $wsGauthOffice->id, '2026-07-01', '2026-07-03', 'cancelled', 1600.00);

        // ─── Fatima Zahra — 2 bookings ────────────────────────────────────────

        // Booking 10: Fatima — Marina Hot Desk — July 28 - Aug 1 (pending)
        $b10 = $this->createBooking($fatima->id, $wsMarinaHot->id, '2026-07-28', '2026-08-01', 'pending', 480.00);
        $this->insertBookingDays($b10->id, $wsMarinaHot->id, '2026-07-28', '2026-08-01');

        // Booking 11: Fatima — Medina Hot Desk — Aug 12-15 (confirmed)
        $b11 = $this->createBooking($fatima->id, $wsMedHot->id, '2026-08-12', '2026-08-15', 'confirmed', 330.00);
        $this->insertBookingDays($b11->id, $wsMedHot->id, '2026-08-12', '2026-08-15');
    }

    // ── Private helpers ───────────────────────────────────────────────────────

    private function createSpace(int $hostId, array $attrs, string $photoUrl): Space
    {
        $space = Space::create(array_merge(['host_id' => $hostId], $attrs));

        SpacePhoto::create([
            'space_id' => $space->id,
            'path'     => $photoUrl,
            'order'    => 0,
            'is_cover' => true,
        ]);

        return $space;
    }

    private function createBooking(int $customerId, int $workspaceId, string $start, string $end, string $status, float $total): Booking
    {
        return Booking::create([
            'customer_id'        => $customerId,
            'space_workspace_id' => $workspaceId,
            'start_date'         => $start,
            'end_date'           => $end,
            'status'             => $status,
            'total_amount'       => $total,
            'currency'           => 'MAD',
        ]);
    }

    private function insertBookingDays(int $bookingId, int $workspaceId, string $startDate, string $endDate): void
    {
        $start = Carbon::parse($startDate);
        $end   = Carbon::parse($endDate);
        $rows  = [];
        $now   = now()->toDateTimeString();

        for ($d = $start->copy(); $d->lt($end); $d->addDay()) {
            $rows[] = [
                'booking_id'         => $bookingId,
                'space_workspace_id' => $workspaceId,
                'day'                => $d->toDateString(),
                'created_at'         => $now,
                'updated_at'         => $now,
            ];
        }

        if ($rows) {
            DB::table('booking_days')->insert($rows);
        }
    }
}
