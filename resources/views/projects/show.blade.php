@extends('layouts.dashboard')

@section('content')
    @component('layouts.content-layout')
        @slot('breadcrumb')
            <a href="{{ route('projects.index') }}" class="dashboard-link">
                <i class="fas fa-project-diagram me-2"></i>
                Projects
            </a>
        @endslot

        @slot('title', $project->name)
        @slot('subtitle', $project->description ?? 'No description provided')
        @slot('icon', 'fas fa-project-diagram')
        
        @slot('actions')
            <a href="{{ route('projects.edit', $project) }}" class="btn btn-primary">
                <i class="fas fa-edit me-2"></i>Edit Project
            </a>
            <a href="{{ route('devices.create') }}?project={{ $project->id }}" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>Add Device
            </a>
        @endslot

        @slot('stats')
            <div class="col-md-4" data-aos="fade-up" data-aos-delay="100">
                <div class="card stats-card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="stats-icon">
                                <i class="fas fa-microchip"></i>
                            </div>
                            <div class="ms-3">
                                <h6 class="text-muted mb-0">Total Devices</h6>
                                <h3 class="gradient-text mb-0">{{ $project->devices->count() }}</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4" data-aos="fade-up" data-aos-delay="200">
                <div class="card stats-card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="stats-icon">
                                <i class="fas fa-signal"></i>
                            </div>
                            <div class="ms-3">
                                <h6 class="text-muted mb-0">Online Devices</h6>
                                <h3 class="gradient-text mb-0">{{ $project->devices->where('is_online', true)->count() }}</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4" data-aos="fade-up" data-aos-delay="300">
                <div class="card stats-card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="stats-icon">
                                <i class="fas fa-clock"></i>
                            </div>
                            <div class="ms-3">
                                <h6 class="text-muted mb-0">Last Seen</h6>
                                <h3 class="gradient-text mb-0">{{ $project->effective_updated_at->diffForHumans() }}</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endslot

        <!-- Project Info & Devices -->
        <div class="col-lg-4">
            <div class="project-card" data-aos="fade-up" data-aos-delay="100">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="gradient-text mb-0">Project Details</h5>
                    </div>
                    
                    <div class="info-list">
                        <div class="info-item">
                            <div class="info-label">
                                <i class="fas fa-calendar-alt me-2 gradient-icon"></i>
                                Created
                            </div>
                            <div class="info-value">
                                {{ $project->created_at->format('M d, Y') }}
                            </div>
                        </div>

                        <div class="info-item">
                            <div class="info-label">
                                <i class="fas fa-clock me-2 gradient-icon"></i>
                                Last Seen
                            </div>
                            <div class="info-value">
                                <span data-timestamp="{{ $project->effective_updated_at->format('Y-m-d H:i:s') }}">{{ $project->effective_updated_at->diffForHumans() }}</span>
                            </div>
                        </div>

                        <div class="info-item">
                            <div class="info-label">
                                <i class="fas fa-microchip me-2 gradient-icon"></i>
                                Total Devices
                            </div>
                            <div class="info-value">
                                {{ $project->devices->count() }} Devices
                            </div>
                        </div>

                        <div class="info-item">
                            <div class="info-label">
                                <i class="fas fa-signal me-2 gradient-icon"></i>
                                Online Devices
                            </div>
                            <div class="info-value">
                                {{ $project->devices->where('is_online', true)->count() }} Online
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Devices List -->
        <div class="col-lg-8">
            <div class="project-card" data-aos="fade-up" data-aos-delay="200">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="gradient-text mb-0">Project Devices</h5>
                        <a href="{{ route('devices.create') }}?project={{ $project->id }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus me-2"></i>Add Device
                        </a>
                    </div>

                    @if($project->devices->isEmpty())
                        <div class="text-center py-5" data-aos="fade-up" data-aos-delay="100">
                            <div class="empty-state-icon mb-4">
                                <i class="fas fa-microchip fa-4x"></i>
                            </div>
                            <h4 class="mb-3">No Devices Yet</h4>
                            <p class="text-muted mb-4">Start by adding your first IoT device to this project</p>
                            <a href="{{ route('devices.create') }}?project={{ $project->id }}" class="btn btn-primary">
                                <i class="fas fa-plus me-2"></i>Add Device
                            </a>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th class="py-3">Name</th>
                                        <th class="py-3">Status</th>
                                        <th class="py-3">Last Seen</th>
                                        <th class="py-3">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($project->devices as $device)
                                    <tr class="fade-in-row">
                                        <td class="py-3 fw-medium">{{ $device->name }}</td>
                                        <td class="py-3">
                                            <span class="badge bg-{{ $device->is_online ? 'success' : 'warning' }} status-badge">
                                                {{ $device->is_online ? 'Online' : 'Offline' }}
                                            </span>
                                        </td>
                                        <td class="py-3">{{ $device->last_seen_at ? $device->last_seen_at->diffForHumans() : 'Never' }}</td>
                                        <td class="py-3">
                                            <div class="d-flex gap-2">
                                                <a href="{{ route('devices.show', $device) }}" class="btn btn-primary btn-sm">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('devices.edit', $device) }}" class="btn btn-light btn-sm">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                @if($device->pins->isNotEmpty())
                                                <a href="{{ route('pins.charts') }}?device={{ $device->id }}" class="btn btn-light btn-sm">
                                                    <i class="fas fa-chart-line"></i>
                                                </a>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endcomponent
@endsection

@push('styles')
<link href="{{ asset('css/projects.css') }}" rel="stylesheet">
<link href="{{ asset('css/project-detail.css') }}" rel="stylesheet">
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