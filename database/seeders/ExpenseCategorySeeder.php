<?php

namespace Database\Seeders;

use App\Models\ExpenseCategory;
use Illuminate\Database\Seeder;

class ExpenseCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            'Food',
            'Transport',
            'Rent',
            'Bills',
            'Shopping',
            'Health',
            'Family',
            'Others',
        ];

        foreach ($categories as $category) {
            ExpenseCategory::firstOrCreate(['name' => $category]);
        }
    }
}
