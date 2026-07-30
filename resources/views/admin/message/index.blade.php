@if ($message = Session::get('success'))
    <div class="toast-container">
        <div class="toast align-items-center text-bg-success border-0" role="alert" aria-live="assertive"
            aria-atomic="true" data-bs-delay="{{ 5000 }}">
            <div class="toast-header">
                <strong class="me-auto">Information</strong>
                <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body">
                {{ $message }}
            </div>
        </div>
    </div>
@endif

@if ($errors->any())
    <div class="toast-container">
        @foreach ($errors->all() as $index => $error)
            <div class="toast align-items-center text-bg-danger border-0" role="alert" aria-live="assertive"
                aria-atomic="true" data-bs-delay="{{ 5000 * ($index + 1) }}">
                <div class="toast-header">
                    <strong class="me-auto">Information</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
                <div class="toast-body">
                    {{ $error }}
                </div>
            </div>
        @endforeach
    </div>
@endif
@if ($message = Session::get('warning'))
    <div class="toast-container">
        <div class="toast align-items-center text-bg-warning border-0" role="alert" aria-live="assertive"
            aria-atomic="true" data-bs-delay="{{ 5000 }}">
            <div class="toast-header">
                <strong class="me-auto">Information</strong>
                <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body">
                {{ $message }}
            </div>
        </div>
    </div>
@endif

@if ($message = Session::get('error'))
    <div class="toast-container">
        <div class="toast align-items-center text-bg-danger border-0" role="alert" aria-live="assertive"
            aria-atomic="true" data-bs-delay="{{ 5000 }}">
            <div class="toast-header">
                <strong class="me-auto">Information</strong>
                <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body">
                {{ $message }}
            </div>
        </div>
    </div>
@endif

@if ($message = Session::get('info'))
    <div class="toast-container">
        <div class="toast align-items-center text-bg-primary border-0" role="alert" aria-live="assertive"
            aria-atomic="true" data-bs-delay="{{ 5000 }}">
            <div class="toast-header">
                <strong class="me-auto">Information</strong>
                <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body">
                {{ $message }}
            </div>
        </div>
    </div>
@endif
