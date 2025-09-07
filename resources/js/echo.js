import axios from 'axios';
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

function getCookie(name) {
    const match = document.cookie.match(new RegExp('(^| )' + name + '=([^;]+)'));
    return match ? decodeURIComponent(match[2]) : null;
}

async function initEcho() {
    try {
        // Get CSRF cookie if using Sanctum
        await axios.get('/sanctum/csrf-cookie', { withCredentials: true });

        return new Promise((resolve, reject) => {
            window.Echo = new Echo({
                broadcaster: 'pusher',
                key: import.meta.env.VITE_PUSHER_APP_KEY,
                cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER || 'eu', // Required!
                wsHost: import.meta.env.VITE_PUSHER_HOST,
                wsPort: 6001,
                wssPort: 6001,
                forceTLS: false,
                encrypted: false,
                disableStats: true,
                enabledTransports: ['ws', 'wss'],
                authEndpoint: '/broadcasting/auth',
                auth: {
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
                        'Authorization': `Bearer ${localStorage.getItem('token') || ''}`
                    },
                },
            });

            // Connection event handlers
            window.Echo.connector.pusher.connection.bind('connected', () => {
                console.log('✅ [ECHO] Connected to Soketi');
                console.log('🔗 Connection ID:', window.Echo.connector.pusher.connection.socket_id);
                resolve(window.Echo);
            });

            window.Echo.connector.pusher.connection.bind('error', (err) => {
                console.error('❌ [ECHO] Connection error:', err);
                reject(err);
            });

            window.Echo.connector.pusher.connection.bind('disconnected', () => {
                console.log('🔌 [ECHO] Disconnected from Soketi');
            });

            window.Echo.connector.pusher.connection.bind('reconnecting', () => {
                console.log('🔄 [ECHO] Reconnecting to Soketi...');
            });

            // Timeout handler
            setTimeout(() => {
                reject(new Error('Echo connection timeout after 15 seconds'));
            }, 15000);
        });
    } catch (error) {
        console.error('❌ Error initializing Echo:', error);
        throw error;
    }
}

// Usage example
function setupChannelListeners() {
    initEcho().then(() => {
        const channelName = 'test-channel';
        console.log('📡 Subscribing to channel:', channelName);

        const channel = window.Echo.channel(channelName);

        // Subscription callbacks
        channel.subscribed(() => {
            console.log(`✅ Successfully subscribed to ${channelName}`);
        });

        channel.error((error) => {
            console.error(`❌ Subscription error for ${channelName}:`, error);
        });

        // Listen for specific event
        channel.listen('TestEvent', (payload) => {
            console.log('🎉 [ECHO EVENT] TestEvent received:', payload);
        });

        // Debug: Listen to all events
        channel.listenToAll((eventName, data) => {
            console.log('🔊 [ALL EVENTS]', eventName, data);
        });

        // Raw Pusher debugging
        const pusherChannel = window.Echo.connector.pusher.channel(channelName);
        pusherChannel.bind_global((eventName, data) => {
            console.log('🔄 [PUSHER GLOBAL]', eventName, data);
        });

    }).catch(error => {
        console.error('❌ Failed to initialize Echo:', error);
    });
}

// Initialize when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', setupChannelListeners);
} else {
    setupChannelListeners();
}

export { initEcho, setupChannelListeners };
