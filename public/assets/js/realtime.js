/**
 * Real-Time Reactor (WebSockets)
 * E11-009, E11-010, E11-011
 */
class RealTimeReactor {
    constructor() {
        this.socket = null;
        this.reconnectAttempts = 0;
        this.maxReconnectAttempts = 5;
        this.connect();
    }

    connect() {
        const wsProtocol = window.location.protocol === 'https:' ? 'wss:' : 'ws:';
        // For local dev, we might use 8080. In production, it might be proxied.
        const wsUrl = `${wsProtocol}//${window.location.hostname}:8080`;

        console.log('Connecting to Real-Time Reactor:', wsUrl);
        this.socket = new WebSocket(wsUrl);

        this.socket.onopen = () => {
            console.log('Real-Time Connected ✅');
            this.reconnectAttempts = 0;
        };

        this.socket.onmessage = (event) => {
            const payload = JSON.parse(event.data);
            this.handlePayload(payload);
        };

        this.socket.onclose = () => {
            console.warn('Real-Time Disconnected ❌');
            if (this.reconnectAttempts < this.maxReconnectAttempts) {
                this.reconnectAttempts++;
                setTimeout(() => this.connect(), 3000 * this.reconnectAttempts);
            }
        };

        this.socket.onerror = (error) => {
            console.error('WebSocket Error:', error);
        };
    }

    handlePayload(payload) {
        const { type, data } = payload;
        console.log('RT Input:', type, data);

        switch (type) {
            case 'new_message':
                this.handleNewMessage(data);
                break;
            case 'notification':
                this.handleNotification(data);
                break;
            case 'ticket_status_update':
                this.handleStatusUpdate(data);
                break;
        }
    }

    handleNewMessage(data) {
        // If we are on the ticket detail page for this ticket
        const ticketId = data.ticket_id;
        const currentPath = window.location.pathname;
        
        if (currentPath.includes(`/ticket/detail/${ticketId}`)) {
            // Append message to chat if function exists
            if (typeof window.appendChatMessage === 'function') {
                window.appendChatMessage(data);
            } else {
                // Fallback: show toast if not looking at the chat
                window.showToast(`Nuevo mensaje de ${data.user_name} en Ticket #${ticketId}`, 'info');
            }
        } else {
            window.showToast(`Nuevo mensaje en Ticket #${ticketId}`, 'info');
        }
    }

    handleNotification(data) {
        // Show Toast
        window.showToast(data.message || data.title, data.type || 'info');
        
        // Trigger notification badge update if notifications.js is loaded
        const badge = document.getElementById('notification-badge');
        if (badge) {
            let count = parseInt(badge.textContent || '0');
            badge.textContent = count + 1;
            badge.classList.remove('d-none');
        }
    }

    handleStatusUpdate(data) {
        window.showToast(`Ticket #${data.ticket_number} actualizado a: ${data.status}`, 'success');
        // Optionally reload parts of the UI
        if (window.location.pathname.includes(`/ticket/detail/${data.ticket_id}`)) {
            setTimeout(() => window.location.reload(), 2000);
        }
    }
}

window.RTReactor = new RealTimeReactor();
