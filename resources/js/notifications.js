document.addEventListener('DOMContentLoaded', function () {

    let lastUnreadCount = null;

    const notificationSound = new Audio('/sounds/beep.mp3');

    notificationSound.preload = 'auto';

    // Browser audio permission unlock
    document.addEventListener('click', function () {
        notificationSound.load();
    }, { once: true });


    async function checkNotifications() {

        try {

            const response = await fetch('/api/v1/notifications/unread-count', {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin'
            });

            if (!response.ok) {
                return;
            }

            const data = await response.json();

            const unreadCount = parseInt(
                data.unread_count ?? 0,
                10
            );


            // First request: only store current count
            if (lastUnreadCount === null) {
                lastUnreadCount = unreadCount;
                return;
            }


            // New notification detected
            if (unreadCount > lastUnreadCount) {

                console.log(
                    '[Notifications] New notification received'
                );

                notificationSound.currentTime = 0;

                notificationSound.play()
                    .then(() => {
                        console.log(
                            '[Notifications] Sound played'
                        );
                    })
                    .catch(error => {
                        console.log(
                            '[Notifications] Sound blocked:',
                            error
                        );
                    });
            }


            lastUnreadCount = unreadCount;

        } catch (error) {

            console.error(
                '[Notifications] Check failed:',
                error
            );
        }
    }


    // Initial check
    checkNotifications();


    // Check every 10 seconds
    setInterval(
        checkNotifications,
        10000
    );

});