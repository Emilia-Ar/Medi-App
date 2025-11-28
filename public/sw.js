// Evento de Instalación: Se dispara cuando el SW se registra por primera vez.
self.addEventListener('install', event => {
    console.log('Service Worker: Instalado');
    // Forzar al SW a activarse inmediatamente
    self.skipWaiting();
});

// Evento de Activación: Se dispara después de la instalación.
self.addEventListener('activate', event => {
    console.log('Service Worker: Activado');
    // Tomar control inmediato de todas las páginas
    event.waitUntil(self.clients.claim());
});

// 🔴 IMPORTANTE: eliminamos el evento fetch porque no lo usamos
// y nos estaba generando errores "Failed to fetch" innecesarios.
// self.addEventListener('fetch', event => {
//     event.respondWith(fetch(event.request));
// });

/* ------------------------------------------------------------------
   LISTENER PARA PUSH (recibe la notificación del servidor)
   ------------------------------------------------------------------ */
self.addEventListener('push', event => {
    console.log('Service Worker: Push Recibido.');

    let data;
    try {
        data = event.data ? event.data.json() : {};
    } catch (e) {
        console.error('Error al parsear payload:', e);
        data = { title: 'Notificación', body: 'Revisa tu aplicación.' };
    }

    const title = data.title || 'Recordatorio de Medicina';

    const options = {
        body: data.body || 'Es hora de tu toma.',
        icon: data.icon || '/images/icons/icon-192x192.png',
        badge: data.badge || '/images/icons/icon-192x192.png',
        image: data.image || undefined,
        data: data.data || {},
        actions: data.actions || [], // botones 'open_app' y 'skip'
        requireInteraction: true
    };

    event.waitUntil(self.registration.showNotification(title, options));
});

/* ------------------------------------------------------------------
   LISTENER PARA CLICK EN NOTIFICACIÓN
   ------------------------------------------------------------------ */
self.addEventListener('notificationclick', event => {
    console.log('Service Worker: Clic en Notificación Recibido.');

    event.notification.close();

    const data = event.notification.data || {};
    const action = event.action; // 'open_app', 'skip', o vacío

    if (action === 'skip') {
        console.log('Acción: Saltar');
    } else {
        console.log('Acción: Abrir App');
        
        event.waitUntil(
            clients.openWindow(data.url || '/dashboard')
        );
    }

});
