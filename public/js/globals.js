window.CONFIG = {
    api: {
        baseUrl: "/api/v1",
        tokenKey: "auth_token",
        userKey: "user",
    },
    routes: {
        dashboard: "/dashboard",
        login: "/login",
        register: "/register",
        posts: "/posts",
    },
    publicPaths: ["/login", "/register"],
};

window.Services = {
    token: {
        get() {
            return document.cookie
                .split("; ")
                .find((row) => row.startsWith("auth_token="))
                ?.split("=")[1];
        },
        set(token) {
            document.cookie = `auth_token=${token}; path=/`;
        },
        remove() {
            document.cookie =
                "auth_token=; path=/; expires=Thu, 01 Jan 1970 00:00:01 GMT";
        },
    },

    user: {
        get() {
            const data = localStorage.getItem(CONFIG.api.userKey);
            return data ? JSON.parse(data) : null;
        },
        set(user) {
            localStorage.setItem(CONFIG.api.userKey, JSON.stringify(user));
        },
        remove() {
            localStorage.removeItem(CONFIG.api.userKey);
        },
    },

    http: {
        setup() {
            $.ajaxSetup({
                headers: {
                    "X-CSRF-TOKEN": document
                        .querySelector('meta[name="csrf-token"]')
                        ?.getAttribute("content"),
                    Authorization: `Bearer ${Services.token.get()}`,
                },
            });
        },

        get(endpoint) {
            return this.request("GET", endpoint);
        },

        post(endpoint, data = null) {
            return this.request("POST", endpoint, data);
        },

        put(endpoint, data = null) {
            return this.request("PUT", endpoint, data);
        },

        delete(endpoint) {
            return this.request("DELETE", endpoint);
        },

        async request(method, endpoint, data = null, isFormData = false) {
            try {
                const config = {
                    url: `${CONFIG.api.baseUrl}${endpoint}`,
                    method: method,
                    xhrFields: {
                        withCredentials: true,
                    },
                };

                if (data) {
                    if (isFormData) {
                        config.processData = false;
                        config.contentType = false;
                        config.data = data;
                    } else {
                        config.data = JSON.stringify(data);
                        config.contentType = "application/json";
                    }
                }

                const token = Services.token.get();
                if (token) {
                    config.headers = {
                        Authorization: `Bearer ${token}`,
                        "X-CSRF-TOKEN": document
                            .querySelector('meta[name="csrf-token"]')
                            ?.getAttribute("content"),
                    };
                }

                const response = await $.ajax(config);
                return response;
            } catch (error) {
                console.error("API Request Error:", {
                    endpoint,
                    error: error,
                    response: error.responseJSON,
                });
                throw error.responseJSON || error;
            }
        },
    },

    auth: {
        async login(credentials) {
            const response = await Services.http.post("/login", credentials);
            if (response.access_token) {
                this.handleAuthSuccess(response);
            }
            return response;
        },

        async register(userData) {
            const response = await Services.http.post("/register", userData);
            if (response.access_token) {
                this.handleAuthSuccess(response);
            }
            return response;
        },

        async logout() {
            try {
                await Services.http.post("/logout");
            } catch (error) {
                console.error("Logout error:", error);
            } finally {
                this.clearAuth();
                window.location.replace(CONFIG.routes.login);
            }
        },

        handleAuthSuccess(response) {
            Services.token.set(response.access_token);
            Services.user.set(response.user);
            Services.http.setup();
        },

        clearAuth() {
            Services.token.remove();
            Services.user.remove();
        },

        isAuthenticated() {
            return !!Services.token.get();
        },

        requireAuth() {
            if (!this.isAuthenticated()) {
                window.location.replace(CONFIG.routes.login);
            }
        },
    },

    analytics: {
        async getDashboardStats() {
            return await Services.http.get("/analytics");
        },
    },

    platforms: {
        async getAll() {
            try {
                const response = await Services.http.get("/platforms");
                console.log("Platforms API Response:", response);
                return response;
            } catch (error) {
                console.error("Platforms API Error:", {
                    status: error.status,
                    statusText: error.statusText,
                    response: error.responseJSON,
                    error: error,
                });
                throw error;
            }
        },

        async connect(platformId, credentials) {
            return await Services.http.post(
                `/platforms/${platformId}/connect`,
                credentials
            );
        },

        async disconnect(platformId) {
            return await Services.http.delete(
                `/platforms/${platformId}/disconnect`
            );
        },

        async getConnectionStatus(platformId) {
            return await Services.http.get(`/platforms/${platformId}/status`);
        },
    },
};

