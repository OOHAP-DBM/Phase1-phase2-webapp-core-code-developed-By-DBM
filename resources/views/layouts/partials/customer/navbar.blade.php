{{-- Customer Navbar --}}
<header class="bg-white border-b border-gray-200 h-16 flex items-center justify-between px-2 sm:px-4">
    <p></p>
    <div class="flex items-center gap-2 sm:gap-3 md:gap-5 pr-2 md:mr-4 ">
        <button id="mobile-menu-btn" type="button"
            class="block md:hidden p-2 text-gray-600 hover:bg-gray-100 rounded-md order-first md:order-none">
            <svg class="w-6 h-7" fill="none" stroke="currentColor" viewBox="0 0 19 19">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
        </button>

        <div id="notificationDropdown" x-data="notificationDropdown()" class="relative block order-last md:order-none">

            <button @click="unlockSound(); open = !open" type="button"
                class="relative p-2 text-gray-400 hover:text-gray-600 rounded-full bg-gray-100 hover:bg-gray-100 cursor-pointer">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                </svg>
                <template x-if="unreadCount > 0">
                    <span
                        class="absolute -top-1.5 -right-1.5 bg-red-500 text-white text-xs font-bold rounded-full w-5 h-5 flex items-center justify-center min-w-[1.25rem] min-h-[1.25rem]">
                        <span x-text="unreadCount"></span>
                    </span>
                </template>
            </button>

            <div x-show="open" @click.away="open = false" x-transition :class="{
                    'absolute right-0 mt-2 w-80': window.innerWidth >= 768,
                    'fixed left-1/2 top-20 w-11/12 max-w-sm -translate-x-1/2 z-50': window.innerWidth < 768
                 }" class="bg-white rounded-lg shadow-xl border border-gray-200 z-50" style="display: none;">

                <div class="px-4 py-3 border-b border-gray-200">
                    <h3 class="text-sm font-semibold text-gray-900">Notifications</h3>
                </div>

                <div class="max-h-96 overflow-y-auto" id="notificationList">
                    @forelse(auth()->user()->notifications->take(5) as $notification)
                        <a href="{{ route('notifications.open', $notification->id) }}"
                            class="group block px-4 py-3 border-b border-gray-100 transition-all duration-200
                                                                                                                                                                      {{ $notification->read_at ? 'bg-white' : 'bg-blue-50/70' }} hover:bg-gray-50">

                            <div class="flex gap-3 items-start">
                                <div class="mt-1">
                                    <div
                                        class="w-9 h-9 rounded-full flex items-center justify-center
                                                                                                                                                                                    {{ $notification->read_at ? 'bg-gray-100 text-gray-500' : 'bg-blue-100 text-blue-600' }}">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M15 17h5l-1.4-1.4A2 2 0 0118 14.2V11a6 6 0 10-12 0v3.2c0 .53-.21 1.04-.6 1.42L4 17h5m6 0a3 3 0 11-6 0" />
                                        </svg>
                                    </div>
                                </div>

                                <div class="flex-1 min-w-0">
                                    <p
                                        class="text-sm font-semibold {{ $notification->read_at ? 'text-gray-800' : 'text-blue-900' }}">
                                        {{ $notification->data['title'] ?? 'New Notification' }}
                                    </p>
                                    <p class="text-xs text-gray-600 mt-1 leading-relaxed line-clamp-2">
                                        {{ $notification->data['message'] ?? '' }}
                                    </p>
                                    <div class="flex items-center gap-1.5 mt-2 text-[11px] text-blue-400">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 opacity-70" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <circle cx="12" cy="12" r="9" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 7v5l3 2" />
                                        </svg>
                                        <span>{{ $notification->created_at->diffForHumans() }}</span>
                                    </div>
                                </div>

                                @if(!$notification->read_at)
                                    <div class="mt-2">
                                        <span class="w-2.5 h-2.5 rounded-full bg-blue-500 block animate-pulse"></span>
                                    </div>
                                @endif
                            </div>
                        </a>
                    @empty
                        <div class="px-4 py-8 text-center text-gray-500">
                            <p class="text-sm">No notifications yet</p>
                        </div>
                    @endforelse
                </div>

                <div class="px-4 py-3 border-t border-gray-200 text-center">
                    <a href="{{ route('customer.notifications.index') }}"
                        class="text-sm text-blue-600 hover:text-blue-800 font-medium">
                        View all notifications
                    </a>
                </div>
            </div>
        </div>

        <form action="{{ route('search') }}" method="GET" class="flex md:hidden items-center max-w-[50%] px-1 gap-1">
            <input type="text" name="location" placeholder="Search..."
                class="flex-1 w-full px-2 py-2 text-sm border border-gray-300 rounded-md focus:outline-none">
        </form>

        @auth
            <div id="profileMenu" class="relative">

                {{-- Parent Button --}}
                <button type="button" id="profileMenuButton" aria-expanded="false" class="flex items-center justify-center p-1.5 rounded-full
                       hover:bg-gray-100 transition cursor-pointer
                       focus:outline-none">
                    <div class="w-8 h-8 rounded-full overflow-hidden bg-gray-200 flex items-center justify-center">
                        @if(auth()->user()->profile_photo_path)
                            <img src="{{ asset('storage/' . auth()->user()->profile_photo_path) }}"
                                alt="{{ auth()->user()->name }}" class="w-full h-full object-cover">
                        @else
                            <span class="text-sm font-semibold text-gray-600">
                                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                            </span>
                        @endif
                    </div>
                </button>


                {{-- Child Dropdown --}}
                <div id="profileMenuDropdown" class="hidden absolute right-0 top-full mt-2 w-52
                       bg-white rounded-xl border border-gray-200
                       shadow-xl overflow-hidden z-[9999]">

                    <div class="px-4 py-3 border-b border-gray-100">
                        <p class="text-sm font-semibold text-gray-800">
                            {{ auth()->user()->name }}
                        </p>

                        <p class="text-xs text-gray-500 truncate">
                            {{ auth()->user()->email }}
                        </p>
                    </div>


                    <a href="{{ url('customer/profile') }}" class="flex items-center gap-3 px-4 py-3
                           text-sm text-gray-700 hover:bg-gray-50 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>

                        <span>Profile</span>
                    </a>


                    <form method="POST" action="{{ route('logout') }}">
                        @csrf

                        <button type="submit" class="w-full flex items-center gap-3 px-4 py-3
                               text-sm text-red-600 hover:bg-red-50 transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                            </svg>

                            <span>Logout</span>
                        </button>
                    </form>

                </div>

            </div>
        @endauth


        <a href="{{ route('cart.index') }}" class="relative inline-block text-gray-400 hover:text-gray-600"
            title="Cart">
            <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path
                    d="M1.46 3.15018H16.674C18.052 3.15018 19.047 4.42018 18.669 5.69818L17.015 11.2982C16.76 12.1582 15.946 12.7502 15.02 12.7502H5.862C4.935 12.7502 4.12 12.1572 3.866 11.2982L1.46 3.15018ZM1.46 3.15018L0.75 0.750183M14.25 18.7502C14.6478 18.7502 15.0294 18.5921 15.3107 18.3108C15.592 18.0295 15.75 17.648 15.75 17.2502C15.75 16.8524 15.592 16.4708 15.3107 16.1895C15.0294 15.9082 14.6478 15.7502 14.25 15.7502C13.8522 15.7502 13.4706 15.9082 13.1893 16.1895C12.908 16.4708 12.75 16.8524 12.75 17.2502C12.75 17.648 12.908 18.0295 13.1893 18.3108C13.4706 18.5921 13.8522 18.7502 14.25 18.7502ZM6.25 18.7502C6.64782 18.7502 7.02936 18.5921 7.31066 18.3108C7.59196 18.0295 7.75 17.648 7.75 17.2502C7.75 16.8524 7.59196 16.4708 7.31066 16.1895C7.02936 15.9082 6.64782 15.7502 6.25 15.7502C5.85218 15.7502 5.47064 15.9082 5.18934 16.1895C4.90804 16.4708 4.75 16.8524 4.75 17.2502C4.75 17.648 4.90804 18.0295 5.18934 18.3108C5.47064 18.5921 5.85218 18.7502 6.25 18.7502Z"
                    stroke="#6E6E6E" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
            @php
                $cartCount = 0;
                if (auth()->check()) {
                    $cartCount = \Illuminate\Support\Facades\DB::table('carts')
                        ->where('user_id', auth()->id())
                        ->count();
                }
            @endphp
            @if($cartCount > 0)
                <span
                    class="absolute -top-3 -right-3 bg-red-500 text-white text-xs font-bold rounded-full w-5 h-5 flex items-center justify-center">
                    {{ $cartCount }}
                </span>
            @endif
        </a>

    </div>
