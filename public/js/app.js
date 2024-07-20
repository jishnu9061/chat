import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: 'pusher',
    key: process.env.MIX_PUSHER_APP_KEY,
    cluster: process.env.MIX_PUSHER_APP_CLUSTER,
    forceTLS: true
});

// Assuming you have the chat ID available
const chatId = 1;

window.Echo.private(`chat.${chatId}`)
    .listen('MessageSent', (e) => {
        console.log(e);
        // Handle the received message data
    });
