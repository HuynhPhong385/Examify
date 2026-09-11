(() => {
    const appBase = (document.querySelector('meta[name="app-base-url"]')?.content || '').replace(/\/$/, '');

    window.Examify = {
        url(path = '') {
            return appBase + '/' + String(path).replace(/^\//, '');
        },
        api(path, options = {}) {
            const token = document.querySelector('meta[name="csrf-token"]')?.content || '';
            const method = String(options.method || 'GET').toUpperCase();
            const headers = {
                'Accept': 'application/json',
                ...(method !== 'GET' && method !== 'HEAD' ? { 'X-CSRF-Token': token } : {}),
                ...(options.headers || {})
            };

            return new Promise((resolve, reject) => {
                $.ajax({
                    url: appBase + '/api' + path,
                    method,
                    data: options.body || undefined,
                    contentType: options.body ? 'application/json; charset=utf-8' : undefined,
                    dataType: 'json',
                    headers,
                    xhrFields: { withCredentials: true }
                })
                    .done(resolve)
                    .fail(xhr => {
                        const message = xhr.responseJSON?.error || `HTTP ${xhr.status}`;
                        reject(new Error(message));
                    });
            });
        },
        toast(message, type = 'ok') {
            const el = document.getElementById('toast');
            if (!el) return;
            el.textContent = message;
            el.className = `toast show ${type}`;
            clearTimeout(window.__toastTimer);
            window.__toastTimer = setTimeout(() => el.className = 'toast', 2200);
        },
        escapeHtml(value) {
            return String(value ?? '').replace(/[&<>"']/g, c => ({
                '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'
            })[c]);
        }
    };

    // Menu mobile
    const navToggle = document.getElementById('navToggle');
    const topbarNav = document.getElementById('topbarNav');
    if (navToggle && topbarNav) {
        navToggle.addEventListener('click', () => {
            const isOpen = topbarNav.classList.toggle('open');
            navToggle.classList.toggle('open', isOpen);
            navToggle.setAttribute('aria-expanded', String(isOpen));
        });
        topbarNav.addEventListener('click', e => {
            if (e.target.tagName === 'A') {
                topbarNav.classList.remove('open');
                navToggle.classList.remove('open');
                navToggle.setAttribute('aria-expanded', 'false');
            }
        });
    }
})();
