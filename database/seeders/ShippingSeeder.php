<?php

namespace Database\Seeders;

use App\Models\Courier;
use App\Models\CourierService;
use App\Models\ShippingRate;
use Illuminate\Database\Seeder;

class ShippingSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Couriers
        $couriersData = [
            'jne' => ['name' => 'JNE', 'is_active' => true],
            'jnt' => ['name' => 'J&T Express', 'is_active' => true],
            'sicepat' => ['name' => 'SiCepat', 'is_active' => true],
        ];

        $couriers = [];
        foreach ($couriersData as $code => $data) {
            $couriers[$code] = Courier::firstOrCreate(['code' => $code], $data);
        }

        // 2. Services
        $servicesData = [
            'jne' => [
                ['code' => 'reg', 'name' => 'Reguler', 'days' => '2-3 hari'],
                ['code' => 'oke', 'name' => 'Ekonomis (OKE)', 'days' => '3-5 hari'],
                ['code' => 'yes', 'name' => 'Express (YES)', 'days' => '1 hari'],
            ],
            'jnt' => [
                ['code' => 'ez', 'name' => 'Reguler (EZ)', 'days' => '2-3 hari'],
                ['code' => 'express', 'name' => 'Express', 'days' => '1-2 hari'],
            ],
            'sicepat' => [
                ['code' => 'sicepat_reg', 'name' => 'Reguler', 'days' => '2-3 hari'],
                ['code' => 'sicepat_best', 'name' => 'BEST (Next Day)', 'days' => '1 hari'],
            ],
        ];

        $services = [];
        foreach ($servicesData as $cCode => $srvList) {
            foreach ($srvList as $srv) {
                $services[$cCode.'_'.$srv['code']] = CourierService::firstOrCreate(
                    [
                        'courier_id' => $couriers[$cCode]->id,
                        'service_code' => $srv['code'],
                    ],
                    [
                        'service_name' => $srv['name'],
                        'estimated_days' => $srv['days'],
                        'is_active' => true,
                    ]
                );
            }
        }

        // 3. Shipping Rates
        // Special city rate for Bandung (ensures Rp15.000 default test invariant)
        $rates = [
            // Bandung, Jawa Barat (Store origin vicinity)
            ['srv' => 'jne_reg', 'prov' => 'Jawa Barat', 'city' => 'Bandung', 'base' => 15000, 'per_kg' => 10000],
            ['srv' => 'jne_oke', 'prov' => 'Jawa Barat', 'city' => 'Bandung', 'base' => 12000, 'per_kg' => 8000],
            ['srv' => 'jne_yes', 'prov' => 'Jawa Barat', 'city' => 'Bandung', 'base' => 25000, 'per_kg' => 15000],
            ['srv' => 'jnt_ez', 'prov' => 'Jawa Barat', 'city' => 'Bandung', 'base' => 15000, 'per_kg' => 10000],
            ['srv' => 'jnt_express', 'prov' => 'Jawa Barat', 'city' => 'Bandung', 'base' => 24000, 'per_kg' => 14000],
            ['srv' => 'sicepat_sicepat_reg', 'prov' => 'Jawa Barat', 'city' => 'Bandung', 'base' => 14000, 'per_kg' => 9000],
            ['srv' => 'sicepat_sicepat_best', 'prov' => 'Jawa Barat', 'city' => 'Bandung', 'base' => 22000, 'per_kg' => 14000],

            // Jawa Barat (Province fallback)
            ['srv' => 'jne_reg', 'prov' => 'Jawa Barat', 'city' => null, 'base' => 18000, 'per_kg' => 11000],
            ['srv' => 'jne_oke', 'prov' => 'Jawa Barat', 'city' => null, 'base' => 14000, 'per_kg' => 9000],
            ['srv' => 'jne_yes', 'prov' => 'Jawa Barat', 'city' => null, 'base' => 28000, 'per_kg' => 16000],
            ['srv' => 'jnt_ez', 'prov' => 'Jawa Barat', 'city' => null, 'base' => 17000, 'per_kg' => 10000],
            ['srv' => 'sicepat_sicepat_reg', 'prov' => 'Jawa Barat', 'city' => null, 'base' => 16000, 'per_kg' => 10000],

            // DKI Jakarta
            ['srv' => 'jne_reg', 'prov' => 'DKI Jakarta', 'city' => null, 'base' => 10000, 'per_kg' => 8000],
            ['srv' => 'jne_yes', 'prov' => 'DKI Jakarta', 'city' => null, 'base' => 20000, 'per_kg' => 12000],
            ['srv' => 'jnt_ez', 'prov' => 'DKI Jakarta', 'city' => null, 'base' => 10000, 'per_kg' => 8000],
            ['srv' => 'sicepat_sicepat_reg', 'prov' => 'DKI Jakarta', 'city' => null, 'base' => 10000, 'per_kg' => 8000],

            // Banten
            ['srv' => 'jne_reg', 'prov' => 'Banten', 'city' => null, 'base' => 12000, 'per_kg' => 9000],
            ['srv' => 'jne_yes', 'prov' => 'Banten', 'city' => null, 'base' => 22000, 'per_kg' => 13000],
            ['srv' => 'jnt_ez', 'prov' => 'Banten', 'city' => null, 'base' => 12000, 'per_kg' => 9000],

            // Jawa Tengah
            ['srv' => 'jne_reg', 'prov' => 'Jawa Tengah', 'city' => null, 'base' => 18000, 'per_kg' => 12000],
            ['srv' => 'jne_oke', 'prov' => 'Jawa Tengah', 'city' => null, 'base' => 14000, 'per_kg' => 10000],
            ['srv' => 'jnt_ez', 'prov' => 'Jawa Tengah', 'city' => null, 'base' => 18000, 'per_kg' => 11000],
            ['srv' => 'sicepat_sicepat_reg', 'prov' => 'Jawa Tengah', 'city' => null, 'base' => 17000, 'per_kg' => 11000],

            // DI Yogyakarta
            ['srv' => 'jne_reg', 'prov' => 'DI Yogyakarta', 'city' => null, 'base' => 18000, 'per_kg' => 12000],
            ['srv' => 'jnt_ez', 'prov' => 'DI Yogyakarta', 'city' => null, 'base' => 18000, 'per_kg' => 11000],

            // Jawa Timur
            ['srv' => 'jne_reg', 'prov' => 'Jawa Timur', 'city' => null, 'base' => 20000, 'per_kg' => 13000],
            ['srv' => 'jne_oke', 'prov' => 'Jawa Timur', 'city' => null, 'base' => 16000, 'per_kg' => 11000],
            ['srv' => 'jnt_ez', 'prov' => 'Jawa Timur', 'city' => null, 'base' => 19000, 'per_kg' => 12000],
            ['srv' => 'sicepat_sicepat_reg', 'prov' => 'Jawa Timur', 'city' => null, 'base' => 18000, 'per_kg' => 12000],

            // Bali
            ['srv' => 'jne_reg', 'prov' => 'Bali', 'city' => null, 'base' => 28000, 'per_kg' => 18000],
            ['srv' => 'jnt_ez', 'prov' => 'Bali', 'city' => null, 'base' => 27000, 'per_kg' => 17000],

            // Sumatera Utara
            ['srv' => 'jne_reg', 'prov' => 'Sumatera Utara', 'city' => null, 'base' => 35000, 'per_kg' => 25000],
            ['srv' => 'jnt_ez', 'prov' => 'Sumatera Utara', 'city' => null, 'base' => 34000, 'per_kg' => 24000],

            // Sumatera Selatan
            ['srv' => 'jne_reg', 'prov' => 'Sumatera Selatan', 'city' => null, 'base' => 28000, 'per_kg' => 18000],
            ['srv' => 'jnt_ez', 'prov' => 'Sumatera Selatan', 'city' => null, 'base' => 26000, 'per_kg' => 17000],
        ];

        foreach ($rates as $r) {
            $service = $services[$r['srv']] ?? null;
            if ($service) {
                ShippingRate::firstOrCreate(
                    [
                        'courier_service_id' => $service->id,
                        'province' => $r['prov'],
                        'city' => $r['city'],
                    ],
                    [
                        'min_weight' => 0,
                        'max_weight' => 30000,
                        'base_cost' => $r['base'],
                        'cost_per_kg' => $r['per_kg'],
                        'is_active' => true,
                    ]
                );
            }
        }
    }
}
