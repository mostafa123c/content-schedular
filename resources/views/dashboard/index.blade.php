@extends('layouts.app')

@section('title', 'Dashboard - Content Scheduler')

@section('content')
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12 mb-4">
                <div class="card welcome-card">
                    <div class="card-body">
                        <h4 class="welcome-text">Welcome back</h4>
                        <p class="text-muted">Here's what's happening with your content</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row dashboard-stats g-4">
            <div class="col-md-6 col-lg-3">
                <div class="card stat-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h6 class="stat-title">Total Posts</h6>
                                <h3 class="stat-value" id="totalPosts">-</h3>
                            </div>
                            <div class="stat-icon">
                                <i class="bi bi-file-text"></i>
                            </div>
                        </div>
                        <p class="stat-description mb-0">All time posts</p>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-lg-3">
                <div class="card stat-card scheduled">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h6 class="stat-title">Scheduled</h6>
                                <h3 class="stat-value" id="scheduledPosts">-</h3>
                            </div>
                            <div class="stat-icon">
                                <i class="bi bi-calendar2-check"></i>
                            </div>
                        </div>
                        <p class="stat-description mb-0">Upcoming posts</p>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-lg-3">
                <div class="card stat-card published">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h6 class="stat-title">Published</h6>
                                <h3 class="stat-value" id="publishedPosts">-</h3>
                            </div>
                            <div class="stat-icon">
                                <i class="bi bi-check2-circle"></i>
                            </div>
                        </div>
                        <p class="stat-description mb-0">Successfully posted</p>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-lg-3">
                <div class="card stat-card draft">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h6 class="stat-title">Drafts</h6>
                                <h3 class="stat-value" id="draftPosts">-</h3>
                            </div>
                            <div class="stat-icon">
                                <i class="bi bi-file-earmark"></i>
                            </div>
                        </div>
                        <p class="stat-description mb-0">Work in progress</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Platform Performance</h5>
                    </div>
                    <div class="card-body">
                        <div id="platformStats">
                            <div class="text-center py-4">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mt-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Upcoming Posts</h5>
                        <a href="{{ route('posts.create') }}" class="btn btn-primary">
                            <i class="bi bi-plus-lg"></i> New Post
                        </a>
                    </div>
                    <div class="card-body">
                        <div id="upcomingPosts">
                            <div class="text-center py-4">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Publishing Success Rate</h5>
                    </div>
                    <div class="card-body">
                        <div class="text-center mb-3">
                            <h2 class="mb-0" id="successRate">-%</h2>
                            <p class="text-muted">Success Rate</p>
                        </div>
                        <div class="progress" style="height: 10px;">
                            <div id="successRateProgress" class="progress-bar bg-success" role="progressbar"
                                style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>
                </div>

                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Quick Actions</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="{{ route('posts.create') }}" class="btn btn-primary">
                                <i class="bi bi-plus-lg"></i> Create New Post
                            </a>
                            <a href="{{ route('calendar') }}" class="btn btn-outline-primary">
                                <i class="bi bi-calendar3"></i> View Calendar
                            </a>
                            <a href="{{ route('platforms') }}" class="btn btn-outline-primary">
                                <i class="bi bi-share"></i> Manage Platforms
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .welcome-card {
            background: linear-gradient(to right, #4e73df, #6f42c1);
            color: white;
            border: none;
            border-radius: 15px;
        }

        .welcome-text {
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
        }

        .stat-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.05);
            transition: transform 0.2s;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-title {
            color: #6c757d;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .stat-value {
            font-size: 2rem;
            font-weight: 600;
            margin: 0.5rem 0;
        }

        .stat-description {
            color: #6c757d;
            font-size: 0.85rem;
        }

        .stat-icon {
            background: rgba(78, 115, 223, 0.1);
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .stat-icon i {
            font-size: 1.5rem;
            color: #4e73df;
        }

        .stat-card.scheduled .stat-icon {
            background: rgba(28, 187, 140, 0.1);
        }

        .stat-card.scheduled .stat-icon i {
            color: #1cbb8c;
        }

        .stat-card.published .stat-icon {
            background: rgba(54, 185, 204, 0.1);
        }

        .stat-card.published .stat-icon i {
            color: #36b9cc;
        }

        .stat-card.draft .stat-icon {
            background: rgba(133, 135, 150, 0.1);
        }

        .stat-card.draft .stat-icon i {
            color: #858796;
        }

        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.05);
        }

        .card-header {
            background: none;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            padding: 1.5rem;
        }

        .card-title {
            color: #5a5c69;
            font-size: 1.1rem;
            font-weight: 600;
        }

        .platform-stat {
            background: #f8f9fc;
            border-radius: 10px;
            padding: 1rem;
            transition: all 0.2s ease;
        }

        .platform-stat:hover {
            background: #f0f2f8;
        }

        .platform-header {
            cursor: pointer;
            user-select: none;
            padding: 0.5rem;
            border-radius: 8px;
        }

        .platform-header:hover {
            background: rgba(0, 0, 0, 0.03);
        }

        .platform-toggle {
            transition: transform 0.2s ease;
        }

        .rotate-180 {
            transform: rotate(180deg);
        }

        .platform-posts {
            background: white;
            border-radius: 8px;
            margin-top: 0.5rem;
        }

        .platform-post {
            transition: background-color 0.2s ease;
        }

        .platform-post:hover {
            background-color: #f8f9fc;
        }

        .platform-post:last-child {
            border-bottom: none !important;
        }

        .platform-post .btn-outline-primary {
            padding: 0.25rem 0.5rem;
            line-height: 1;
        }

        .platform-post .btn-outline-primary:hover {
            color: white;
        }

        .upcoming-post {
            padding: 1rem;
            border-radius: 10px;
            background: #f8f9fc;
        }

        .upcoming-post:hover {
            background: #f0f2f8;
        }

        .scheduled-time .time {
            font-size: 1.1rem;
            font-weight: 600;
            color: #4e73df;
        }

        .progress {
            background-color: #eaecf4;
            border-radius: 10px;
        }

        .progress-bar {
            border-radius: 10px;
        }

        .btn-primary {
            padding: 0.5rem 1rem;
            font-weight: 500;
        }

        .btn-primary i {
            margin-right: 0.5rem;
        }

        #successRate {
            font-size: 2.5rem;
            font-weight: 600;
            color: #1cc88a;
        }
    </style>

    @push('scripts')
        <script>
            $(document).ready(function() {
                const user = Services.user.get();
                if (user && user.name) {
                    $('.welcome-text').text('Welcome back, ' + user.name + '!');
                } else {
                    $('.welcome-text').text('Welcome back!');
                }

                Services.analytics.getDashboardStats().then(data => {
                    UI.dashboard.updateStats(data);
                }).catch(error => {
                    UI.notify.error('Failed to load dashboard data');
                    console.error('Dashboard error:', error);
                });

                setInterval(() => {
                    Services.analytics.getDashboardStats().then(data => {
                        UI.dashboard.updateStats(data);
                    });
                }, 300000); // Refresh every 5 minutes
            });
        </script>
    @endpush
@endsection
