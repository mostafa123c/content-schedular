const Handlers = {
    auth: {
        async handleLogin(event) {
            event.preventDefault();
            const form = $(event.target);
            const submitBtn = form.find('button[type="submit"]');

            UI.loading.show(submitBtn);

            try {
                const credentials = {
                    email: form.find("#email").val(),
                    password: form.find("#password").val(),
                };

                if (!credentials.email || !credentials.password) {
                    UI.notify.error("Please fill in all fields");
                    return;
                }

                await Services.auth.login(credentials);
                UI.notify.success("Login successful!");
                window.location.replace(CONFIG.routes.dashboard);
            } catch (error) {
                UI.notify.error(error.message || "Invalid credentials");
                form.find("#password").val("");
            } finally {
                UI.loading.hide(submitBtn);
            }
        },

        async handleRegister(event) {
            event.preventDefault();
            const form = $(event.target);
            const submitBtn = form.find('button[type="submit"]');

            UI.loading.show(submitBtn);

            try {
                const userData = {
                    name: form.find("#name").val(),
                    email: form.find("#email").val(),
                    password: form.find("#password").val(),
                    password_confirmation: form
                        .find("#password_confirmation")
                        .val(),
                };

                if (
                    !userData.name ||
                    !userData.email ||
                    !userData.password ||
                    !userData.password_confirmation
                ) {
                    UI.notify.error("Please fill in all fields");
                    return;
                }

                if (userData.password !== userData.password_confirmation) {
                    UI.notify.error("Passwords do not match");
                    return;
                }

                await Services.auth.register(userData);
                UI.notify.success("Registration successful!");
                window.location.replace(CONFIG.routes.dashboard);
            } catch (error) {
                UI.notify.error(error.message || "Registration failed");
            } finally {
                UI.loading.hide(submitBtn);
            }
        },

        async handleLogout(event) {
            event.preventDefault();
            if (confirm("Are you sure you want to logout?")) {
                await Services.auth.logout();
            }
        },
    },
};

$(document).ready(function () {
    Services.http.setup();

    toastr.options = {
        closeButton: true,
        progressBar: true,
        positionClass: "toast-top-right",
        timeOut: "5000",
    };

    const currentPath = window.location.pathname;

    if (
        CONFIG.publicPaths.includes(currentPath) &&
        Services.auth.isAuthenticated()
    ) {
        window.location.replace(CONFIG.routes.dashboard);
    } else if (
        !CONFIG.publicPaths.includes(currentPath) &&
        !Services.auth.isAuthenticated()
    ) {
        Services.auth.requireAuth();
    }

    $(document).on("click", ".logout-btn", Handlers.auth.handleLogout);
    $(document).on("submit", "#loginForm", Handlers.auth.handleLogin);
    $(document).on("submit", "#registerForm", Handlers.auth.handleRegister);

    $(document).ajaxError(function (event, xhr) {
        if (xhr.status === 401) {
            if (!CONFIG.publicPaths.includes(window.location.pathname)) {
                Services.auth.clearAuth();
                window.location.replace(CONFIG.routes.login);
            }
        }
    });
});

window.handleLogin = Handlers.auth.handleLogin;
window.handleRegister = Handlers.auth.handleRegister;
