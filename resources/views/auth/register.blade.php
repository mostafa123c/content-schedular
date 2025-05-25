@extends('layouts.app')

@section('title', 'Register - Content Scheduler')

@section('content')
    <div class="container">
        <div class="auth-card">
            <div class="auth-logo text-center mb-4">
                <i class="bi bi-calendar-check" style="font-size: 3rem; color: #007bff;"></i>
                <h2>Join ContentHub</h2>
                <p class="text-muted">Create your account to get started</p>
            </div>

            <form id="registerForm">
                @csrf
                <div class="mb-3">
                    <label for="name" class="form-label">Full Name</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-person"></i></span>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label">Email Address</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-lock"></i></span>
                        <input type="password" class="form-control" id="password" name="password" required minlength="8">
                    </div>
                    <div class="form-text">Must be at least 8 characters long</div>
                </div>

                <div class="mb-3">
                    <label for="password_confirmation" class="form-label">Confirm Password</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                        <input type="password" class="form-control" id="password_confirmation" name="password_confirmation"
                            required>
                    </div>
                </div>

                <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input" id="terms" required>
                    <label class="form-check-label" for="terms">
                        I agree to the <a href="#" class="text-decoration-none">Terms of Service</a> and <a
                            href="#" class="text-decoration-none">Privacy Policy</a>
                    </label>
                </div>

                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <span class="spinner spinner-border spinner-border-sm d-none" role="status"
                            aria-hidden="true"></span>
                        <span class="button-text">Create Account</span>
                    </button>
                </div>
            </form>

            <div class="text-center mt-4">
                <p class="mb-0">Already have an account? <a href="{{ route('login') }}" class="text-decoration-none">Sign
                        in</a></p>
            </div>
        </div>
    </div>

    <style>
        .auth-card {
            max-width: 400px;
            margin: 2rem auto;
            padding: 2rem;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        }

        .auth-logo {
            margin-bottom: 2rem;
        }

        .auth-logo h2 {
            margin: 1rem 0 0.5rem;
            color: #333;
        }

        .input-group-text {
            background: #f8f9fa;
            border-right: none;
        }

        .input-group .form-control {
            border-left: none;
        }

        .input-group .form-control:focus {
            border-color: #ced4da;
            box-shadow: none;
        }

        .btn-primary {
            padding: 0.8rem;
            font-weight: 500;
        }

        .spinner {
            margin-right: 0.5rem;
        }

        .password-strength {
            margin-top: 0.5rem;
        }

        .password-strength .progress {
            height: 4px;
            margin-bottom: 0.25rem;
        }

        .password-strength small {
            font-size: 0.75rem;
        }
    </style>

    @push('scripts')
        <script>
            $(document).ready(function() {
                $('#name').focus();

                $('#password_confirmation').on('input', function() {
                    const password = $('#password').val();
                    const confirmation = $(this).val();

                    if (confirmation && password !== confirmation) {
                        $(this).addClass('is-invalid');
                    } else {
                        $(this).removeClass('is-invalid');
                    }
                });

            });
        </script>
    @endpush
@endsection
