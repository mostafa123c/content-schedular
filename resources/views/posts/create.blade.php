@extends('layouts.app')

@section('title',
    isset($post) && is_object($post)
    ? 'Edit Post - Content Scheduler'
    : 'Create Post - Content
    Scheduler')

@section('content')
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">{{ isset($post) && is_object($post) ? 'Edit Post' : 'Create New Post' }}
                        </h5>
                    </div>
                    <div class="card-body">
                        <form id="postForm" class="needs-validation" novalidate>
                            @csrf
                            <input type="hidden" id="postId"
                                value="{{ isset($post) && is_object($post) ? $post->id : '' }}">

                            <div class="mb-4">
                                <label for="title" class="form-label">Post Title</label>
                                <input type="text" class="form-control" id="title" name="title" required>
                                <div class="invalid-feedback">Please enter a title for your post.</div>
                            </div>

                            <div class="mb-4">
                                <label for="content" class="form-label">Content</label>
                                <textarea class="form-control" id="content" name="content" rows="4" required></textarea>
                                <div class="invalid-feedback">Please enter content for your post.</div>
                                <div class="form-text">
                                    <span id="characterCount">0</span>/280 characters
                                </div>
                            </div>

                            <div class="mb-4">
                                <label for="image" class="form-label">Image</label>
                                <div class="image-upload-container">
                                    <input type="file" class="form-control" id="image" name="image_url"
                                        accept="image/*" {{ isset($post) && is_object($post) ? '' : 'required' }}>
                                    <div class="invalid-feedback">Please select an image.</div>
                                    <div class="current-image mt-2 d-none">
                                        <img src="" alt="Current post image" class="img-thumbnail"
                                            style="max-height: 200px;">
                                    </div>
                                    <div id="imagePreview" class="mt-2 d-none">
                                        <img src="" alt="Image preview" class="img-thumbnail"
                                            style="max-height: 200px;">
                                    </div>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label">Platforms</label>
                                <div class="platforms-grid" id="platformsGrid">
                                    <div class="text-center py-4">
                                        <div class="spinner-border text-primary" role="status">
                                            <span class="visually-hidden">Loading platforms...</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="invalid-feedback">Please select at least one platform.</div>
                            </div>

                            <div class="mb-4">
                                <label for="scheduledTime" class="form-label">Schedule Time</label>
                                <input type="datetime-local" class="form-control" id="scheduledTime" name="scheduled_time"
                                    required>
                                <div class="invalid-feedback">Please select a schedule time.</div>
                            </div>

                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary" data-action="schedule">
                                    <i class="bi bi-calendar-plus me-1"></i>
                                    Schedule Post
                                    <span class="spinner spinner-border spinner-border-sm d-none" role="status"
                                        aria-hidden="true"></span>
                                </button>
                                <button type="submit" class="btn btn-outline-secondary" data-action="draft">
                                    <i class="bi bi-file-earmark me-1"></i>
                                    Save as Draft
                                    <span class="spinner spinner-border spinner-border-sm d-none" role="status"
                                        aria-hidden="true"></span>
                                </button>
                                <button type="button" class="btn btn-outline-danger" onclick="history.back()">
                                    <i class="bi bi-x-circle me-1"></i>
                                    Cancel
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Post Preview</h5>
                    </div>
                    <div class="card-body">
                        <div class="post-preview">
                            <div class="preview-header mb-3">
                                <select class="form-select" id="previewPlatform">
                                    <option value="">Select platform to preview</option>
                                </select>
                            </div>
                            <div class="preview-content" id="postPreview">
                                <div class="text-center text-muted py-4">
                                    Select a platform to preview your post
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Publishing Info</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span>Daily Posts Remaining:</span>
                            <span class="badge bg-primary" id="remainingPosts">-</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span>Created By:</span>
                            <span class="created-by">-</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span>Created At:</span>
                            <span class="created-at">-</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span>Status:</span>
                            <span class="badge status-badge bg-secondary">Draft</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('styles')
        <style>
            .platforms-grid {
                display: grid;
                grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
                gap: 1rem;
            }

            .platform-card {
                border: 1px solid #dee2e6;
                border-radius: 8px;
                padding: 1rem;
                cursor: pointer;
                transition: all 0.2s ease;
            }

            .platform-card:hover {
                border-color: #6c757d;
                background-color: #f8f9fa;
            }

            .platform-card.selected {
                border-color: #0d6efd;
                background-color: #f0f7ff;
            }

            .platform-card .platform-icon {
                font-size: 1.5rem;
                margin-bottom: 0.5rem;
                color: #6c757d;
            }

            .platform-card.selected .platform-icon {
                color: #0d6efd;
            }

            .post-preview {
                min-height: 300px;
            }

            .preview-content {
                border: 1px solid #dee2e6;
                border-radius: 8px;
                padding: 1rem;
                margin-top: 1rem;
            }

            .image-upload-container {
                position: relative;
            }

            #imagePreview img {
                max-width: 100%;
                height: auto;
            }
        </style>
    @endpush

    @push('scripts')
        <script src="{{ asset('js/pages/post-form.js') }}"></script>
        <script>
            $(document).ready(function() {
                PostForm.init();
            });
        </script>
    @endpush
@endsection
