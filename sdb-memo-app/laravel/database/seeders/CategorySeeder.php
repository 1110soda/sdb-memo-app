<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Category;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        DB::table('categories')->truncate();
        DB::table('memo_category')->truncate();
        Schema::enableForeignKeyConstraints();

        $categories = [
            ['name' => '期限付き', 'color_code' => '#10B981'], //green
            ['name' => '重要', 'color_code' => '#EF4444'], //red
            ['name' => '作業中', 'color_code' => '#F59E0B'], //yellow
            ['name' => '後で', 'color_code' => '#CBCBCB'], //light gray
        ];

        foreach ($categories as $categoryData) {
            Category::firstOrCreate(
                ['name' => $categoryData['name']],
                ['color_code' => $categoryData['color_code']]
            );
        }
    }
}
