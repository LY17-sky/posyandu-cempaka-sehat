// Dashboard functionality and UI initialization
document.addEventListener('DOMContentLoaded', function () {
    // ===== Sidebar Menu Accordion (redesigned) =====
    const menuToggles = document.querySelectorAll('.menu-toggle');
    const currentModule = new URLSearchParams(window.location.search).get('module') || 'dashboard';

    menuToggles.forEach(toggle => {
        const mod = toggle.dataset.module;
        const dropdown = document.querySelector(`.menu-content[data-module="${mod}"]`);

        // Open active module on page load
        if (mod === currentModule && dropdown) {
            dropdown.classList.add('open');
            toggle.classList.add('dropdown-open', 'active');
        }

        toggle.addEventListener('click', function () {
            const isOpen = dropdown && dropdown.classList.contains('open');

            // Close all dropdowns
            document.querySelectorAll('.menu-content').forEach(d => d.classList.remove('open'));
            document.querySelectorAll('.menu-toggle').forEach(t => t.classList.remove('dropdown-open'));

            // Re-open if it was closed
            if (dropdown && !isOpen) {
                dropdown.classList.add('open');
                toggle.classList.add('dropdown-open');
            }
        });
    });

    // ===== Dark Mode Toggle =====
    const darkModeToggle = document.getElementById('darkModeToggle');
    const html = document.documentElement;
    
    // Check for saved theme preference - default to light mode
    const savedTheme = localStorage.getItem('theme');
    if (savedTheme === 'dark') {
        html.classList.add('dark');
    } else {
        html.classList.remove('dark');
        localStorage.setItem('theme', 'light');
    }
    
    if (darkModeToggle) {
        darkModeToggle.addEventListener('click', function() {
            html.classList.toggle('dark');
            const isDark = html.classList.contains('dark');
            localStorage.setItem('theme', isDark ? 'dark' : 'light');
            updateDarkModeIcon();
        });
    }

    function updateDarkModeIcon() {
        const isDark = html.classList.contains('dark');
        const icon = darkModeToggle?.querySelector('svg');
        if (icon) {
            if (isDark) {
                icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path>';
            } else {
                icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path>';
            }
        }
    }

    // ===== Sidebar Toggle for Mobile =====
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebarCloseBtn = document.getElementById('sidebarCloseBtn');
    const sidebar = document.getElementById('sidebar');
    
    const toggleSidebar = () => {
        if (sidebar) {
            sidebar.classList.toggle('-translate-x-full');
        }
    };

    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', toggleSidebar);
    }

    if (sidebarCloseBtn) {
        sidebarCloseBtn.addEventListener('click', toggleSidebar);
    }

    // Close sidebar when clicking outside on mobile
    document.addEventListener('click', function(event) {
        if (sidebar && !sidebar.contains(event.target) && !sidebarToggle?.contains(event.target)) {
            if (window.innerWidth < 1024 && !sidebar.classList.contains('-translate-x-full')) {
                sidebar.classList.add('-translate-x-full');
            }
        }
    });

    // Close sidebar when clicking on a menu item on mobile
    const menuLinks = sidebar?.querySelectorAll('nav a');
    if (menuLinks) {
        menuLinks.forEach(link => {
            link.addEventListener('click', () => {
                if (window.innerWidth < 1024) {
                    sidebar?.classList.add('-translate-x-full');
                }
            });
        });
    }

    // ===== User Menu Dropdown =====
    const userMenuBtn = document.getElementById('userMenuBtn');
    const userDropdown = document.getElementById('userDropdown');

    if (userMenuBtn && userDropdown) {
        userMenuBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            userDropdown.classList.toggle('hidden');
        });

        document.addEventListener('click', (e) => {
            if (!userMenuBtn.contains(e.target) && !userDropdown.contains(e.target)) {
                userDropdown.classList.add('hidden');
            }
        });
    }

    // ===== Toast Notifications System =====
    window.showToast = function(message, type = 'success', duration = 3000) {
        const toast = document.createElement('div');
        const toastId = 'toast-' + Date.now();
        toast.id = toastId;
        
         const bgColor = {
             success: 'bg-blue-400',
             error: 'bg-red-500',
             warning: 'bg-pink-400',
             info: 'bg-blue-500'
         }[type] || 'bg-blue-400';

        const icon = {
            success: '<svg class="w-5 h-5 inline mr-2" fill="currentColor" viewBox="0 0 24 24"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"></path></svg>',
            error: '<svg class="w-5 h-5 inline mr-2" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"></path></svg>',
            warning: '<svg class="w-5 h-5 inline mr-2" fill="currentColor" viewBox="0 0 24 24"><path d="M1 21h22L12 2 1 21zm12-3h-2v-2h2v2zm0-4h-2v-4h2v4z"></path></svg>',
            info: '<svg class="w-5 h-5 inline mr-2" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"></path></svg>'
        }[type] || '';

        toast.className = `fixed bottom-4 right-4 ${bgColor} text-white px-6 py-3 rounded-lg shadow-lg animate-fade-in z-999 flex items-center gap-2`;
        toast.innerHTML = icon + '<span>' + message + '</span>';
        
        document.body.appendChild(toast);

        setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transform = 'translateY(20px)';
            toast.style.transition = 'all 0.3s ease';
            setTimeout(() => {
                toast.remove();
            }, 300);
        }, duration);
    };

    // ===== Form Enhancements =====
    // Add focus effects to all inputs
    const inputs = document.querySelectorAll('input, select, textarea');
    inputs.forEach(input => {
        input.addEventListener('focus', function() {
            this.parentElement?.classList.add('focused');
        });
        input.addEventListener('blur', function() {
            this.parentElement?.classList.remove('focused');
        });
    });

    // ===== Mobile Menu Optimization =====
    // Close mobile sidebar when window resizes
    window.addEventListener('resize', () => {
        if (window.innerWidth >= 1024 && sidebar) {
            sidebar.classList.remove('-translate-x-full');
        }
    });

    // ===== Smooth Scroll for Anchor Links =====
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            const href = this.getAttribute('href');
            if (href !== '#') {
                e.preventDefault();
                const target = document.querySelector(href);
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            }
        });
    });

    // ===== Loading States =====
    window.showLoading = function() {
        const loader = document.createElement('div');
        loader.id = 'loading-overlay';
        loader.className = 'fixed inset-0 bg-black/50 flex items-center justify-center z-50';
        loader.innerHTML = '<div class="spinner-lg"></div>';
        document.body.appendChild(loader);
    };

    window.hideLoading = function() {
        document.getElementById('loading-overlay')?.remove();
    };

    // ===== Confirmation Dialogs =====
    window.showConfirm = async function(title, message) {
        if (typeof Swal !== 'undefined') {
            const result = await Swal.fire({
                title: title,
                text: message,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Ya',
                cancelButtonText: 'Batal'
            });
            return result.isConfirmed;
        }
        return confirm(message);
    };

    // ===== Add Animation Classes to Elements =====
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('animate-fade-in');
                observer.unobserve(entry.target);
            }
        });
    }, observerOptions);

    document.querySelectorAll('.card, .alert, [data-animate]').forEach(el => {
        observer.observe(el);
    });

    // ===== Table Row Hover Effects =====
    document.querySelectorAll('table tbody tr').forEach(row => {
        row.addEventListener('mouseenter', function() {
            this.style.backgroundColor = 'rgba(16, 185, 129, 0.05)';
        });
        row.addEventListener('mouseleave', function() {
            this.style.backgroundColor = '';
        });
    });

    // ===== Keyboard Shortcuts =====
    window.addEventListener('keydown', (e) => {
        // Alt + D: Toggle Dark Mode
        if (e.altKey && e.key === 'd') {
            e.preventDefault();
            darkModeToggle?.click();
        }
        // Alt + M: Toggle Mobile Menu
        if (e.altKey && e.key === 'm') {
            e.preventDefault();
            sidebarToggle?.click();
        }
    });

    // ===== Form Validation Helpers =====
    window.validateForm = function(formId) {
        const form = document.getElementById(formId);
        if (!form) return false;
        
        const requiredFields = form.querySelectorAll('[required]');
        let isValid = true;
        
        requiredFields.forEach(field => {
            if (!field.value.trim()) {
                field.classList.add('border-red-500');
                isValid = false;
            } else {
                field.classList.remove('border-red-500');
            }
        });
        
        return isValid;
    };

    // ===== Number Input Formatting =====
    const numberInputs = document.querySelectorAll('input[type="number"]');
    numberInputs.forEach(input => {
        input.addEventListener('input', function() {
            if (this.value < 0) this.value = 0;
        });
    });

    // ===== Auto-hide Alerts =====
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            if (alert) {
                alert.style.opacity = '0';
                alert.style.transition = 'opacity 0.3s ease';
                setTimeout(() => {
                    if (alert.parentElement) {
                        alert.remove();
                    }
                }, 300);
            }
        }, 5000);
    });

    // ===== Initialize Tooltips =====
    const tooltipElements = document.querySelectorAll('[data-tooltip]');
    tooltipElements.forEach(el => {
        el.setAttribute('title', el.dataset.tooltip);
    });

    // ===== Prevent Default Form Submission =====
    const forms = document.querySelectorAll('form[data-ajax]');
    forms.forEach(form => {
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const submitBtn = form.querySelector('button[type="submit"]');
            const originalText = submitBtn?.textContent;
            
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<div class="spinner inline mr-2"></div>Processing...';
            }
            
            try {
                const formData = new FormData(form);
                const response = await fetch(form.action, {
                    method: form.method || 'POST',
                    body: formData
                });
                
                if (response.ok) {
                    const data = await response.json();
                    if (data.success) {
                        window.showToast(data.message || 'Success!', 'success');
                        if (data.redirect) {
                            setTimeout(() => {
                                window.location.href = data.redirect;
                            }, 1500);
                        }
                    } else {
                        window.showToast(data.message || 'Error occurred', 'error');
                    }
                } else {
                    window.showToast('Request failed', 'error');
                }
            } catch (error) {
                console.error('Form submission error:', error);
                window.showToast('An error occurred', 'error');
            } finally {
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                }
            }
        });
    });

    // ===== Add Page Load Animation =====
    document.documentElement.style.opacity = '0';
    window.addEventListener('load', () => {
        document.documentElement.style.transition = 'opacity 0.3s ease';
        document.documentElement.style.opacity = '1';
    });

    // ===== Error Boundary for Global Errors =====
    window.addEventListener('error', (event) => {
        console.error('Global error:', event.error);
        // Don't show toast for non-critical errors to avoid spam
    });

    window.addEventListener('unhandledrejection', (event) => {
        console.error('Unhandled promise rejection:', event.reason);
    });

    console.log('Dashboard initialized successfully!');
});
