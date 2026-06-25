<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed admin user
        \App\Models\User::updateOrCreate(
            ['email' => 'admin@wedding.com'],
            [
                'name' => 'Admin Wedding',
                'password' => bcrypt('admin123'),
            ]
        );

        // Seed default invitation
        \App\Models\Invitation::updateOrCreate(
            ['slug' => 'sari-raju'],
            [
                'groom_name_short' => 'Raju',
                'groom_name_full' => 'Raju Aliansyah, S.T.',
                'groom_father' => 'Bpk. Ir. H. Aliansyah Kurnia',
                'groom_mother' => 'Ibu Hj. Siti Aminah',
                
                'bride_name_short' => 'Sari',
                'bride_name_full' => 'Sari Puspita Indah, S.Kom.',
                'bride_father' => 'Bpk. H. Bambang Puspito',
                'bride_mother' => 'Ibu Hjh. Lilis Suryani',
                
                'welcome_message' => 'Maha Suci Allah SWT yang telah menciptakan makhluk-Nya berpasang-pasangan. Dengan memohon rahmat dan ridho-Mu ya Allah, kami bermaksud menyelenggarakan pernikahan putra-putri kami.',
                'greetings_message' => 'Dengan penuh rasa syukur, kami mengundang Bapak/Ibu/Saudara/i untuk menghadiri acara pernikahan kami.',
                
                'event_date' => now()->addMonths(3)->setTime(9, 0, 0), // 3 months in the future
                'akad_time' => '09:00 - 10:30 WIB',
                'akad_location' => 'Masjid Raya Baiturrahman, Jakarta Selatan',
                'resepsi_time' => '11:00 - 14:00 WIB',
                'resepsi_location' => 'Ballroom Grand Hyatt, Jakarta Pusat',
                'maps_url' => 'https://maps.app.goo.gl/k9X558XpX45y4X5e7', // Dummy maps url
                'maps_embed_url' => 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3966.2736173003056!2d106.7975416!3d-6.2276077!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e69f14fb4b8e217%3A0x35607062463bf92a!2sMasjid%20Agung%20Al-Azhar!5e0!3m2!1sid!2sid!4v1700000000000!5m2!1sid!2sid',
                'latitude' => -6.2276077,
                'longitude' => 106.7975416,
                'template' => 'elegant',
                
                'bg_music_url' => asset('audio/sah.mp3')
            ]
        );
    }
}
