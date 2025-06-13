{{-- Content Layout Component --}}
<div class="container-fluid py-4">
    {{-- Breadcrumb (if provided) --}}
    @if(isset($breadcrumb))
        <div class="d-flex align-items-center mb-4" data-aos="fade-up">
            {!! $breadcrumb !!}
        </div>
    @endif

    {{-- Header --}}
    <div class="content-header" data-aos="fade-up">
        <div class="d-flex justify-content-between align-items-start">
            <div class="d-flex align-items-center">
                @if(isset($icon))
                    <div class="content-icon me-3">
                        <i class="{{ $icon }}"></i>
                    </div>
                @endif
                <div>
                    <h1 class="content-title h2 mb-0 gradient-text">{{ $title }}</h1>
                    @if(isset($subtitle))
                        <p class="content-subtitle mb-0">{{ $subtitle }}</p>
                    @endif
                </div>
            </div>
            @if(isset($actions))
                <div class="d-flex gap-2">
                    {!! $actions !!}
                </div>
            @endif
        </div>
    </div>

    {{-- Stats Cards (if provided) --}}
    @if(isset($stats))
        <div class="row g-4 mb-4">
            {!! $stats !!}
        </div>
    @endif

    {{-- Main Content --}}
    <div class="row g-4">
        {{ $slot }}
    </div>
</div>

@push('styles')
<style>
/* Content Layout Styles */
.content-header {
    margin-bottom: 3rem;
}

.content-title {
    font-size: 1.75rem;
    font-weight: 600;
    color: #1a202c;
    margin-bottom: 0.5rem;
}

.content-subtitle {
    font-size: 1rem;
    color: #6B7280;
    margin-bottom: 0;
}

.content-icon {
    width: 48px;
    height: 48px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--gradient-mix, linear-gradient(135deg, #00BFA6 0%, #0093E9 100%));
    border-radius: 12px;
    margin-right: 1rem;
}

.content-icon i {
    font-size: 1.5rem;
    color: white;
}

/* Gradient Text */
.gradient-text {
    background: var(--gradient-mix, linear-gradient(135deg, #00BFA6 0%, #0093E9 100%));
    -webkit-background-clip: text;
    background-clip: text;
    -webkit-text-fill-color: transparent;
    color: #00BFA6; /* Fallback */
}

/* Main Content Spacing */
.row.g-4 {
    margin-top: 0.5rem;
}

/* Responsive Adjustments */
@media (max-width: 768px) {
    .content-header .d-flex {
        flex-direction: column;
        gap: 1rem;
    }
    
    .content-header .d-flex.justify-content-between {
        align-items: flex-start !important;
    }
    
    .content-header .d-flex.gap-2 {
        width: 100%;
    }
    
    .content-header .btn {
        width: 100%;
    }
}
</style>
@endpush 