</header>

<script>

</script>

<script>
    function notificationDropdown() {

        return {
            open: false,
            unreadCount: {{ auth()->user()->unreadNotifications->count() ?? 0 }},
            lastUnreadCount: {{ auth()->user()->unreadNotifications->count() ?? 0 }},
            notificationSound: null,
            notificationSound: null,
            soundUnlocked: false,
            init() {

                this.notificationSound = new Audio(
                    "{{ asset('sounds/beep.mp3') }}"
                );

                this.notificationSound.preload = 'auto';
                this.notificationSound.volume = 1.0;

                // First user interaction par audio unlock
                const unlockOnce = () => {
                    this.unlockSound();
                };

                document.addEventListener('click', unlockOnce, {
                    once: true,
                    passive: true
                });

                document.addEventListener('touchstart', unlockOnce, {
                    once: true,
                    passive: true
                });

                // Initial notification check
                this.checkNotifications();

                // Every 10 seconds
                setInterval(() => {
                    this.checkNotifications();
                }, 10000);
            },

            unlockSound() {

                if (this.soundUnlocked) {
                    return;
                }

                if (!this.notificationSound) {
                    return;
                }

                this.notificationSound.muted = true;
                this.notificationSound.currentTime = 0;

                const playPromise = this.notificationSound.play();

                if (playPromise !== undefined) {

                    playPromise
                        .then(() => {

                            this.notificationSound.pause();
                            this.notificationSound.currentTime = 0;
                            this.notificationSound.muted = false;

                            this.soundUnlocked = true;

                            console.log('🔓 Notification sound unlocked');

                        })
                        .catch(error => {

                            console.log(
                                '❌ Audio unlock failed:',
                                error
                            );

                        });
                }
            },
            async checkNotifications() {

                try {

                    const response = await fetch(
                        "{{ url('/api/v1/notifications/unread-count') }}",
                        {
                            method: 'GET',
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            credentials: 'same-origin'
                        }
                    );

                    if (!response.ok) {
                        return;
                    }

                    const data = await response.json();

                    const newCount = parseInt(
                        data.unread_count ?? 0,
                        10
                    );


                    // New notification detected
                    if (newCount > this.lastUnreadCount) {

                        console.log(
                            'New notification received'
                        );

                        // Update badge
                        this.unreadCount = newCount;


                        this.playNotificationSound();

                        // Refresh notification list
                        await this.loadNotifications();

                    } else {


                        this.unreadCount = newCount;

                    }


                    this.lastUnreadCount = newCount;

                } catch (error) {

                    console.error(
                        'Notification check failed:',
                        error
                    );

                }
            },


            playNotificationSound() {

                if (!this.soundUnlocked) {

                    console.log(
                        'Notification sound is not unlocked yet'
                    );

                    return;
                }

                const audio = new Audio(
                    "{{ asset('sounds/beep.mp3') }}"
                );

                audio.volume = 1.0;
                audio.currentTime = 0;

                audio.play()
                    .then(() => {

                        console.log(
                            'NEW NOTIFICATION SOUND PLAYED'
                        );

                    })
                    .catch(error => {

                        console.error(
                            'Notification sound blocked:',
                            error
                        );

                    });
            },
            async loadNotifications() {

                try {

                    const response = await fetch(
                        "{{ url('/api/v1/notifications') }}",
                        {
                            method: 'GET',
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            credentials: 'same-origin'
                        }
                    );

                    if (!response.ok) {
                        return;
                    }

                    const result = await response.json();

                    const notifications = result.data ?? [];

                    this.renderNotifications(notifications);

                } catch (error) {

                    console.error(
                        'Notification loading failed:',
                        error
                    );

                }
            },


            renderNotifications(notifications) {

                const container = document.getElementById(
                    'notificationList'
                );

                if (!container) {
                    return;
                }


                if (!notifications.length) {

                    container.innerHTML = `
                    <div class="px-4 py-8 text-center text-gray-500">
                        <p class="text-sm">
                            No notifications yet
                        </p>
                    </div>
                `;

                    return;
                }


                container.innerHTML = notifications
                    .slice(0, 5)
                    .map(notification => {

                        const data = notification.data || {};

                        const title =
                            data.title || 'New Notification';

                        const message =
                            data.message || '';

                        const createdAt =
                            notification.created_at || '';

                        const isUnread =
                            !notification.read_at;


                        return `

                        <a href="/notifications/open/${notification.id}"
                           class="group block px-4 py-3 border-b border-gray-100 transition-all duration-200
                           ${isUnread ? 'bg-blue-50/70' : 'bg-white'}
                           hover:bg-gray-50">

                            <div class="flex gap-3 items-start">

                                <div class="mt-1">

                                    <div class="w-9 h-9 rounded-full flex items-center justify-center
                                        ${isUnread
                                ? 'bg-blue-100 text-blue-600'
                                : 'bg-gray-100 text-gray-500'}">

                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            class="w-5 h-5"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                            stroke="currentColor"
                                            stroke-width="1.8">

                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                d="M15 17h5l-1.4-1.4A2.032 2.032 0 0118 14.2V11a6 6 0 10-12 0v3.2c0 .53-.21 1.04-.6 1.42L4 17h5m6 0a3 3 0 11-6 0"/>
                                        </svg>

                                    </div>

                                </div>


                                <div class="flex-1 min-w-0">

                                    <p class="text-sm font-semibold
                                        ${isUnread
                                ? 'text-blue-900'
                                : 'text-gray-800'}">

                                        ${title}

                                    </p>


                                    <p class="text-xs text-gray-600 mt-1 leading-relaxed line-clamp-2">

                                        ${message}

                                    </p>


                                    <div class="flex items-center gap-1.5 mt-2 text-[11px] text-blue-400">

                                        <span>
                                            ${createdAt}
                                        </span>

                                    </div>

                                </div>


                                ${isUnread
                                ?
                                `
                                    <div class="mt-2">
                                        <span class="w-2.5 h-2.5 rounded-full bg-blue-500 block animate-pulse"></span>
                                    </div>
                                    `
                                :
                                ''
                            }

                            </div>

                        </a>

                    `;

                    })
                    .join('');
            }

        };
    }
</script>

<script>
    window.notificationOpenUrl = "{{ route('notifications.open', ['notification' => '__ID__']) }}";
    function testNotificationSound() {
        const audio = new Audio('/sounds/beep.mp3');

        audio.volume = 1.0;

        audio.play()
            .then(() => {
                console.log('Notification sound playing');
            })
            .catch(error => {
                console.error('Sound blocked:', error);
            });
    }
</script>

<script>
    document.addEventListener('DOMContentLoaded', function () {

        const button = document.getElementById('profileMenuButton');
        const dropdown = document.getElementById('profileMenuDropdown');
        const menu = document.getElementById('profileMenu');

        if (!button || !dropdown || !menu) {
            return;
        }

        button.addEventListener('click', function (event) {
            event.preventDefault();
            event.stopPropagation();

            const isOpen = !dropdown.classList.contains('hidden');

            dropdown.classList.toggle('hidden', isOpen);
            button.setAttribute('aria-expanded', isOpen ? 'false' : 'true');
        });

        dropdown.addEventListener('click', function (event) {
            event.stopPropagation();
        });

        document.addEventListener('click', function () {
            dropdown.classList.add('hidden');
            button.setAttribute('aria-expanded', 'false');
        });

    });
</script>