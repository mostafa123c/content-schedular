@extends('layouts.app')

@section('title', 'Platforms & Settings - Content Scheduler')

@section('content')
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Platforms & Settings</h5>
                    </div>
                    <div class="card-body">
                        <div id="platformsList" class="row g-4">
                            <div class="text-center py-4">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading platforms...</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="platformSettingsModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Platform Settings</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="platformSettingsForm">
                        <div id="settingsFields">
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="savePlatformSettings">
                        <i class="bi bi-save me-1"></i>
                        Save Settings
                        <span class="spinner spinner-border spinner-border-sm d-none" role="status"
                            aria-hidden="true"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    @push('styles')
        <style>
            .platform-card {
                border: 1px solid #dee2e6;
                border-radius: 8px;
                padding: 1.5rem;
                height: 100%;
                transition: all 0.2s ease;
            }

            .platform-card:hover {
                border-color: #6c757d;
                background-color: #f8f9fa;
            }

            .platform-icon {
                font-size: 2rem;
                margin-bottom: 1rem;
            }

            .platform-name {
                font-size: 1.25rem;
                font-weight: 500;
                margin-bottom: 0.5rem;
            }

            .platform-status {
                margin-bottom: 1rem;
            }

            .platform-actions {
                display: flex;
                gap: 0.5rem;
            }

            .settings-field {
                margin-bottom: 1rem;
            }

            .settings-field label {
                font-weight: 500;
                margin-bottom: 0.5rem;
            }

            .platform-twitter .platform-icon {
                color: #1DA1F2;
            }

            .platform-instagram .platform-icon {
                color: #E4405F;
            }

            .platform-linkedin .platform-icon {
                color: #0A66C2;
            }
        </style>
    @endpush

    @push('scripts')
        <script src="{{ asset('js/pages/platforms.js') }}"></script>
        <script>
            $(document).ready(function() {
                Platforms.init();
            });
        </script>
    @endpush
@endsection
