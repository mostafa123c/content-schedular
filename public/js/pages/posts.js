window.Posts = {
    filters: {
        status: "", // 0: draft, 1: scheduled, 2: published
        sort_key: "created_at", // created_at or scheduled_time
        sort_type: "desc", // asc or desc
        start_date: "",
        end_date: "",
        page: 1,
    },

    init() {
        this.setupEventListeners();
        this.loadPosts();

        $("#statusFilter").val(this.filters.status);
        $("#sortKey").val(this.filters.sort_key);
        $("#sortType").val(this.filters.sort_type);
        $("#startDate").val(this.filters.start_date);
        $("#endDate").val(this.filters.end_date);
    },

    setupEventListeners() {
        $("#filterForm").on("submit", (e) => {
            e.preventDefault();
            this.filters = {
                status: $("#statusFilter").val(),
                sort_key: $("#sortKey").val(),
                sort_type: $("#sortType").val(),
                start_date: $("#startDate").val(),
                end_date: $("#endDate").val(),
                page: 1,
            };
            this.loadPosts();
        });

        $("#resetFilters").on("click", () => {
            $("#filterForm")[0].reset();
            this.filters = {
                status: "",
                sort_key: "created_at",
                sort_type: "desc",
                start_date: "",
                end_date: "",
                page: 1,
            };
            this.loadPosts();
        });

        $(document).on("click", ".pagination .page-link", (e) => {
            e.preventDefault();
            const page = $(e.currentTarget).data("page");
            if (page) {
                this.filters.page = page;
                this.loadPosts();
            }
        });
    },

    getQueryParams() {
        const params = {};

        if (this.filters.status !== "") {
            params.status = parseInt(this.filters.status);
        }

        if (
            this.filters.sort_key &&
            ["created_at", "scheduled_time"].includes(this.filters.sort_key)
        ) {
            params.sort_key = this.filters.sort_key;
        }

        if (
            this.filters.sort_type &&
            ["asc", "desc"].includes(this.filters.sort_type)
        ) {
            params.sort_type = this.filters.sort_type;
        }

        if (this.filters.start_date) {
            params.start_date = this.filters.start_date;
        }

        if (this.filters.end_date) {
            params.end_date = this.filters.end_date;
        }

        if (this.filters.page > 1) {
            params.page = this.filters.page;
        }

        return new URLSearchParams(params).toString();
    },

    async loadPosts() {
        try {
            UI.loading.show("#postsTable");
            const queryParams = this.getQueryParams();
            const response = await Services.http.get(
                `/user/posts?${queryParams}`
            );

            document.getElementById("fromCount").textContent =
                response.from || 0;
            document.getElementById("toCount").textContent = response.to || 0;
            document.getElementById("totalCount").textContent =
                response.total || 0;

            const posts = response.items || [];
            const pagination = {
                current_page: response.current || 1,
                total_pages: Math.ceil(
                    (response.total || 0) / (response.per || 15)
                ),
                total: response.total || 0,
                prev_page_url: response.prev_page_url,
                next_page_url: response.next_page_url,
            };

            this.renderPosts(posts);
            this.renderPagination(pagination);
        } catch (error) {
            console.error("Posts loading error details:", {
                error,
                message: error.message,
                response: error.responseJSON,
            });
            UI.notify.error(
                error.responseJSON?.message ||
                    error.message ||
                    "Failed to load posts"
            );
        } finally {
            UI.loading.hide("#postsTable");
        }
    },

    renderPosts(posts) {
        const tbody = $("#postsTableBody");
        tbody.empty();

        if (!posts.length) {
            tbody.append(`
                <tr>
                    <td colspan="6" class="text-center py-4">
                        <div class="text-muted">
                            <i class="bi bi-inbox fs-2"></i>
                            <p class="mt-2">No posts found</p>
                        </div>
                    </td>
                </tr>
            `);
            return;
        }

        const getPlatformIcon = (platformName) => {
            if (typeof UI?.getPlatformIcon !== "function") {
                const icons = {
                    Twitter: "twitter",
                    Instagram: "instagram",
                    LinkedIn: "linkedin",
                    Facebook: "facebook",
                };
                return icons[platformName] || "share";
            }
            return UI.getPlatformIcon(platformName);
        };

        posts.forEach((post) => {
            tbody.append(`
                <tr>
                    <td>
                        <div class="d-flex align-items-center">
                            ${
                                post.image_url
                                    ? `<img src="/storage/${post.image_url}"
                                     class="rounded me-2"
                                     style="width: 40px; height: 40px; object-fit: cover;">`
                                    : ""
                            }
                            <div>
                                <h6 class="mb-0">${post.title}</h6>
                                <small class="text-muted">${post.content.substring(
                                    0,
                                    50
                                )}...</small>
                            </div>
                        </div>
                    </td>
                    <td>
                        <div class="platforms">
                            ${post.platforms
                                .map(
                                    (platform) => `
                                <span class="badge bg-light text-dark me-1">
                                    <i class="bi bi-${getPlatformIcon(
                                        platform.name
                                    )} me-1"></i>
                                    ${platform.name}
                                </span>
                            `
                                )
                                .join("")}
                        </div>
                    </td>
                    <td>${UI.formatDate(post.scheduled_time)}</td>
                    <td>
                        <span class="badge ${UI.getStatusBadgeClass(
                            post.status
                        )}">
                            ${post.status}
                        </span>
                    </td>
                    <td>${UI.formatDate(post.created_at)}</td>
                    <td>
                        <div class="d-flex gap-1">
                            <a href="/posts/${
                                post.id
                            }/edit" class="btn btn-sm btn-outline-primary" title="Edit">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <button class="btn btn-sm btn-outline-info" title="Repost" onclick="Posts.repostPost(${
                                post.id
                            })">
                                <i class="bi bi-arrow-repeat"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger" title="Delete" onclick="Posts.deletePost(${
                                post.id
                            })">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            `);
        });
    },

    renderPagination(meta) {
        const pagination = $(".pagination");
        pagination.empty();

        if (meta.total_pages <= 1) return;

        pagination.append(`
            <li class="page-item ${!meta.prev_page_url ? "disabled" : ""}">
                <a class="page-link" href="#" data-page="${
                    meta.current_page - 1
                }">
                    <i class="bi bi-chevron-left"></i>
                </a>
            </li>
        `);

        for (let i = 1; i <= meta.total_pages; i++) {
            if (
                i === 1 ||
                i === meta.total_pages ||
                (i >= meta.current_page - 1 && i <= meta.current_page + 1)
            ) {
                pagination.append(`
                    <li class="page-item ${
                        i === meta.current_page ? "active" : ""
                    }">
                        <a class="page-link" href="#" data-page="${i}">${i}</a>
                    </li>
                `);
            } else if (
                i === meta.current_page - 2 ||
                i === meta.current_page + 2
            ) {
                pagination.append(`
                    <li class="page-item disabled">
                        <span class="page-link">...</span>
                    </li>
                `);
            }
        }

        pagination.append(`
            <li class="page-item ${!meta.next_page_url ? "disabled" : ""}">
                <a class="page-link" href="#" data-page="${
                    meta.current_page + 1
                }">
                    <i class="bi bi-chevron-right"></i>
                </a>
            </li>
        `);
    },

    async deletePost(postId) {
        if (!confirm("Are you sure you want to delete this post?")) {
            return;
        }

        try {
            const deleteButton = document.querySelector(
                `button[onclick="Posts.deletePost(${postId})"]`
            );
            if (deleteButton) UI.loading.show(deleteButton);

            await Services.http.delete(`/posts/${postId}`);

            UI.notify.success("Post deleted successfully");
            this.loadPosts();
        } catch (error) {
            console.error("Delete post error:", error);
            UI.notify.error(error.message || "Failed to delete post");
        } finally {
            const deleteButton = document.querySelector(
                `button[onclick="Posts.deletePost(${postId})"]`
            );
            if (deleteButton) UI.loading.hide(deleteButton);
        }
    },

    async repostPost(postId) {
        if (
            !confirm(
                "Are you sure you want to repost this post? This will create a new draft."
            )
        ) {
            return;
        }

        const repostButton = document.querySelector(
            `button[onclick="Posts.repostPost(${postId})"]`
        );
        try {
            if (repostButton) UI.loading.show(repostButton);

            const response = await Services.http.post(
                `/posts/${postId}/repost`
            );

            UI.notify.success(
                `Post "${response.title}" reposted successfully as a draft.`
            );
            this.loadPosts();
        } catch (error) {
            console.error("Repost post error:", error);
            UI.notify.error(error.message || "Failed to repost post");
        } finally {
            if (repostButton) UI.loading.hide(repostButton);
        }
    },
};

$(document).ready(() => {
    Posts.init();
});
