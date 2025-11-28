import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();


if ('serviceWorker' in navigator && 'PushManager' in window) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/sw.js')
            .then(swReg => {
                console.log('Service Worker registrado:', swReg);
                // Guarda la registración del SW para usarla después
                window.swRegistration = swReg;
                // Revisa el estado de la suscripción al cargar
                checkSubscriptionStatus();
            })
            .catch(err => {
                console.error('Fallo en el registro del Service Worker:', err);
            });
    });
} else {
    console.warn('Push messaging no es soportado en este navegador.');
}

function checkSubscriptionStatus() {
    // Revisa si ya tenemos una suscripción
    window.swRegistration.pushManager.getSubscription()
        .then(subscription => {
            const isSubscribed = !(subscription === null);
            if (isSubscribed) {
                console.log('Usuario YA está suscrito.');
                // (Opcional) Sincronizar con el backend
                // sendSubscriptionToBackend(subscription);
            } else {
                console.log('Usuario NO está suscrito.');
                // Muestra el botón para suscribirse
                displayNotificationButton();
            }
        });
}

function displayNotificationButton() {
    const notificationArea = document.getElementById('push-notification-area');
    const enableButton = document.getElementById('enable-push-notifications');
    
    if (!notificationArea || !enableButton) return;

    notificationArea.style.display = 'block';
    
    enableButton.addEventListener('click', () => {
        askForNotificationPermission();
        enableButton.disabled = true;
        enableButton.textContent = 'Procesando...';
    });
}

function askForNotificationPermission() {
    Notification.requestPermission(status => {
        console.log('Estado del permiso de notificación:', status);
        if (status === 'granted') {
            console.log('Permiso concedido. Suscribiendo al usuario...');
            subscribeUserToPush();
        } else {
            console.warn('Permiso denegado.');
            document.getElementById('enable-push-notifications').textContent = 'Permiso denegado';
        }
    });
}

function subscribeUserToPush() {
    // Función para convertir la clave VAPID de base64 a Uint8Array
    function urlBase64ToUint8Array(base64String) {
        const padding = '='.repeat((4 - base64String.length % 4) % 4);
        const base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
        const rawData = window.atob(base64);
        const outputArray = new Uint8Array(rawData.length);
        for (let i = 0; i < rawData.length; ++i) {
            outputArray[i] = rawData.charCodeAt(i);
        }
        return outputArray;
    }

    const applicationServerKey = urlBase64ToUint8Array(window.vapidPublicKey);
    
    window.swRegistration.pushManager.subscribe({
        userVisibleOnly: true,
        applicationServerKey: applicationServerKey
    })
    .then(subscription => {
        console.log('Usuario suscrito:', subscription);
        // Envía la suscripción al backend de Laravel
        sendSubscriptionToBackend(subscription);
        document.getElementById('push-notification-area').style.display = 'none';
    })
    .catch(err => {
        console.error('Fallo al suscribir al usuario:', err);
        document.getElementById('enable-push-notifications').textContent = 'Error al activar';
        document.getElementById('enable-push-notifications').disabled = false;
    });
}


// Envía el objeto de suscripción al backend.
function sendSubscriptionToBackend(subscription) {
    const key = subscription.getKey('p256dh');
    const token = subscription.getKey('auth');

    fetch('/push-subscribe', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            endpoint: subscription.endpoint,
            publicKey: key ? btoa(String.fromCharCode.apply(null, new Uint8Array(key))) : null,
            authToken: token ? btoa(String.fromCharCode.apply(null, new Uint8Array(token))) : null
        })
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Fallo al enviar suscripción al backend.');
        }
        return response.json();
    })
    .then(data => console.log('Suscripción guardada en backend:', data))
    .catch(err => console.error(err));
}



// === WebSocket Notifications (Laravel Echo + Reverb) ===
// Escucha canal privado del usuario actual

if (typeof window !== 'undefined'
    && window.user
    && typeof Echo !== 'undefined'
) {
    console.log('[Echo] Escuchando canal privado del usuario', window.user.id);

    function toastContainer() {
        let c = document.getElementById('echo-toast-container');
        if (!c) {
            c = document.createElement('div');
            c.id = 'echo-toast-container';
            c.className = 'fixed top-4 right-4 z-50 space-y-2';
            document.body.appendChild(c);
        }
        return c;
    }

    Echo.private(`App.Models.User.${window.user.id}`)
        .notification((n) => {
            console.log('[Echo] Notificación recibida:', n);

            const container = toastContainer();
            const card = document.createElement('div');
            card.className =
                'max-w-xs bg-white dark:bg-gray-800 border-l-4 border-blue-500 ' +
                'rounded-lg shadow-lg p-4 cursor-pointer transition transform hover:scale-105';
            card.innerHTML = `
                <p class="text-sm font-semibold text-blue-700 dark:text-blue-300 mb-1">${n.title}</p>
                <p class="text-lg text-gray-800 dark:text-gray-100 font-bold">${n.body}</p>
            `;

            if (n.url) {
                card.addEventListener('click', () => window.location.href = n.url);
            }

            container.appendChild(card);
            setTimeout(() => card.remove(), 7000);
        });
} else {
    console.log('[Echo] No disponible (sin usuario o sin Echo).');
}
