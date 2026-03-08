<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CommunitySeeder extends Seeder
{
    public function run(): void
    {

        $topics = [
            ['author' => 'Sarah Mitchell',  'title' => 'Best hidden gems in Portugal?',          'tags' => ['Portugal','Budget','Solo'],       'body' => 'I\'m planning a 2-week solo trip to Portugal and want to skip the tourist traps. Any recommendations beyond Lisbon and Porto?', 'replies' => 5],
            ['author' => 'James Okafor',    'title' => 'Southeast Asia on $40/day — is it still possible?', 'tags' => ['SEAsia','Budget','Backpacking'], 'body' => 'Heard prices have gone up a lot post-2024. What\'s your experience budgeting in Thailand, Vietnam, and Cambodia lately?', 'replies' => 12],
            ['author' => 'Priya Sharma',    'title' => 'Morocco solo travel safety tips for women', 'tags' => ['Morocco','Safety','Solo','Women'], 'body' => 'Planning to visit Marrakech and the Atlas Mountains. Would love advice from women who\'ve done this solo.', 'replies' => 8],
            ['author' => 'Lukas Bauer',     'title' => 'Epic road trip routes across Patagonia',  'tags' => ['Patagonia','RoadTrip','Adventure'], 'body' => 'Did anyone drive from Puerto Montt to Ushuaia? How long did it take and what are the must-stop spots?', 'replies' => 3],
            ['author' => 'Amara Diallo',    'title' => 'Best time to visit Japan for cherry blossoms', 'tags' => ['Japan','SpringTravel','Culture'], 'body' => 'I know late March to early April is the window, but which cities had the best displays last year?', 'replies' => 7],
            ['author' => 'Chen Wei',        'title' => 'Digital nomad visa options in 2026',      'tags' => ['NomadLife','Visa','Remote'],       'body' => 'Compiling a list of countries with proper digital nomad visas. Who has first-hand experience with Portugal, Indonesia, or Thailand?', 'replies' => 15],
        ];

        foreach ($topics as $t) {
            DB::table('community_topics')->insert([
                'author'     => $t['author'],
                'title'      => $t['title'],
                'tags'       => json_encode($t['tags']),
                'body'       => $t['body'],
                'replies'    => $t['replies'],
                'created_at' => Carbon::now()->subHours(rand(1, 72)),
                'updated_at' => Carbon::now()->subHours(rand(0, 10)),
            ]);
        }

        $groups = [
            ['organizer' => 'Marco Rossi',    'name' => 'Morocco Desert Adventure',    'destination' => 'Marrakech, Morocco',    'date' => 'May 2026',     'spots_left' => 4, 'status' => 'open'],
            ['organizer' => 'Yuki Tanaka',    'name' => 'Japan Cherry Blossom Tour',   'destination' => 'Tokyo & Kyoto, Japan',  'date' => 'April 2026',   'spots_left' => 2, 'status' => 'open'],
            ['organizer' => 'Emma Laurent',   'name' => 'Amalfi Coast Road Trip',      'destination' => 'Amalfi, Italy',         'date' => 'June 2026',    'spots_left' => 0, 'status' => 'full'],
            ['organizer' => 'David Chen',     'name' => 'Patagonia Trekking Crew',     'destination' => 'Torres del Paine, Chile','date' => 'November 2026','spots_left' => 6, 'status' => 'open'],
            ['organizer' => 'Fatima Al-Said', 'name' => 'Bali Wellness Retreat',       'destination' => 'Ubud, Bali',            'date' => 'July 2026',    'spots_left' => 3, 'status' => 'open'],
            ['organizer' => 'Oliver Smith',   'name' => 'Balkan Backpacking Express',  'destination' => 'Balkans, Europe',       'date' => 'August 2026',  'spots_left' => 0, 'status' => 'full'],
        ];

        foreach ($groups as $g) {
            DB::table('community_groups')->insert([
                'organizer'   => $g['organizer'],
                'name'        => $g['name'],
                'destination' => $g['destination'],
                'date'        => $g['date'],
                'spots_left'  => $g['spots_left'],
                'status'      => $g['status'],
                'created_at'  => Carbon::now()->subDays(rand(1, 14)),
                'updated_at'  => Carbon::now()->subDays(rand(0, 3)),
            ]);
        }

        $stories = [
            [
                'author'       => 'Sarah Mitchell',
                'title'        => '30 Days Across Vietnam on a Motorbike',
                'excerpt'      => 'From the misty mountains of Sapa to the golden lanterns of Hoi An, riding south through Vietnam was the most liberating thing I\'ve ever done. Every broken-down village, every bowl of pho at 6am, every winding mountain pass changed me.',
                'image_url'    => 'https://images.unsplash.com/photo-1528360983277-13d401cdc186?w=800&q=80&fit=crop',
                'likes'        => 243,
                'comments'     => 31,
                'published_at' => Carbon::now()->subDays(3),
            ],
            [
                'author'       => 'James Okafor',
                'title'        => 'Sleeping Under the Stars in the Sahara',
                'excerpt'      => 'No phone signal, no electricity, just a blanket of stars so dense it looked like someone had spilled sugar across the sky. Our Berber guide, Hassan, told stories around the fire that I\'ll carry for the rest of my life.',
                'image_url'    => 'https://images.unsplash.com/photo-1509316785289-025f5b846b35?w=800&q=80&fit=crop',
                'likes'        => 189,
                'comments'     => 24,
                'published_at' => Carbon::now()->subDays(7),
            ],
            [
                'author'       => 'Yuki Tanaka',
                'title'        => 'Finding Quiet in Crowded Japan',
                'excerpt'      => 'Everyone warns you about Tokyo\'s crowds. Nobody tells you about the moss-carpeted temple at 6am with only monks and birdsong for company, or the ramen shop in a Kyoto back alley where the chef has been perfecting one broth for 40 years.',
                'image_url'    => 'https://images.unsplash.com/photo-1480796927426-f609979314bd?w=800&q=80&fit=crop',
                'likes'        => 312,
                'comments'     => 47,
                'published_at' => Carbon::now()->subDays(12),
            ],
            [
                'author'       => 'Priya Sharma',
                'title'        => 'Solo Through Rajasthan: A Love Letter to India',
                'excerpt'      => 'Pink cities, blue cities, golden deserts. India overwhelms all the senses at once — but somewhere between the chaos of Jaipur and the silence of the Thar Desert, I found a version of myself I didn\'t know existed.',
                'image_url'    => 'https://images.unsplash.com/photo-1524492412937-b28074a5d7da?w=800&q=80&fit=crop',
                'likes'        => 156,
                'comments'     => 19,
                'published_at' => Carbon::now()->subDays(18),
            ],
            [
                'author'       => 'Marco Rossi',
                'title'        => 'The Camino de Santiago Changed My Life',
                'excerpt'      => '800 kilometres on foot. Blisters, sunburn, and the occasional downpour. But also strangers who became family, sunrises over ancient hills, and the peculiar joy of having everything you need on your back.',
                'image_url'    => 'https://images.unsplash.com/photo-1501854140801-50d01698950b?w=800&q=80&fit=crop',
                'likes'        => 427,
                'comments'     => 63,
                'published_at' => Carbon::now()->subDays(25),
            ],
            [
                'author'       => 'Emma Laurent',
                'title'        => 'Island-Hopping the Greek Cyclades',
                'excerpt'      => 'White walls, cobalt domes, and the Aegean stretching endlessly blue. Each island has its own personality — Santorini for sunsets, Milos for beaches, Folegandros for soul. The ferry between them becomes your living room.',
                'image_url'    => 'https://images.unsplash.com/photo-1533105079780-92b9be482077?w=800&q=80&fit=crop',
                'likes'        => 198,
                'comments'     => 28,
                'published_at' => Carbon::now()->subDays(30),
            ],
        ];

        foreach ($stories as $s) {
            DB::table('community_stories')->insert([
                'author'       => $s['author'],
                'title'        => $s['title'],
                'excerpt'      => $s['excerpt'],
                'image_url'    => $s['image_url'],
                'likes'        => $s['likes'],
                'comments'     => $s['comments'],
                'published_at' => $s['published_at'],
                'created_at'   => $s['published_at'],
                'updated_at'   => $s['published_at'],
            ]);
        }

        $topicIds = DB::table('community_topics')->pluck('id');

        $sampleReplies = [
            ['author' => 'Lukas Bauer',   'body' => 'Sintra is absolutely magical and very underrated. Also check out Monsanto village — it\'s like a fairytale.'],
            ['author' => 'Emma Laurent',  'body' => 'Alentejo region is stunning and still largely untouched by mass tourism. Wine, cork forests, medieval towns.'],
            ['author' => 'Chen Wei',      'body' => 'Totally agree with the budget concerns. Vietnam is still around $35-40/day if you avoid tourist restaurants.'],
            ['author' => 'Amara Diallo',  'body' => 'I did this last year — Hanoi for 3 days, then south. Street food keeps costs down dramatically.'],
            ['author' => 'Priya Sharma',  'body' => 'Dress conservatively, always negotiate prices before getting in a taxi, and the medina is safe during daylight.'],
            ['author' => 'Sarah Mitchell','body' => 'Stayed at a women-only riad in Marrakech — highly recommend for solo female travellers.'],
        ];

        foreach ($topicIds as $tid) {
            $replyCount = rand(1, 3);
            for ($i = 0; $i < $replyCount; $i++) {
                $reply = $sampleReplies[array_rand($sampleReplies)];
                DB::table('community_replies')->insert([
                    'topic_id'   => $tid,
                    'author'     => $reply['author'],
                    'body'       => $reply['body'],
                    'created_at' => Carbon::now()->subHours(rand(1, 48)),
                    'updated_at' => Carbon::now()->subHours(rand(0, 5)),
                ]);
            }
        }

        $this->command->info('Community seeded: ' . count($topics) . ' topics, ' . count($groups) . ' groups, ' . count($stories) . ' stories.');
    }
}
