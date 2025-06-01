@extends('layouts.app')

@section('content')
<div class="container">
    <!-- Overview Stats -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Total Projects</h5>
                    <div class="d-flex align-items-center">
                        <i class="fas fa-project-diagram fa-2x text-primary me-3"></i>
                        <h2 class="mb-0">{{ $totalProjects }}</h2>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Total Devices</h5>
                    <div class="d-flex align-items-center">
                        <i class="fas fa-microchip fa-2x text-success me-3"></i>
                        <h2 class="mb-0">{{ $totalDevices }}</h2>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Online Devices</h5>
                    <div class="d-flex align-items-center">
                        <i class="fas fa-signal fa-2x text-info me-3"></i>
                        <h2 class="mb-0">{{ $onlineDevices }}</h2>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Projects -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Recent Projects</h5>
                    <a href="{{ route('projects.index') }}" class="btn btn-primary btn-sm">View All</a>
                </div>
                <div class="card-body">
                    @if($projects->isEmpty())
                        <p class="text-muted text-center mb-0">No projects found. <a href="{{ route('projects.create') }}">Create your first project</a></p>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Devices</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($projects as $project)
                                    <tr>
                                        <td>{{ $project->name }}</td>
                                        <td>{{ $project->devices_count }}</td>
                                        <td>
                                            <span class="badge bg-{{ $project->status === 'active' ? 'success' : ($project->status === 'inactive' ? 'warning' : 'secondary') }}">
                                                {{ ucfirst($project->status) }}
                                            </span>
                                        </td>
                                        <td>
                                            <a href="{{ route('projects.show', $project) }}" class="btn btn-sm btn-info">
                                                <i class="fas fa-eye"></i>
                                            </a>
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
    </div>

    <!-- Device Status -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Device Status</h5>
                    <a href="{{ route('devices.index') }}" class="btn btn-primary btn-sm">View All</a>
                </div>
                <div class="card-body">
                    @if($devices->isEmpty())
                        <p class="text-muted text-center mb-0">No devices found. <a href="{{ route('devices.create') }}">Add your first device</a></p>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Device</th>
                                        <th>Project</th>
                                        <th>Status</th>
                                        <th>Last Seen</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($devices as $device)
                                    <tr>
                                        <td>{{ $device->name }}</td>
                                        <td>{{ $device->project->name }}</td>
                                        <td>
                                            <span class="badge bg-{{ $device->is_online ? 'success' : 'danger' }}">
                                                {{ $device->is_online ? 'Online' : 'Offline' }}
                                            </span>
                                        </td>
                                        <td>{{ $device->last_seen ? $device->last_seen->diffForHumans() : 'Never' }}</td>
                                        <td>
                                            <a href="{{ route('devices.show', $device) }}" class="btn btn-sm btn-info">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            @if($device->pins->isNotEmpty())
                                            <a href="{{ route('pins.charts') }}?device={{ $device->id }}" class="btn btn-sm btn-success">
                                                <i class="fas fa-chart-line"></i>
                                            </a>
                                            @endif
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
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Refresh page every 30 seconds to update device status
    setTimeout(function() {
        window.location.reload();
    }, 30000);
</script>
@endpush 