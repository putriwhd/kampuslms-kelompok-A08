document.addEventListener('DOMContentLoaded', () => {
    const wrapper = document.getElementById('notificationWrapper');
    const bell = document.getElementById('notificationBell');
    const dropdown = document.getElementById('notificationDropdown');
    const badge = document.getElementById('notificationBadge');
    const preview = document.getElementById('notificationPreview');

    if (!wrapper || !bell || !dropdown || !badge || !preview) return;

    const endpoint = wrapper.dataset.endpoint;
    let loading = false;

    bell.addEventListener('click', () => {
        const isOpening = dropdown.hidden;

        dropdown.hidden = !isOpening;
        bell.setAttribute('aria-expanded', String(isOpening));
        bell.setAttribute('aria-label', isOpening ? 'Tutup notifikasi' : 'Buka notifikasi');

        if (isOpening) loadNotifications();
    });

    document.addEventListener('click', (event) => {
        if (!wrapper.contains(event.target)) {
            dropdown.hidden = true;
            bell.setAttribute('aria-expanded', 'false');
            bell.setAttribute('aria-label', 'Buka notifikasi');
        }
    });

    function updateBadge(count) {
        const unread = Math.max(0, Number(count) || 0);

        badge.textContent = unread > 99 ? '99+' : String(unread);
        badge.hidden = unread === 0;
    }

    function renderNotifications(items) {
        preview.replaceChildren();

        if (!Array.isArray(items) || items.length === 0) {
            const empty = document.createElement('p');
            empty.className = 'notification-empty';
            empty.textContent = 'Belum ada notifikasi.';
            preview.append(empty);
            return;
        }

        items.slice(0, 5).forEach((item) => {
            const data = item.data && typeof item.data === 'object' ? item.data : {};
            const link = document.createElement('a');
            link.className = 'notification-item';

            if (!item.read_at) {
                link.classList.add('is-unread');
            }

            // Gunakan URL internal yang disediakan backend.
            // Jangan memasukkan HTML mentah dari respons API.
            link.href = typeof data.url === 'string'
                && data.url.startsWith('/')
                && !data.url.startsWith('//')
                ? data.url
                : '#';

            const title = document.createElement('strong');
            title.textContent = data.title || data.assignment_title || 'Notifikasi';

            const message = document.createElement('p');
            message.textContent = data.message
                || data.body
                || (data.course_name ? `Mata kuliah: ${data.course_name}` : 'Ada informasi baru untuk kamu.');

            const time = document.createElement('small');
            time.textContent = item.created_at || '';

            link.append(title, message, time);
            preview.append(link);
        });
    }

    async function loadNotifications() {
        if (loading) return;

        loading = true;

        try {
            const response = await fetch(endpoint, {
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'same-origin',
            });

            if (!response.ok) {
                throw new Error('Gagal mengambil notifikasi.');
            }

            const result = await response.json();

            // Sesuaikan dengan bentuk respons API dari backend.
            const items = result.data ?? result.notifications ?? [];
            const unreadCount = result.unread_count ?? 0;

            updateBadge(unreadCount);
            renderNotifications(items);
        } catch (error) {
            console.error('Notifikasi:', error);
        } finally {
            loading = false;
        }
    }

    loadNotifications();
    window.setInterval(loadNotifications, 15000);
});