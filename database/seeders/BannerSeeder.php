<?php

namespace Database\Seeders;

use App\Models\Banner;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class BannerSeeder extends Seeder
{
    public function run(): void
    {
        $banners = [
            [
                'title' => 'Promoção de Lançamento',
                'image_url' => '/images/banners/lancamento.jpg',
                'link_url' => '/',
                'position' => 1,
            ],
            [
                'title' => 'Frete Grátis acima de R$ 199',
                'image_url' => '/images/banners/frete-gratis.jpg',
                'link_url' => '/',
                'position' => 2,
            ],
        ];

        foreach ($banners as $data) {
            Banner::firstOrCreate(
                ['title' => $data['title']],
                [
                    'public_id' => (string) Str::uuid(),
                    'image_url' => $data['image_url'],
                    'link_url' => $data['link_url'],
                    'position' => $data['position'],
                    'is_active' => true,
                    'starts_at' => Carbon::now()->subDay(),
                    'ends_at' => Carbon::now()->addMonths(3),
                ],
            );
        }
    }
}

