<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class DesignSystemController extends Controller
{
    public function index()
    {
        // Mock data for design system showcase
        $mockResources = [
            [
                'id' => 1,
                'name' => 'Lakeside Villa',
                'slug' => 'lakeside-villa',
                'description' => 'Enjoy breathtaking lake views from this stunning villa with modern amenities.',
                'media' => [
                    ['id' => 1, 'original_url' => 'https://images.unsplash.com/photo-1518780664697-55e3ad937233?w=800'],
                ],
                'features' => [
                    ['id' => 1, 'name' => 'WiFi'],
                    ['id' => 2, 'name' => 'AC'],
                    ['id' => 3, 'name' => 'Kitchen'],
                    ['id' => 4, 'name' => 'Lake View'],
                    ['id' => 5, 'name' => 'Private Dock'],
                ],
                'rates' => [
                    ['id' => 1, 'name' => 'Standard', 'price' => 1500000, 'currency' => 'IDR', 'price_type' => 'night', 'is_active' => true],
                ],
            ],
            [
                'id' => 2,
                'name' => 'Mountain Cabin',
                'slug' => 'mountain-cabin',
                'description' => 'Cozy cabin nestled in the mountains, perfect for a peaceful retreat.',
                'media' => [
                    ['id' => 2, 'original_url' => 'https://images.unsplash.com/photo-1449158743715-0a90ebb6d2d8?w=800'],
                ],
                'features' => [
                    ['id' => 1, 'name' => 'Fireplace'],
                    ['id' => 2, 'name' => 'Hiking Trail'],
                    ['id' => 3, 'name' => 'BBQ Grill'],
                    ['id' => 4, 'name' => 'Mountain View'],
                    ['id' => 5, 'name' => 'Hot Tub'],
                    ['id' => 6, 'name' => 'Stargazing Deck'],
                ],
                'rates' => [
                    ['id' => 2, 'name' => 'Weekend', 'price' => 2000000, 'currency' => 'IDR', 'price_type' => 'night', 'is_active' => true],
                ],
            ],
            [
                'id' => 3,
                'name' => 'Beach House',
                'slug' => 'beach-house',
                'description' => 'Wake up to the sound of waves in this beautiful beachfront property.',
                'media' => [
                    ['id' => 3, 'original_url' => 'https://images.unsplash.com/photo-1499793983690-e29da59ef1c2?w=800'],
                ],
                'features' => [
                    ['id' => 1, 'name' => 'Beachfront'],
                    ['id' => 2, 'name' => 'Pool'],
                    ['id' => 3, 'name' => 'Surfboard'],
                    ['id' => 4, 'name' => 'Sunset View'],
                ],
                'rates' => [
                    ['id' => 3, 'name' => 'Premium', 'price' => 2500000, 'currency' => 'IDR', 'price_type' => 'night', 'is_active' => true],
                ],
            ]
        ];

        return Inertia::render('DesignSystem/Index', [
            'sampleResources' => $mockResources,
        ]);
    }
}
