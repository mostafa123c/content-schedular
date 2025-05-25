@extends('layouts.app')

@section('title', 'Activity Logs - Content Scheduler')

@push('styles')
    <style>
        .activity-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
        }

        .activity-post {
            background-color: #e3f2fd;
            color: #0d6efd;
        }

        .activity-platform {
            background-color: #f5e6ff;
            color: #6f42c1;
        }

        .activity-user {
            background-color: #e8f5e9;
            color: #198754;
        }

        .timeline-item {
            position: relative;
            padding-left: 3rem;
            padding-bottom: 1.5rem;
            border-left: 2px solid #e9ecef;
        }

        .timeline-item:last-child {
            border-left-color: transparent;
        }

        .timeline-item::before {
            content: '';
            position: absolute;
            left: -8px;
            top: 0;
            width: 14px;
            height: 14px;
            border-radius: 50%;
            background: #fff;
            border: 2px solid #0d6efd;
        }

        .timeline-date {
            font-size: 0.875rem;
            color: #6c757d;
        }

        .activity-card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .pagination {
            margin-bottom: 0;
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">Activity Logs</h5>
                            <div>
                                <select class="form-select" id="actionTypeFilter">
                                    <option value="">All Actions</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div id="activityTimeline" class="timeline">
                        </div>

                        <div class="d-flex justify-content-between align-items-center mt-4">
                            <div class="text-muted small">
                                Showing <span id="fromCount">0</span> to <span id="toCount">0</span> of <span
                                    id="totalCount">0</span> activities
                            </div>
                            <nav aria-label="Activity log pagination">
                                <ul class="pagination" id="pagination">
                                </ul>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('js/pages/activity-logs.js') }}"></script>
@endpush
