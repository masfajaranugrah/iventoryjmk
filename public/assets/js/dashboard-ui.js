(function () {
    "use strict";

    // Sidebar accordion
    var accordions = document.querySelectorAll('.accordion-button.toggle, .sidemenu-link.toggle');
    accordions.forEach(function (accordion) {
        accordion.addEventListener('click', function () {
            var siblings = Array.from(
                this.closest('.accordion-collapse')?.querySelectorAll('.accordion-button.toggle, .sidemenu-link.toggle') || accordions
            );

            siblings.forEach(function (acc) {
                if (acc !== accordion) {
                    acc.classList.remove('open');
                    acc.setAttribute('aria-expanded', 'false');
                    if (acc.nextElementSibling) {
                        acc.nextElementSibling.style.display = 'none';
                    }
                }
            });

            this.classList.toggle('open');
            var panel = this.nextElementSibling;
            if (!panel) {
                return;
            }

            if (panel.style.display === 'block') {
                panel.style.display = 'none';
                this.setAttribute('aria-expanded', 'false');
            } else {
                panel.style.display = 'block';
                this.setAttribute('aria-expanded', 'true');
            }
        });
    });

    // Sidebar open/close
    var hideToggle = document.getElementById('hide-sidebar-toggle');
    var hideToggle2 = document.getElementById('hide-sidebar-toggle2');
    var sidebar = document.getElementById('sidebar-area');
    var mainContent = document.getElementById('main-content');
    var headerArea = document.getElementById('header-area');

    function toggleSidebar(button) {
        if (!sidebar || !mainContent || !headerArea) {
            return;
        }
        button.classList.toggle('active');
        sidebar.classList.toggle('active');
        mainContent.classList.toggle('active');
        headerArea.classList.toggle('active');
    }

    if (hideToggle) {
        hideToggle.addEventListener('click', function () {
            toggleSidebar(hideToggle);
        });
    }
    if (hideToggle2) {
        hideToggle2.addEventListener('click', function () {
            toggleSidebar(hideToggle2);
        });
    }

    // Profile dropdown
    var dropdownButtons = document.querySelectorAll('#dropdownToggleBtn');
    if (dropdownButtons.length) {
        dropdownButtons.forEach(function (button) {
            button.addEventListener('click', function (event) {
                var isActive = this.classList.contains('active');
                dropdownButtons.forEach(function (btn) {
                    btn.classList.remove('active');
                });
                if (!isActive) {
                    this.classList.add('active');
                }
                event.stopPropagation();
            });
        });

        document.addEventListener('click', function () {
            dropdownButtons.forEach(function (btn) {
                btn.classList.remove('active');
            });
        });
    }

    // Dark / light theme
    var themeToggle = document.getElementById('light-dark-toggle');
    var html = document.documentElement;
    var savedTheme = localStorage.getItem('trezo_theme');
    if (savedTheme) {
        html.classList.add(savedTheme === 'dark' ? 'dark' : 'light');
    }
    if (themeToggle) {
        themeToggle.addEventListener('click', function () {
            if (html.classList.contains('dark')) {
                html.classList.remove('dark');
                html.classList.add('light');
                localStorage.setItem('trezo_theme', 'light');
            } else {
                html.classList.remove('light');
                html.classList.add('dark');
                localStorage.setItem('trezo_theme', 'dark');
            }
        });
    }

    // Fullscreen
    var fullscreenBtn = document.getElementById('fullscreenBtn');
    var fullscreenIcon = document.getElementById('fullscreenIcon');
    if (fullscreenBtn && fullscreenIcon) {
        fullscreenBtn.addEventListener('click', function () {
            if (!document.fullscreenElement) {
                document.documentElement.requestFullscreen?.();
                fullscreenIcon.textContent = 'fullscreen_exit';
            } else {
                document.exitFullscreen?.();
                fullscreenIcon.textContent = 'fullscreen';
            }
        });
    }

    // RTL toggle
    var rtlToggle = document.getElementById('rtl-mode-toggle');
    if (rtlToggle) {
        var savedDirection = localStorage.getItem('textDirection') || 'ltr';
        html.setAttribute('dir', savedDirection);
        if (savedDirection === 'rtl') {
            rtlToggle.classList.add('open');
        }
        rtlToggle.addEventListener('click', function () {
            var next = html.getAttribute('dir') === 'rtl' ? 'ltr' : 'rtl';
            html.setAttribute('dir', next);
            rtlToggle.classList.toggle('open', next === 'rtl');
            localStorage.setItem('textDirection', next);
        });
    }

    // Password show/hide
    ['', '2', '3'].forEach(function (suffix) {
        var wrapper = document.getElementById('passwordHideShow' + suffix);
        var button = document.getElementById('toggleButton' + suffix);
        var input = document.getElementById('password' + suffix);
        if (!wrapper || !button || !input) {
            return;
        }
        button.addEventListener('click', function () {
            var icon = this.querySelector('i');
            if (input.type === 'password') {
                input.type = 'text';
                icon?.classList.replace('ri-eye-off-line', 'ri-eye-line');
            } else {
                input.type = 'password';
                icon?.classList.replace('ri-eye-line', 'ri-eye-off-line');
            }
        });
    });

    // Front navbar burger
    var burger = document.getElementById('navbar-burger-menu');
    var navbarCollapse = document.getElementById('navbar-collapse');
    if (burger && navbarCollapse) {
        burger.addEventListener('click', function () {
            burger.classList.toggle('active');
            navbarCollapse.classList.toggle('active');
        });
    }
})();
