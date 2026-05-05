(function () {
    'use strict';

    function closestFormMessage(form) {
        var message = form.querySelector('[data-blog-subscribe-message]');
        if (!message) {
            message = document.createElement('div');
            message.className = 'fs-12 mt-2';
            message.setAttribute('data-blog-subscribe-message', '');
            form.appendChild(message);
        }
        return message;
    }

    document.querySelectorAll('.mb-blog-subscribe-form').forEach(function (form) {
        form.addEventListener('submit', function (event) {
            event.preventDefault();

            var button = form.querySelector('button[type="submit"]');
            var message = closestFormMessage(form);
            var original = button ? button.innerHTML : '';

            if (button) {
                button.disabled = true;
                button.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
            }
            message.className = 'fs-12 mt-2 text-muted';
            message.textContent = '';

            fetch(form.action, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: new FormData(form)
            }).then(function (response) {
                return response.json().then(function (data) {
                    return { ok: response.ok, data: data };
                });
            }).then(function (result) {
                if (result.ok && result.data.success) {
                    form.innerHTML = '<div class="alert alert-success mb-0">' + result.data.message + '</div>';
                    if (window.gtag) {
                        window.gtag('event', 'blog_subscribe');
                    }
                    return;
                }

                message.className = 'fs-12 mt-2 text-danger';
                message.textContent = result.data.message || 'Something went wrong. Please try again.';
            }).catch(function () {
                message.className = 'fs-12 mt-2 text-danger';
                message.textContent = 'Something went wrong. Please try again.';
            }).finally(function () {
                if (button) {
                    button.disabled = false;
                    button.innerHTML = original;
                }
            });
        });
    });

    var tocLinks = Array.prototype.slice.call(document.querySelectorAll('.mb-blog-toc a[href^="#"]'));
    if (tocLinks.length) {
        tocLinks.forEach(function (link) {
            link.addEventListener('click', function (event) {
                var target = document.querySelector(link.getAttribute('href'));
                if (!target) {
                    return;
                }
                event.preventDefault();
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                history.replaceState(null, '', link.getAttribute('href'));
            });
        });

        var observer = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (!entry.isIntersecting) {
                    return;
                }
                tocLinks.forEach(function (link) {
                    link.classList.toggle('is-active', link.getAttribute('href') === '#' + entry.target.id);
                });
            });
        }, { rootMargin: '-20% 0px -70% 0px' });

        tocLinks.forEach(function (link) {
            var target = document.querySelector(link.getAttribute('href'));
            if (target) {
                observer.observe(target);
            }
        });
    }

    document.querySelectorAll('[data-blog-copy-link]').forEach(function (button) {
        button.addEventListener('click', function () {
            var wrapper = button.closest('[data-share-url]');
            var url = wrapper ? wrapper.getAttribute('data-share-url') : window.location.href;
            var success = wrapper ? wrapper.querySelector('[data-blog-copy-success]') : null;

            if (!navigator.clipboard) {
                return;
            }

            navigator.clipboard.writeText(url).then(function () {
                if (success) {
                    success.classList.remove('d-none');
                    window.setTimeout(function () {
                        success.classList.add('d-none');
                    }, 1800);
                }
            });
        });
    });

    function escapeHtml(value) {
        return String(value || '').replace(/[&<>"']/g, function (character) {
            return {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            }[character];
        });
    }

    function productCard(product, isSidebar) {
        var columnClass = isSidebar ? 'col-12' : 'col-md-6';

        return '' +
            '<div class="' + columnClass + ' mb-3">' +
                '<article class="mb-blog-product-card border rounded bg-white h-100 overflow-hidden">' +
                    '<a href="' + escapeHtml(product.url) + '" class="d-block text-reset">' +
                        '<div class="h-160px overflow-hidden bg-light">' +
                            '<img src="' + escapeHtml(product.thumbnail) + '" alt="' + escapeHtml(product.name) + '" class="img-fit h-100 w-100 has-transition" loading="lazy">' +
                        '</div>' +
                        '<div class="p-3">' +
                            '<span class="badge badge-soft-primary fs-11 fw-600 mb-2">' + escapeHtml(product.badge || 'Available on Mayush') + '</span>' +
                            '<h3 class="fs-14 fw-700 text-truncate-2 mb-2">' + escapeHtml(product.name) + '</h3>' +
                            (product.vendor_name ? '<div class="fs-12 opacity-70 mb-2">' + escapeHtml(product.vendor_name) + '</div>' : '') +
                            '<div class="d-flex align-items-center justify-content-between">' +
                                '<span class="fw-700 text-primary">' + escapeHtml(product.price) + '</span>' +
                                '<span class="fs-12 fw-700 text-primary">Shop on Mayush</span>' +
                            '</div>' +
                        '</div>' +
                    '</a>' +
                '</article>' +
            '</div>';
    }

    document.querySelectorAll('[data-blog-products-lazy]').forEach(function (block) {
        var target = block.querySelector('[data-blog-products-target]');
        var url = block.getAttribute('data-blog-products-url');
        var isSidebar = block.getAttribute('data-blog-placement') === 'sidebar';
        var params = new URLSearchParams({
            blog_id: block.getAttribute('data-blog-id') || '',
            placement: block.getAttribute('data-blog-placement') || 'manual',
            count: block.getAttribute('data-blog-count') || '4'
        });

        if (!target || !url) {
            return;
        }

        fetch(url + '?' + params.toString(), {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        }).then(function (response) {
            return response.json();
        }).then(function (payload) {
            var products = payload && payload.success ? payload.data : [];
            if (!products.length) {
                block.remove();
                return;
            }

            target.innerHTML = products.map(function (product) {
                return productCard(product, isSidebar);
            }).join('');
        }).catch(function () {
            block.remove();
        });
    });

    var progress = document.querySelector('[data-blog-scroll-progress]');
    if (progress) {
        var updateProgress = function () {
            var scrollTop = window.scrollY || document.documentElement.scrollTop;
            var scrollable = document.documentElement.scrollHeight - window.innerHeight;
            var width = scrollable > 0 ? Math.min(100, Math.max(0, (scrollTop / scrollable) * 100)) : 0;
            progress.style.width = width + '%';
        };

        updateProgress();
        window.addEventListener('scroll', updateProgress, { passive: true });
        window.addEventListener('resize', updateProgress);
    }
})();
