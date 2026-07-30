@csrf

<div class="form-group">
    <label class="form-label">Judul</label>
    <input type="text" name="title" class="form-control" value="{{ old('title', $announcement->title ?? '') }}" required>
</div>

<div class="form-group">
    <label class="form-label">Isi Pengumuman</label>
    <textarea name="content" class="form-control" rows="8" placeholder="Tulis pengumuman...">{{ old('content', $announcement->content ?? '') }}</textarea>
</div>

<div class="row g-3">
    <div class="col-12 col-md-4">
        <label class="form-label">Status</label>
        @php
            $isPublished = old('is_published', ($announcement->is_published ?? false)) ? true : false;
        @endphp
        <div class="d-flex align-items-center gap-2">
            <input type="hidden" name="is_published" value="0">
            <input type="checkbox" name="is_published" value="1" {{ $isPublished ? 'checked' : '' }}>
            <span class="text-muted">Publish</span>
        </div>
    </div>
    <div class="col-12 col-md-8">
        <label class="form-label">Waktu Publish (opsional)</label>
        <input type="datetime-local" name="published_at" class="form-control"
               value="{{ old('published_at', isset($announcement->published_at) ? $announcement->published_at->format('Y-m-d\\TH:i') : '') }}">
        <small class="text-muted">Kalau publish dicentang tapi waktu kosong, otomatis pakai waktu sekarang.</small>
    </div>
</div>


