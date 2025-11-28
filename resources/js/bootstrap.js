// resources/js/bootstrap.js

import axios from 'axios';
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

// ---------------------------------------------
// Axios básico
// ---------------------------------------------
window.axios = axios;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

const tokenTag = document.head.querySelector('meta[name="csrf-token"]');
if (tokenTag) {
    window.axios.defaults.headers.common['X-CSRF-TOKEN'] = tokenTag.content;
}

// ---------------------------------------------
// Laravel Echo + Reverb usando Pusher JS
// (sin laravel-reverb-js, todo desde npm normal)
// ---------------------------------------------
window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: 'pusher', // 👈 importante: usamos el driver "pusher"
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: import.meta.env.VITE_REVERB_HOST ?? window.location.hostname,
    wsPort: import.meta.env.VITE_REVERB_PORT ?? 8080,
    wssPort: import.meta.env.VITE_REVERB_PORT ?? 8080,
    forceTLS: false,
    enabledTransports: ['ws', 'wss'],
    cluster: 'mt1',
});


