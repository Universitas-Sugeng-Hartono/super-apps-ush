<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Course;

class CourseSeeder extends Seeder
{
    public function run(): void
    {
        $courses = [
            // Semester 1
            ['IUM', '0001', 1, 'Bahasa Indonesia'],
            ['IUM', '0002', 1, 'Pancasila Education'],
            ['IUM', '0003', 1, 'Citizenship Education'],
            ['IUM', '0004', 1, 'Religious Education'],
            ['IUM', '0005', 1, 'Mandarin I'],
            ['IUM', '0011', 1, 'Academic English'],
            ['IUM', '0012', 1, 'Design Thinking'],
            ['IDM', '0501', 1, 'Economic Theory'],
            ['IDM', '0502', 1, 'Digital Information System'],

            // Semester 2
            ['IUM', '0006', 2, 'Mandarin II'],
            ['IFM', '0202', 2, 'English for Business'],
            ['IDM', '0503', 2, 'Business & Management Introduction'],
            ['IDM', '0504', 2, 'Business Statistic'],
            ['IDM', '0505', 2, 'Business Accounting'],
            ['IDM', '0506', 2, 'Programming Algorithm & Fundamental'],
            ['IDM', '0507', 2, 'Database Management'],
            ['IDM', '0508', 2, 'Organizational Behaviour'],

            // Semester 3
            ['IUM', '0007', 3, 'Mandarin III'],
            ['IDM', '0509', 3, 'Marketing Management'],
            ['IDM', '0510', 3, 'Financial Management'],
            ['IDM', '0511', 3, 'Human Resource Management'],
            ['IDM', '0512', 3, 'Operational Management'],
            ['IDM', '0513', 3, 'Strategic Management'],
            ['IDM', '0514', 3, 'Web Development'],
            ['IDM', '0515', 3, 'Business Intelligence'],
            ['IDM', '0516', 3, 'Risk Management'],

            // Semester 4
            ['IUM', '0008', 4, 'Mandarin IV'],
            ['IDM', '0517', 4, 'Digital Marketing'],
            ['IDM', '0518', 4, 'Digital Economic Literacy'],
            ['IUM', '0013', 4, 'Introduction of Entrepreneurship'],
            ['IDM', '0519', 4, 'UI/UX'],
            ['IDM', '0520', 4, 'Mobile Development'],
            ['IDM', '0521', 4, 'Methodology Research'],
            ['IFM', '0201', 4, 'Professional Development'],

            // Semester 5.1 DM
            ['IUM', '0009', 5, 'Mandarin V'],
            ['IDM', '0522', 5, 'Sistem Informasi Keuangan I'],
            ['IDM', '0523', 5, 'Digital Marketing Research & Trend'],
            ['IDM', '0524', 5, 'Digital Branding & Reputation'],
            ['IDM', '0525', 5, 'Consumer Analytics'],
            ['IDM', '0526', 5, 'Digital Marketing Strategic'],
            ['IDM', '0527', 5, 'E-commerce'],
            ['IDM', '0528', 5, 'Social Media Management'],
            ['IDM', '0529', 5, 'Visual Communication'],

            // Semester 5.2 DF
            ['IUM', '0009', 5, 'Mandarin V'],
            ['IDM', '0522', 5, 'Sistem Informasi Keuangan I'],
            ['IDM', '0530', 5, 'Digital Financial (Financial Technology)'],
            ['IDM', '0531', 5, 'Accounting Management'],
            ['IDM', '0532', 5, 'Business Valuation'],
            ['IDM', '0533', 5, 'Investment Management'],
            ['IDM', '0534', 5, 'Financial Accounting'],
            ['IDM', '0535', 5, 'Tax of Business'],
            ['IDM', '0536', 5, 'Capital Fundraising'],

            // Semester 5.3 DBA
            ['IUM', '0009', 5, 'Mandarin V'],
            ['IDM', '0522', 5, 'Sistem Informasi Keuangan I'],
            ['IDM', '0537', 5, 'Big Data for Business'],
            ['IDM', '0538', 5, 'Business Statistic II'],
            ['IDM', '0539', 5, 'Business Mathematics'],
            ['IDM', '0540', 5, 'Ethical Hacking for Business'],
            ['IDM', '0541', 5, 'Data Mining'],
            ['IDM', '0542', 5, 'Data Processing and Visualization'],
            ['IDM', '0543', 5, 'Data Science Introduction'],

            // Semester 5.4 EBC
            ['IUM', '0009', 5, 'Mandarin V'],
            ['IDM', '0522', 5, 'Sistem Informasi Keuangan I'],
            ['IDM', '0544', 5, 'Product Management'],
            ['IDM', '0545', 5, 'Content Creation'],
            ['IDM', '0546', 5, 'Business Leadership'],
            ['IDM', '0547', 5, 'Economic Creative'],
            ['IDM', '0548', 5, 'Business Integrity & Law'],
            ['IDM', '0549', 5, 'Business Trend & Culture'],
            ['IDM', '0550', 5, 'Model Business Development'],

            // Semester 6.1 DM
            ['IUM', '0010', 6, 'Mandarin VI'],
            ['IDM', '0551', 6, 'Sistem Informasi Keuangan II'],
            ['IDM', '0552', 6, 'Search Engine Optimization (SEO) & SEM'],
            ['IDM', '0553', 6, 'Integrated Marketing Communication'],
            ['IDM', '0554', 6, 'Digital Creativepreneurship'],
            ['IDM', '0555', 6, 'Artificial Intelligence for Marketing'],
            ['IDM', '0556', 6, 'Digital Marketing Project Capstone'],

            // Semester 6.2 DF
            ['IUM', '0010', 6, 'Mandarin VI'],
            ['IDM', '0551', 6, 'Sistem Informasi Keuangan II'],
            ['IDM', '0557', 6, 'Budgeting'],
            ['IDM', '0558', 6, 'International Finance Management'],
            ['IDM', '0554', 6, 'Digital Creativepreneurship'],
            ['IDM', '0559', 6, 'Artificial Intelligence for Finance'],
            ['IDM', '0560', 6, 'Digital Finance Regulatory'],

            // Semester 6.3 DBA
            ['IUM', '0010', 6, 'Mandarin VI'],
            ['IDM', '0551', 6, 'Sistem Informasi Keuangan II'],
            ['IDM', '0561', 6, 'Digital Business Research & Trend'],
            ['IDM', '0562', 6, 'Artificial Intelligence'],
            ['IDM', '0563', 6, 'Digital Supply Chain'],
            ['IDM', '0554', 6, 'Digital Creativepreneurship'],
            ['IDM', '0564', 6, 'Data Analytics Project'],

            // Semester 6.4 EBC
            ['IUM', '0010', 6, 'Mandarin VI'],
            ['IDM', '0551', 6, 'Sistem Informasi Keuangan II'],
            ['IDM', '0565', 6, 'Event Management'],
            ['IDM', '0566', 6, 'Internet of Thing'],
            ['IDM', '0554', 6, 'Digital Creativepreneurship'],
            ['IDM', '0567', 6, 'Artificial Intelligence for Business'],
            ['IDM', '0568', 6, 'Business Feasibility and Funding'],

            // Semester 7
            ['IDM', '0569', 7, 'Internship'],
            ['IDM', '0570', 7, 'Thematic Community Service Program'],
            ['IDM', '0571', 7, 'Proposal Seminar'],

            // Semester 8
            ['IDM', '0572', 8, 'Thesis/Final Project'],
        ];

        foreach ($courses as $c) {
            Course::create([
                'code_prefix' => $c[0],
                'code_number' => $c[1],
                'semester'    => $c[2],
                'name'        => $c[3],
                'sks'         => 3, // default SKS, bisa diubah sesuai kebutuhan
            ]);
        }
    }
}//php artisan db:seed --class=CourseSeeder