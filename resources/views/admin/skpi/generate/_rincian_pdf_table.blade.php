<table style="border-collapse:collapse; width:100%; margin-bottom:14px; font-size:10px;">
    <thead>
        <tr>
            <th style="border:1px solid #000; padding:6px 8px; text-align:center; width:30px; font-weight:normal;">No</th>
            <th style="border:1px solid #000; padding:6px 8px; text-align:center; font-weight:normal;">Nama Kegiatan</th>
            <th style="border:1px solid #000; padding:6px 8px; text-align:center; width:80px; font-weight:normal;">Tempat</th>
            <th style="border:1px solid #000; padding:6px 8px; text-align:center; width:50px; font-weight:normal;">Tahun</th>
            <th style="border:1px solid #000; padding:6px 8px; text-align:center; width:60px; font-weight:normal;">Nilai skp</th>
            <th style="border:1px solid #000; padding:6px 8px; text-align:center; width:70px; font-weight:normal;">Bukti Fisik</th>
        </tr>
    </thead>
    <tbody>
        @forelse($items as $i => $item)
        <tr>
            <td style="border:1px solid #000; padding:4px 6px; text-align:center;">{{ $i + 1 }}</td>
            <td style="border:1px solid #000; padding:4px 6px;">{{ $item->activity_type_label ?? $item->activity_type }}</td>
            <td style="border:1px solid #000; padding:4px 6px; text-align:center;">{{ $item->level ?? '-' }}</td>
            <td style="border:1px solid #000; padding:4px 6px; text-align:center;">{{ $item->created_at ? $item->created_at->format('Y') : '-' }}</td>
            <td style="border:1px solid #000; padding:4px 6px; text-align:center;">{{ $item->skp_points }}</td>
            <td style="border:1px solid #000; padding:4px 6px; text-align:center;">
                @if($item->certificate)
                Terlampir
                @else
                -
                @endif
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="6" style="border:1px solid #000; padding:6px; text-align:center; color:#999;">Belum ada data</td>
        </tr>
        @endforelse
    </tbody>
    @if($items->count() > 0)
    <tfoot>
        <tr>
            <td colspan="4" style="border:1px solid #000; padding:6px 8px; text-align:left;">Jumlah skp</td>
            <td style="border:1px solid #000; padding:6px 8px; text-align:center;">{{ $items->sum('skp_points') }}</td>
            <td style="border:1px solid #000; padding:6px 8px;"></td>
        </tr>
    </tfoot>
    @endif
</table>