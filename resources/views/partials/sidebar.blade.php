<div class="sidebar">
    <div class="sidebar-header">
        <h3><i class="bi bi-calendar-check"> </i>ContentHub</h3>
    </div>
    <ul class="sidebar-menu">
        <li class="sidebar-item {{ request()->is('/dashboard') || request()->is('dashboard') ? 'active' : '' }}">
            <a href="{{ route('dashboard') }}" class="sidebar-link">
                <i class="bi bi-speedometer2"></i>
                <span>Dashboard</span>
            </a>
        </li>
        <li class="sidebar-item {{ request()->is('posts/create') ? 'active' : '' }}">
            <a href="{{ route('posts.create') }}" class="sidebar-link">
                <i class="bi bi-plus-circle"></i>
                <span>Create Post</span>
            </a>
        </li>
        <li class="sidebar-item {{ request()->is('posts/index') ? 'active' : '' }}">
            <a href="{{ route('posts.index') }}" class="sidebar-link">
                <i class="bi bi-file-earmark-text"></i>
                <span>My Posts</span>
            </a>
        </li>
        <li class="sidebar-item {{ request()->is('calendar') ? 'active' : '' }}">
            <a href="{{ route('calendar') }}" class="sidebar-link">
                <i class="bi bi-calendar3"></i>
                <span>Calendar</span>
            </a>
        </li>

        <li class="sidebar-item {{ request()->is('platforms') ? 'active' : '' }}">
            <a href="{{ route('platforms') }}" class="sidebar-link">
                <i class="bi bi-gear"></i>
                <span>Platforms & Settings</span>
            </a>
        </li>
        <li class="sidebar-item {{ request()->is('activity-logs') ? 'active' : '' }}">
            <a href="{{ route('activity-logs') }}" class="sidebar-link">
                <i class="bi bi-clock-history"></i>
                <span>Activity Logs</span>
            </a>
        </li>
        <li>
            <a href="#" class="logout-btn sidebar-link">
                <i class="bi bi-box-arrow-right"></i>
                <span>Logout</span>
            </a>
        </li>
    </ul>
</div>
