@extends('layouts.dashboard')

@section('content')
<div class="container-fluid">
    <!-- Floating Icons -->
    <div class="floating-icons" data-aos="fade-in" data-aos-duration="1500">
        <i class="fas fa-leaf floating-icon" style="top: 15%; left: 10%; animation-delay: 0s;"></i>
        <i class="fas fa-tint floating-icon" style="top: 60%; left: 15%; animation-delay: 1s;"></i>
        <i class="fas fa-seedling floating-icon" style="top: 25%; right: 15%; animation-delay: 2s;"></i>
        <i class="fas fa-flask floating-icon" style="top: 70%; right: 10%; animation-delay: 3s;"></i>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-4" data-aos="fade-up">
        <div class="d-flex align-items-center">
            <i class="fas fa-tachometer-alt me-3" style="font-size: 1.75rem; background: var(--primary-gradient); -webkit-background-clip: text; -webkit-text-fill-color: transparent;"></i>
            <h1 class="h2 mb-0 fw-bold" style="background: var(--primary-gradient); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">Dashboard Overview</h1>
        </div>
        <a href="{{ route('projects.create') }}" class="btn btn-primary" data-aos="fade-left" data-aos-delay="100">
            <i class="fas fa-plus"></i> New Project
        </a>
    </div>

    <!-- Overview Stats -->
    <div class="row g-4 mb-5">
        <div class="col-md-4" data-aos="zoom-in" data-aos-delay="100">
            <div class="card stats-card h-100">
                <div class="card-body d-flex align-items-center justify-content-between py-4">
                    <div>
                        <h6 class="card-title mb-2">TOTAL PROJECTS</h6>
                        <p class="stats-value mb-0">{{ $totalProjects }}</p>
                    </div>
                    <i class="fas fa-project-diagram fa-2x"></i>
                </div>
            </div>
        </div>
        <div class="col-md-4" data-aos="zoom-in" data-aos-delay="200">
            <div class="card stats-card h-100">
                <div class="card-body d-flex align-items-center justify-content-between py-4">
                    <div>
                        <h6 class="card-title mb-2">TOTAL DEVICES</h6>
                        <p class="stats-value mb-0" data-stat="total-devices">{{ $totalDevices }}</p>
                    </div>
                    <i class="fas fa-microchip fa-2x"></i>
                </div>
            </div>
        </div>
        <div class="col-md-4" data-aos="zoom-in" data-aos-delay="300">
            <div class="card stats-card h-100">
                <div class="card-body d-flex align-items-center justify-content-between py-4">
                    <div>
                        <h6 class="card-title mb-2">ONLINE DEVICES</h6>
                        <p class="stats-value mb-0" data-stat="online-devices">{{ $onlineDevices }}</p>
                    </div>
                    <i class="fas fa-signal fa-2x"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Projects & Devices Overview -->
    <div class="row">
        <div class="col-12">
            <div class="card overflow-hidden" data-aos="fade-up" data-aos-delay="400">
                <div class="card-header p-0 bg-transparent">
                    <nav class="nav nav-tabs">
                        <button class="tab-button active flex-grow-1 py-3 px-4 border-0 bg-transparent" id="projects-tab" data-bs-toggle="tab" data-bs-target="#projects" type="button" role="tab" aria-controls="projects" aria-selected="true">
                            <div class="d-flex align-items-center gap-2">
                                <i class="fas fa-project-diagram" style="background: var(--primary-gradient); -webkit-background-clip: text; -webkit-text-fill-color: transparent;"></i>
                                <span class="fw-bold">Recent Projects</span>
                            </div>
                        </button>
                        <button class="tab-button flex-grow-1 py-3 px-4 border-0 bg-transparent" id="devices-tab" data-bs-toggle="tab" data-bs-target="#devices" type="button" role="tab" aria-controls="devices" aria-selected="false">
                            <div class="d-flex align-items-center gap-2">
                                <i class="fas fa-microchip" style="background: var(--primary-gradient); -webkit-background-clip: text; -webkit-text-fill-color: transparent;"></i>
                                <span class="fw-bold">Device Status</span>
                            </div>
                        </button>
                    </nav>
                </div>
                <div class="card-body p-0">
                    <div class="tab-content">
                        <!-- Projects Tab -->
                        <div class="tab-pane fade show active p-4" id="projects" role="tabpanel" aria-labelledby="projects-tab">
                            @if($projects->isEmpty())
                                <div class="text-center py-5">
                                    <div class="empty-state-icon mb-4">
                                        <i class="fas fa-project-diagram fa-4x" style="background: var(--primary-gradient); -webkit-background-clip: text; -webkit-text-fill-color: transparent;"></i>
                                    </div>
                                    <h4 class="mb-3 fw-bold">No Projects Yet</h4>
                                    <p class="text-muted mb-4 px-4 mx-auto" style="max-width: 400px;">Start by creating your first IoT project to begin monitoring and controlling your devices</p>
                                    <a href="{{ route('projects.create') }}" class="btn btn-primary btn-lg">
                                        <i class="fas fa-plus"></i> Create Project
                                    </a>
                                </div>
                            @else
                                <div class="d-flex justify-content-end mb-3">
                                    <a href="{{ route('projects.index') }}" class="btn btn-primary">
                                        <i class="fas fa-th-list me-2"></i>View All Projects
                                    </a>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th class="py-3">Name</th>
                                                <th class="py-3">Devices</th>
                                                <th class="py-3">Status</th>
                                                <th class="py-3">Last Seen</th>
                                                <th class="py-3">Created At</th>
                                                <th class="py-3">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($projects as $project)
                                            <tr class="fade-in-row">
                                                <td class="py-3 fw-medium">{{ $project->name }}</td>
                                                <td class="py-3">{{ $project->devices_count }}</td>
                                                <td class="py-3">
                                                    @php
                                                        $hasOnlineDevices = $project->devices->where('is_online', true)->count() > 0;
                                                    @endphp
                                                    <span class="badge bg-{{ $hasOnlineDevices ? 'success' : 'warning' }}">
                                                        {{ $hasOnlineDevices ? 'Active' : 'Inactive' }}
                                                    </span>
                                                </td>
                                                <td class="py-3" data-timestamp="{{ $project->effective_updated_at->format('Y-m-d H:i:s') }}">{{ $project->effective_updated_at->diffForHumans() }}</td>
                                                <td class="py-3">{{ $project->created_at->format('Y-m-d H:i:s') }}</td>
                                                <td class="py-3">
                                                    <a href="{{ route('projects.show', $project) }}" class="btn btn-primary">
                                                        <i class="fas fa-eye"></i> View
                                                    </a>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mt-4">
                                    <div class="d-flex gap-2">
                                        @if($projects->currentPage() > 1)
                                            <a href="{{ $projects->previousPageUrl() }}&tab=projects" class="btn btn-outline-primary d-flex align-items-center gap-2">
                                                <i class="fas fa-chevron-left"></i>
                                                Previous
                                            </a>
                                        @endif
                                        
                                        @if($projects->hasMorePages())
                                            <a href="{{ $projects->nextPageUrl() }}&tab=projects" class="btn btn-outline-primary d-flex align-items-center gap-2">
                                                Next
                                                <i class="fas fa-chevron-right"></i>
                                            </a>
                                        @endif
                                    </div>
                                    
                                    <div class="pagination-info">
                                        <span class="text-muted">
                                            Showing {{ $projects->lastItem() ?? 0 }} of {{ $projects->total() }} entries
                                        </span>
                                    </div>
                                </div>
                            @endif
                        </div>

                        <!-- Devices Tab -->
                        <div class="tab-pane fade p-4" id="devices" role="tabpanel" aria-labelledby="devices-tab">
                            @if($devices->isEmpty())
                                <div class="text-center py-5">
                                    <div class="empty-state-icon mb-4">
                                        <i class="fas fa-microchip fa-4x" style="background: var(--primary-gradient); -webkit-background-clip: text; -webkit-text-fill-color: transparent;"></i>
                                    </div>
                                    <h4 class="mb-3 fw-bold">No Devices Yet</h4>
                                    <p class="text-muted mb-4 px-4 mx-auto" style="max-width: 400px;">Add your first IoT device to start monitoring its status and data</p>
                                    <a href="{{ route('devices.create') }}" class="btn btn-primary btn-lg">
                                        <i class="fas fa-plus"></i> Add Device
                                    </a>
                                </div>
                            @else
                                <div class="d-flex justify-content-end mb-4">
                                    <a href="{{ route('devices.index') }}" class="btn btn-primary">
                                        <i class="fas fa-th-list me-2"></i>View All Devices
                                    </a>
                                </div>
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>Device</th>
                                                <th>Project</th>
                                                <th>Status</th>
                                                <th>Last Seen</th>
                                                <th>Created At</th>
                                                <th>Updated At</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($devices as $device)
                                            <tr id="device-{{ $device->id }}" class="fade-in-row">
                                                <td class="fw-medium text-break">{{ $device->name }}</td>
                                                <td class="text-break">{{ $device->project->name }}</td>
                                                <td>
                                                    <span class="badge bg-{{ $device->is_online ? 'success' : 'danger' }} status-badge">
                                                        {{ $device->is_online ? 'Online' : 'Offline' }}
                                                    </span>
                                                </td>
                                                <td data-timestamp="{{ $device->is_online ? 'Just now' : ($device->last_online ? $device->last_online->format('Y-m-d H:i:s') : 'Never') }}">{{ $device->is_online ? 'Just now' : ($device->last_online ? $device->last_online->diffForHumans() : 'Never') }}</td>
                                                <td>{{ $device->created_at->format('Y-m-d H:i:s') }}</td>
                                                <td>{{ $device->updated_at->format('Y-m-d H:i:s') }}</td>
                                                <td>
                                                    <div class="d-flex gap-2 flex-wrap">
                                                        <a href="{{ route('devices.show', $device) }}" class="btn btn-primary">
                                                            <i class="fas fa-eye"></i> View
                                                        </a>
                                                        @if($device->pins->isNotEmpty())
                                                        <a href="{{ route('pins.charts') }}?device={{ $device->id }}" class="btn btn-primary">
                                                            <i class="fas fa-chart-line"></i> Charts
                                                        </a>
                                                        @endif
                                                    </div>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mt-4">
                                    <div class="d-flex gap-2">
                                        @if($devices->currentPage() > 1)
                                            <a href="{{ $devices->previousPageUrl() }}&tab=devices" class="btn btn-outline-primary d-flex align-items-center gap-2">
                                                <i class="fas fa-chevron-left"></i>
                                                Previous
                                            </a>
                                        @endif
                                        
                                        @if($devices->hasMorePages())
                                            <a href="{{ $devices->nextPageUrl() }}&tab=devices" class="btn btn-outline-primary d-flex align-items-center gap-2">
                                                Next
                                                <i class="fas fa-chevron-right"></i>
                                            </a>
                                        @endif
                                    </div>
                                    
                                    <div class="pagination-info">
                                        <span class="text-muted">
                                            Showing {{ $devices->firstItem() ?? 0 }} to {{ $devices->lastItem() ?? 0 }} of {{ $devices->total() }} entries
                                        </span>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link href="{{ asset('css/dashboard.css') }}" rel="stylesheet">
@endpush

@push('scripts')
<!-- Include moment.js -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
<script src="{{ asset('js/dashboard.js') }}"></script>
@endpush 