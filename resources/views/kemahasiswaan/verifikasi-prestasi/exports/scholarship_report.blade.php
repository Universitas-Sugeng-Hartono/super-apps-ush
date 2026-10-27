<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Beasiswa</title>
</head>
<body>
<table>
    <thead>
        {{-- Baris Judul Laporan --}}
        <tr>
            <th colspan="17" style="font-size: 15px; font-weight: bold; text-align: left; color: #1E293B;">
                LAPORAN EVALUASI CAPAIAN BEASISWA &amp; STUDENT AMBASSADOR
            </th>
        </tr>
        <tr>
            <th colspan="17" style="font-size: 12px; font-weight: bold; text-align: left; color: #334155;">
                UNIVERSITAS SUGENG HARTONO (POKEMA &amp; SIAKAD INTEGRATION)
            </th>
        </tr>
        <tr>
            <th colspan="17" style="font-size: 10px; font-style: italic; text-align: left; color: #64748B;">
                Tanggal Ekspor: {{ $exportDate }} | Filter: {{ $filterDescription }} | Status Portal: {{ $isPortalOpen ? 'Dibuka (Aktif)' : 'Ditutup (Periode Evaluasi)' }}
            </th>
        </tr>
        <tr>
            <th colspan="17"></th>
        </tr>

        {{-- Baris Header Kelompok (Group Header) --}}
        <tr>
            <th colspan="6" style="background-color: #1E3A8A; color: #FFFFFF; font-weight: bold; text-align: center; border: 1px solid #0F172A; font-size: 11px;">
                IDENTITAS MAHASISWA
            </th>
            <th colspan="3" style="background-color: #0369A1; color: #FFFFFF; font-weight: bold; text-align: center; border: 1px solid #0F172A; font-size: 11px;">
                CAPAIAN AKADEMIK
            </th>
            <th colspan="4" style="background-color: #B45309; color: #FFFFFF; font-weight: bold; text-align: center; border: 1px solid #0F172A; font-size: 11px;">
                POIN POKEMA &amp; SKP
            </th>
            <th colspan="4" style="background-color: #047857; color: #FFFFFF; font-weight: bold; text-align: center; border: 1px solid #0F172A; font-size: 11px;">
                EVALUASI &amp; REKOMENDASI BEASISWA
            </th>
        </tr>

        {{-- Baris Header Kolom --}}
        <tr>
            {{-- Identitas --}}
            <th style="background-color: #DBEAFE; color: #1E3A8A; font-weight: bold; text-align: center; border: 1px solid #94A3B8;">No</th>
            <th style="background-color: #DBEAFE; color: #1E3A8A; font-weight: bold; text-align: center; border: 1px solid #94A3B8;">NIM</th>
            <th style="background-color: #DBEAFE; color: #1E3A8A; font-weight: bold; text-align: left; border: 1px solid #94A3B8;">Nama Mahasiswa</th>
            <th style="background-color: #DBEAFE; color: #1E3A8A; font-weight: bold; text-align: left; border: 1px solid #94A3B8;">Program Studi</th>
            <th style="background-color: #DBEAFE; color: #1E3A8A; font-weight: bold; text-align: center; border: 1px solid #94A3B8;">Angkatan</th>
            <th style="background-color: #DBEAFE; color: #1E3A8A; font-weight: bold; text-align: center; border: 1px solid #94A3B8;">Status Mahasiswa</th>

            {{-- Capaian Akademik --}}
            <th style="background-color: #E0F2FE; color: #0369A1; font-weight: bold; text-align: center; border: 1px solid #94A3B8;">Semester</th>
            <th style="background-color: #E0F2FE; color: #0369A1; font-weight: bold; text-align: center; border: 1px solid #94A3B8;">IPK Resmi</th>
            <th style="background-color: #E0F2FE; color: #0369A1; font-weight: bold; text-align: center; border: 1px solid #94A3B8;">Status Standar IPK</th>

            {{-- Poin POKEMA --}}
            <th style="background-color: #FEF3C7; color: #92400E; font-weight: bold; text-align: center; border: 1px solid #94A3B8;">Total Poin POKEMA</th>
            <th style="background-color: #FEF3C7; color: #92400E; font-weight: bold; text-align: center; border: 1px solid #94A3B8;">Target Poin</th>
            <th style="background-color: #FEF3C7; color: #92400E; font-weight: bold; text-align: center; border: 1px solid #94A3B8;">Capaian 7 Bidang</th>
            <th style="background-color: #FEF3C7; color: #92400E; font-weight: bold; text-align: center; border: 1px solid #94A3B8;">Status Bidang</th>

            {{-- Evaluasi / Rekomendasi --}}
            <th style="background-color: #D1FAE5; color: #065F46; font-weight: bold; text-align: center; border: 1px solid #94A3B8;">Beasiswa Saat Ini</th>
            <th style="background-color: #D1FAE5; color: #065F46; font-weight: bold; text-align: center; border: 1px solid #94A3B8;">Rekomendasi Baru</th>
            <th style="background-color: #D1FAE5; color: #065F46; font-weight: bold; text-align: left; border: 1px solid #94A3B8;">Keputusan / Status Rekomendasi</th>
            <th style="background-color: #D1FAE5; color: #065F46; font-weight: bold; text-align: left; border: 1px solid #94A3B8;">Catatan &amp; Alasan Evaluasi</th>
        </tr>
    </thead>
    <tbody>
        @forelse($students as $index => $student)
            @php
                $eval = $student->pokema_evaluation ?? [];
                $ipk = (float) ($eval['ipk'] ?? $student->ipk ?? 0);
                $isIpkSafe = ($ipk >= 3.00);
                $totalPts = (int) ($eval['total_approved_points'] ?? 0);
                $targetPts = (int) ($eval['total_target_points'] ?? $eval['target_current'] ?? 0);
                $fulfilledFields = (int) ($eval['fulfilled_fields_count'] ?? 0);
                $allFieldsMet = (bool) ($eval['all_fields_met'] ?? false);
                $unfulfilledCount = (int) ($eval['unfulfilled_fields_count'] ?? 0);
                $currentBeasiswa = $student->beasiswa ?: ($eval['current_beasiswa'] ?? '0%');
                $recommendedBeasiswa = $eval['recommended_beasiswa'] ?? $currentBeasiswa;
            @endphp
            <tr>
                {{-- No --}}
                <td style="text-align: center; border: 1px solid #CBD5E1; vertical-align: middle;">
                    {{ $index + 1 }}
                </td>

                {{-- NIM (Prepend kutip agar terbaca sebagai teks & angka 0 di depan tidak hilang di Excel) --}}
                <td style="text-align: center; border: 1px solid #CBD5E1; vertical-align: middle;">
                    '{{ $student->nim }}
                </td>

                {{-- Nama Mahasiswa --}}
                <td style="font-weight: 600; border: 1px solid #CBD5E1; vertical-align: middle;">
                    {{ $student->nama_lengkap }}
                </td>

                {{-- Program Studi --}}
                <td style="border: 1px solid #CBD5E1; vertical-align: middle;">
                    {{ $student->program_studi }}
                </td>

                {{-- Angkatan --}}
                <td style="text-align: center; border: 1px solid #CBD5E1; vertical-align: middle;">
                    {{ $student->angkatan ?: '-' }}
                </td>

                {{-- Status Mahasiswa --}}
                <td style="text-align: center; border: 1px solid #CBD5E1; vertical-align: middle;">
                    {{ $student->status === 'studentambassador' ? 'Student Ambassador' : 'Mahasiswa' }}
                </td>

                {{-- Semester --}}
                <td style="text-align: center; border: 1px solid #CBD5E1; vertical-align: middle;">
                    {{ $eval['semester'] ?? $student->semester ?? '-' }}
                </td>

                {{-- IPK Resmi --}}
                <td style="text-align: center; font-weight: 600; border: 1px solid #CBD5E1; vertical-align: middle;">
                    {{ number_format($ipk, 2) }}
                </td>

                {{-- Status Standar IPK --}}
                <td style="text-align: center; border: 1px solid #CBD5E1; vertical-align: middle; color: {{ $isIpkSafe ? '#059669' : '#DC2626' }};">
                    {{ $isIpkSafe ? 'Memenuhi (≥ 3.00)' : 'Di Bawah Standar (< 3.00)' }}
                </td>

                {{-- Total Poin POKEMA Approved --}}
                <td style="text-align: center; font-weight: 600; border: 1px solid #CBD5E1; vertical-align: middle;">
                    {{ $totalPts }} Poin
                </td>

                {{-- Target Poin Periode --}}
                <td style="text-align: center; border: 1px solid #CBD5E1; vertical-align: middle;">
                    {{ $targetPts > 0 ? "{$targetPts} Poin" : '-' }}
                </td>

                {{-- Capaian 7 Bidang --}}
                <td style="text-align: center; border: 1px solid #CBD5E1; vertical-align: middle;">
                    {{ $fulfilledFields }} / 7 Bidang
                </td>

                {{-- Status Kelengkapan Bidang --}}
                <td style="text-align: center; border: 1px solid #CBD5E1; vertical-align: middle; color: {{ $allFieldsMet ? '#059669' : '#D97706' }};">
                    {{ $allFieldsMet ? 'Lengkap (7 Bidang Terpenuhi)' : ($unfulfilledCount > 0 ? "Kurang {$unfulfilledCount} Bidang" : 'Belum Ada Kegiatan') }}
                </td>

                {{-- Beasiswa Saat Ini --}}
                <td style="text-align: center; font-weight: bold; border: 1px solid #CBD5E1; vertical-align: middle; background-color: #F8FAFC;">
                    {{ $currentBeasiswa }}
                </td>

                {{-- Rekomendasi Beasiswa Baru --}}
                <td style="text-align: center; font-weight: bold; border: 1px solid #CBD5E1; vertical-align: middle; background-color: #ECFDF5; color: #047857;">
                    {{ $recommendedBeasiswa }}
                </td>

                {{-- Keputusan / Status Rekomendasi --}}
                <td style="border: 1px solid #CBD5E1; vertical-align: middle; font-weight: 500;">
                    {{ $eval['action_label'] ?? '-' }}
                </td>

                {{-- Catatan & Alasan Evaluasi POKEMA --}}
                <td style="border: 1px solid #CBD5E1; vertical-align: middle; font-size: 11px; color: #475569;">
                    {{ $eval['reason'] ?? '-' }}
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="17" style="text-align: center; padding: 20px; font-style: italic; color: #64748B;">
                    Tidak ada data penerima beasiswa / student ambassador yang sesuai dengan filter.
                </td>
            </tr>
        @endforelse
    </tbody>
</table>
</body>
</html>
