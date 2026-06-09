<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Export SKPI Registrations</title>
</head>
<body>
<table>
    <thead>
        <tr>
            <th style="font-weight: bold; text-align: center; background-color: #f8cbad;">No</th>
            <th style="font-weight: bold; text-align: center; background-color: #f8cbad;">NIM</th>
            <th style="font-weight: bold; text-align: center; background-color: #f8cbad;">NIK</th>
            <th style="font-weight: bold; text-align: center; background-color: #f8cbad;">NISN</th>
            <th style="font-weight: bold; text-align: center; background-color: #f8cbad;">Nama Lengkap</th>
            <th style="font-weight: bold; text-align: center; background-color: #f8cbad;">Nama Ayah/Wali</th>
            <th style="font-weight: bold; text-align: center; background-color: #f8cbad;">Nama Ibu Kandung</th>
            <th style="font-weight: bold; text-align: center; background-color: #f8cbad;">Jenis Kelamin</th>
            <th style="font-weight: bold; text-align: center; background-color: #f8cbad;">Tempat Lahir</th>
            <th style="font-weight: bold; text-align: center; background-color: #f8cbad;">Tanggal Lahir</th>
            <th style="font-weight: bold; text-align: center; background-color: #f8cbad;">Alamat</th>
            <th style="font-weight: bold; text-align: center; background-color: #f8cbad;">No. Telepon</th>
            <th style="font-weight: bold; text-align: center; background-color: #f8cbad;">No. Telepon Orang Tua</th>
            <th style="font-weight: bold; text-align: center; background-color: #f8cbad;">Email</th>
            <th style="font-weight: bold; text-align: center; background-color: #f8cbad;">Angkatan</th>
            <th style="font-weight: bold; text-align: center; background-color: #f8cbad;">Program Studi</th>
            <th style="font-weight: bold; text-align: center; background-color: #f8cbad;">Fakultas</th>
            <th style="font-weight: bold; text-align: center; background-color: #f8cbad;">IPK</th>
            <th style="font-weight: bold; text-align: center; background-color: #f8cbad;">SKS</th>
            <th style="font-weight: bold; text-align: center; background-color: #f8cbad;">Status Mahasiswa</th>
            <th style="font-weight: bold; text-align: center; background-color: #f8cbad;">Tahun Masuk</th>
            <th style="font-weight: bold; text-align: center; background-color: #f8cbad;">Tanggal Lulus</th>
            <th style="font-weight: bold; text-align: center; background-color: #f8cbad;">Status Pendaftaran SKPI</th>
            <th style="font-weight: bold; text-align: center; background-color: #f8cbad;">Nomor Ijazah</th>
            <th style="font-weight: bold; text-align: center; background-color: #f8cbad;">Tanggal Pengajuan SKPI</th>
        </tr>
    </thead>
    <tbody>
    @foreach($registrations as $index => $reg)
        @php
            $student = $reg->student;
        @endphp
        <tr>
            <td style="text-align: center;">{{ $index + 1 }}</td>
            <td style="text-align: center;">'{{ $student->nim ?? '-' }}</td>
            <td style="text-align: center;">'{{ $student->nik ?? '-' }}</td>
            <td style="text-align: center;">'{{ $student->nisn ?? '-' }}</td>
            <td>{{ $student->nama_lengkap ?? '-' }}</td>
            <td>{{ $student->nama_orangtua ?? '-' }}</td>
            <td>{{ $student->nama_ibu_kandung ?? '-' }}</td>
            <td style="text-align: center;">{{ $student->jenis_kelamin === 'L' ? 'Laki-laki' : ($student->jenis_kelamin === 'P' ? 'Perempuan' : '-') }}</td>
            <td>{{ $student->tempat_lahir ?? '-' }}</td>
            <td style="text-align: center;">{{ $student->tanggal_lahir ? \Carbon\Carbon::parse($student->tanggal_lahir)->locale('id')->translatedFormat('d F Y') : '-' }}</td>
            <td>{{ $student->alamat ?? '-' }}</td>
            <td style="text-align: center;">'{{ $student->no_telepon ?? '-' }}</td>
            <td style="text-align: center;">'{{ $student->no_telepon_orangtua ?? '-' }}</td>
            <td>{{ $student->email ?? '-' }}</td>
            <td style="text-align: center;">{{ $student->angkatan ?? '-' }}</td>
            <td style="text-align: center;">{{ $student->program_studi ?? '-' }}</td>
            <td style="text-align: center;">{{ $student->fakultas ?? '-' }}</td>
            <td style="text-align: center;">{{ $student->ipk ?? '-' }}</td>
            <td style="text-align: center;">{{ $student->sks ?? '-' }}</td>
            <td style="text-align: center;">{{ $student->status_mahasiswa ?? '-' }}</td>
            <td style="text-align: center;">{{ $student->tanggal_masuk ? \Carbon\Carbon::parse($student->tanggal_masuk)->locale('id')->translatedFormat('Y') : '-' }}</td>
            <td style="text-align: center;">{{ $reg->periode_lulus ? \Carbon\Carbon::parse($reg->periode_lulus)->locale('id')->translatedFormat('d F Y') : '-' }}</td>
            <td style="text-align: center;">
                @if($reg->status === 'draft')
                    Draft
                @elseif($reg->status === 'pending')
                    Pending
                @elseif($reg->status === 'needs_revision')
                    Revisi
                @elseif($reg->status === 'approved')
                    Disetujui
                @else
                    {{ ucfirst($reg->status) }}
                @endif
            </td>
            <td>'{{ $reg->nomor_ijazah ?? '-' }}</td>
            <td style="text-align: center;">{{ $reg->created_at ? $reg->created_at->format('d-m-Y H:i') : '-' }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
</body>
</html>
