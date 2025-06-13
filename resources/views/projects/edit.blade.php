@extends('layouts.dashboard')

@section('content')
    @component('layouts.content-layout')
        @slot('breadcrumb')
            <a href="{{ route('projects.index') }}" class="dashboard-link">
                <i class="fas fa-project-diagram me-2"></i>
                Projects
            </a>
        @endslot

        @slot('title', 'Edit Project')
        @slot('subtitle', 'Update your project details below')
        @slot('icon', 'fas fa-project-diagram')

        @include('components.confirm-modal', [
            'title' => 'Delete Project',
            'message' => 'Are you sure you want to delete this project? This action cannot be undone.',
            'formId' => 'deleteProjectForm'
        ])

        <div class="col-lg-8 mx-auto">
            <div class="project-card" data-aos="fade-up" data-aos-duration="1000">
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('projects.update', $project) }}" class="form-centered">
                        @csrf
                        @method('PUT')

                        <!-- Project Name -->
                        <div class="form-group mb-4" data-aos="fade-up" data-aos-delay="100">
                            <label for="name" class="form-label">
                                <i class="fas fa-project-diagram me-2 gradient-icon"></i><span class="gradient-text">Project Name</span>
                            </label>
                            <div class="input-group input-group-custom">
                                <span class="input-group-text">
                                    <i class="fas fa-tag gradient-icon"></i>
                                </span>
                                <input type="text" class="form-control form-control-custom @error('name') is-invalid @enderror"
                                       id="name" name="name" value="{{ old('name', $project->name) }}"
                                       placeholder="Enter project name" required>
                                @error('name')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                            <small class="form-text text-muted mt-2">Choose a unique and descriptive name for your project</small>
                        </div>

                        <!-- Description -->
                        <div class="form-group mb-4" data-aos="fade-up" data-aos-delay="200">
                            <label for="description" class="form-label">
                                <i class="fas fa-align-left me-2 gradient-icon"></i><span class="gradient-text">Description</span>
                            </label>
                            <div class="input-group input-group-custom">
                                <span class="input-group-text">
                                    <i class="fas fa-comment-alt gradient-icon"></i>
                                </span>
                                <textarea class="form-control form-control-custom @error('description') is-invalid @enderror"
                                          id="description" name="description" rows="4"
                                          placeholder="Enter project description">{{ old('description', $project->description) }}</textarea>
                                @error('description')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                            <small class="form-text text-muted mt-2">Provide a brief description of your project (optional)</small>
                        </div>

                        <div class="form-actions d-flex justify-content-between align-items-center mt-5" data-aos="fade-up" data-aos-delay="400">
                            <a href="{{ route('projects.show', $project) }}" class="btn btn-light">
                                <i class="fas fa-arrow-left me-2"></i>Back
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Update Project
                            </button>
                        </div>
                    </form>

                    <div class="danger-zone" data-aos="fade-up" data-aos-delay="500">
                        <h5 class="danger-zone-title">
                            <i class="fas fa-exclamation-triangle"></i>
                            Danger Zone
                        </h5>
                        <form method="POST" action="{{ route('projects.destroy', $project) }}" id="deleteProjectForm">
                            @csrf
                            @method('DELETE')
                            <button type="button" class="btn btn-outline-danger" onclick="confirmDelete('deleteProjectForm')">
                                <i class="fas fa-trash-alt me-2"></i>Delete Project
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endcomponent
@endsection

@push('styles')
<link href="{{ asset('css/projects.css') }}" rel="stylesheet">
@endpush 