const UI = {
    loading: {
        show(element) {
            const button = $(element);
            const spinner = button.find(".spinner");
            const text = button.find(".button-text");
            button.prop("disabled", true);
            spinner.removeClass("d-none");
            if (text.length) text.addClass("d-none");
        },

        hide(element) {
            const button = $(element);
            const spinner = button.find(".spinner");
            const text = button.find(".button-text");
            button.prop("disabled", false);
            spinner.addClass("d-none");
            if (text.length) text.removeClass("d-none");
        },
    },

    notify: {
        error(message) {
            toastr.error(message || "An error occurred");
        },
        success(message) {
            toastr.success(message);
        },
        info(message) {
            toastr.info(message);
        },
    },

    redirect(path) {
        window.location.href = path;
    },

    formatDate(date) {
        return new Date(date).toLocaleDateString([], {
            month: "short",
            day: "numeric",
            hour: "2-digit",
            minute: "2-digit",
        });
    },

    getStatusBadgeClass(status) {
        const statusClasses = {
            published: "bg-success",
            scheduled: "bg-primary",
            draft: "bg-secondary",
            failed: "bg-danger",
        };
        return statusClasses[status?.toLowerCase()] || "bg-secondary";
    },

    getPlatformIcon(platformName) {
        const icons = {
            Twitter: "bi bi-twitter",
            Instagram: "bi bi-instagram",
            LinkedIn: "bi bi-linkedin",
            Facebook: "bi bi-facebook",
        };
        return icons[platformName] || "bi bi-share";
    },

    getPlatformType(platformName) {
        return platformName?.toLowerCase() || "unknown";
    },

    validatePlatformRequirements(platform, content, hasImage) {
        const errors = [];

        if (platform.requirements) {
            if (
                platform.character_limit &&
                content.length > platform.character_limit
            ) {
                errors.push(
                    `Content exceeds ${platform.character_limit} character limit for ${platform.name}`
                );
            }

            if (platform.requirements.image_required && !hasImage) {
                errors.push(`${platform.name} requires an image`);
            }

            if (
                !platform.requirements.support_link &&
                content.includes("http")
            ) {
                errors.push(
                    `${platform.name} does not support links in content`
                );
            }
        }

        return errors;
    },

    dashboard: {
        updateStats(data) {
            $("#totalPosts").text(data.posts_count || 0);

            $("#scheduledPosts").text(data.posts_by_status?.scheduled || 0);
            $("#publishedPosts").text(data.posts_by_status?.published || 0);
            $("#draftPosts").text(data.posts_by_status?.draft || 0);

            const successRate = data.publish_success_rate || 0;
            $("#successRate").text(`${successRate}%`);
            $("#successRateProgress").css("width", `${successRate}%`);

            if (data.posts_per_platform?.length) {
                const platformStatsHtml = data.posts_per_platform
                    .map(
                        (platform) => `
                    <div class="platform-stat mb-3">
                        <div class="d-flex justify-content-between align-items-center platform-header" data-platform="${
                            platform.id
                        }">
                            <div class="d-flex align-items-center">
                                <i class="${UI.getPlatformIcon(
                                    platform.name
                                )} me-2 fs-5"></i>
                                <h6 class="mb-0">${platform.name}</h6>
                            </div>
                            <div class="d-flex align-items-center">
                                <span class="me-3">${
                                    platform.posts_count
                                } posts</span>
                                <i class="bi bi-chevron-down platform-toggle"></i>
                            </div>
                        </div>
                        <div class="platform-posts mt-3" style="display: none;">
                            ${
                                platform.posts?.length
                                    ? platform.posts
                                          .map(
                                              (post) => `
                                <div class="platform-post p-3 border-bottom">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h6 class="mb-1">${post.title}</h6>
                                            <small class="text-muted">
                                                <i class="bi bi-clock me-1"></i>
                                                ${UI.formatDateTime(
                                                    post.scheduled_time
                                                )}
                                            </small>
                                            <span class="ms-2 badge ${UI.getStatusBadgeClass(
                                                post.status_text
                                            )}">${post.status_text}</span>
                                        </div>
                                        <a href="/posts/${
                                            post.id
                                        }/edit" class="btn btn-outline-primary btn-sm">
                                            <i class="bi bi-pencil"></i> Edit
                                        </a>
                                    </div>
                                </div>
                            `
                                          )
                                          .join("")
                                    : '<p class="text-center text-muted my-3">No posts available</p>'
                            }
                        </div>
                    </div>
                `
                    )
                    .join("");

                $("#platformStats").html(platformStatsHtml);

                $(".platform-header").on("click", function () {
                    const platformId = $(this).data("platform");
                    const postsDiv = $(this).siblings(".platform-posts");
                    const toggleIcon = $(this).find(".platform-toggle");

                    postsDiv.slideToggle();
                    toggleIcon.toggleClass("rotate-180");
                });
            } else {
                $("#platformStats").html(
                    '<p class="text-center text-muted my-4">No platform data available</p>'
                );
            }

            if (data.upcoming_posts?.length) {
                const upcomingPostsHtml = data.upcoming_posts
                    .map(
                        (post) => `
                    <div class="upcoming-post mb-3">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h6 class="mb-1">${post.title}</h6>
                                <div class="scheduled-time">
                                    <i class="bi bi-clock me-1"></i>
                                    <span class="time">${UI.formatDateTime(
                                        post.scheduled_time
                                    )}</span>
                                </div>
                                <div class="mt-1">
                                    ${post.platforms
                                        ?.map(
                                            (platform) =>
                                                `<i class="${UI.getPlatformIcon(
                                                    platform.name
                                                )} me-2" title="${
                                                    platform.name
                                                }"></i>`
                                        )
                                        .join("")}
                                </div>
                            </div>
                            <a href="/posts/${
                                post.id
                            }/edit" class="btn btn-outline-primary btn-sm">
                                <i class="bi bi-pencil"></i> Edit
                            </a>
                        </div>
                    </div>
                `
                    )
                    .join("");

                $("#upcomingPosts").html(upcomingPostsHtml);
            } else {
                $("#upcomingPosts").html(
                    '<p class="text-center text-muted my-4">No upcoming posts</p>'
                );
            }
        },
    },

    formatDateTime(dateString) {
        if (!dateString) return "";
        const date = new Date(dateString);
        const now = new Date();
        const tomorrow = new Date(now);
        tomorrow.setDate(tomorrow.getDate() + 1);

        const timeStr = date.toLocaleTimeString("en-US", {
            hour: "numeric",
            minute: "2-digit",
            hour12: true,
        });

        if (date.toDateString() === now.toDateString()) {
            return `Today at ${timeStr}`;
        } else if (date.toDateString() === tomorrow.toDateString()) {
            return `Tomorrow at ${timeStr}`;
        } else {
            return date.toLocaleDateString("en-US", {
                month: "short",
                day: "numeric",
                hour: "numeric",
                minute: "2-digit",
                hour12: true,
            });
        }
    },
};

window.UI = UI;
