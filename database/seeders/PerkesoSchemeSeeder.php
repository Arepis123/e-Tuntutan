<?php

namespace Database\Seeders;

use App\Models\PerkesoScheme;
use Illuminate\Database\Seeder;

class PerkesoSchemeSeeder extends Seeder
{
    public function run(): void
    {
        $schemes = [
            ['value' => 'skim_bencana_kerja',      'label' => 'Skim Bencana Kerja',      'sort_order' => 1],
            ['value' => 'skim_pengurusan_jenazah',  'label' => 'Skim Pengurusan Jenazah', 'sort_order' => 2],
            ['value' => 'skim_luar_waktu_bekerja',  'label' => 'Skim Luar Waktu Bekerja', 'sort_order' => 3],
        ];

        foreach ($schemes as $scheme) {
            PerkesoScheme::firstOrCreate(['value' => $scheme['value']], $scheme);
        }
    }
}
