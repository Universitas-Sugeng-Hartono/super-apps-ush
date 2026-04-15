{{-- Rincian table partial for Lampiran 4 --}}
<table class="transkrip-table rincian-table">
    <thead>
        <tr>
            <th style="width:40px">No</th>
            <th>Nama Kegiatan</th>
            <th style="width:120px">Tingkat</th>
            <th style="width:120px">Jabatan/Peran</th>
            <th style="width:80px">Nilai SKP</th>
            <th style="width:80px">Bukti Fisik</th>
        </tr>
    </thead>
    <tbody>
        @forelse($items as $i => $item)
        <tr>
            <td>{{ $i + 1 }}</td>
            <td>{{ $item->activity_type_label ?? $item->activity_type }}</td>
            <td>{{ $item->level }}</td>
            <td>{{ $item->participation_role ?? '-' }}</td>
            <td>{{ $item->skp_points }}</td>
            <td>
                @if($item->certificate)
                    <a href="{{ asset('storage/' . $item->certificate) }}" target="_blank" class="doc-link" title="Lihat Bukti">
                        <i class="bi bi-file-earmark-check"></i>
                    </a>
                @else
                    <span class="text-muted">-</span>
                @endif
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="6" style="text-align:center; color:#9CA3AF; padding:16px;">Belum ada data kegiatan</td>
        </tr>
        @endforelse
    </tbody>
    @if($items->count() > 0)
    <tfoot>
        <tr class="subtotal-row">
            <td colspan="4" style="text-align:right;"><strong>Jumlah SKP</strong></td>
            <td><strong>{{ $items->sum('skp_points') }}</strong></td>
            <td></td>
        </tr>
    </tfoot>
    @endif
</table>
