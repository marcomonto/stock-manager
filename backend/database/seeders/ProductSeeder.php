<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        for ($i = 0; $i < 50; $i++) {
            Product::query()
                ->create([
                    'id' => Str::ulid(),
                    'name' => fake()->words(rand(2, 4), true),
                    'description' => fake()->paragraph(rand(2, 5)),
                    'stock_quantity' => fake()->numberBetween(0, 100),
                    'is_active' => fake()->boolean(80),
                ]);
        }

        $products = [
            [
                'name' => 'iPhone 15 Pro',
                'description' => 'Smartphone Apple con chip A17 Pro, fotocamera principale da 48MP e design in titanio. Display Super Retina XDR da 6.1 pollici con ProMotion.',
                'stock_quantity' => 25,
                'is_active' => true,
            ],
            [
                'name' => 'Samsung Galaxy S24 Ultra',
                'description' => 'Flagship Android con S Pen integrata, fotocamera da 200MP e display Dynamic AMOLED 2X da 6.8 pollici. Processore Snapdragon 8 Gen 3.',
                'stock_quantity' => 18,
                'is_active' => true,
            ],
            [
                'name' => 'MacBook Air M3',
                'description' => 'Laptop ultraleggero con chip Apple M3, 8GB di RAM e SSD da 256GB. Display Liquid Retina da 13.6 pollici con True Tone.',
                'stock_quantity' => 12,
                'is_active' => true,
            ],
            [
                'name' => 'Dell XPS 13',
                'description' => 'Notebook premium con processore Intel Core i7 di 13a generazione, 16GB LPDDR5 e display InfinityEdge 4K da 13.4 pollici.',
                'stock_quantity' => 8,
                'is_active' => true,
            ],
            [
                'name' => 'Sony WH-1000XM5',
                'description' => 'Cuffie wireless con cancellazione del rumore leader del settore, driver da 30mm e autonomia fino a 30 ore. Controlli touch intuitivi.',
                'stock_quantity' => 45,
                'is_active' => true,
            ],
            [
                'name' => 'AirPods Pro 2',
                'description' => 'Auricolari true wireless con cancellazione attiva del rumore, audio spaziale personalizzato e custodia di ricarica MagSafe.',
                'stock_quantity' => 67,
                'is_active' => true,
            ],
            [
                'name' => 'Nintendo Switch OLED',
                'description' => 'Console ibrida con schermo OLED da 7 pollici, 64GB di memoria interna e dock con porta LAN integrata. Include Joy-Con.',
                'stock_quantity' => 22,
                'is_active' => true,
            ],
            [
                'name' => 'PlayStation 5',
                'description' => 'Console di ultima generazione con SSD ultra-veloce, ray tracing hardware e controller DualSense con feedback aptico avanzato.',
                'stock_quantity' => 5,
                'is_active' => true,
            ],
            [
                'name' => 'iPad Pro 12.9"',
                'description' => 'Tablet professionale con chip M2, display Liquid Retina XDR e supporto per Apple Pencil 2. Ideale per creativi e professionisti.',
                'stock_quantity' => 15,
                'is_active' => true,
            ],
            [
                'name' => 'Samsung Galaxy Tab S9',
                'description' => 'Tablet Android premium con display Dynamic AMOLED 2X da 11 pollici, S Pen inclusa e resistenza all\'acqua IP68.',
                'stock_quantity' => 10,
                'is_active' => true,
            ],
            [
                'name' => 'Canon EOS R6 Mark II',
                'description' => 'Fotocamera mirrorless full-frame con sensore da 24.2MP, stabilizzazione a 5 assi e registrazione video 4K senza crop.',
                'stock_quantity' => 7,
                'is_active' => true,
            ],
        ];

        foreach ($products as $productData) {
            Product::query()->create([
                'id' => Str::ulid(),
                'name' => $productData['name'],
                'description' => $productData['description'],
                'stock_quantity' => $productData['stock_quantity'],
                'is_active' => $productData['is_active'],
            ]);

        }
    }
}
