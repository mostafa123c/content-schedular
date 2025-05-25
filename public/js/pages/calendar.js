window.Calendar = {
    calendar: null,
    filters: {
        status: "",
    },

    init() {
        this.initCalendar();
        this.setupEventListeners();
    },

    initCalendar() {
        const calendarEl = document.getElementById("calendar");
        if (!calendarEl) {
            console.error("Calendar element not found");
            return;
        }

        this.calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: "dayGridMonth",
            headerToolbar: {
                left: "prev,next today",
                center: "title",
                right: "dayGridMonth,timeGridWeek,timeGridDay,listWeek",
            },
            height: "auto",
            dayMaxEvents: true,
            nowIndicator: true,
            initialDate: new Date(),
            displayEventTime: true,
            eventDisplay: "block",
            slotEventOverlap: false,
            eventTimeFormat: {
                hour: "numeric",
                minute: "2-digit",
                meridiem: "short",
            },
            slotMinTime: "00:00:00",
            slotMaxTime: "23:00:00",
            eventClick: this.handleEventClick.bind(this),
            eventDidMount: this.handleEventMount.bind(this),
            datesSet: () => {
                this.loadEvents();
            },
            eventContent: function (arg) {
                return {
                    html: `
                        <div class="fc-content">
                            <div class="fc-time">${arg.timeText}</div>
                            <div class="fc-title">${arg.event.title}</div>
                        </div>
                    `,
                };
            },
            eventClassNames: function (arg) {
                return ["calendar-event", arg.event.extendedProps.className];
            },
        });

        this.calendar.render();
        console.log("Calendar initialized");
    },

    setupEventListeners() {
        $("#calendarFilterForm").on("submit", (e) => {
            e.preventDefault();
            this.filters.status = $("#calendarStatusFilter").val();
            this.loadEvents();
        });

        $("#resetCalendarFilters").on("click", () => {
            $("#calendarFilterForm")[0].reset();
            this.filters.status = "";
            this.loadEvents();
        });

        $("#calendarViewType").on("change", (e) => {
            this.calendar.changeView(e.target.value);
        });
    },

    formatDate(date) {
        const d = new Date(date);
        const year = d.getFullYear();
        const month = String(d.getMonth() + 1).padStart(2, "0");
        const day = String(d.getDate()).padStart(2, "0");
        return `${year}-${month}-${day}`;
    },

    getStatusClass(status) {
        if (typeof status === "string") {
            switch (status.toLowerCase()) {
                case "draft":
                    return "draft";
                case "scheduled":
                    return "scheduled";
                case "published":
                    return "published";
                default:
                    return "draft";
            }
        }

        switch (parseInt(status)) {
            case 0:
                return "draft";
            case 1:
                return "scheduled";
            case 2:
                return "published";
        }
    },

    getStatusText(status) {
        if (typeof status === "string") {
            return (
                status.charAt(0).toUpperCase() + status.slice(1).toLowerCase()
            );
        }

        switch (parseInt(status)) {
            case 0:
                return "Draft";
            case 1:
                return "Scheduled";
            case 2:
                return "Published";
            default:
                return "Scheduled";
        }
    },

    getStatusValue(status) {
        if (typeof status === "string") {
            switch (status.toLowerCase()) {
                case "draft":
                    return 0;
                case "scheduled":
                    return 1;
                case "published":
                    return 2;
                default:
                    return 1;
            }
        }
        return parseInt(status) || 0;
    },

    async loadEvents() {
        try {
            const view = this.calendar.view;
            const start = this.formatDate(view.activeStart);
            const end = this.formatDate(view.activeEnd);

            const params = new URLSearchParams({
                start_date: start,
                end_date: end,
                ...(this.filters.status && { status: this.filters.status }),
            });

            this.calendar.removeAllEvents();

            const response = await Services.http.get(`/user/posts?${params}`);

            let posts = [];
            if (Array.isArray(response)) {
                posts = response;
            } else if (response.items) {
                posts = response.items;
            } else if (response.data?.items) {
                posts = response.data.items;
            } else if (response.data && Array.isArray(response.data)) {
                posts = response.data;
            }

            if (!Array.isArray(posts)) {
                return;
            }

            console.log("Processing posts:", posts);

            const events = posts
                .map((post) => {
                    try {
                        const scheduledTime = new Date(post.scheduled_time);
                        if (isNaN(scheduledTime.getTime())) {
                            console.error("Invalid date for post:", post);
                            return null;
                        }

                        const statusValue = this.getStatusValue(post.status);

                        return {
                            id: post.id,
                            title: post.title,
                            start: scheduledTime,
                            end: scheduledTime,
                            allDay: false,
                            className: this.getStatusClass(post.status),
                            extendedProps: {
                                content: post.content,
                                status: post.status,
                                created_at: post.created_at,
                                image_url: post.image_url,
                            },
                        };
                    } catch (e) {
                        console.error("Error processing post:", e);
                        return null;
                    }
                })
                .filter((event) => event !== null);

            console.log("Adding events:", events);

            events.forEach((event) => {
                this.calendar.addEvent(event);
            });
        } catch (error) {
            UI.notify.error("Failed to load posts");
        }
    },

    handleEventClick(info) {
        const event = info.event;

        const modal = $("#postPreviewModal");
        modal.find(".preview-title").text(event.title);
        modal.find(".preview-content").text(event.extendedProps?.content || "");

        const scheduledTime = event.start;
        const createdTime = event.extendedProps?.created_at
            ? new Date(event.extendedProps.created_at)
            : null;

        modal.find(".preview-time").text(
            scheduledTime.toLocaleString("en-US", {
                weekday: "short",
                year: "numeric",
                month: "short",
                day: "numeric",
                hour: "numeric",
                minute: "2-digit",
                hour12: true,
            })
        );

        if (createdTime) {
            modal.find(".preview-created").text(
                createdTime.toLocaleString("en-US", {
                    weekday: "short",
                    year: "numeric",
                    month: "short",
                    day: "numeric",
                    hour: "numeric",
                    minute: "2-digit",
                    hour12: true,
                })
            );
        }

        if (event.extendedProps?.status !== undefined) {
            modal.find(".preview-status-badge").html(`
                <span class="badge ${UI.getStatusBadgeClass(
                    event.extendedProps.status
                )}">
                    ${this.getStatusText(event.extendedProps.status)}
                </span>
            `);
        }

        modal.find(".edit-post-btn").attr("href", `/posts/${event.id}/edit`);
        modal.modal("show");
    },

    handleEventMount(info) {
        const event = info.event;
        const tooltip = new bootstrap.Tooltip(info.el, {
            title: `${event.title}\n${event.start.toLocaleString("en-US", {
                weekday: "short",
                month: "short",
                day: "numeric",
                hour: "numeric",
                minute: "2-digit",
                hour12: true,
            })}`,
            placement: "top",
            trigger: "hover",
            container: "body",
        });
    },
};
