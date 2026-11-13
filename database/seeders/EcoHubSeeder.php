<?php

namespace Database\Seeders;

use App\Models\Hub;
use App\Models\Product;
use App\Models\Producer;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class EcoHubSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Data matches the mock data in ecoService.ts for seamless integration.
     */
    public function run(): void
    {
        // ── Users ──
        $consumer = User::create([
            'name'     => 'Jean Dupont',
            'email'    => 'jean@ecohub.fr',
            'password' => Hash::make('password'),
            'role'     => 'consumer',
        ]);

        $farmer = User::create([
            'name'     => 'Marc Laurent (Maraîcher)',
            'email'    => 'pro@ecohub.fr',
            'password' => Hash::make('password'),
            'role'     => 'farmer',
        ]);

        $merchant = User::create([
            'name'     => 'Sophie Martin (Commerçante)',
            'email'    => 'merchant@ecohub.fr',
            'password' => Hash::make('password'),
            'role'     => 'merchant',
        ]);

        // ── Hubs (matching MOCK_HUB + getHubs() mocks) ──
        Hub::create([
            'name'              => 'Boulangerie du Coin',
            'address'           => '14 rue des Alouettes',
            'city'              => '44000 Nantes',
            'collection_time'   => 'Ven. 16h30 - 19h30',
            'closing_countdown' => 'Dans 17h 42m',
            'is_active'         => true,
        ]);

        Hub::create([
            'name'              => 'Épicerie Bio du Quartier',
            'address'           => '8 boulevard de la Prairie',
            'city'              => '44000 Nantes',
            'collection_time'   => 'Ven. 17h00 - 19h00',
            'closing_countdown' => 'Dans 17h 42m',
            'is_active'         => true,
        ]);

        Hub::create([
            'name'              => 'Café Associatif Le Lieu',
            'address'           => '22 rue Maréchal Joffre',
            'city'              => '44000 Nantes',
            'collection_time'   => 'Ven. 16h00 - 18h30',
            'closing_countdown' => 'Dans 17h 42m',
            'is_active'         => true,
        ]);

        // ── Producers (matching MOCK_PRODUCTS producer names) ──
        $producer1 = Producer::create([
            'name'             => 'Ferme des Bruyères',
            'category'         => 'Maraîchage sur sol vivant (MSV)',
            'distance_km'      => 11,
            'bio_certified'    => true,
            'avatar'           => 'https://images.unsplash.com/photo-1595435934249-5df7ed86e1c0?auto=format&fit=crop&w=400&q=80',
            'cover_image'      => 'https://images.unsplash.com/photo-1500937386664-56d1dfef3854?auto=format&fit=crop&w=1000&q=80',
            'description'      => 'Maraîchers passionnés à Saint-Aubin • Depuis 2018',
            'fair_share_percent' => 85,
            'farmers_name'     => 'Marc & Sophie Laurent',
            'quote'            => 'Nourrir le sol avant de nourrir la plante. En laissant la vie mycorhizienne s\'épanouir sans labour, chaque carotte et chaque courge retrouve la saveur authentique de notre terroir.',
            'varieties_count'  => 14,
            'hectares'         => 3.2,
        ]);

        $producer2 = Producer::create([
            'name'             => 'Chèvrerie des Collines',
            'category'         => 'Élevage caprin bio',
            'distance_km'      => 8,
            'bio_certified'    => true,
            'avatar'           => 'https://images.unsplash.com/photo-1516467508483-a7212febe31a?auto=format&fit=crop&w=200&q=80',
            'cover_image'      => 'https://images.unsplash.com/photo-1500595046743-cd271d694d30?auto=format&fit=crop&w=1200&q=80',
            'description'      => 'Éleveurs de chèvres en plein air • Fromages affinés à la ferme',
            'fair_share_percent' => 78,
            'farmers_name'     => 'Pierre Durand',
            'quote'            => 'Nos chèvres pâturent toute l\'année dans les collines nantaises. Le lait cru donne un goût incomparable à nos fromages.',
            'varieties_count'  => 6,
            'hectares'         => 5.0,
        ]);

        $producer3 = Producer::create([
            'name'             => 'Vergers du Val',
            'category'         => 'Fruits bio de saison',
            'distance_km'      => 14,
            'bio_certified'    => true,
            'avatar'           => 'https://images.unsplash.com/photo-1560493676-04071c5f467b?auto=format&fit=crop&w=200&q=80',
            'cover_image'      => 'https://images.unsplash.com/photo-1523741543316-beb7fc772693?auto=format&fit=crop&w=1200&q=80',
            'description'      => 'Verger familial bio • Fruits de saison et anciens',
            'fair_share_percent' => 80,
            'farmers_name'     => 'Marie & Thomas Leroy',
            'quote'            => 'Nos vergers abritent des variétés anciennes que vous ne trouverez nulle part ailleurs. Chaque fruit est cueilli à maturité.',
            'varieties_count'  => 22,
            'hectares'         => 12.0,
        ]);

        $producer4 = Producer::create([
            'name'             => 'Boulangerie du Coin',
            'category'         => 'Boulangerie artisanale',
            'distance_km'      => 0,
            'bio_certified'    => false,
            'avatar'           => 'https://images.unsplash.com/photo-1555507036-ab1f4038024a?auto=format&fit=crop&w=200&q=80',
            'cover_image'      => 'https://images.unsplash.com/photo-1509440159596-0249088772ff?auto=format&fit=crop&w=1200&q=80',
            'description'      => 'Boulangerie artisanale • Farines paysannes et levain naturel',
            'fair_share_percent' => 75,
            'farmers_name'     => 'Antoine Moreau',
            'quote'            => 'Mon pain cuit lentement au four à bois avec des farines moulues localement. Le levain a plus de 20 ans.',
            'varieties_count'  => 8,
            'hectares'         => 0,
        ]);

        // ── Products (matching MOCK_PRODUCTS exactly) ──
        Product::create([
            'producer_id' => $producer1->id,
            'name'        => 'Tomates Marmande Bio',
            'subtitle'    => 'Marc & Sophie',
            'price'       => 3.80,
            'unit'        => 'le kg',
            'distance_km' => 11,
            'image'       => 'https://images.unsplash.com/photo-1592924357228-91a4daadcfea?auto=format&fit=crop&w=600&q=80',
            'category'    => 'Légumes & Fruits bio',
            'in_stock'    => true,
        ]);

        Product::create([
            'producer_id' => $producer2->id,
            'name'        => 'Chèvre Frais au Lait Cru',
            'subtitle'    => 'Moulé à la louche',
            'price'       => 4.20,
            'unit'        => 'pièce (180g)',
            'distance_km' => 8,
            'image'       => 'https://images.unsplash.com/photo-1486297678162-eb2a19b0a32d?auto=format&fit=crop&w=600&q=80',
            'category'    => 'Fromages & Laitiers',
            'in_stock'    => true,
        ]);

        Product::create([
            'producer_id' => $producer3->id,
            'name'        => 'Fraises Gariguette',
            'subtitle'    => 'Sucrées & parfumées',
            'price'       => 4.50,
            'unit'        => 'barquette 250g',
            'distance_km' => 14,
            'image'       => 'https://images.unsplash.com/photo-1464965911861-746a04b4bca6?auto=format&fit=crop&w=600&q=80',
            'category'    => 'Légumes & Fruits bio',
            'in_stock'    => true,
        ]);

        Product::create([
            'producer_id' => $producer4->id,
            'name'        => 'Pain de Campagne Levain',
            'subtitle'    => 'Farines paysannes T80',
            'price'       => 3.60,
            'unit'        => 'pièce (500g)',
            'distance_km' => 0,
            'image'       => 'https://images.unsplash.com/photo-1509440159596-0249088772ff?auto=format&fit=crop&w=600&q=80',
            'category'    => 'Boulangerie & Céréales',
            'in_stock'    => true,
        ]);
    }
}
