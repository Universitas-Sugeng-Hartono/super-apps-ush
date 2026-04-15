<?php

namespace App\Services;

class SkpPointCalculator
{
    // Caching for options structure to avoid redundant processing
    private static $cachedStructure = null;

    /**
     * Get the full SKPI dictionary structure mapped from DOCX
     */
    public static function getDictionary(): array
    {
        if (self::$cachedStructure !== null) {
            return self::$cachedStructure;
        }

        $levelsStd = [
            'Internasional' => 'Internasional',
            'Nasional' => 'Nasional',
            'Daerah/Regional' => 'Daerah/Regional',
            'Universitas' => 'Universitas',
            'Fakultas' => 'Fakultas',
            'Jurusan/Program Studi' => 'Jurusan/Program Studi',
        ];

        $levelsNoJurusan = $levelsStd;
        unset($levelsNoJurusan['Jurusan/Program Studi']);

        $levelsSingkat = [
            'Internasional' => 'Internasional',
            'Nasional' => 'Nasional',
            'Regional' => 'Regional',
            'Daerah' => 'Daerah',
        ];

        $roleJuara = [
            'Juara I' => 'Juara I',
            'Juara II' => 'Juara II',
            'Juara III' => 'Juara III',
            'Finalis' => 'Finalis',
            'Peserta Terpilih' => 'Peserta Terpilih'
        ];

        self::$cachedStructure = [
            'wajib' => [
                'label' => 'Kegiatan Wajib',
                'types' => [
                    'entrepreneurship_day' => [
                        'label' => 'Entepreunership Day',
                        'levels' => ['-' => '-'],
                        'roles' => ['Peserta' => 'Peserta'],
                        'points' => ['-' => ['Peserta' => 25]]
                    ],
                    'pkkmb' => [
                        'label' => 'Universitas (PKKMB)',
                        'levels' => ['-' => '-'],
                        'roles' => ['Peserta' => 'Peserta'],
                        'points' => ['-' => ['Peserta' => 20]]
                    ],
                    'ujian_kompetensi' => [
                        'label' => 'TOEFL/TDA/Ujian Kompetensi',
                        'levels' => ['-' => '-'],
                        'roles' => ['Peserta' => 'Peserta'],
                        'points' => ['-' => ['Peserta' => 20]]
                    ],
                    'kuliah_pakar' => [
                        'label' => 'Kuliah pakar',
                        'levels' => ['-' => '-'],
                        'roles' => ['Peserta' => 'Peserta'],
                        'points' => ['-' => ['Peserta' => 20]]
                    ],
                ]
            ],
            'organisasi' => [
                'label' => 'Bidang Organisasi & Kepemimpinan',
                'types' => [
                    'pengurus_organisasi' => [
                        'label' => 'Pengurus Organisasi',
                        'levels' => $levelsStd,
                        'roles' => [
                            'Ketua' => 'Ketua',
                            'Wakil Ketua' => 'Wakil Ketua',
                            'Sekretaris' => 'Sekretaris',
                            'Wakil Sekretaris' => 'Wakil Sekretaris',
                            'Bendahara' => 'Bendahara',
                            'Wakil Bendahara' => 'Wakil Bendahara',
                            'Ketua Seksi' => 'Ketua Seksi',
                            'Anggota Pengurus' => 'Anggota Pengurus'
                        ],
                        'points' => [
                            'Internasional' => ['Ketua'=>100, 'Wakil Ketua'=>95, 'Sekretaris'=>90, 'Wakil Sekretaris'=>85, 'Bendahara'=>80, 'Wakil Bendahara'=>75, 'Ketua Seksi'=>70, 'Anggota Pengurus'=>65],
                            'Nasional' => ['Ketua'=>80, 'Wakil Ketua'=>75, 'Sekretaris'=>70, 'Wakil Sekretaris'=>65, 'Bendahara'=>60, 'Wakil Bendahara'=>55, 'Ketua Seksi'=>50, 'Anggota Pengurus'=>45],
                            'Daerah/Regional' => ['Ketua'=>70, 'Wakil Ketua'=>65, 'Sekretaris'=>60, 'Wakil Sekretaris'=>55, 'Bendahara'=>50, 'Wakil Bendahara'=>45, 'Ketua Seksi'=>40, 'Anggota Pengurus'=>35],
                            'Universitas' => ['Ketua'=>60, 'Wakil Ketua'=>55, 'Sekretaris'=>55, 'Wakil Sekretaris'=>50, 'Bendahara'=>45, 'Wakil Bendahara'=>40, 'Ketua Seksi'=>35, 'Anggota Pengurus'=>30],
                            'Fakultas' => ['Ketua'=>50, 'Wakil Ketua'=>45, 'Sekretaris'=>40, 'Wakil Sekretaris'=>35, 'Bendahara'=>30, 'Wakil Bendahara'=>25, 'Ketua Seksi'=>20, 'Anggota Pengurus'=>15],
                            'Jurusan/Program Studi' => ['Ketua'=>30, 'Wakil Ketua'=>25, 'Sekretaris'=>20, 'Wakil Sekretaris'=>15, 'Bendahara'=>10, 'Wakil Bendahara'=>8, 'Ketua Seksi'=>6, 'Anggota Pengurus'=>5],
                        ]
                    ],
                    'pelatihan_kepemimpinan' => [
                        'label' => 'Mengikuti Pelatihan Kepemimpinan',
                        'levels' => ['Lanjut'=>'Lanjut', 'Menengah'=>'Menengah', 'Dasar'=>'Dasar', 'Lainnya'=>'Lainnya'],
                        'roles' => ['Peserta' => 'Peserta'],
                        'points' => [
                            'Lanjut' => ['Peserta'=>45], 'Menengah' => ['Peserta'=>40], 'Dasar' => ['Peserta'=>35], 'Lainnya' => ['Peserta'=>25]
                        ]
                    ],
                    'panitia_kegiatan' => [
                        'label' => 'Panitia dalam Suatu Kegiatan Kemahasiswaan',
                        'levels' => $levelsStd,
                        'roles' => ['Panitia' => 'Panitia'],
                        'points' => [
                            'Internasional' => ['Panitia'=>50], 'Nasional' => ['Panitia'=>45], 'Daerah/Regional' => ['Panitia'=>40], 'Universitas' => ['Panitia'=>35], 'Fakultas' => ['Panitia'=>30], 'Jurusan/Program Studi' => ['Panitia'=>25]
                        ]
                    ],
                    'calon_organisasi_intra' => [
                        'label' => 'Calon Ketua/Anggota Organisasi Intra',
                        'levels' => ['Universitas'=>'Universitas', 'Fakultas'=>'Fakultas', 'Prodi'=>'Prodi'],
                        'roles' => ['Calon/Anggota' => 'Calon/Anggota'],
                        'points' => ['Universitas' => ['Calon/Anggota'=>35], 'Fakultas' => ['Calon/Anggota'=>30], 'Prodi' => ['Calon/Anggota'=>25]]
                    ],
                    'calon_organisasi_ekstra' => [
                        'label' => 'Calon Ketua/Anggota Organisasi Ekstra Universitas',
                        'levels' => ['Internasional'=>'Internasional', 'Nasional'=>'Nasional', 'Lokal'=>'Lokal'],
                        'roles' => ['Calon/Anggota' => 'Calon/Anggota'],
                        'points' => ['Internasional' => ['Calon/Anggota'=>45], 'Nasional' => ['Calon/Anggota'=>35], 'Lokal' => ['Calon/Anggota'=>30]]
                    ],
                ]
            ],
            'penalaran' => [
                'label' => 'Bidang Penalaran dan Keilmuan',
                'types' => [
                    'lomba_ilmiah' => [
                        'label' => 'Memperoleh Prestasi Lomba Karya Ilmiah dsb.',
                        'levels' => $levelsStd,
                        'roles' => $roleJuara,
                        'points' => [
                            'Internasional' => ['Juara I'=>150, 'Juara II'=>145, 'Juara III'=>140, 'Finalis'=>135, 'Peserta Terpilih'=>130],
                            'Nasional' => ['Juara I'=>100, 'Juara II'=>95, 'Juara III'=>90, 'Finalis'=>85, 'Peserta Terpilih'=>80],
                            'Daerah/Regional' => ['Juara I'=>90, 'Juara II'=>85, 'Juara III'=>80, 'Finalis'=>75, 'Peserta Terpilih'=>70],
                            'Universitas' => ['Juara I'=>65, 'Juara II'=>60, 'Juara III'=>55, 'Finalis'=>50, 'Peserta Terpilih'=>45],
                            'Fakultas' => ['Juara I'=>45, 'Juara II'=>40, 'Juara III'=>35, 'Finalis'=>30, 'Peserta Terpilih'=>25],
                            'Jurusan/Program Studi' => ['Juara I'=>25, 'Juara II'=>20, 'Juara III'=>15, 'Finalis'=>10, 'Peserta Terpilih'=>8],
                        ]
                    ],
                    'ikut_lomba' => [
                        'label' => 'Mengikuti Kegiatan Lomba (Peserta)',
                        'levels' => $levelsStd, // as extracted
                        'roles' => ['Peserta' => 'Peserta'],
                        'points' => [
                            'Internasional' => ['Peserta'=>90],
                            'Nasional' => ['Peserta'=>85],
                            'Daerah/Regional' => ['Peserta'=>70],
                            'Universitas' => ['Peserta'=>30],
                            'Fakultas' => ['Peserta'=>25],
                            'Jurusan/Program Studi' => ['Peserta'=>15],
                        ]
                    ],
                    'forum_ilmiah' => [
                        'label' => 'Mengikuti Kegiatan / Forum Ilmiah',
                        'levels' => array_merge($levelsStd, ['Jurusan/Program Studi' => 'Jurusan/Program Studi']), // Jurusan/Program Studi in table
                        'roles' => ['Pembicara'=>'Pembicara', 'Moderator'=>'Moderator', 'Peserta'=>'Peserta'],
                        'points' => [
                            'Internasional' => ['Pembicara'=>150, 'Moderator'=>70, 'Peserta'=>35],
                            'Nasional' => ['Pembicara'=>80, 'Moderator'=>60, 'Peserta'=>20],
                            'Daerah/Regional' => ['Pembicara'=>65, 'Moderator'=>35, 'Peserta'=>20],
                            'Universitas' => ['Pembicara'=>40, 'Moderator'=>20, 'Peserta'=>10],
                            'Fakultas' => ['Pembicara'=>30, 'Moderator'=>20, 'Peserta'=>15],
                            'Jurusan/Program Studi' => ['Pembicara'=>25, 'Moderator'=>15, 'Peserta'=>10],
                        ]
                    ],
                    'paten' => [
                        'label' => 'Menghasilkan Penemuan Inovasi yang Dipatenkan',
                        'levels' => ['-' => '-'],
                        'roles' => ['Penemu/Kreator' => 'Penemu/Kreator'],
                        'points' => ['-' => ['Penemu/Kreator'=> 150]]
                    ],
                    'karya_ilmiah' => [
                        'label' => 'Karya Ilmiah / Jurnal Dipublikasikan',
                        'levels' => ['Internasional'=>'Internasional', 'Nasional Akreditasi'=>'Nasional Akreditasi', 'Tidak Terakreditasi'=>'Tidak Terakreditasi'],
                        'roles' => ['Ketua/Penulis Utama'=>'Ketua/Penulis Utama', 'Anggota/Penulis Anggota'=>'Anggota/Penulis Anggota'],
                        'points' => [
                            'Internasional' => ['Ketua/Penulis Utama'=>150, 'Anggota/Penulis Anggota'=>100],
                            'Nasional Akreditasi' => ['Ketua/Penulis Utama'=>100, 'Anggota/Penulis Anggota'=>70],
                            'Tidak Terakreditasi' => ['Ketua/Penulis Utama'=>50, 'Anggota/Penulis Anggota'=>30],
                        ]
                    ],
                    'karya_populer' => [
                        'label' => 'Karya Ilmiah Populer di Media Massa',
                        'levels' => ['Internasional'=>'Internasional', 'Nasional'=>'Nasional', 'Daerah/Regional'=>'Daerah/Regional', 'Universitas'=>'Universitas'],
                        'roles' => ['Ketua'=>'Ketua', 'Anggota'=>'Anggota'],
                        'points' => [
                            'Internasional' => ['Ketua'=>70, 'Anggota'=>40],
                            'Nasional' => ['Ketua'=>40, 'Anggota'=>35],
                            'Daerah/Regional' => ['Ketua'=>30, 'Anggota'=>20],
                            'Universitas' => ['Ketua'=>20, 'Anggota'=>10],
                        ]
                    ],
                    'karya_didanai' => [
                        'label' => 'Menghasilkan Karya yang Didanai Pihak Lain',
                        'levels' => ['-' => '-'],
                        'roles' => ['Ketua'=>'Ketua', 'Anggota'=>'Anggota'],
                        'points' => ['-' => ['Ketua'=> 20, 'Anggota'=>10]]
                    ],
                    'berikan_pelatihan' => [
                        'label' => 'Memberikan Pelatihan Penyusunan Karya Tulis',
                        'levels' => ['-' => '-'],
                        'roles' => ['Pemateri'=>'Pemateri'],
                        'points' => ['-' => ['Pemateri'=> 25]]
                    ],
                    'kuliah_tamu' => [
                        'label' => 'Mengikuti Kuliah Tamu/Umum',
                        'levels' => ['-' => '-'],
                        'roles' => ['Peserta'=>'Peserta'],
                        'points' => ['-' => ['Peserta'=> 10]]
                    ],
                    'penelitian_dosen' => [
                        'label' => 'Terlibat Dalam Penelitian Dosen',
                        'levels' => ['-' => '-'],
                        'roles' => ['Asisten/Anggota'=>'Asisten/Anggota'],
                        'points' => ['-' => ['Asisten/Anggota'=> 20]]
                    ],
                    'pilmapres_debat' => [
                        'label' => 'Pemilihan Mahasiswa Berprestasi (Pilmapres) / Debat',
                        'levels' => $levelsNoJurusan,
                        'roles' => $roleJuara,
                        'points' => [
                            'Internasional' => ['Juara I'=>150, 'Juara II'=>145, 'Juara III'=>140, 'Finalis'=>135, 'Peserta Terpilih'=>120],
                            'Nasional' => ['Juara I'=>100, 'Juara II'=>90, 'Juara III'=>85, 'Finalis'=>80, 'Peserta Terpilih'=>50],
                            'Daerah/Regional' => ['Juara I'=>80, 'Juara II'=>75, 'Juara III'=>70, 'Finalis'=>65, 'Peserta Terpilih'=>40],
                            'Universitas' => ['Juara I'=>60, 'Juara II'=>40, 'Juara III'=>30, 'Finalis'=>25, 'Peserta Terpilih'=>20],
                            'Fakultas' => ['Juara I'=>35, 'Juara II'=>30, 'Juara III'=>25, 'Finalis'=>20, 'Peserta Terpilih'=>20], // Adjusted missing Finalis for Fakultas
                        ]
                    ],
                    'pelatihan_softskill' => [
                        'label' => 'Pelatihan / Pembinaan Softskills',
                        'levels' => $levelsSingkat,
                        'roles' => ['Peserta' => 'Peserta'],
                        'points' => [
                            'Internasional' => ['Peserta'=>50], 'Nasional' => ['Peserta'=>45], 'Regional' => ['Peserta'=>40], 'Daerah' => ['Peserta'=>30]
                        ]
                    ],
                ]
            ],
            'minat_bakat' => [
                'label' => 'Bidang Minat dan Bakat',
                'types' => [
                    'prestasi_minat_bakat' => [
                        'label' => 'Prestasi Minat dan Bakat (Olahraga, Seni, dll)',
                        'levels' => $levelsNoJurusan,
                        'roles' => $roleJuara,
                        'points' => [
                            'Internasional' => ['Juara I'=>150, 'Juara II'=>100, 'Juara III'=>85, 'Finalis'=>75, 'Peserta Terpilih'=>65],
                            'Nasional' => ['Juara I'=>100, 'Juara II'=>95, 'Juara III'=>90, 'Finalis'=>80, 'Peserta Terpilih'=>60],
                            'Daerah/Regional' => ['Juara I'=>80, 'Juara II'=>70, 'Juara III'=>50, 'Finalis'=>30, 'Peserta Terpilih'=>20],
                            'Universitas' => ['Juara I'=>50, 'Juara II'=>20, 'Juara III'=>15, 'Finalis'=>10, 'Peserta Terpilih'=>8],
                            'Fakultas' => ['Juara I'=>40, 'Juara II'=>35, 'Juara III'=>20, 'Finalis'=>15, 'Peserta Terpilih'=>10],
                        ]
                    ],
                    'ikut_minat_bakat' => [
                        'label' => 'Mengikuti Kegiatan Minat dan Bakat',
                        'levels' => $levelsNoJurusan,
                        'roles' => ['Delegasi'=>'Delegasi', 'Peserta Undangan'=>'Peserta Undangan', 'Peserta Biasa'=>'Peserta Biasa'],
                        'points' => [
                            'Internasional' => ['Delegasi'=>100, 'Peserta Undangan'=>85, 'Peserta Biasa'=>75],
                            'Nasional' => ['Delegasi'=>70, 'Peserta Undangan'=>45, 'Peserta Biasa'=>25],
                            'Daerah/Regional' => ['Delegasi'=>60, 'Peserta Undangan'=>35, 'Peserta Biasa'=>15],
                            'Universitas' => ['Delegasi'=>45, 'Peserta Undangan'=>25, 'Peserta Biasa'=>15],
                            'Fakultas' => ['Delegasi'=>30, 'Peserta Undangan'=>20, 'Peserta Biasa'=>10], // assumed 10
                        ]
                    ],
                    'pelatih_minat_bakat' => [
                        'label' => 'Menjadi Pelatih/Pembimbing Kegiatan Minat Bakat',
                        'levels' => ['Nasional'=>'Nasional', 'Daerah/Regional'=>'Daerah/Regional', 'Universitas'=>'Universitas', 'Fakultas'=>'Fakultas', 'Lainnya'=>'Lainnya'],
                        'roles' => ['Pelatih/Pembimbing'=>'Pelatih/Pembimbing'],
                        'points' => ['Nasional'=>['Pelatih/Pembimbing'=>100], 'Daerah/Regional'=>['Pelatih/Pembimbing'=>75], 'Universitas'=>['Pelatih/Pembimbing'=>40], 'Fakultas'=>['Pelatih/Pembimbing'=>30], 'Lainnya'=>['Pelatih/Pembimbing'=>10]]
                    ],
                    'pembinaan_khusus' => [
                        'label' => 'Melaksanakan Pembinaan Khusus Minat Bakat',
                        'levels' => ['-' => '-'],
                        'roles' => ['Pembina'=>'Pembina'],
                        'points' => ['-'=>['Pembina'=>35]]
                    ],
                    'mitra_tanding' => [
                        'label' => 'Menjadi Mitra Tanding',
                        'levels' => ['-' => '-'],
                        'roles' => ['Mitra Tanding'=>'Mitra Tanding'],
                        'points' => ['-'=>['Mitra Tanding'=>25]]
                    ],
                    'karya_seni' => [
                        'label' => 'Menghasilkan Karya Seni (Konser, Pameran, dll)',
                        'levels' => ['Internasional'=>'Internasional', 'Nasional'=>'Nasional', 'Regional'=>'Regional', 'Universitas'=>'Universitas', 'Fakultas'=>'Fakultas', 'Prodi'=>'Prodi'],
                        'roles' => ['Kreator/Seniman'=>'Kreator/Seniman'],
                        'points' => ['Internasional'=>['Kreator/Seniman'=>150], 'Nasional'=>['Kreator/Seniman'=>100], 'Regional'=>['Kreator/Seniman'=>80], 'Universitas'=>['Kreator/Seniman'=>60], 'Fakultas'=>['Kreator/Seniman'=>40], 'Prodi'=>['Kreator/Seniman'=>30]]
                    ],
                    'wirausaha' => [
                        'label' => 'Mengelola Kewirausahaan',
                        'levels' => ['Mandiri'=>'Mandiri', 'Kemitraan'=>'Kemitraan'],
                        'roles' => ['Pengelola'=>'Pengelola'],
                        'points' => ['Mandiri'=>['Pengelola'=>30], 'Kemitraan'=>['Pengelola'=>20]]
                    ]
                ]
            ],
            'kepedulian_sosial' => [
                'label' => 'Bidang Kepedulian Sosial',
                'types' => [
                    'bakti_sosial' => [
                        'label' => 'Mengikuti Pelaksanaan Bakti Sosial',
                        'levels' => ['Internasional'=>'Internasional', 'Nasional'=>'Nasional', 'Regional'=>'Regional', 'Universitas'=>'Universitas', 'Fakultas'=>'Fakultas', 'Jurusan/Prodi'=>'Jurusan/Prodi'],
                        'roles' => ['Peserta/Relawan'=>'Peserta/Relawan'],
                        'points' => [
                            'Internasional' => ['Peserta/Relawan'=>80],
                            'Nasional' => ['Peserta/Relawan'=>65],
                            'Regional' => ['Peserta/Relawan'=>40],
                            'Universitas' => ['Peserta/Relawan'=>25],
                            'Fakultas' => ['Peserta/Relawan'=>15],
                            'Jurusan/Prodi' => ['Peserta/Relawan'=>10],
                        ]
                    ],
                    'penanganan_bencana' => [
                        'label' => 'Penanganan Bencana',
                        'levels' => ['-' => '-'],
                        'roles' => ['Relawan'=>'Relawan'],
                        'points' => ['-' => ['Relawan'=>35]]
                    ],
                    'bimbingan_rutin' => [
                        'label' => 'Bimbingan Rutin (Sekolah, Pengajian, TPA, PAUD)',
                        'levels' => ['-' => '-'],
                        'roles' => ['Pembimbing'=>'Pembimbing'],
                        'points' => ['-' => ['Pembimbing'=>25]]
                    ],
                    'kegiatan_sosial_lain' => [
                        'label' => 'Kegiatan Sosial Individual Lainnya',
                        'levels' => ['-' => '-'],
                        'roles' => ['Pelaku'=>'Pelaku'],
                        'points' => ['-' => ['Pelaku'=>10]]
                    ]
                ]
            ],
            'lainnya' => [
                'label' => 'Kegiatan Lainnya',
                'types' => [
                    'upacara_apel' => [
                        'label' => 'Upacara / Apel',
                        'levels' => ['-' => '-'],
                        'roles' => ['Peserta'=>'Peserta'],
                        'points' => ['-' => ['Peserta'=>5]]
                    ],
                    'organisasi_alumni' => [
                        'label' => 'Berpartisipasi dalam Organisasi Alumni',
                        'levels' => ['-' => '-'],
                        'roles' => ['Anggota'=>'Anggota'],
                        'points' => ['-' => ['Anggota'=>15]]
                    ],
                    'studi_banding' => [
                        'label' => 'Kunjungan / Studi Banding',
                        'levels' => ['-' => '-'],
                        'roles' => ['Peserta'=>'Peserta'],
                        'points' => ['-' => ['Peserta'=>20]]
                    ],
                    'magang_non_akademik' => [
                        'label' => 'Magang Kerja Non-Akademik',
                        'levels' => ['-' => '-'],
                        'roles' => ['Peserta Magang'=>'Peserta Magang'],
                        'points' => ['-' => ['Peserta Magang'=>50]]
                    ],
                ]
            ],
            'volunteer' => [
                'label' => 'Volunteer Mahasiswa',
                'types' => [
                    'pmb' => [
                        'label' => 'Penerimaan Mahasiswa Baru / Ekspo PMB',
                        'levels' => ['Universitas'=>'Universitas'],
                        'roles' => ['Volunteer'=>'Volunteer'],
                        'points' => ['Universitas' => ['Volunteer'=>75]]
                    ],
                    'dies_natalis' => [
                        'label' => 'Dies Natalis',
                        'levels' => ['Universitas'=>'Universitas'],
                        'roles' => ['Volunteer'=>'Volunteer'],
                        'points' => ['Universitas' => ['Volunteer'=>75]]
                    ],
                    'panitia_pkkmb' => [
                        'label' => 'Panitia PKKMB',
                        'levels' => ['Universitas'=>'Universitas'],
                        'roles' => ['Panitia'=>'Panitia'],
                        'points' => ['Universitas' => ['Panitia'=>75]]
                    ],
                    'asisten_dosen' => [
                        'label' => 'Asisten Dosen',
                        'levels' => ['Universitas'=>'Universitas'],
                        'roles' => ['Asisten'=>'Asisten'],
                        'points' => ['Universitas' => ['Asisten'=>100]]
                    ]
                ]
            ]
        ];

        return self::$cachedStructure;
    }

    public static function getCategoryOptions(): array
    {
        $opts = [];
        foreach (self::getDictionary() as $catKey => $catData) {
            $opts[$catKey] = $catData['label'];
        }
        return $opts;
    }

    public static function calculate(?string $category, ?string $activityType, ?string $level, ?string $role): int
    {
        if (!$category || !$activityType || !$level || !$role) {
            return 0;
        }

        $dict = self::getDictionary();
        if (!isset($dict[$category])) return 0;
        $catData = $dict[$category]['types'];
        if (!isset($catData[$activityType])) return 0;
        
        $lvlData = $catData[$activityType]['points'];
        if (!isset($lvlData[$level])) return 0;

        $rolesData = $lvlData[$level];
        if (!isset($rolesData[$role])) return 0;

        return $rolesData[$role];
    }
}
