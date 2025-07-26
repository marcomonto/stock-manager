<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Enums\OrderStatus;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        $orders = [
            [
                'name' => 'Ordine Gaming Setup',
                'description' => 'Ordine completo per setup gaming con console, cuffie e accessori per streaming professionale.',
                'status' => OrderStatus::DELIVERED,
            ],
            [
                'name' => 'Ordine Home Office',
                'description' => 'Equipaggiamento completo per ufficio domestico: laptop, monitor, scrivania ergonomica e accessori.',
                'status' => OrderStatus::DELIVERED,
            ],
            [
                'name' => 'Ordine Smart Home',
                'description' => 'Sistema domotico completo con hub, lampadine intelligenti, speaker e dispositivi di sicurezza.',
                'status' => OrderStatus::DELIVERED,
            ],
            [
                'name' => 'Ordine Fotografia',
                'description' => 'Kit fotografico professionale con fotocamera mirrorless, obiettivi e accessori per studio.',
                'status' => OrderStatus::DELIVERED,
            ],
            [
                'name' => 'Ordine Cucina Smart',
                'description' => 'Elettrodomestici intelligenti per cucina moderna: robot da cucina, macchina caffè e purificatore aria.',
                'status' => OrderStatus::DELIVERED,
            ],
            [
                'name' => 'Ordine Mobile Tech',
                'description' => 'Dispositivi mobili e accessori: smartphone, tablet, cuffie wireless e power bank.',
                'status' => OrderStatus::DELIVERED,
            ],
            [
                'name' => 'Ordine Fitness Tracker',
                'description' => 'Dispositivi per il monitoraggio della salute e fitness: smartwatch, bilancia smart e accessori sport.',
                'status' => OrderStatus::DELIVERED,
            ],
            [
                'name' => 'Ordine Streaming Kit',
                'description' => 'Attrezzatura completa per content creator: microfono, webcam, illuminazione e stream deck.',
                'status' => OrderStatus::DELIVERED,
            ],
            [
                'name' => 'Ordine Audio Premium',
                'description' => 'Sistema audio hi-end con cuffie professionali, speaker bluetooth e amplificatori portatili.',
                'status' => OrderStatus::DELIVERED,
            ],
            [
                'name' => 'Ordine Travel Tech',
                'description' => 'Gadget tecnologici per viaggi: drone portatile, fotocamera istantanea e accessori da viaggio.',
                'status' => OrderStatus::DELIVERED,
            ],
        ];

        foreach ($orders as $orderData) {
            Order::query()->create([
                'id' => Str::ulid(),
                'name' => $orderData['name'],
                'description' => $orderData['description'],
                'status' => $orderData['status'],
            ]);
        }
    }
}
