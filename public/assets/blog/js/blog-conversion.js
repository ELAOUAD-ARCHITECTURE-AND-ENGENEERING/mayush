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
})();
