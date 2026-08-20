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
            '062201009' => 'Sentiment Analysis of the "Makan Bergizi Gratis" (MBG) Program on Platform X Using the Indobert Model',
            '062202049' => 'The Influence of Environmental Concern on Purchasing Decisions for Wardah Skincare Product',
            '062202063' => 'The Influence of TikTok Influencer Live Streaming on Purchase Decisions for Sabrina Dress Gamis Fashion Products: A Case Study of the TikTok Account @dyscaaaa',
            '062101012' => 'Development of an Educational Game on the Dangers of Online Gambling Using Unity with Simulated Win-Rate Manipulation in a Rock-Paper-Scissors Mini-Game',
            '062201008' => 'Sentiment Analysis of the Brand Reputation of Five-Star Hotels in Surakarta Based on Google Maps Reviews',
            '062201021' => 'Comparative Performance Analysis of XGBoost, Complement Naive Bayes, and Optimized Support Vector Machine Algorithms for Indonesian Hoax Detection',
            '062202038' => 'The Impact of Digital Marketing Strategies on Brand Awareness of Berkat Jahe MSME',
            '062101008' => 'pengembangan aplikasi berbasis website dengan integrasi whatsapp dan sosial media lainnya',
            '062201010' => 'Naive Bayes-Based Sentiment Analysis of X Users Regarding the Use of Generative AI (ChatGPT, DeepSeek, and Meta AI) as Learning Tools',
            '062101001' => 'Implementation of YOLOv8 in a Real-Time Early Fire Detection System for Industrial Environments',
            '062202034' => 'EFEKTIVITAS DISKON FLASH SALE TERHADAP IMPULSE BUYING PADA APLIKASI SHOPEE',
            '062202012' => 'The Effectiveness of Influencer Personal Branding Through Social Media Engagement in Increasing Purchase Intention for Local Culinary Products on TikTok: A Case Study of @ravie.pie’s Culinary Content',
            '062201011' => 'Phishing Website Detection Using an Ensemble Model of Random Forest, XGBoost, and Neural Network with Real-Time Web Implementation',
            '062202047' => 'The Influence of Trust and TikTok Affiliate Marketing Content Quality on the Stages of Purchase Decision-Making Among University Students in Surakarta',
            '062103003' => 'PENGARUH PENYULUHAN GIZI MENGGUNAKAN MEDIA BOLPOIN KIPAS DAN LEAFLET TERHADAP PENGETAHUAN IBU DALAM PENCEGAHAN STUNTING',
            '062103007' => 'The Relationship Between Energy and Protein Intake and Length of Hospital Stay Among Pediatric Patients Without Complications at Indriati Hospital Solo Baru',
        ];

        DB::transaction(function () use ($data) {
            $updatedCount = 0;
            $insertedCount = 0;

            foreach ($data as $nim => $title) {
                // Find student by NIM
                $student = DB::table('students')->where('nim', $nim)->first();

                if ($student) {
                    // Update or insert into final_projects table
                    $fpExists = DB::table('final_projects')->where('student_id', $student->id)->exists();
                    if ($fpExists) {
                        DB::table('final_projects')->where('student_id', $student->id)->update([
                            'title' => $title,
                            'title_en' => $title,
                            'updated_at' => now(),
                        ]);
                        $updatedCount++;
                    } else {
                        DB::table('final_projects')->insert([
                            'student_id' => $student->id,
                            'title' => $title,
                            'title_en' => $title,
                            'status' => 'proposal',
                            'progress_percentage' => 0,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                        $insertedCount++;
                    }

                    // Update skpi_registrations table if registration exists
                    DB::table('skpi_registrations')->where('student_id', $student->id)->update([
                        'judul_ta_indo' => $title,
                        'judul_ta_inggris' => $title,
                        'updated_at' => now(),
                    ]);
                }
            }

            $this->command->info("Finished updating/inserting thesis titles: {$updatedCount} updated, {$insertedCount} inserted.");
        });
    }
}
