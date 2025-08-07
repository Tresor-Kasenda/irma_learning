// Système de notification global
window.notificationSystem = function() {
    return {
        notifications: [],
        
        init() {
            // Écouter les événements Livewire
            this.$wire && this.$wire.on('notify', (data) => {
                this.show(data[0] || data);
            });
            
            // Écouter les événements de notification Livewire mis à jour
            this.$wire && this.$wire.on('notification-add', (data) => {
                this.showDetailed(data[0] || data);
            });
            
            // Écouter les événements globaux
            window.addEventListener('notify', (event) => {
                this.show(event.detail);
            });
            
            window.addEventListener('notification-add', (event) => {
                this.showDetailed(event.detail);
            });
        },
        
        show(notification) {
            const id = Date.now() + Math.random();
            const notif = {
                id,
                message: notification.message || 'Notification',
                type: notification.type || 'info',
                duration: notification.duration || 5000
            };
            
            this.notifications.push(notif);
            
            // Auto-remove après la durée spécifiée
            setTimeout(() => {
                this.remove(id);
            }, notif.duration);
        },
        
        showDetailed(notification) {
            const id = Date.now() + Math.random();
            const notif = {
                id,
                title: notification.title || '',
                message: notification.message || 'Notification',
                type: notification.type || 'info',
                duration: notification.duration || 5000
            };
            
            this.notifications.push(notif);
            
            // Auto-remove après la durée spécifiée
            setTimeout(() => {
                this.remove(id);
            }, notif.duration);
        },
        
        remove(id) {
            const index = this.notifications.findIndex(n => n.id === id);
            if (index > -1) {
                this.notifications.splice(index, 1);
            }
        },
        
        getTypeClasses(type) {
            const classes = {
                success: 'bg-green-500 text-white',
                error: 'bg-red-500 text-white',
                warning: 'bg-yellow-500 text-white',
                info: 'bg-blue-500 text-white'
            };
            return classes[type] || classes.info;
        }
    }
};

// Fonction helper globale pour déclencher des notifications
window.notify = function(message, type = 'info', duration = 5000) {
    window.dispatchEvent(new CustomEvent('notify', {
        detail: { message, type, duration }
    }));
};

// Fonction helper globale pour déclencher des notifications détaillées
window.notifyDetailed = function(title, message, type = 'info', duration = 5000) {
    window.dispatchEvent(new CustomEvent('notification-add', {
        detail: { title, message, type, duration }
    }));
};

// Listen to Livewire events when document is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Global Livewire event listeners
    Livewire.on('notification-add', (data) => {
        const notification = data[0] || data;
        window.notifyDetailed(
            notification.title || '',
            notification.message || 'Notification',
            notification.type || 'info',
            notification.duration || 5000
        );
    });
});
