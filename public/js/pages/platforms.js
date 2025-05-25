window.Platforms = {
    currentPlatform: null,
    platformsData: [],
    settingsModal: null,

    init() {
        this.settingsModal = new bootstrap.Modal(
            document.getElementById("platformSettingsModal")
        );
        this.loadPlatforms();
        this.setupEventListeners();
    },

    setupEventListeners() {
        document
            .getElementById("savePlatformSettings")
            ?.addEventListener("click", () => {
                this.saveSettings();
            });
    },

    getAuthHeaders() {
        const token = document.cookie
            .split("; ")
            .find((row) => row.startsWith("auth_token="))
            ?.split("=")[1];

        return {
            Authorization: `Bearer ${token}`,
            "X-CSRF-TOKEN": document
                .querySelector('meta[name="csrf-token"]')
                ?.getAttribute("content"),
            Accept: "application/json",
            "Content-Type": "application/json",
        };
    },

    async loadPlatforms() {
        try {
            const response = await fetch("/api/v1/user/platforms", {
                method: "GET",
                headers: this.getAuthHeaders(),
                credentials: "include",
            });

            if (!response.ok) {
                const error = await response.json();
                throw new Error(error.message || "Failed to load platforms");
            }

            const data = await response.json();
            this.platformsData = Array.isArray(data) ? data : [];
            this.renderPlatforms();
        } catch (error) {
            console.error("Failed to load platforms:", error);
            UI.notify.error(error.message || "Failed to load platforms");
            this.showLoadError();
        }
    },

    renderPlatforms() {
        const container = document.getElementById("platformsList");

        if (!this.platformsData?.length) {
            container.innerHTML =
                '<div class="col-12"><div class="alert alert-info">No platforms available</div></div>';
            return;
        }

        container.innerHTML = this.platformsData
            .map(
                (platform) => `
            <div class="col-md-4">
                <div class="platform-card platform-${platform.type.toLowerCase()}">
                    <div class="platform-icon">
                        <i class="bi bi-${this.getPlatformIcon(
                            platform.type
                        )}"></i>
                    </div>
                    <div class="platform-name">${platform.name}</div>
                    <div class="platform-status">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch"
                                id="platform-${platform.id}-toggle"
                                ${platform.is_active_for_user ? "checked" : ""}
                                onchange="Platforms.togglePlatform(${
                                    platform.id
                                }, this)">
                            <label class="form-check-label" for="platform-${
                                platform.id
                            }-toggle">
                                ${
                                    platform.is_active_for_user
                                        ? "Active"
                                        : "Inactive"
                                }
                            </label>
                        </div>
                    </div>
                    <div class="platform-actions">
                        <button type="button" class="btn btn-sm btn-primary"
                            onclick="Platforms.openSettings(${platform.id})"
                            ${!platform.is_active_for_user ? "disabled" : ""}>
                            <i class="bi bi-gear me-1"></i>
                            Settings
                        </button>
                    </div>
                </div>
            </div>
        `
            )
            .join("");
    },

    getPlatformIcon(type) {
        const icons = {
            twitter: "twitter",
            instagram: "instagram",
            linkedin: "linkedin",
        };
        return icons[type.toLowerCase()] || "share";
    },

    async togglePlatform(platformId, toggleElement) {
        try {
            const response = await fetch(
                `/api/v1/platforms/${platformId}/toggle`,
                {
                    method: "POST",
                    headers: this.getAuthHeaders(),
                    credentials: "include",
                }
            );

            if (!response.ok) {
                const error = await response.json();
                throw new Error(error.message || "Failed to toggle platform");
            }

            const data = await response.json();

            const platform = this.platformsData.find(
                (p) => p.id === platformId
            );
            if (platform) {
                platform.is_active_for_user = data.is_active_for_user;
            }

            const label = toggleElement.nextElementSibling;
            label.textContent = data.is_active_for_user ? "Active" : "Inactive";

            const settingsBtn = toggleElement
                .closest(".platform-card")
                .querySelector(".btn-primary");
            settingsBtn.disabled = !data.is_active_for_user;

            UI.notify.success(
                `${data.name} ${
                    data.is_active_for_user ? "activated" : "deactivated"
                } successfully`
            );
        } catch (error) {
            console.error("Failed to toggle platform:", error);
            toggleElement.checked = !toggleElement.checked;
            UI.notify.error(error.message || "Failed to toggle platform");
        }
    },

    async openSettings(platformId) {
        try {
            const platform = this.platformsData.find(
                (p) => p.id === platformId
            );
            if (!platform) throw new Error("Platform not found");

            if (!platform.is_active_for_user) {
                UI.notify.error("Please activate the platform first");
                return;
            }

            const response = await fetch(
                `/api/v1/platforms/${platformId}/settings`,
                {
                    method: "GET",
                    headers: this.getAuthHeaders(),
                    credentials: "include",
                }
            );

            if (!response.ok) {
                const error = await response.json();
                throw new Error(error.message || "Failed to load settings");
            }

            const data = await response.json();
            this.currentPlatform = platformId;

            const settingsFields = platform.settingskeys || [];

            const existingSettings = data.settings
                ? JSON.parse(data.settings)
                : {};

            document.getElementById("settingsFields").innerHTML = settingsFields
                .map(
                    (field) => `
                    <div class="settings-field mb-3">
                        <label class="form-label">${this.formatSettingLabel(
                            field.key
                        )}</label>
                        <input type="${field.type || "text"}"
                               class="form-control"
                               name="${field.key}"
                               value="${existingSettings[field.key] || ""}"
                               ${field.required ? "required" : ""}>
                    </div>
                `
                )
                .join("");

            document.querySelector(
                "#platformSettingsModal .modal-title"
            ).textContent = `${platform.name} Settings`;

            this.settingsModal.show();
        } catch (error) {
            console.error("Failed to load platform settings:", error);
            UI.notify.error(
                error.message || "Failed to load platform settings"
            );
        }
    },

    async saveSettings() {
        if (!this.currentPlatform) return;

        const form = document.getElementById("platformSettingsForm");
        const saveBtn = document.getElementById("savePlatformSettings");
        const formData = new FormData(form);
        const settings = {};

        formData.forEach((value, key) => {
            if (value.trim()) {
                settings[key] = value.trim();
            }
        });

        try {
            UI.loading.show(saveBtn);

            const response = await fetch(
                `/api/v1/platforms/${this.currentPlatform}/settings`,
                {
                    method: "PUT",
                    headers: this.getAuthHeaders(),
                    credentials: "include",
                    body: JSON.stringify({ settings }),
                }
            );

            if (!response.ok) {
                const error = await response.json();
                throw new Error(error.message || "Failed to save settings");
            }

            UI.notify.success("Settings saved successfully");
            this.settingsModal.hide();
        } catch (error) {
            console.error("Failed to save settings:", error);
            UI.notify.error(error.message || "Failed to save settings");
        } finally {
            UI.loading.hide(saveBtn);
        }
    },

    formatSettingLabel(key) {
        return key
            .split("_")
            .map((word) => word.charAt(0).toUpperCase() + word.slice(1))
            .join(" ");
    },

    showLoadError() {
        const container = document.getElementById("platformsList");
        container.innerHTML = `
            <div class="col-12">
                <div class="alert alert-danger">
                    <p class="mb-2">Failed to load platforms</p>
                    <button class="btn btn-danger btn-sm" onclick="Platforms.loadPlatforms()">
                        <i class="bi bi-arrow-clockwise me-1"></i>
                        Retry
                    </button>
                </div>
            </div>
        `;
    },
};
