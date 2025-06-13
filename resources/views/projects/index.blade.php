@extends('layouts.dashboard')

@section('content')
    @component('layouts.content-layout')
        @slot('title', 'My IoT Projects')
        @slot('icon', 'fas fa-project-diagram')
        
        @slot('actions')
            <a href="{{ route('projects.create') }}" class="btn btn-primary" data-aos="fade-left" data-aos-delay="100">
                <i class="fas fa-plus me-2"></i>New Project
            </a>
        @endslot

        @if($projects->isEmpty())
            <div class="col-12">
                <div class="project-card" data-aos="fade-up" data-aos-delay="200">
                    <div class="card-body text-center py-5">
                        <div class="empty-state-icon mb-4">
                            <i class="fas fa-project-diagram fa-4x"></i>
                        </div>
                        <h4 class="mb-3 fw-bold">No Projects Yet</h4>
                        <p class="text-muted mb-4 px-4 mx-auto" style="max-width: 400px;">Start by creating your first IoT project to begin monitoring and controlling your devices</p>
                        <a href="{{ route('projects.create') }}" class="btn btn-primary btn-lg">
                            <i class="fas fa-plus me-2"></i>Create Project
                        </a>
                    </div>
                </div>
            </div>
        @else
            @foreach($projects as $project)
                <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="{{ $loop->iteration * 100 }}">
                    <div class="project-card h-100">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <h5 class="card-title mb-1 fw-bold gradient-text">{{ $project->name }}</h5>
                                    <div class="d-flex align-items-center gap-2 text-muted">
                                        <i class="fas fa-microchip"></i>
                                        <span>{{ $project->devices_count }} Devices</span>
                                    </div>
                                </div>
                            </div>
                            
                            <p class="card-text text-muted mb-4">
                                {{ $project->description ?? 'No description provided' }}
                            </p>

                            <div class="d-flex align-items-center mb-4">
                                <div class="me-4">
                                    <small class="text-muted d-block mb-1">Created</small>
                                    <div class="d-flex align-items-center text-muted">
                                        <i class="fas fa-calendar-alt me-2"></i>
                                        {{ $project->created_at->format('M d, Y') }}
                                    </div>
                                </div>
                                <div>
                                    <small class="text-muted d-block mb-1">Last Seen</small>
                                    <div class="d-flex align-items-center text-muted">
                                        <i class="fas fa-clock me-2"></i>
                                        <span data-timestamp="{{ $project->effective_updated_at->format('Y-m-d H:i:s') }}">{{ $project->effective_updated_at->diffForHumans() }}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex gap-2">
                                <a href="{{ route('projects.show', $project) }}" class="btn btn-primary flex-grow-1">
                                    <i class="fas fa-eye me-2"></i>View Project
                                </a>
                                <a href="{{ route('projects.edit', $project) }}" class="btn btn-light">
                                    <i class="fas fa-edit"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        @endif
    @endcomponent
@endsection

@push('styles')
<link href="{{ asset('css/projects.css') }}" rel="stylesheet">
@endpush

@push('scripts')
<!-- Include moment.js -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
<script>
// Function to update relative timestamps
function updateRelativeTimestamps() {
    const timestamps = document.querySelectorAll('[data-timestamp]');
    timestamps.forEach(element => {
        const timestamp = element.getAttribute('data-timestamp');
        const relativeTime = moment(timestamp).fromNow();
        element.textContent = relativeTime;
    });
}

document.addEventListener('DOMContentLoaded', function() {
    // Update timestamps every minute
    setInterval(updateRelativeTimestamps, 60000);
});
</script>
@endpush 