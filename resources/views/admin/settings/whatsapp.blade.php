@extends('admin.layouts.super-app')

@section('content')
@php
    $pageTitle = 'Pengaturan WhatsApp';
@endphp

@push('css')
<style>
    .settings-card {
        background: white;
        border-radius: 20px;
        box-shadow: 0 4px 24px rgba(0,0,0,0.07);
        overflow: hidden;
    }
    .settings-header {
        background: linear-gradient(135deg, #25D366 0%, #128C7E 100%);
        padding: 28px 32px 24px;
        color: white;
    }
    .settings-header h3 {
        font-size: 1.4rem;
        font-weight: 700;
        margin: 0 0 4px;
    }
    .settings-header p {
        margin: 0;
        opacity: 0.85;
        font-size: 0.875rem;
    }
    .settings-body {
        padding: 32px;
    }
    .section-divider {
        border: none;
        border-top: 1.5px dashed #E5E7EB;
        margin: 28px 0;
    }
    .section-label {
        font-size: 0.78rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.8px;
        color: #6B7280;
        margin-bottom: 16px;
    }
    .form-label {
        font-size: 0.875rem;
        font-weight: 600;
        color: #374151;
        margin-bottom: 6px;
    }
    .form-label .hint {
        font-weight: 400;
        color: #9CA3AF;
        font-size: 0.78rem;
        margin-left: 4px;
    }
    .form-control, .form-select {
        border-radius: 10px;
        border: 1.5px solid #E5E7EB;
        padding: 10px 14px;
        font-size: 0.875rem;
        transition: border-color 0.2s;
    }
    .form-control:focus, .form-select:focus {
        border-color: #25D366;
        box-shadow: 0 0 0 3px rgba(37,211,102,0.12);
    }
    /* Toggle Switch */
    .toggle-wrapper {
        display: flex;
        align-items: center;
        gap: 14px;
        background: #F9FAFB;
        border-radius: 14px;
        padding: 16px 20px;
        border: 1.5px solid #E5E7EB;
    }
    .form-switch .form-check-input {
        width: 48px;
        height: 26px;
        cursor: pointer;
    }
    .form-switch .form-check-input:checked {
        background-color: #25D366;
        border-color: #25D366;
    }
    .form-switch .form-check-input:focus {
        box-shadow: 0 0 0 3px rgba(37,211,102,0.2);
    }
    .toggle-info strong {
        font-size: 0.9rem;
        color: #111827;
        display: block;
    }
    .toggle-info span {
        font-size: 0.78rem;
        color: #6B7280;
    }
    /* Role Checkboxes */
    .role-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
        gap: 12px;
    }
    .role-card {
        position: relative;
    }
    .role-card input[type="checkbox"] {
        position: absolute;
        opacity: 0;
        width: 0; height: 0;
    }
    .role-card label {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 12px 16px;
        border-radius: 12px;
        border: 2px solid #E5E7EB;
        cursor: pointer;
        transition: all 0.2s ease;
        font-size: 0.875rem;
        font-weight: 500;
        color: #374151;
        background: #F9FAFB;
        user-select: none;
    }
    .role-card label .role-icon {
        width: 32px; height: 32px;
        border-radius: 8px;
        display: flex; align-items: center; justify-content: center;
        font-size: 16px;
        background: #E5E7EB;
        color: #6B7280;
        transition: all 0.2s;
        flex-shrink: 0;
    }
    .role-card input:checked + label {
        border-color: #25D366;
        background: #F0FDF4;
        color: #166534;
    }
    .role-card input:checked + label .role-icon {
        background: #25D366;
        color: white;
    }
    .role-card label:hover {
        border-color: #86EFAC;
        background: #F0FDF4;
    }
    /* Preview box */
    .wa-preview {
        background: #F0FDF4;
        border-radius: 16px;
        padding: 20px 24px;
        border: 1.5px solid #86EFAC;
        display: flex;
        align-items: flex-start;
        gap: 16px;
    }
    .wa-preview-icon {
        width: 52px; height: 52px;
        border-radius: 50%;
        background: linear-gradient(135deg, #25D366, #128C7E);
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
        box-shadow: 0 4px 12px rgba(37,211,102,0.3);
    }
    .wa-preview-icon svg { width: 28px; height: 28px; fill: white; }
    .wa-preview-info small {
        display: block;
        font-size: 0.72rem;
        font-weight: 700;
        color: #25D366;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .wa-preview-info p {
        margin: 2px 0 6px;
        font-weight: 600;
        color: #111827;
        font-size: 0.9rem;
    }
    .wa-preview-info .preview-msg {
        background: white;
        border-radius: 8px;
        padding: 8px 12px;
        font-size: 0.8rem;
        color: #374151;
        border-left: 3px solid #25D366;
        margin-top: 6px;
        white-space: pre-wrap;
    }
    /* Save Button */
    .btn-save-settings {
        background: linear-gradient(135deg, #25D366, #128C7E);
        color: white;
        border: none;
        border-radius: 12px;
        padding: 12px 32px;
        font-size: 0.9rem;
        font-weight: 700;
        cursor: pointer;
        transition: filter 0.2s, transform 0.15s;
        box-shadow: 0 4px 14px rgba(37,211,102,0.35);
    }
    .btn-save-settings:hover {
        filter: brightness(1.08);
        transform: translateY(-1px);
        color: white;
    }
    .char-counter {
        font-size: 0.75rem;
        color: #9CA3AF;
        text-align: right;
        margin-top: 4px;
    }
    .char-counter.warn { color: #F59E0B; }
    .char-counter.danger { color: #EF4444; }
</style>
@endpush

<div class="container-fluid px-0">
    <div class="row justify-content-center">
        <div class="col-12 col-xl-9">

            {{-- Alert Success --}}
            @if(session('success'))
            <div class="alert alert-success d-flex align-items-center gap-2 rounded-3 mb-4 shadow-sm">
                <i class="bi bi-check-circle-fill fs-5"></i>
                <span>{{ session('success') }}</span>
            </div>
            @endif

            <form method="POST" action="{{ route('admin.settings.whatsapp.save') }}" id="waSettingsForm">
                @csrf

                <div class="settings-card">
                    {{-- Header --}}
                    <div class="settings-header">
                        <div class="d-flex align-items-center gap-3">
                            <div style="width:48px;height:48px;background:rgba(255,255,255,0.2);border-radius:14px;display:flex;align-items:center;justify-content:center;">
                                <svg viewBox="0 0 24 24" style="width:26px;height:26px;fill:white;" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
                                </svg>
                            </div>
                            <div>
                                <h3>Pengaturan Tombol WhatsApp</h3>
                                <p>Konfigurasi nomor, pesan, dan visibilitas tombol WA untuk mahasiswa & pengguna lainnya.</p>
                            </div>
                        </div>
                    </div>

                    <div class="settings-body">

                        {{-- ── SECTION 1: Toggle Aktif/Nonaktif ── --}}
                        <p class="section-label"><i class="bi bi-toggle-on me-1"></i> Status Tombol</p>
                        <div class="toggle-wrapper mb-4">
                            <div class="form-check form-switch mb-0">
                                <input class="form-check-input" type="checkbox" role="switch"
                                    id="wa_button_enabled" name="wa_button_enabled"
                                    {{ ($settings['wa_button_enabled']->value ?? '1') == '1' ? 'checked' : '' }}>
                            </div>
                            <div class="toggle-info">
                                <strong>Aktifkan Tombol WhatsApp</strong>
                                <span>Jika dimatikan, tombol WA tidak akan muncul di halaman manapun.</span>
                            </div>
                        </div>

                        <hr class="section-divider">

                        {{-- ── SECTION 2: Nomor & Template ── --}}
                        <p class="section-label"><i class="bi bi-telephone me-1"></i> Kontak & Pesan</p>

                        <div class="mb-3">
                            <label for="wa_number" class="form-label">
                                Nomor WhatsApp Admin
                                <span class="hint">Format: kode negara + nomor tanpa 0 (contoh: 6281234567890)</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text rounded-start-3" style="border:1.5px solid #E5E7EB;border-right:none;background:#F9FAFB;">
                                    <svg viewBox="0 0 24 24" style="width:16px;height:16px;fill:#25D366;" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
                                    </svg>
                                </span>
                                <input type="text" class="form-control @error('wa_number') is-invalid @enderror"
                                    id="wa_number" name="wa_number"
                                    value="{{ old('wa_number', $settings['wa_number']->value ?? '6289613942890') }}"
                                    placeholder="6281234567890"
                                    style="border-left:none;border-radius:0 10px 10px 0;">
                                @error('wa_number')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="wa_message_template" class="form-label">
                                Template Pesan Awal
                                <span class="hint">Pesan yang otomatis terisi saat mahasiswa membuka chat WA</span>
                            </label>
                            <textarea class="form-control @error('wa_message_template') is-invalid @enderror"
                                id="wa_message_template" name="wa_message_template"
                                rows="3" maxlength="500"
                                placeholder="Halo Admin USH, saya mahasiswa yang ingin bertanya mengenai...">{{ old('wa_message_template', $settings['wa_message_template']->value ?? '') }}</textarea>
                            <div class="char-counter" id="msgCounter">0 / 500</div>
                            @error('wa_message_template')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <hr class="section-divider">

                        {{-- ── SECTION 3: Konten Tooltip ── --}}
                        <p class="section-label"><i class="bi bi-chat-square-text me-1"></i> Konten Popup / Tooltip</p>

                        <div class="mb-3">
                            <label for="wa_tooltip_title" class="form-label">
                                Judul Tooltip
                                <span class="hint">Teks sapaan di bagian atas popup</span>
                            </label>
                            <input type="text" class="form-control @error('wa_tooltip_title') is-invalid @enderror"
                                id="wa_tooltip_title" name="wa_tooltip_title"
                                value="{{ old('wa_tooltip_title', $settings['wa_tooltip_title']->value ?? 'Butuh bantuan? 👋') }}"
                                maxlength="100">
                            @error('wa_tooltip_title')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="wa_tooltip_message" class="form-label">
                                Pesan Tooltip
                                <span class="hint">Teks deskripsi di dalam popup</span>
                            </label>
                            <textarea class="form-control @error('wa_tooltip_message') is-invalid @enderror"
                                id="wa_tooltip_message" name="wa_tooltip_message"
                                rows="2" maxlength="300"
                                placeholder="Halo! Ada yang bisa kami bantu?...">{{ old('wa_tooltip_message', $settings['wa_tooltip_message']->value ?? '') }}</textarea>
                            <div class="char-counter" id="tooltipMsgCounter">0 / 300</div>
                            @error('wa_tooltip_message')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Preview --}}
                        <div class="wa-preview mb-2">
                            <div class="wa-preview-icon">
                                <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/></svg>
                            </div>
                            <div class="wa-preview-info w-100">
                                <small>Preview Tooltip</small>
                                <p id="previewTitle">{{ $settings['wa_tooltip_title']->value ?? 'Butuh bantuan? 👋' }}</p>
                                <div class="preview-msg" id="previewMsg">{{ $settings['wa_tooltip_message']->value ?? '' }}</div>
                            </div>
                        </div>

                        <hr class="section-divider">

                        {{-- ── SECTION 4: Visibilitas Role ── --}}
                        <p class="section-label"><i class="bi bi-people me-1"></i> Tampilkan Kepada Role</p>
                        <p class="text-muted" style="font-size:0.82rem;margin-top:-8px;margin-bottom:16px;">
                            Pilih role pengguna yang dapat melihat tombol WhatsApp ini.
                        </p>

                        <div class="role-grid mb-2">
                            @foreach($availableRoles as $roleKey => $roleLabel)
                            @php
                                $roleIcons = [
                                    'student'     => 'bi-mortarboard-fill',
                                    'admin'       => 'bi-person-workspace',
                                    'superadmin'  => 'bi-shield-check',
                                    'masteradmin' => 'bi-star-fill',
                                ];
                            @endphp
                            <div class="role-card">
                                <input type="checkbox"
                                    id="role_{{ $roleKey }}"
                                    name="wa_visible_roles[]"
                                    value="{{ $roleKey }}"
                                    {{ in_array($roleKey, $visibleRoles) ? 'checked' : '' }}>
                                <label for="role_{{ $roleKey }}">
                                    <div class="role-icon">
                                        <i class="bi {{ $roleIcons[$roleKey] ?? 'bi-person' }}"></i>
                                    </div>
                                    {{ $roleLabel }}
                                </label>
                            </div>
                            @endforeach
                        </div>
                        <p class="text-muted" style="font-size:0.78rem;">
                            <i class="bi bi-info-circle me-1"></i>
                            Jika tidak ada role yang dipilih, tombol WA tidak akan tampil meski status aktif.
                        </p>

                        <hr class="section-divider">

                        {{-- ── Save Button ── --}}
                        <div class="d-flex justify-content-end gap-3 align-items-center flex-wrap">
                            <a href="{{ route('admin.dashboard') }}" class="btn btn-light" style="border-radius:10px;padding:10px 24px;font-weight:600;">
                                Batal
                            </a>
                            <button type="submit" class="btn btn-save-settings">
                                <i class="bi bi-floppy me-2"></i>Simpan Pengaturan
                            </button>
                        </div>

                    </div>{{-- end settings-body --}}
                </div>{{-- end settings-card --}}
            </form>

        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // ── Character counters ──
    function initCounter(textareaId, counterId, max) {
        var el = document.getElementById(textareaId);
        var counter = document.getElementById(counterId);
        if (!el || !counter) return;
        function update() {
            var len = el.value.length;
            counter.textContent = len + ' / ' + max;
            counter.className = 'char-counter';
            if (len > max * 0.9) counter.classList.add('danger');
            else if (len > max * 0.7) counter.classList.add('warn');
        }
        el.addEventListener('input', update);
        update();
    }
    initCounter('wa_message_template', 'msgCounter', 500);
    initCounter('wa_tooltip_message', 'tooltipMsgCounter', 300);

    // ── Live preview ──
    var titleEl = document.getElementById('wa_tooltip_title');
    var msgEl   = document.getElementById('wa_tooltip_message');
    var prevTitle = document.getElementById('previewTitle');
    var prevMsg   = document.getElementById('previewMsg');

    if (titleEl && prevTitle) {
        titleEl.addEventListener('input', function () {
            prevTitle.textContent = this.value || 'Butuh bantuan? 👋';
        });
    }
    if (msgEl && prevMsg) {
        msgEl.addEventListener('input', function () {
            prevMsg.textContent = this.value || '...';
        });
    }

    // ── Nomor WA: hanya angka ──
    var waNum = document.getElementById('wa_number');
    if (waNum) {
        waNum.addEventListener('input', function () {
            this.value = this.value.replace(/[^0-9]/g, '');
        });
    }
});
</script>
@endpush
