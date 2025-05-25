@extends('layouts.app')

@section('title', 'Calendar - Content Scheduler')

@push('styles')
    <link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css' rel='stylesheet' />


    <style>
        #calendar {
            background: white;
            padding: 1rem;
            border-radius: 8px;
            min-height: 700px;
        }

        .calendar-event {
            margin: 2px 0 !important;
            padding: 2px 4px !important;
            border-radius: 4px !important;
            border: none !important;
            background-color: #f8f9fa;
        }

        .fc-event {
            cursor: pointer !important;
            border: none !important;
            padding: 2px 4px !important;
            margin: 1px 2px !important;
            font-size: 0.85em !important;
        }

        .fc-daygrid-event {
            white-space: normal !important;
            align-items: center !important;
            margin: 2px !important;
        }

        .fc-event-title {
            padding: 2px 4px !important;
            display: block !important;
            overflow: hidden !important;
            text-overflow: ellipsis !important;
            white-space: nowrap !important;
            font-weight: 500 !important;
            line-height: 1.2 !important;
        }

        .fc-event-time {
            font-size: 0.8em !important;
            opacity: 0.8 !important;
            margin-right: 4px !important;
            font-weight: normal !important;
        }

        .fc-event.draft {
            background-color: #6c757d !important;
            color: white !important;
        }

        .fc-event.scheduled {
            background-color: #0d6efd !important;
            color: white !important;
        }

        .fc-event.published {
            background-color: #198754 !important;
            color: white !important;
        }

        .fc-daygrid-day {
            min-height: 100px !important;
        }

        .fc-day-today {
            background-color: rgba(13, 110, 253, 0.05) !important;
        }

        .fc-daygrid-day-number {
            font-weight: 500 !important;
            padding: 8px !important;
            color: #212529 !important;
        }

        .fc-toolbar-title {
            font-size: 1.5rem !important;
            font-weight: 600 !important;
            color: #212529 !important;
        }

        .fc-button-primary {
            background-color: #0d6efd !important;
            border-color: #0a58ca !important;
            box-shadow: none !important;
        }

        .fc-button-primary:disabled {
            background-color: #6c757d !important;
            border-color: #5a6268 !important;
        }

        .fc-content {
            padding: 2px 4px;
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .fc-content .fc-time {
            font-size: 0.8em;
            opacity: 0.9;
        }

        .fc-content .fc-title {
            font-weight: 500;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
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
                            <h5 class="card-title mb-0">Content Calendar</h5>
                            <a href="{{ route('posts.create') }}" class="btn btn-primary">
                                <i class="bi bi-plus-circle me-1"></i>
                                Create Post
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <form id="calendarFilterForm" class="row g-3 mb-4">
                            <div class="col-md-3">
                                <label for="calendarStatusFilter" class="form-label">Status</label>
                                <select class="form-select" id="calendarStatusFilter">
                                    <option value="">All</option>
                                    <option value="0">Draft</option>
                                    <option value="1">Scheduled</option>
                                    <option value="2">Published</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="calendarViewType" class="form-label">Calendar View</label>
                                <select class="form-select" id="calendarViewType">
                                    <option value="dayGridMonth">Month</option>
                                    <option value="timeGridWeek">Week</option>
                                    <option value="timeGridDay">Day</option>
                                    <option value="listWeek">List</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary me-2">
                                    <i class="bi bi-funnel me-1"></i>
                                    Apply Filters
                                </button>
                                <button type="button" class="btn btn-outline-secondary" id="resetCalendarFilters">
                                    <i class="bi bi-x-circle me-1"></i>
                                    Reset
                                </button>
                            </div>
                        </form>

                        <div id="calendar"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="postPreviewModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Post Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="post-preview-content">
                        <h6 class="preview-title mb-3"></h6>
                        <p class="preview-content mb-3"></p>
                        <div class="preview-schedule mb-3">
                            <strong>Scheduled for:</strong>
                            <span class="preview-time"></span>
                        </div>
                        <div class="preview-created mb-3">
                            <strong>Created at:</strong>
                            <span class="preview-created"></span>
                        </div>
                        <div class="preview-status">
                            <strong>Status:</strong>
                            <span class="preview-status-badge"></span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <a href="#" class="btn btn-primary edit-post-btn">
                        <i class="bi bi-pencil me-1"></i>
                        Edit Post
                    </a>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('js/pages/calendar.js') }}"></script>
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js'></script>
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/locales-all.min.js'></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof Calendar !== 'undefined') {
                Calendar.init();
            } else {
                console.error('Calendar object is not defined');
            }
        });
    </script>
@endpush
