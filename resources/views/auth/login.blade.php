@extends('layouts.app')

@section('title', 'Login - Content Scheduler')

@section('content')
    <div class="container">
        <div class="auth-card">
            <div class="auth-logo text-center mb-4">
                <i class="bi bi-calendar-check" style="font-size: 3rem; color: #007bff;"></i>
                <h2>ContentHub</h2>
                <p class="text-muted">Schedule your content across platforms</p>
            </div>

            <form id="loginForm">
                @csrf
                <div class="mb-3">
                    <label for="email" class="form-label">Email Address</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                        <input type="email" class="form-control" id="email" required autofocus>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-lock"></i></span>
                        <input type="password" class="form-control" id="password" required>
                    </div>
                </div>

                <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input" id="remember">
                    <label class="form-check-label" for="remember">
                        Remember me
                    </label>
                </div>

                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <span class="spinner spinner-border spinner-border-sm d-none" role="status"
                            aria-hidden="true"></span>
                        <span class="button-text">Login</span>
                    </button>
                </div>
            </form>

            <div class="text-center mt-4">
                <p class="mb-0">Don't have an account? <a href="{{ route('register') }}" class="text-decoration-none">Sign
                        up</a></p>
                <a href="#" class="text-muted text-decoration-none small">Forgot password?</a>
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
    </style>
@endsection
