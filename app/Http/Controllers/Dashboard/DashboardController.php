<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;

class DashboardController extends Controller
{

    public function index()
    {
        return view('dashboard.index');
    }

    public function login()
    {
        return view('auth.login');
    }

    public function register()
    {
        return view('auth.register');
    }

    public function createPost()
    {
        return view('posts.edit');
    }

    public function editPost($post)
    {
        return view('posts.edit', ['post' => $post]);
    }

    public function posts()
    {
        return view('posts.index');
    }

    public function calendar()
    {
        return view('calendar.index');
    }

    public function platforms()
    {
        return view('platforms.index');
    }

    public function activityLogs()
    {
        return view('activity-logs.index');
    }
}
