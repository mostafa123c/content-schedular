@extends('layouts.app')

@section('title', 'Posts - Content Scheduler')

@section('content')
    <div class="container-fluid py-4">
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">Posts</h5>
                            <a href="{{ route('posts.create') }}" class="btn btn-primary">
                                <i class="bi bi-plus-circle me-1"></i>
                                Create Post
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <form id="filterForm" class="row g-3 mb-4">
                            <div class="col-md-2">
                                <label for="statusFilter" class="form-label">Status</label>
                                <select class="form-select" id="statusFilter">
                                    <option value="">All</option>
                                    <option value="0">Draft</option>
                                    <option value="1">Scheduled</option>
                                    <option value="2">Published</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="sortKey" class="form-label">Sort By</label>
                                <select class="form-select" id="sortKey">
                                    <option value="created_at">Created Date</option>
                                    <option value="scheduled_time">Scheduled Time</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="sortType" class="form-label">Sort Order</label>
                                <select class="form-select" id="sortType">
                                    <option value="desc">Descending</option>
                                    <option value="asc">Ascending</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="startDate" class="form-label">Start Date</label>
                                <input type="date" class="form-control" id="startDate">
                            </div>
                            <div class="col-md-3">
                                <label for="endDate" class="form-label">End Date</label>
                                <input type="date" class="form-control" id="endDate">
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary me-2">
                                    <i class="bi bi-funnel me-1"></i>
                                    Apply Filters
                                </button>
                                <button type="button" class="btn btn-outline-secondary" id="resetFilters">
                                    <i class="bi bi-x-circle me-1"></i>
                                    Reset
                                </button>
                            </div>
                        </form>

                        <div class="table-responsive" id="postsTable">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Post</th>
                                        <th>Platforms</th>
                                        <th>Scheduled Time</th>
                                        <th>Status</th>
                                        <th>Created At</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="postsTableBody">
                                    <tr>
                                        <td colspan="6" class="text-center py-4">
                                            <div class="spinner-border text-primary" role="status">
                                                <span class="visually-hidden">Loading posts...</span>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mt-4">
                            <div class="text-muted small">
                                Showing <span id="fromCount">0</span> to <span id="toCount">0</span> of <span
                                    id="totalCount">0</span> posts
                            </div>
                            <nav aria-label="Posts pagination">
                                <ul class="pagination mb-0"></ul>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
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
    </style>
@endpush

@push('scripts')
    <script src="{{ asset('js/pages/posts.js') }}"></script>
    <script>
        $(document).ready(function() {
            Posts.init();
        });
    </script>
@endpush
