<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Webkul\Category\Repositories\CategoryRepository;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    protected $categoryRepository;

    public function __construct(CategoryRepository $categoryRepository)
    {
        $this->categoryRepository = $categoryRepository;
    }

    public function run(): void
    {
        $parentCategories = [
            'Men',
            'Women',
        ];

        foreach ($parentCategories as $parentName) {
            $parent = $this->categoryRepository->create([
                'name'           => $parentName,
                'slug'           => strtolower(str_replace(' ', '-', $parentName)),
                'description'    => "$parentName category",
                'meta_title'     => "$parentName",
                'meta_keywords'  => "$parentName",
                'meta_description' => "$parentName products",
                'display_mode'   => 'products_and_description',
                'status'         => 1,
            ]);

            // Add Subcategories to Each Parent
            $subcategories = [
                'Men' => ['Shirts', 'T-Shirts'],
                'Women' => ['Dresses', 'Tops'],
            ];

            foreach ($subcategories[$parentName] ?? [] as $childName) {
                $this->categoryRepository->create([
                    'name'           => $childName,
                    'slug'           => strtolower(str_replace(' ', '-', $childName)),
                    'description'    => "$childName under $parentName",
                    'meta_title'     => "$childName",
                    'meta_keywords'  => "$childName",
                    'meta_description' => "$childName category",
                    'parent_id'      => $parent->id,
                    'display_mode'   => 'products_and_description',
                    'status'         => 1,
                ]);
            }

        }
    }
}
