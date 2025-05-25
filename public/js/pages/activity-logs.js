window.ActivityLogs = {
    currentPage: 1,
    filters: {
        action: "",
    },

    init() {
        this.loadActionTypes();
        this.loadLogs(1);
        this.setupEventListeners();
    },

    setupEventListeners() {
        document
            .getElementById("actionTypeFilter")
            .addEventListener("change", (e) => {
                this.filters.action = e.target.value;
                this.loadLogs(1);
            });
    },

    async loadActionTypes() {
        try {
            const response = await Services.http.get("/activity-logs/types");
            this.renderActionTypes(response.actionLogTypes);
        } catch (error) {
            console.error("Failed to load action types:", error);
            UI.notify.error("Failed to load action types");
        }
    },

    renderActionTypes(types) {
        const select = document.getElementById("actionTypeFilter");
        const options = types.map((type) => {
            const formattedType = type
                .split("_")
                .map((word) => word.charAt(0).toUpperCase() + word.slice(1))
                .join(" ");
            return `<option value="${type}">${formattedType}</option>`;
        });

        select.innerHTML =
            '<option value="">All Actions</option>' + options.join("");
    },

    getActionIcon(action) {
        const icons = {
            post_created: '<i class="bi bi-plus-circle"></i>',
            post_updated: '<i class="bi bi-pencil"></i>',
            post_deleted: '<i class="bi bi-trash"></i>',
            platform_toggled: '<i class="bi bi-toggle-on"></i>',
            platform_settings_updated: '<i class="bi bi-gear"></i>',
            user_login: '<i class="bi bi-box-arrow-in-right"></i>',
            user_logout: '<i class="bi bi-box-arrow-right"></i>',
            user_registered: '<i class="bi bi-person-plus"></i>',
            publish_post: '<i class="bi bi-send"></i>',
        };
        return icons[action] || '<i class="bi bi-activity"></i>';
    },

    getActionClass(action) {
        if (action.includes("post")) return "activity-post";
        if (action.includes("platform")) return "activity-platform";
        if (action.includes("user")) return "activity-user";
        return "activity-post";
    },

    formatDate(dateString) {
        const date = new Date(dateString);
        return date.toLocaleString("en-US", {
            weekday: "short",
            year: "numeric",
            month: "short",
            day: "numeric",
            hour: "numeric",
            minute: "2-digit",
            hour12: true,
        });
    },

    async loadLogs(page = 1) {
        try {
            const params = new URLSearchParams({ page });

            if (this.filters.action) {
                params.append("action", this.filters.action);
            }

            const response = await Services.http.get(
                `/activity-logs?${params}`
            );
            this.currentPage = page;
            this.renderLogs(response);
        } catch (error) {
            console.error("Failed to load activity logs:", error);
            UI.notify.error("Failed to load activity logs");
        }
    },

    renderLogs(data) {
        const timeline = document.getElementById("activityTimeline");

        document.getElementById("fromCount").textContent = data.from || 0;
        document.getElementById("toCount").textContent = data.to || 0;
        document.getElementById("totalCount").textContent = data.total || 0;

        if (!data.items.length) {
            timeline.innerHTML = `
                <div class="text-center text-muted py-5">
                    <i class="bi bi-inbox fs-1"></i>
                    <p class="mt-3">No activity logs found</p>
                </div>
            `;
            return;
        }

        timeline.innerHTML = data.items
            .map(
                (activity) => `
            <div class="timeline-item">
                <div class="d-flex align-items-start mb-3">
                    <div class="activity-icon ${this.getActionClass(
                        activity.action
                    )} me-3">
                        ${this.getActionIcon(activity.action)}
                    </div>
                    <div class="flex-grow-1">
                        <div class="d-flex justify-content-between align-items-center">
                            <h6 class="mb-0">${activity.user.name}</h6>
                            <span class="timeline-date">${this.formatDate(
                                activity.created_at
                            )}</span>
                        </div>
                        <p class="mb-0 text-muted">${activity.description}</p>
                    </div>
                </div>
            </div>
        `
            )
            .join("");

        this.renderPagination(data);
    },

    renderPagination(data) {
        const pagination = document.getElementById("pagination");
        const totalPages = Math.ceil(data.total / data.per);

        if (totalPages <= 1) {
            pagination.innerHTML = "";
            return;
        }

        let html = "";

        html += `
            <li class="page-item ${!data.prev_page_url ? "disabled" : ""}">
                <a class="page-link" href="#" onclick="ActivityLogs.loadLogs(${
                    this.currentPage - 1
                })" ${
            !data.prev_page_url ? 'tabindex="-1" aria-disabled="true"' : ""
        }>
                    <i class="bi bi-chevron-left"></i>
                </a>
            </li>
        `;

        for (let i = 1; i <= totalPages; i++) {
            if (
                i === 1 ||
                i === totalPages ||
                (i >= this.currentPage - 1 && i <= this.currentPage + 1)
            ) {
                html += `
                    <li class="page-item ${
                        i === this.currentPage ? "active" : ""
                    }">
                        <a class="page-link" href="#" onclick="ActivityLogs.loadLogs(${i})">${i}</a>
                    </li>
                `;
            } else if (
                i === this.currentPage - 2 ||
                i === this.currentPage + 2
            ) {
                html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
            }
        }

        html += `
            <li class="page-item ${!data.next_page_url ? "disabled" : ""}">
                <a class="page-link" href="#" onclick="ActivityLogs.loadLogs(${
                    this.currentPage + 1
                })" ${
            !data.next_page_url ? 'tabindex="-1" aria-disabled="true"' : ""
        }>
                    <i class="bi bi-chevron-right"></i>
                </a>
            </li>
        `;

        pagination.innerHTML = html;
    },
};

document.addEventListener("DOMContentLoaded", function () {
    ActivityLogs.init();
});
