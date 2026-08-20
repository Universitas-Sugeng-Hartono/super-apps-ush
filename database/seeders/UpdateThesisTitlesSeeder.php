<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UpdateThesisTitlesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            '062201009' => ['lang' => 'en', 'title' => 'Sentiment Analysis of the "Makan Bergizi Gratis" (MBG) Program on Platform X Using the Indobert Model'],
            '062202049' => ['lang' => 'en', 'title' => 'The Influence of Environmental Concern on Purchasing Decisions for Wardah Skincare Product'],
            '062202063' => ['lang' => 'en', 'title' => 'The Influence of TikTok Influencer Live Streaming on Purchase Decisions for Sabrina Dress Gamis Fashion Products: A Case Study of the TikTok Account @dyscaaaa'],
            '062101012' => ['lang' => 'en', 'title' => 'Development of an Educational Game on the Dangers of Online Gambling Using Unity with Simulated Win-Rate Manipulation in a Rock-Paper-Scissors Mini-Game'],
            '062201008' => ['lang' => 'en', 'title' => 'Sentiment Analysis of the Brand Reputation of Five-Star Hotels in Surakarta Based on Google Maps Reviews'],
            '062201021' => ['lang' => 'en', 'title' => 'Comparative Performance Analysis of XGBoost, Complement Naive Bayes, and Optimized Support Vector Machine Algorithms for Indonesian Hoax Detection'],
            '062202038' => ['lang' => 'en', 'title' => 'The Impact of Digital Marketing Strategies on Brand Awareness of Berkat Jahe MSME'],
            '062101008' => ['lang' => 'id', 'title' => 'pengembangan aplikasi berbasis website dengan integrasi whatsapp dan sosial media lainnya'],
            '062201010' => ['lang' => 'en', 'title' => 'Naive Bayes-Based Sentiment Analysis of X Users Regarding the Use of Generative AI (ChatGPT, DeepSeek, and Meta AI) as Learning Tools'],
            '062101001' => ['lang' => 'en', 'title' => 'Implementation of YOLOv8 in a Real-Time Early Fire Detection System for Industrial Environments'],
            '062202034' => ['lang' => 'id', 'title' => 'EFEKTIVITAS DISKON FLASH SALE TERHADAP IMPULSE BUYING PADA APLIKASI SHOPEE'],
            '062202012' => ['lang' => 'en', 'title' => 'The Effectiveness of Influencer Personal Branding Through Social Media Engagement in Increasing Purchase Intention for Local Culinary Products on TikTok: A Case Study of @ravie.pie’s Culinary Content'],
            '062201011' => ['lang' => 'en', 'title' => 'Phishing Website Detection Using an Ensemble Model of Random Forest, XGBoost, and Neural Network with Real-Time Web Implementation'],
            '062202047' => ['lang' => 'en', 'title' => 'The Influence of Trust and TikTok Affiliate Marketing Content Quality on the Stages of Purchase Decision-Making Among University Students in Surakarta'],
            '062103003' => ['lang' => 'id', 'title' => 'PENGARUH PENYULUHAN GIZI MENGGUNAKAN MEDIA BOLPOIN KIPAS DAN LEAFLET TERHADAP PENGETAHUAN IBU DALAM PENCEGAHAN STUNTING'],
            '062103007' => ['lang' => 'en', 'title' => 'The Relationship Between Energy and Protein Intake and Length of Hospital Stay Among Pediatric Patients Without Complications at Indriati Hospital Solo Baru'],
        ];

        DB::transaction(function () use ($data) {
            $updatedCount = 0;
            $insertedCount = 0;

            foreach ($data as $nim => $info) {
                // Find student by NIM
                $student = DB::table('students')->where('nim', $nim)->first();

                if ($student) {
                    $lang = $info['lang'];
                    $title = $info['title'];

                    $updateFp = [
                        'updated_at' => now(),
                    ];
                    $updateSkpi = [
                        'updated_at' => now(),
                    ];

                    if ($lang === 'id') {
                        $updateFp['title'] = $title;
                        $updateSkpi['judul_ta_indo'] = $title;
                    } else {
                        $updateFp['title_en'] = $title;
                        $updateSkpi['judul_ta_inggris'] = $title;
                    }

                    // Update final_projects table if exists
                    $fpUpdated = DB::table('final_projects')->where('student_id', $student->id)->update($updateFp);
                    if ($fpUpdated) {
                        $updatedCount++;
                    }

                    // Update skpi_registrations table if exists
                    DB::table('skpi_registrations')->where('student_id', $student->id)->update($updateSkpi);
                }
            }

            $this->command->info("Finished updating thesis titles: {$updatedCount} records updated.");
        });
    }
}
