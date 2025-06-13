@extends('layouts.dashboard')

@push('styles')
<link href="{{ asset('css/device-code.css') }}" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Fira+Code:wght@400;500&display=swap" rel="stylesheet">
@endpush

@section('content')
    @component('layouts.content-layout')
        @slot('breadcrumb')
            <div class="d-flex align-items-center gap-3">
                <a href="{{ route('devices.index') }}" class="dashboard-link" data-aos="fade-down" data-aos-duration="500">
                    <i class="fas fa-microchip me-2"></i>
                    Devices
                </a>
                <i class="fas fa-chevron-right text-muted" data-aos="fade-down" data-aos-duration="500" data-aos-delay="100"></i>
                <a href="{{ route('devices.show', $device) }}" class="dashboard-link" data-aos="fade-down" data-aos-duration="500" data-aos-delay="200">
                    <i class="fas fa-tablet me-2"></i>
                    {{ $device->name }}
                </a>
            </div>
        @endslot

        @slot('title', 'Device Code')
        @slot('subtitle', 'ESP32 code configuration for ' . $device->name)
        @slot('icon', 'fas fa-code')

        @slot('actions')
            <button class="btn btn-primary" onclick="copyDeviceCode()" id="copyButton" data-aos="fade-down" data-aos-duration="500" data-aos-delay="300">
                <i class="fas fa-copy me-2"></i>Copy Code
            </button>
        @endslot

        <div class="col-12">
            <div class="code-container" data-aos="fade-down" data-aos-duration="800" data-aos-delay="400">
                <pre><code id="deviceCode">
@include('devices.esp.device')
                </code></pre>
            </div>
        </div>
    @endcomponent
@endsection

@push('scripts')
<script>
function copyDeviceCode() {
    const codeElement = document.getElementById('deviceCode');
    const textArea = document.createElement('textarea');
    textArea.value = codeElement.textContent;
    document.body.appendChild(textArea);
    textArea.select();
    document.execCommand('copy');
    document.body.removeChild(textArea);

    // Show success message with animation
    const successMessage = document.createElement('div');
    successMessage.className = 'copy-success';
    successMessage.innerHTML = '<i class="fas fa-check me-2"></i>Code copied successfully!';
    document.body.appendChild(successMessage);

    // Update button text
    const copyButton = document.getElementById('copyButton');
    const originalText = copyButton.innerHTML;
    copyButton.innerHTML = '<i class="fas fa-check me-2"></i>Copied!';

    // Remove success message and restore button after 2 seconds
    setTimeout(() => {
        document.body.removeChild(successMessage);
        copyButton.innerHTML = originalText;
    }, 2000);
}
</script>
@endpush 