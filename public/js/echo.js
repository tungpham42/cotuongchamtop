import Echo from 'laravel-echo/dist/echo.js';

window.Echo = new Echo({
    broadcaster: 'pusher',
    key: '0edf295675fc9fc1c2d3',
    cluster: 'ap1',
});

window.Echo.connector.pusher.connection.bind('connected', function () {
    console.log('Connected to Pusher!');
});