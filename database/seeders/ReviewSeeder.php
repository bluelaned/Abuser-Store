<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ReviewSeeder extends Seeder
{
    public function run(): void
    {
        $comments = [
            // 5 star comments
            "Absolutely amazing product! Best purchase I've made this year. Highly recommend to everyone!",
            "Top tier quality, delivery was super fast. Will definitely buy again!",
            "Exceeded my expectations. The product works flawlessly and support was great.",
            "10/10 experience. Everything went smooth and the product is exactly as described.",
            "Phenomenal! I've tried many similar products but this one is by far the best.",
            "Outstanding quality and great value for money. Very satisfied customer!",
            "Purchased for my whole team and everyone loves it. Perfect execution.",
            "Incredible service and top quality. No issues whatsoever. 5 stars all day.",
            "Super fast and reliable. Exactly what I was looking for. Will return!",
            "Best store ever. Product is legit and support team is super responsive.",
            "Cannot believe how good this is. Worth every penny spent!",
            "Flawless experience from checkout to delivery. Highly impressed.",

            // 4 star comments
            "Great product overall. Minor delay but resolved quickly. Happy with purchase.",
            "Very good quality. Slight issue at first but support fixed it fast. 4 stars.",
            "Really impressed with the quality. Would give 5 stars if delivery was faster.",
            "Good stuff! Works as advertised. Just wish there were more options available.",
            "Solid product with great performance. Small room for improvement but overall great.",
            "Pretty satisfied. Product does what it promises. Delivery could be a tiny bit faster.",
            "Good value for money. Quality is there, packaging was nice. Recommended.",
            "Works perfectly. A bit pricey but totally worth it for the quality.",

            // 3 star comments
            "Average experience. Product works but nothing extraordinary about it.",
            "Decent product, met basic expectations. Some features could be better.",
            "It's okay. Did what I needed but had some minor issues along the way.",
            "Middle of the road experience. Not bad but not great either.",
            "Product works fine. Setup took longer than expected. Average overall.",
            "Acceptable quality. Customer support was helpful when I had questions.",
            "It does the job. Nothing special to write home about but functional.",

            // 2 star comments
            "Had some problems with the product. Support helped but took too long.",
            "Not entirely what I expected. Still functional but disappointing.",
            "Below average experience. Product had issues and took time to resolve.",
            "Quality could be much better for this price point. Somewhat disappointed.",

            // 1 star comments
            "Very disappointed. Product did not work as described at all.",
            "Waste of money. Quality is far below what was advertised.",
        ];

        $avatarNames = [
            'Alex Johnson', 'Maria Garcia', 'James Wilson', 'Sarah Brown', 'Michael Davis',
            'Emma Martinez', 'David Anderson', 'Olivia Taylor', 'Daniel Thomas', 'Sophia Jackson',
            'Matthew White', 'Isabella Harris', 'Christopher Martin', 'Mia Thompson', 'Joshua Garcia',
            'Charlotte Robinson', 'Andrew Lewis', 'Amelia Lee', 'Ryan Walker', 'Harper Hall',
            'Nathan Allen', 'Evelyn Young', 'Brandon Hernandez', 'Abigail King', 'Tyler Wright',
            'Emily Lopez', 'Justin Hill', 'Elizabeth Scott', 'Kevin Green', 'Samantha Adams',
            'Patrick Baker', 'Grace Nelson', 'Steven Carter', 'Lily Mitchell', 'Jeffrey Perez',
            'Chloe Roberts', 'Eric Turner', 'Zoey Phillips', 'Jonathan Campbell', 'Penelope Parker',
        ];

        // Distribution: 5★ 45%, 4★ 30%, 3★ 15%, 2★ 7%, 1★ 3%
        $ratingPool = array_merge(
            array_fill(0, 54, 5),
            array_fill(0, 36, 4),
            array_fill(0, 18, 3),
            array_fill(0, 8,  2),
            array_fill(0, 4,  1)
        );

        $commentsByRating = [
            5 => array_slice($comments, 0, 12),
            4 => array_slice($comments, 12, 8),
            3 => array_slice($comments, 20, 7),
            2 => array_slice($comments, 27, 4),
            1 => array_slice($comments, 31, 2),
        ];

        $usedUserIds = [];

        for ($i = 0; $i < 120; $i++) {
            // Create a dummy user for each review
            $name = $avatarNames[$i % count($avatarNames)] . ' ' . ($i + 1);
            $email = 'dummy' . ($i + 1) . '@abuser-dummy.test';

            // Skip if email already exists
            $existing = DB::table('users')->where('email', $email)->first();
            if ($existing) {
                $userId = $existing->id;
            } else {
                $userId = DB::table('users')->insertGetId([
                    'name'          => $name,
                    'email'         => $email,
                    'password'      => Hash::make('password'),
                    'role'          => 'user',
                    'provider_name' => 'discord',
                    'avatar'        => 'https://ui-avatars.com/api/?name=' . urlencode($name) . '&background=' . substr(md5($name), 0, 6) . '&color=fff',
                    'created_at'    => now()->subDays(rand(1, 365)),
                    'updated_at'    => now(),
                ]);
            }

            $rating = $ratingPool[$i];
            $pool   = $commentsByRating[$rating];
            $comment = $pool[array_rand($pool)];

            // Add some variation to positive comments
            if ($rating >= 4) {
                $extras = [' Really happy with this!', ' Would recommend.', ' Great experience overall.', '', '', ''];
                $comment .= $extras[array_rand($extras)];
            }

            DB::table('reviews')->insert([
                'user_id'      => $userId,
                'rating'       => $rating,
                'comment'      => $comment,
                'is_published' => true,
                'created_at'   => now()->subDays(rand(0, 180))->subHours(rand(0, 23)),
                'updated_at'   => now(),
            ]);
        }
    }
}
