@extends('layouts.dashboard')

@section('content')
    @component('layouts.content-layout')
        @slot('breadcrumb')
            <a href="{{ route('projects.index') }}" class="dashboard-link">
                <i class="fas fa-project-diagram me-2"></i>
                Projects
            </a>
        @endslot

        @slot('title', 'Create New Project')
        @slot('subtitle', 'Fill in the details below to create your new IoT project')
        @slot('icon', 'fas fa-project-diagram')

        <div class="col-lg-8 mx-auto">
            <div class="project-card" data-aos="fade-up" data-aos-duration="1000">
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('projects.store') }}" class="form-centered">
                        @csrf

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
                                       id="name" name="name" value="{{ old('name') }}"
                                       placeholder="Enter project name" required autofocus>
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
                                          placeholder="Enter project description">{{ old('description') }}</textarea>
                                @error('description')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                            <small class="form-text text-muted mt-2">Provide a brief description of your project (optional)</small>
                        </div>

                        <div class="form-actions d-flex justify-content-between align-items-center mt-5" data-aos="fade-up" data-aos-delay="300">
                            <a href="{{ route('projects.index') }}" class="btn btn-light">
                                <i class="fas fa-arrow-left me-2"></i>Back
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Create Project
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endcomponent
@endsection

@push('styles')
<link href="{{ asset('css/projects.css') }}" rel="stylesheet">
@endpush 