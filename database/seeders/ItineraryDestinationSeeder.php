<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ItineraryDestinationSeeder extends Seeder
{
    public function run(): void
    {
        if (DB::table('itinerary_destinations')->count() > 0) {
            $this->command->info('Itinerary destinations already seeded — skipping.');
            return;
        }

        $now = Carbon::now();

        $destinations = [
            ['code' => 'bali',      'label' => 'Bali, Indonesia'],
            ['code' => 'paris',     'label' => 'Paris, France'],
            ['code' => 'kyoto',     'label' => 'Kyoto, Japan'],
            ['code' => 'swiss',     'label' => 'Swiss Alps, Switzerland'],
            ['code' => 'santorini', 'label' => 'Santorini, Greece'],
            ['code' => 'lisbon',    'label' => 'Lisbon, Portugal'],
            ['code' => 'bangkok',   'label' => 'Bangkok, Thailand'],
            ['code' => 'amalfi',    'label' => 'Amalfi Coast, Italy'],
            ['code' => 'nz',        'label' => 'New Zealand'],
            ['code' => 'morocco',   'label' => 'Morocco'],
        ];

        foreach ($destinations as &$d) {
            $d['is_active']  = true;
            $d['created_at'] = $now;
            $d['updated_at'] = $now;
        }

        DB::table('itinerary_destinations')->insert($destinations);

        // ── Day plans ──────────────────────────────────────────────────────
        $plans = [
            'bali' => [
                [1, 'Arrival in Ubud',         'Arrive at Ngurah Rai Airport. Transfer to your villa in Ubud. Traditional Balinese welcome ceremony.'],
                [2, 'Rice Terraces & Temples',  'Morning at Tegallalang Rice Terraces. Visit Tirta Empul temple for purification.'],
                [3, 'Adventure Day',            'White-water rafting on Ayung River. Evening Kecak dance performance.'],
                [4, 'Cooking & Culture',        'Balinese cooking class. Explore Ubud art market and local crafts.'],
                [5, 'Beach Time',               'Transfer to Seminyak. Relax at the beach, enjoy sunset cocktails.'],
                [6, 'Island Exploration',       'Day trip to Nusa Penida for snorkeling and cliff views.'],
                [7, 'Spa & Departure',          'Morning spa treatment. Last-minute shopping. Departure transfer.'],
            ],
            'paris' => [
                [1, 'Arrival in Paris',         'Arrive at CDG. Check into hotel near Louvre. Evening Seine River walk.'],
                [2, 'Eiffel Tower & Louvre',    'Morning at Eiffel Tower. Afternoon at Louvre Museum.'],
                [3, 'Notre-Dame & Montmartre',  'Visit Notre-Dame Cathedral. Explore Montmartre and Sacré-Cœur.'],
                [4, 'Versailles Day Trip',      'Full-day trip to Palace of Versailles. Hall of Mirrors tour.'],
                [5, 'Art & Fashion',            "Visit Musée d'Orsay. Shopping in Le Marais district."],
                [6, 'Food Tour',                'French cooking class. Cheese and wine tasting in Latin Quarter.'],
                [7, 'Au Revoir Paris',          'Morning at Luxembourg Gardens. Final croissants. Departure.'],
            ],
            'kyoto' => [
                [1, 'Arrival in Kyoto',         'Arrive at Kansai Airport. Transfer to ryokan in Gion district. Evening stroll through Hanamikoji.'],
                [2, 'Temples & Bamboo',         'Arashiyama bamboo grove at dawn. Tenryu-ji Zen garden. Monkey Park.'],
                [3, 'Geisha District',          'Fushimi Inari torii gates at sunrise. Afternoon tea ceremony in Gion.'],
                [4, 'Nishiki Market',           'Explore Nishiki Market. Kinkaku-ji Golden Pavilion. Ryoan-ji rock garden.'],
                [5, 'Day Trip to Nara',         'Nara deer park. Todai-ji Great Buddha. Return for kaiseki dinner.'],
                [6, 'Philosopher\'s Path',      'Walk the Philosopher\'s Path. Nanzen-ji aqueduct. Heian Shrine.'],
                [7, 'Farewell Kyoto',           'Morning at Kiyomizu-dera. Souvenir shopping on Ninenzaka. Departure.'],
            ],
            'swiss' => [
                [1, 'Arrival in Interlaken',    'Arrive at Zurich Airport. Train to Interlaken. Check in with Jungfrau views.'],
                [2, 'Jungfraujoch',             'Cogwheel train to Top of Europe at 3,454m. Snow activities and panoramic views.'],
                [3, 'Grindelwald Hike',         'Hike First Cliff Walk. Bachalpsee lake reflection. Paragliding option.'],
                [4, 'Lauterbrunnen Valley',     'Staubbach Falls. Trümmelbach Falls inside the mountain. Valley walk.'],
                [5, 'Lucerne Day Trip',         'Chapel Bridge, Lion Monument, and lake cruise. Swiss chocolate tasting.'],
                [6, 'Skiing or Snowshoeing',    'Full day on Schilthorn slopes. Revolving restaurant lunch with Eiger views.'],
                [7, 'Departure',                'Morning lakeside walk. Train back to Zurich Airport. Departure.'],
            ],
            'santorini' => [
                [1, 'Arrival in Santorini',     'Arrive at Santorini Airport. Transfer to Oia. Sunset from the castle ruins.'],
                [2, 'Caldera Cruise',           'Catamaran cruise around the caldera. Hot springs swim. Volcanic island stop.'],
                [3, 'Fira & Firostefani',       'Walk the caldera path from Fira to Oia. Lunch at cliffside taverna.'],
                [4, 'Ancient Akrotiri',         'Minoan ruins at Akrotiri. Red Beach and White Beach. Wine tasting at sunset.'],
                [5, 'Perissa Black Beach',      'Relax at Perissa black sand beach. Snorkeling. Seafood dinner.'],
                [6, 'Pyrgos Village',           'Hike to Pyrgos castle. Panoramic views. Cooking class with local chef.'],
                [7, 'Farewell Santorini',       'Final Oia sunrise. Ferry or flight departure.'],
            ],
            'lisbon' => [
                [1, 'Arrival in Lisbon',        'Arrive at Lisbon Airport. Explore Baixa district. Sunset at Miradouro da Graça.'],
                [2, 'Alfama & Fado',            'Tram 28 through Alfama. São Jorge Castle. Evening fado show.'],
                [3, 'Belém',                    'Jerónimos Monastery. Tower of Belém. Pastéis de Belém custard tarts.'],
                [4, 'Sintra Day Trip',          'Pena Palace. Moorish Castle. Quinta da Regaleira gardens.'],
                [5, 'LX Factory & Bairro Alto', 'LX Factory market. Street art in Mouraria. Ginjinha tasting.'],
                [6, 'Cascais Coast',            'Train to Cascais. Boca do Inferno cliffs. Seafood lunch by the ocean.'],
                [7, 'Farewell Lisbon',          'Morning at Time Out Market. Final pastel de nata. Departure.'],
            ],
            'bangkok' => [
                [1, 'Arrival in Bangkok',       'Arrive at Suvarnabhumi. Khao San Road evening. Street food tour.'],
                [2, 'Grand Palace & Temples',   'Grand Palace and Wat Phra Kaew. Wat Pho reclining Buddha. Chao Phraya boat.'],
                [3, 'Floating Markets',         'Damnoen Saduak floating market. Maeklong railway market. Tuk-tuk ride.'],
                [4, 'Chatuchak Weekend Market', 'Chatuchak market (8,000 stalls). Jim Thompson House. Rooftop bar sunset.'],
                [5, 'Ayutthaya Day Trip',       'Ancient capital ruins. Wat Mahathat tree Buddha. River cruise back.'],
                [6, 'Chinatown & Nightlife',    'Yaowarat Chinatown food crawl. Asiatique night market. Sky bar.'],
                [7, 'Farewell Bangkok',         'Morning Thai massage. Siam Paragon shopping. Departure.'],
            ],
            'amalfi' => [
                [1, 'Arrival in Naples',        'Arrive at Naples Airport. Transfer to Positano. Sunset from the terrace.'],
                [2, 'Positano',                 'Explore Positano\'s vertical streets. Spiaggia Grande beach. Limoncello tasting.'],
                [3, 'Amalfi Town',              'Duomo di Amalfi. Paper museum. Boat trip to Grotta dello Smeraldo.'],
                [4, 'Path of the Gods',         'Hike Sentiero degli Dei from Agerola to Positano. Panoramic views.'],
                [5, 'Ravello',                  'Villa Rufolo gardens. Villa Cimbrone. Classical music concert.'],
                [6, 'Capri Day Trip',           'Ferry to Capri. Blue Grotto. Anacapri village. Chairlift to Monte Solaro.'],
                [7, 'Farewell Amalfi',          'Final espresso in Amalfi. Transfer to Naples. Departure.'],
            ],
            'nz' => [
                [1, 'Arrival in Auckland',      'Arrive at Auckland Airport. Sky Tower. Viaduct Harbour dinner.'],
                [2, 'Hobbiton & Rotorua',       'Hobbiton Movie Set tour. Rotorua geothermal parks. Maori hangi dinner.'],
                [3, 'Tongariro Alpine Crossing', 'Epic 19km volcanic hike. Emerald Lakes. Mount Ngauruhoe views.'],
                [4, 'Wellington',               'Te Papa Museum. Cuba Street. Zealandia wildlife sanctuary.'],
                [5, 'Marlborough Sounds',       'Ferry to South Island. Marlborough wine region. Queen Charlotte Track.'],
                [6, 'Queenstown',               'Fly to Queenstown. Bungee jumping or skydiving. Remarkables views.'],
                [7, 'Farewell New Zealand',     'Milford Sound cruise or Fiordland walk. Departure from Queenstown.'],
            ],
            'morocco' => [
                [1, 'Arrival in Marrakech',     'Arrive at Marrakech Menara. Jemaa el-Fna square. Djemaa el-Fna food stalls.'],
                [2, 'Medina & Souks',           'Bahia Palace. Saadian Tombs. Spice souk. Leather tanneries.'],
                [3, 'Atlas Mountains',          'Day trip to Ourika Valley. Berber villages. Waterfall hike.'],
                [4, 'Essaouira',                'Coastal drive to Essaouira. Blue boats. Argan oil cooperative.'],
                [5, 'Sahara Desert',            'Drive to Merzouga. Camel trek at sunset. Overnight in desert camp.'],
                [6, 'Fes',                      'Drive to Fes. Al-Qarawiyyin University. Chouara tannery views.'],
                [7, 'Farewell Morocco',         'Fes medina morning walk. Transfer to airport. Departure.'],
            ],
        ];

        $rows = [];
        foreach ($plans as $code => $days) {
            foreach ($days as [$day, $title, $desc]) {
                $rows[] = [
                    'destination_code' => $code,
                    'day'              => $day,
                    'title'            => $title,
                    'description'      => $desc,
                    'created_at'       => $now,
                    'updated_at'       => $now,
                ];
            }
        }

        DB::table('itinerary_day_plans')->insert($rows);

        $this->command->info('✓ Itinerary destinations and day plans seeded.');
    }
}
