@extends('layouts.app')

@section('content')
    <div class="container py-4">
        <div class="row">
            <div class="col-md-8 mx-auto">
                <div class="card shadow-sm">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">{{ request()->is('*/edit') ? 'Edit Post' : 'Create Post' }}</h4>
                        <a href="{{ route('posts.index') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-arrow-left"></i> Back to Posts
                        </a>
                    </div>
                    <div class="card-body">
                        <form id="postForm" data-post-id="{{ request()->is('*/edit') ? request()->segment(2) : '' }}">
                            @csrf
                            <div class="mb-4">
                                <label class="form-label">Title</label>
                                <input type="text" class="form-control" name="title" id="title" required>
                            </div>

                            <div class="mb-4">
                                <label class="form-label">Platforms</label>
                                <div id="platformsGrid" class="platform-selection">
                                    <div class="text-center py-4">
                                        <div class="spinner-border text-primary" role="status">
                                            <span class="visually-hidden">Loading platforms...</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label">Content</label>
                                <textarea class="form-control" name="content" id="content" rows="5" required></textarea>
                                <div class="form-text d-flex justify-content-between">
                                    <span>Characters: <span id="characterCount">0</span></span>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label">Image</label>
                                <input type="file" class="form-control" name="image_url" id="image" accept="image/*">

                                <div id="imagePreview" class="mt-2 d-none">
                                    <img src="" alt="Preview" class="img-thumbnail" style="max-height: 200px">
                                </div>

                                <div class="current-image mt-2 d-none">
                                    <img src="" alt="Current image" class="img-thumbnail" style="max-height: 200px">
                                </div>

                                <button type="button" class="btn btn-sm btn-outline-danger mt-2 d-none" id="removeImage">
                                    <i class="bi bi-trash"></i> Remove Image
                                </button>
                            </div>

                            <div class="mb-4">
                                <label class="form-label">Schedule Time</label>
                                <input type="datetime-local" class="form-control" name="scheduled_time" id="scheduledTime"
                                    required>
                            </div>

                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary" data-action="schedule">
                                    <i class="bi bi-calendar-plus me-1"></i>
                                    Schedule Post
                                </button>
                                <button type="submit" class="btn btn-outline-secondary" data-action="draft">
                                    <i class="bi bi-file-earmark me-1"></i>
                                    Save as Draft
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
        </div>
    </div>

    @push('scripts')
        <script src="{{ asset('js/pages/post-form.js') }}"></script>
        <script>
            document.addEventListener('DOMContentLoaded', async function() {
                PostForm.init();

                const postId = document.getElementById('postForm').dataset.postId;
                if (postId) {
                    try {
                        UI.loading.show('form');

                        const response = await fetch(`/api/v1/posts/${postId}`, {
                            headers: {
                                "Authorization": `Bearer ${document.cookie
                                    .split("; ")
                                    .find((row) => row.startsWith("auth_token="))
                                    ?.split("=")[1]}`,
                                "Content-Type": "application/json",
                            },
                            credentials: "include"
                        });

                        if (!response.ok) {
                            throw new Error('Failed to load post');
                        }

                        const data = await response.json();
                        const post = data.data || data;

                        document.getElementById('title').value = post.title || '';
                        document.getElementById('content').value = post.content || '';
                        document.getElementById('characterCount').textContent = (post.content || '').length;

                        if (post.scheduled_time) {
                            const scheduledDate = new Date(post.scheduled_time);
                            document.getElementById('scheduledTime').value = scheduledDate.toISOString().slice(0,
                                16);
                        }

                        PostForm.selectedPlatforms = post.platforms?.map(p => p.id) || [];

                        if (post.image_url) {
                            const currentImage = document.querySelector('.current-image');
                            currentImage.classList.remove('d-none');
                            currentImage.querySelector('img').src = `/storage/${post.image_url}`;
                            document.getElementById('removeImage').classList.remove('d-none');
                        }

                        await PostForm.loadPlatforms();
                    } catch (error) {
                        console.error('Error loading post:', error);
                        UI.notify.error('Failed to load post data');
                    } finally {
                        UI.loading.hide('form');
                    }
                }
            });
        </script>
    @endpush
@endsection
