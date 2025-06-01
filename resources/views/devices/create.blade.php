@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-plus-circle"></i> Create New Device</h5>
                        <a href="{{ route('devices.index') }}" class="btn btn-light btn-sm">
                            <i class="fas fa-arrow-left"></i> Back
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <form method="POST" action="{{ route('devices.store') }}">
                        @csrf

                        <div class="mb-3">
                            <label for="project_id" class="form-label">Select Project</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-project-diagram"></i>
                                </span>
                                <select class="form-select @error('project_id') is-invalid @enderror" 
                                    id="project_id" name="project_id" required>
                                    <option value="">Choose a project...</option>
                                    @foreach($projects as $project)
                                        <option value="{{ $project->id }}" {{ old('project_id') == $project->id ? 'selected' : '' }}>
                                            {{ $project->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('project_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            @if($projects->isEmpty())
                                <small class="text-danger">
                                    <i class="fas fa-exclamation-circle"></i>
                                    You need to create a project first. <a href="{{ route('projects.create') }}">Create one now</a>
                                </small>
                            @endif
                        </div>

                        <div class="mb-3">
                            <label for="name" class="form-label">Device Name</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-microchip"></i>
                                </span>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                    id="name" name="name" value="{{ old('name') }}" required 
                                    placeholder="Enter device name">
                            </div>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-align-left"></i>
                                </span>
                                <textarea class="form-control @error('description') is-invalid @enderror" 
                                    id="description" name="description" rows="3" 
                                    placeholder="Describe your device">{{ old('description') }}</textarea>
                            </div>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> After creating the device, you can add pins and configure them.
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-plus-circle"></i> Create Device
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
.card {
    border-radius: 15px;
}
.card-header {
    border-top-left-radius: 15px;
    border-top-right-radius: 15px;
}
.input-group-text {
    background-color: #f8f9fa;
    border-right: none;
}
.form-control {
    border-left: none;
}
.form-control:focus {
    border-color: #ced4da;
    box-shadow: none;
}
.input-group:focus-within .input-group-text {
    border-color: #86b7fe;
}
</style>
@endpush
@endsection 