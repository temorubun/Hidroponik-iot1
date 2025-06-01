@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-between mb-4">
        <div class="col-auto">
            <h2><i class="fas fa-project-diagram"></i> My IoT Projects</h2>
        </div>
        <div class="col-auto">
            <a href="{{ route('projects.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> New Project
            </a>
        </div>
    </div>

    @if($projects->isEmpty())
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="fas fa-project-diagram fa-3x text-muted mb-3"></i>
                <h5>No Projects Yet</h5>
                <p class="text-muted">Start by creating your first IoT project</p>
                <a href="{{ route('projects.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Create Project
                </a>
            </div>
        </div>
    @else
        <div class="row">
            @foreach($projects as $project)
                <div class="col-md-4 mb-4">
                    <div class="card h-100 shadow-sm">
                        <div class="card-header bg-white">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">{{ $project->name }}</h5>
                                <span class="badge {{ $project->status === 'active' ? 'bg-success' : 'bg-secondary' }}">
                                    {{ ucfirst($project->status) }}
                                </span>
                            </div>
                        </div>
                        <div class="card-body">
                            <p class="text-muted">{{ $project->description ?: 'No description' }}</p>
                            
                            <div class="mb-3">
                                <small class="text-muted">
                                    <i class="fas fa-microchip"></i> Devices: {{ $project->devices->count() }}
                                </small>
                                <br>
                                <small class="text-muted">
                                    <i class="fas fa-code-branch"></i> Type: {{ ucfirst(str_replace('_', ' ', $project->type)) }}
                                </small>
                            </div>

                            <div class="d-grid gap-2">
                                <a href="{{ route('projects.show', $project) }}" class="btn btn-outline-primary">
                                    <i class="fas fa-eye"></i> View Project
                                </a>
                            </div>
                        </div>
                        <div class="card-footer bg-white">
                            <small class="text-muted">
                                <i class="fas fa-clock"></i> Created {{ $project->created_at->diffForHumans() }}
                            </small>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>

@push('styles')
<style>
.card {
    transition: transform 0.2s;
    border-radius: 10px;
}
.card:hover {
    transform: translateY(-5px);
}
.card-header {
    border-top-left-radius: 10px;
    border-top-right-radius: 10px;
}
.badge {
    font-weight: 500;
    padding: 0.5em 0.75em;
}
</style>
@endpush
@endsection 