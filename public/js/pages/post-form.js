window.PostForm = {
    selectedPlatforms: [],
    imageFile: null,

    init() {
        $("#postForm").off("submit");

        this.setupDatePicker();
        this.setupCharacterCounter();
        this.loadPlatforms();
        this.setupImagePreview();
        this.setupFormSubmission();
    },

    setupDatePicker() {
        flatpickr("#scheduledTime", {
            enableTime: true,
            dateFormat: "Y-m-d H:i",
            minDate: "today",
            time_24hr: true,
            defaultHour: new Date().getHours(),
            defaultMinute: new Date().getMinutes(),
        });
    },

    setupCharacterCounter() {
        const content = document.getElementById("content");
        const counter = document.getElementById("characterCount");

        content.addEventListener("input", function () {
            counter.textContent = this.value.length;
        });
    },

    setupImagePreview() {
        const imageInput = $("#image");
        const imagePreview = $("#imagePreview");
        const currentImage = $(".current-image");
        const removeImageBtn = $("#removeImage");

        imagePreview.addClass("d-none").find("img").attr("src", "");
        currentImage.addClass("d-none").find("img").attr("src", "");

        imageInput.on("change", function () {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    imagePreview.addClass("d-none");
                    currentImage.addClass("d-none");

                    imagePreview
                        .removeClass("d-none")
                        .find("img")
                        .attr("src", e.target.result);
                };
                reader.readAsDataURL(file);
            } else {
                imagePreview.addClass("d-none").find("img").attr("src", "");
                currentImage.addClass("d-none").find("img").attr("src", "");
            }
        });

        removeImageBtn.on("click", function () {
            imageInput.val("");
            imagePreview.addClass("d-none").find("img").attr("src", "");
            currentImage.addClass("d-none").find("img").attr("src", "");
        });
    },

    async loadPlatforms() {
        try {
            UI.loading.show("#platformsGrid");

            const authToken = document.cookie
                .split("; ")
                .find((row) => row.startsWith("auth_token="))
                ?.split("=")[1];

            const response = await fetch("/api/v1/platforms", {
                method: "GET",
                headers: {
                    "X-CSRF-TOKEN": document
                        .querySelector('meta[name="csrf-token"]')
                        ?.getAttribute("content"),
                    Authorization: `Bearer ${authToken}`,
                },
                credentials: "include",
            });

            if (!response.ok) {
                throw new Error("Failed to load platforms");
            }

            const data = await response.json();

            const platforms = Array.isArray(data) ? data : data.data || [];

            console.log("Loaded platforms:", platforms);
            this.renderPlatforms(platforms);
        } catch (error) {
            console.error("Failed to load platforms:", error);
            UI.notify.error("Failed to load platforms");

            const grid = document.getElementById("platformsGrid");
            grid.innerHTML = `
                <div class="alert alert-danger">
                    <p class="mb-2">Failed to load platforms</p>
                    <button class="btn btn-danger btn-sm" onclick="PostForm.loadPlatforms()">
                        <i class="bi bi-arrow-clockwise"></i> Retry
                    </button>
                </div>
            `;
        } finally {
            UI.loading.hide("#platformsGrid");
        }
    },

    renderPlatforms(platforms) {
        const grid = document.getElementById("platformsGrid");

        if (!platforms?.length) {
            grid.innerHTML =
                '<div class="alert alert-info">No platforms available</div>';
            return;
        }

        grid.innerHTML = platforms
            .map(
                (platform) => `
            <div class="platform-card ${
                this.selectedPlatforms?.includes(platform.id) ? "selected" : ""
            }" data-platform-id="${platform.id}">
                <div class="platform-content">
                    <div class="platform-header">
                        <i class="bi bi-${this.getPlatformIcon(
                            platform.name
                        )} platform-icon"></i>
                        <span class="platform-name">${platform.name}</span>
                    </div>
                    <div class="platform-requirements small text-muted">
                        ${this.renderPlatformRequirements(platform)}
                    </div>
                </div>
            </div>
        `
            )
            .join("");

        grid.querySelectorAll(".platform-card").forEach((card) => {
            card.addEventListener("click", () => this.togglePlatform(card));
        });

        if (!document.getElementById("platform-styles")) {
            const styles = `
                <style id="platform-styles">
                    .platform-selection {
                        display: grid;
                        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
                        gap: 1rem;
                        padding: 1rem 0;
                    }
                    .platform-card {
                        border: 1px solid #dee2e6;
                        border-radius: 8px;
                        padding: 1rem;
                        cursor: pointer;
                        transition: all 0.2s ease;
                    }
                    .platform-card:hover {
                        border-color: #6c757d;
                        background-color: #f8f9fa;
                    }
                    .platform-card.selected {
                        border-color: #0d6efd;
                        background-color: #f0f7ff;
                    }
                    .platform-header {
                        display: flex;
                        align-items: center;
                        margin-bottom: 0.5rem;
                    }
                    .platform-icon {
                        font-size: 1.25rem;
                        margin-right: 0.5rem;
                        color: #6c757d;
                    }
                    .platform-card.selected .platform-icon {
                        color: #0d6efd;
                    }
                    .platform-name {
                        font-weight: 500;
                    }
                    .platform-requirements {
                        font-size: 0.875rem;
                    }
                </style>
            `;
            document.head.insertAdjacentHTML("beforeend", styles);
        }
    },

    renderPlatformRequirements(platform) {
        const requirements = [];

        if (platform.character_limit) {
            requirements.push(
                `<i class="bi bi-text-paragraph"></i> ${platform.character_limit} characters`
            );
        }
        if (platform.requirements?.image_required) {
            requirements.push(`<i class="bi bi-image"></i> Image required`);
        }
        if (platform.requirements?.support_link === false) {
            requirements.push(
                `<i class="bi bi-link-45deg"></i> Links not supported`
            );
        }

        return requirements.length ? requirements.join("<br>") : "";
    },

    getPlatformIcon(platformName) {
        const icons = {
            Twitter: "twitter",
            Facebook: "facebook",
            Instagram: "instagram",
            LinkedIn: "linkedin",
        };
        return icons[platformName] || "share";
    },

    togglePlatform(card) {
        const platformId = parseInt(card.dataset.platformId);
        this.selectedPlatforms = this.selectedPlatforms || [];

        if (this.selectedPlatforms.includes(platformId)) {
            this.selectedPlatforms = this.selectedPlatforms.filter(
                (id) => id !== platformId
            );
            card.classList.remove("selected");
        } else {
            this.selectedPlatforms.push(platformId);
            card.classList.add("selected");
        }

        console.log("Selected platforms:", this.selectedPlatforms);
    },

    setupFormSubmission() {
        const self = this;

        $(document).off("submit", "#postForm");

        $(document).on("submit", "#postForm", function (e) {
            e.preventDefault();
            e.stopPropagation();

            const form = this;
            const submitButton = e.originalEvent.submitter;
            const action = submitButton.dataset.action;

            if (!self.selectedPlatforms?.length) {
                UI.notify.error("Please select at least one platform");
                return;
            }

            const handleSubmission = async () => {
                try {
                    UI.loading.show(submitButton);

                    const formData = new FormData(form);

                    self.selectedPlatforms.forEach((platformId) => {
                        formData.append(`platforms[]`, platformId);
                    });

                    formData.append("status", action === "draft" ? "0" : "1");

                    const imageFile = document.getElementById("image").files[0];
                    if (imageFile) {
                        formData.set("image_url", imageFile);
                    }

                    const postId = form.dataset.postId;
                    const endpoint = `/api/v1/posts${
                        postId ? `/${postId}` : ""
                    }`;
                    const method = postId ? "PUT" : "POST";

                    console.log("Making API call to:", endpoint);
                    console.log("Form data:", {
                        title: formData.get("title"),
                        content: formData.get("content"),
                        scheduled_time: formData.get("scheduled_time"),
                        status: formData.get("status"),
                        platforms: self.selectedPlatforms,
                    });

                    if (method === "PUT") {
                        formData.append("_method", "PUT");
                    }

                    const response = await fetch(endpoint, {
                        method: "POST",
                        headers: {
                            "X-CSRF-TOKEN": document
                                .querySelector('meta[name="csrf-token"]')
                                ?.getAttribute("content"),
                            Authorization: `Bearer ${
                                document.cookie
                                    .split("; ")
                                    .find((row) =>
                                        row.startsWith("auth_token=")
                                    )
                                    ?.split("=")[1]
                            }`,
                        },
                        credentials: "include",
                        body: formData,
                    });

                    console.log("Response status:", response.status);

                    if (!response.ok) {
                        const error = await response.json();
                        console.error("API error:", error);
                        throw new Error(error.message || "Failed to save post");
                    }

                    const data = await response.json();
                    console.log("API success:", data);

                    const successMessage =
                        action === "draft"
                            ? "Post saved as draft successfully"
                            : postId
                            ? "Post updated successfully"
                            : "Post scheduled successfully";

                    UI.notify.success(successMessage);
                    window.location.href = "/posts";
                } catch (error) {
                    console.error("Form submission error:", error);
                    UI.notify.error(error.message || "Failed to save post");
                } finally {
                    UI.loading.hide(submitButton);
                }
            };

            handleSubmission();

            return false;
        });
    },
};

$(document).ready(() => {
    PostForm.init();
});
