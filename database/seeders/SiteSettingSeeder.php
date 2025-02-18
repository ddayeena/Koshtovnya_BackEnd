<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SiteSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('site_settings')->insertOrIgnore([
            ['setting_key' => 'site_logo', 'setting_value' => 'https://res.cloudinary.com/dbmnxdhpp/image/upload/v1739865404/site-settings/elkwcpmkbdn4mh036fnq.png'],
            ['setting_key' => 'footer_email_info', 'setting_value' => 'koshtovnya@gmail.com'],
            ['setting_key' => 'footer_address_info', 'setting_value' => 'вул. Степана Бандери 22, Коломия'],
            ['setting_key' => 'footer_phone_number', 'setting_value' => '+380123456789'],
        ]);
    }
}
