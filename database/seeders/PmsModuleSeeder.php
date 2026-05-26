<?php

namespace Database\Seeders;

use App\Models\HotelRoom;
use App\Models\HotelRoomType;
use Illuminate\Database\Seeder;

class PmsModuleSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            ['name' => 'Standard Room', 'code' => 'STD', 'base_rate' => 6200, 'max_occupancy' => 2],
            ['name' => 'Deluxe Room', 'code' => 'DLX', 'base_rate' => 7800, 'max_occupancy' => 2],
            ['name' => 'Superior Room', 'code' => 'SUP', 'base_rate' => 9800, 'max_occupancy' => 3],
            ['name' => 'Suite', 'code' => 'STE', 'base_rate' => 12800, 'max_occupancy' => 4],
        ];

        foreach ($types as $type) {
            HotelRoomType::query()->updateOrCreate(
                ['code' => $type['code']],
                [
                    'name' => $type['name'],
                    'base_rate' => $type['base_rate'],
                    'max_occupancy' => $type['max_occupancy'],
                    'housekeeping_buffer_minutes' => 45,
                    'is_active' => true,
                ]
            );
        }

        $rooms = [
            ['101', '1', 'STD', 'vacant_clean', 'clean'],
            ['102', '1', 'STD', 'vacant_clean', 'inspected'],
            ['103', '1', 'STD', 'dirty', 'dirty'],
            ['201', '2', 'DLX', 'vacant_clean', 'clean'],
            ['202', '2', 'DLX', 'reserved', 'clean'],
            ['203', '2', 'DLX', 'out_of_order', 'out_of_order'],
            ['301', '3', 'SUP', 'vacant_clean', 'inspected'],
            ['302', '3', 'SUP', 'dirty', 'dirty'],
            ['401', '4', 'STE', 'vacant_clean', 'clean'],
            ['402', '4', 'STE', 'vacant_clean', 'clean'],
        ];

        foreach ($rooms as [$number, $floor, $typeCode, $status, $housekeeping]) {
            $roomType = HotelRoomType::query()->where('code', $typeCode)->first();

            HotelRoom::query()->updateOrCreate(
                ['room_number' => $number],
                [
                    'room_type_id' => $roomType?->id,
                    'floor' => $floor,
                    'status' => $status,
                    'housekeeping_status' => $housekeeping,
                    'current_rate' => $roomType?->base_rate ?? 0,
                    'active_folio_balance' => 0,
                    'is_active' => true,
                ]
            );
        }
    }
}
