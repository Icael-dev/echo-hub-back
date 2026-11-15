<?php

use App\Models\User;
use App\Models\Hub;
use App\Models\Producer;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Seed test user
    $this->user = User::factory()->create([
        'email' => 'jean.dupont@ecohub.fr',
        'password' => bcrypt('password123'),
        'role' => 'consumer',
    ]);

    // Seed test hub
    $this->hub = Hub::create([
        'name' => 'Boulangerie du Coin',
        'address' => '14 rue des Alouettes',
        'city' => '44000 Nantes',
        'collection_time' => 'Ven. 16h30 - 19h30',
        'closing_countdown' => 'Dans 17h 42m',
        'is_active' => true,
    ]);

    // Seed test producer
    $this->producer = Producer::create([
        'name' => 'Ferme des Bruyères',
        'category' => 'Maraîchage MSV',
        'distance_km' => 11.0,
        'bio_certified' => true,
        'avatar' => 'avatar.jpg',
        'cover_image' => 'cover.jpg',
        'description' => 'Maraîchers passionnés',
        'fair_share_percent' => 85,
        'farmers_name' => 'Marc & Sophie',
        'quote' => 'Nourrir le sol',
        'varieties_count' => 14,
        'hectares' => 3.2,
    ]);

    // Seed test product
    $this->product = Product::create([
        'producer_id' => $this->producer->id,
        'name' => 'Tomates Marmande Bio',
        'subtitle' => 'Marc & Sophie',
        'price' => 3.80,
        'unit' => 'le kg',
        'image' => 'tomates.jpg',
        'category' => 'Légumes & Fruits bio',
        'in_stock' => true,
    ]);
});

test('user can login via api endpoint', function () {
    $response = $this->postJson('/api/login', [
        'email' => 'jean.dupont@ecohub.fr',
        'password' => 'password123',
    ]);

    $response->assertStatus(200)
        ->assertJsonStructure(['token', 'refresh_token', 'user' => ['id', 'name', 'email', 'role']]);
});

test('can retrieve active hub and hubs list', function () {
    $responseActive = $this->getJson('/api/hub/active');
    $responseActive->assertStatus(200)
        ->assertJson(['name' => 'Boulangerie du Coin']);

    $responseList = $this->getJson('/api/hubs');
    $responseList->assertStatus(200)
        ->assertJsonCount(1);
});

test('can retrieve products list via api', function () {
    $response = $this->getJson('/api/products');
    $response->assertStatus(200)
        ->assertJsonCount(1)
        ->assertJsonFragment(['name' => 'Tomates Marmande Bio']);
});

test('user can submit order via api', function () {
    $response = $this->postJson('/api/orders', [
        'items' => [
            [
                'product' => [
                    'id' => $this->product->id,
                    'price' => 3.80,
                ],
                'quantity' => 2,
            ]
        ],
        'hubId' => $this->hub->id,
        'slot' => '17h30-18h',
    ]);

    $response->assertStatus(201)
        ->assertJsonStructure(['success', 'orderId']);
});
