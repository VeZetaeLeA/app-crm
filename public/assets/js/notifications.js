/**
 * Notifications Manager
 */
document.addEventListener('DOMContentLoaded', function () {
    const badge = document.getElementById('notification-badge');
    const container = document.getElementById('notification-dropdown-items');
    const btn = document.getElementById('notification-btn');

    if (!btn) return;

    function fetchNotifications() {
        fetch(window.APP_URL + '/notification/getRecent')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    updateUI(data.notifications, data.count);
                }
            });
    }

    function updateUI(notifications, count) {
        // Update badge
        if (count > 0) {
            badge.textContent = count;
            badge.classList.remove('d-none');
        } else {
            badge.classList.add('d-none');
        }

        if (container) {
            container.innerHTML = '';
            if (notifications && notifications.length > 0) {
                notifications.forEach(n => {
                    let typeIcon = 'notifications';
                    let typeColor = 'text-accent';
                    if (n.type.includes('budget')) { typeIcon = 'request_quote'; typeColor = 'text-warning'; }
                    if (n.type.includes('ticket')) { typeIcon = 'confirmation_number'; typeColor = 'text-primary'; }
                    if (n.type.includes('service') || n.type.includes('paid')) { typeIcon = 'check_circle'; typeColor = 'text-success'; }
                    if (n.type.includes('error')) { typeIcon = 'error'; typeColor = 'text-danger'; }

                    const url = window.APP_URL + '/notification/read/' + n.id;
                    const itemHTML = `
                        <a href="${url}" class="d-flex align-items-start gap-3 p-3 border-bottom border-white-10 text-decoration-none hover-bg-white-5 transition-all">
                            <div class="rounded-circle bg-white-5 d-flex align-items-center justify-content-center p-2">
                                <span class="material-symbols-outlined fs-5 ${typeColor}">${typeIcon}</span>
                            </div>
                            <div>
                                <h6 class="mb-1 text-white small fw-bold">${n.title}</h6>
                                <p class="text-white-50 x-small mb-1" style="line-height: 1.4;">${n.message}</p>
                                <span class="text-white-50 x-small fw-bold opacity-50">${new Date(n.created_at).toLocaleString()}</span>
                            </div>
                        </a>
                    `;
                    container.insertAdjacentHTML('beforeend', itemHTML);
                });
            } else {
                container.innerHTML = '<div class="p-4 text-center text-white-50 small">No hay notificaciones nuevas.</div>';
            }
        }
    }

    // Polling every 60 seconds
    setInterval(fetchNotifications, 60000);
    fetchNotifications();

    // Mark as read
    const markReadBtn = document.getElementById('mark-read-btn');
    if (markReadBtn) {
        markReadBtn.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            fetch(window.APP_URL + '/notification/markRead')
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        badge.classList.add('d-none');
                        badge.textContent = '0';
                        if (container) {
                            container.innerHTML = '<div class="p-4 text-center text-white-50 small">No hay notificaciones nuevas.</div>';
                        }
                    }
                });
        });
    }
});
