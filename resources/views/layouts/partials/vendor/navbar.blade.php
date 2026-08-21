{{-- Vendor Navbar --}}
<header class="bg-white border-b border-gray-200 py-3 flex items-center px-6 gap-4">

    <!-- LEFT : TITLE -->
    <div class="flex-1 min-w-0">

        {{-- Mobile/Tablet: show only page title --}}
        <h1 class="lg:hidden text-sm font-semibold text-gray-800 truncate px-2">
            @yield('title', 'Dashboard')
        </h1>

        {{-- Desktop: show full title + breadcrumb --}}
        <div class="hidden lg:block px-4 md:px-6 py-1 bg-white">
            <div class="flex flex-col">

                @hasSection('breadcrumb')
                    <div class="mb-1">
                        @yield('breadcrumb')
                    </div>
                @endif

                <h1 class="md:text-xl font-semibold text-gray-800">
                    @yield('title', 'Dashboard')
                </h1>

                @if (Request::routeIs('vendor.dashboard'))

                    {{-- No breadcrumb on dashboard --}}

                @elseif (Request::routeIs('vendor.import.enhanced.batch.show'))

                    <div class="text-xs text-gray-500 mt-1">
                        <a href="{{ route('vendor.dashboard') }}">Dashboard</a>
                        &rarr;

                        <a href="{{ route('vendor.import.enhanced') }}">
                            Import Management
                        </a>

                        &rarr;

                        <span>Inventory Details</span>
                    </div>

                @elseif (View::hasSection('breadcrumb'))

                    {{-- Custom breadcrumb section rendered above --}}

                @else

                    <div class="text-xs text-gray-500 mt-1">
                        <a href="{{ route('vendor.dashboard') }}">
                            Dashboard
                        </a>

                        &rarr;

                        @yield('title', 'Dashboard')
                    </div>

                @endif

            </div>
        </div>
    </div>


    <!-- RIGHT : ACTIONS -->
    <div class="flex items-center space-x-4 flex-shrink-0">

        {{-- ========================================= --}}
        {{-- NOTIFICATION DROPDOWN --}}
        {{-- ========================================= --}}

        <div id="notificationDropdown" x-data="notificationDropdown()" class="relative">

            {{-- Notification Button --}}
            <button @click="unlockSound(); open = !open" type="button"
                class="relative p-2 text-gray-400 hover:text-gray-600 rounded-full hover:bg-gray-100 cursor-pointer">

                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0
                        0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2
                        2 0 10-4 0v.341C7.67 6.165 6 8.388
                        6 11v3.159c0 .538-.214 1.055-.595
                        1.436L4 17h5m6 0v1a3 3 0
                        11-6 0v-1m6 0H9">
                    </path>
                </svg>


                {{-- Unread Count --}}
                <template x-if="unreadCount > 0">

                    <span
                        class="absolute -top-0.5 -right-0 bg-red-500 text-white text-xs font-bold rounded-full w-5 h-5 flex items-center justify-center min-w-[1.25rem]">
                        <span x-text="unreadCount"></span>
                    </span>

                </template>

            </button>


            {{-- Dropdown Panel --}}
            <div x-show="open" @click.away="open = false" x-transition :class="{
                    'absolute right-0 mt-2 w-80': window.innerWidth >= 1024,
                    'fixed left-1/2 top-24 w-11/12 max-w-sm -translate-x-1/2': window.innerWidth < 1024
                }" class="bg-white rounded-lg shadow-xl border border-gray-200"
                style="display: none; z-index: 999999 !important;">

                {{-- Header --}}
                <div class="px-4 py-3 border-b border-gray-200">

                    <h3 class="text-sm font-semibold text-gray-900">
                        Notifications
                    </h3>

                </div>


                {{-- Notification List --}}
                <div class="max-h-96 overflow-y-auto" id="vendorNotificationList">

                    @forelse(auth()->user()->notifications->take(5) as $notification)

                                    <a href="{{ route('notifications.open', $notification->id) }}" class="group block px-4 py-3 border-b border-gray-100 transition-all duration-200
                                            {{ $notification->read_at ? 'bg-white' : 'bg-blue-50/70' }}
                                            hover:bg-gray-50">

                                        <div class="flex gap-3 items-start">

                                            {{-- Notification Icon --}}
                                            <div class="mt-1">

                                                <div class="w-9 h-9 rounded-full flex items-center justify-center
                                                        {{ $notification->read_at
                        ? 'bg-gray-100 text-gray-500'
                        : 'bg-blue-100 text-blue-600' }}">

                                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none"
                                                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">

                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.4-1.4A2 2 0
                                                                0118 14.2V11a6 6 0 10-12
                                                                0v3.2c0 .53-.21 1.04-.6
                                                                1.42L4 17h5m6 0a3 3 0
                                                                11-6 0" />

                                                    </svg>

                                                </div>

                                            </div>


                                            {{-- Text --}}
                                            <div class="flex-1 min-w-0">

                                                <p class="text-sm font-semibold
                                                        {{ $notification->read_at
                        ? 'text-gray-800'
                        : 'text-blue-900' }}">
                                                    {{ $notification->data['title'] ?? 'New Notification' }}
                                                </p>


                                                <p class="text-xs text-gray-600 mt-1 leading-relaxed line-clamp-2">
                                                    {{ $notification->data['message'] ?? '' }}
                                                </p>


                                                {{-- Time --}}
                                                <div class="flex items-center gap-1.5 mt-2 text-[11px] text-blue-400">

                                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 opacity-70" fill="none"
                                                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">

                                                        <circle cx="12" cy="12" r="9" />

                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 7v5l3 2" />

                                                    </svg>

                                                    <span>
                                                        {{ $notification->created_at->diffForHumans() }}
                                                    </span>

                                                </div>

                                            </div>


                                            {{-- Unread Dot --}}
                                            @if(!$notification->read_at)

                                                <div class="mt-2">

                                                    <span class="w-2.5 h-2.5 rounded-full bg-blue-500 block animate-pulse">
                                                    </span>

                                                </div>

                                            @endif

                                        </div>

                                    </a>

                    @empty

                        <div class="px-4 py-8 text-center text-gray-500">

                            <p class="text-sm">
                                No notifications yet
                            </p>

                        </div>

                    @endforelse

                </div>


                {{-- View All --}}
                <div class="px-4 py-3 border-t border-gray-200 text-center">

                    <a href="{{ route('vendor.notifications.index') }}"
                        class="text-sm text-blue-600 hover:text-blue-800 font-medium">
                        View all notifications
                    </a>

                </div>

            </div>

        </div>


        {{-- ========================================= --}}
        {{-- USER --}}
        {{-- ========================================= --}}

        <div class="relative">

            <button type="button" class="hidden lg:flex items-center space-x-3 focus:outline-none">

                <span class="text-sm font-medium text-gray-700">
                    {{ auth()->user()->name }}
                </span>

            </button>


            <div id="vendorUserDropdown"
                class="absolute right-0 mt-2 w-64 bg-white border border-gray-200 rounded-lg shadow-lg hidden z-50">

                <a href="javascript:void(0)"
                    class="flex items-center gap-3 px-4 py-3 text-sm text-gray-700 hover:bg-gray-50">

                    <svg class="w-4 h-4 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">

                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v2a2 2 0 002 2h12a2 2
                            0 002-2v-2M7 10l5 5 5-5M12 15V3" />

                    </svg>

                    <span>
                        Download Vendor App
                    </span>

                </a>


                <a href="javascript:void(0)"
                    class="flex items-center gap-3 px-4 py-3 text-sm text-gray-700 hover:bg-gray-50">

                    <svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">

                        <path d="M3 7.00063V4.37063C3.00781
                            3.85366 3.11755 3.34332
                            3.32294 2.86883C3.52832
                            2.39435 3.82532 1.96506
                            4.1969 1.60555C4.56848
                            1.24605 5.00735 0.963395
                            5.48836 0.773792C5.96936
                            0.58419 6.48306 0.491363
                            7 0.500632C7.51694
                            0.491363 8.03064 0.58419
                            8.51164 0.773792C8.99265
                            0.963395 9.43152 1.24605
                            9.8031 1.60555C10.1747
                            1.96506 10.4717 2.39435
                            10.6771 2.86883C10.8824
                            3.34332 10.9922 3.85366
                            11 4.37063V7.00063M9
                            12.2506C9.53043 12.2506
                            10.0391 12.0399 10.4142
                            11.6648C10.7893 11.2898
                            11 10.7811 11 10.2506V8.00063M9
                            12.2506C9 12.5822 8.8683
                            12.9001 8.63388 13.1345C8.39946
                            13.3689 8.08152 13.5006 7.75
                            13.5006H6.25C5.91848
                            13.5006 5.60054 13.3689
                            5.36612 13.1345C5.1317
                            12.9001 5 12.5822 5
                            12.2506C5 11.9191 5.1317
                            11.6012 5.36612 11.3667C5.60054
                            11.1323 5.91848 11.0006 6.25
                            11.0006H7.75C8.08152 11.0006
                            8.8683 11.9191 9 12.2506ZM1.5
                            5.50063H2.5C2.63261 5.50063 2.75979
                            5.55331 2.85355 5.64708C2.94732
                            5.74085 3 5.86802 3 6.00063V9.00063
                            C3 9.13324 2.94732 9.26042 2.85355
                            9.35419C2.75979 9.44795 2.63261 9.50063
                            2.5 9.50063H1.5C1.23478 9.50063
                            0.98043 9.39527 0.792893 9.20774C0.605357
                            9.0202 0.5 8.76585 0.5 8.50063V6.50063C0.5
                            6.23541 0.605357 5.98106 0.792893
                            5.79352C0.98043 5.60599 1.23478
                            5.50063 1.5 5.50063ZM12.5
                            9.50063H11.5C11.3674 9.50063 11.2402
                            9.44795 11.1464 9.35419C11.0527 9.26042
                            11 9.13324 11 9.00063V6.00063C11
                            5.86802 11.0527 5.74085 11.1464
                            5.64708C11.2402 5.55331 11.3674 5.50063
                            11.5 5.50063H12.5C12.7652 5.50063
                            13.0196 5.60599 13.2071 5.79352C13.3946
                            5.98106 13.5 6.23541 13.5 6.50063V8.50063
                            C13.5 8.76585 13.3946 9.0202 13.2071
                            9.20774C13.0196 9.39527 12.7652
                            9.50063 12.5 9.50063Z" stroke="black" stroke-linecap="round" stroke-linejoin="round" />

                    </svg>

                    <span>
                        Contact Customer Support
                    </span>

                </a>

            </div>

        </div>


        {{-- Mobile Menu --}}
        <button id="vendor-mobile-menu-btn" class="block lg:hidden p-2 text-gray-600 hover:bg-gray-100 rounded-md">
            ☰
        </button>

    </div>

</header>


{{-- ================================================= --}}
{{-- VENDOR NOTIFICATION JAVASCRIPT --}}
{{-- ================================================= --}}

<script>

    function notificationDropdown() {

        return {

            open: false,

            unreadCount:
            {{ auth()->user()->unreadNotifications->count() ?? 0 }},

            lastUnreadCount:
            {{ auth()->user()->unreadNotifications->count() ?? 0 }},

            notificationSound: null,

            soundUnlocked: false,


            // =============================================
            // INIT
            // =============================================

            init() {

                // Notification sound
                this.notificationSound = new Audio(
                    "{{ asset('sounds/beep.mp3') }}"
                );

                this.notificationSound.preload = 'auto';
                this.notificationSound.volume = 1.0;


                // =========================================
                // FIRST USER INTERACTION
                // =========================================

                const unlockOnce = () => {

                    this.unlockSound();

                };


                document.addEventListener(
                    'click',
                    unlockOnce,
                    {
                        once: true,
                        passive: true
                    }
                );


                document.addEventListener(
                    'touchstart',
                    unlockOnce,
                    {
                        once: true,
                        passive: true
                    }
                );


                // =========================================
                // INITIAL CHECK
                // =========================================

                this.checkNotifications();


                // =========================================
                // CHECK EVERY 10 SECONDS
                // =========================================

                setInterval(() => {

                    this.checkNotifications();

                }, 10000);

            },


            // =============================================
            // UNLOCK SOUND
            // =============================================

            unlockSound() {

                if (this.soundUnlocked) {
                    return;
                }

                if (!this.notificationSound) {
                    return;
                }


                this.notificationSound.muted = true;

                this.notificationSound.currentTime = 0;


                const playPromise =
                    this.notificationSound.play();


                if (playPromise !== undefined) {

                    playPromise
                        .then(() => {

                            this.notificationSound.pause();

                            this.notificationSound.currentTime = 0;

                            this.notificationSound.muted = false;

                            this.soundUnlocked = true;

                            console.log(
                                '🔓 Vendor notification sound unlocked'
                            );

                        })
                        .catch(error => {

                            console.log(
                                '❌ Vendor audio unlock failed:',
                                error
                            );

                        });

                }

            },


            // =============================================
            // CHECK NOTIFICATIONS
            // =============================================

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


                    const data =
                        await response.json();


                    const newCount =
                        parseInt(
                            data.unread_count ?? 0,
                            10
                        );


                    // =====================================
                    // NEW NOTIFICATION
                    // =====================================

                    if (newCount > this.lastUnreadCount) {

                        console.log(
                            '🔔 Vendor new notification received'
                        );


                        // Update badge
                        this.unreadCount =
                            newCount;


                        // Play sound
                        this.playNotificationSound();


                        // Refresh list
                        await this.loadNotifications();

                    }

                    else {

                        // Keep badge synced
                        this.unreadCount =
                            newCount;

                    }


                    this.lastUnreadCount =
                        newCount;


                }
                catch (error) {

                    console.error(
                        '❌ Vendor notification check failed:',
                        error
                    );

                }

            },


            // =============================================
            // PLAY NOTIFICATION SOUND
            // =============================================

            playNotificationSound() {

                if (!this.soundUnlocked) {

                    console.log(
                        '🔒 Vendor notification sound is not unlocked yet'
                    );

                    return;

                }


                const audio =
                    new Audio(
                        "{{ asset('sounds/beep.mp3') }}"
                    );


                audio.volume = 1.0;

                audio.currentTime = 0;


                audio.play()

                    .then(() => {

                        console.log(
                            '🔔🔊 VENDOR NEW NOTIFICATION SOUND PLAYED'
                        );

                    })

                    .catch(error => {

                        console.error(
                            '❌ Vendor notification sound blocked:',
                            error
                        );

                    });

            },


            // =============================================
            // LOAD NOTIFICATIONS
            // =============================================

            async loadNotifications() {

                try {

                    const response =
                        await fetch(
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


                    const result =
                        await response.json();


                    const notifications =
                        result.data ?? [];


                    this.renderNotifications(
                        notifications
                    );

                }
                catch (error) {

                    console.error(
                        '❌ Vendor notification loading failed:',
                        error
                    );

                }

            },


            // =============================================
            // RENDER NOTIFICATIONS
            // =============================================

            renderNotifications(notifications) {

                const container =
                    document.getElementById(
                        'vendorNotificationList'
                    );


                if (!container) {
                    return;
                }


                // No notifications
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


                container.innerHTML =
                    notifications
                        .slice(0, 5)
                        .map(notification => {

                            const data =
                                notification.data || {};


                            const title =
                                data.title ||
                                'New Notification';


                            const message =
                                data.message ||
                                '';


                            const createdAt =
                                notification.created_at ||
                                '';


                            const isUnread =
                                !notification.read_at;


                            return `

                            <a
                                href="/notifications/open/${notification.id}"
                                class="group block px-4 py-3 border-b border-gray-100 transition-all duration-200
                                ${isUnread
                                    ? 'bg-blue-50/70'
                                    : 'bg-white'}
                                hover:bg-gray-50"
                            >

                                <div class="flex gap-3 items-start">

                                    <div class="mt-1">

                                        <div
                                            class="w-9 h-9 rounded-full flex items-center justify-center
                                            ${isUnread
                                    ? 'bg-blue-100 text-blue-600'
                                    : 'bg-gray-100 text-gray-500'}"
                                        >

                                            <svg
                                                xmlns="http://www.w3.org/2000/svg"
                                                class="w-5 h-5"
                                                fill="none"
                                                viewBox="0 0 24 24"
                                                stroke="currentColor"
                                                stroke-width="1.8"
                                            >

                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    d="M15 17h5l-1.4-1.4A2.032
                                                    2.032 0 0118 14.2V11a6
                                                    6 0 10-12 0v3.2c0 .53-.21
                                                    1.04-.6 1.42L4 17h5m6 0a3
                                                    3 0 11-6 0"
                                                />

                                            </svg>

                                        </div>

                                    </div>


                                    <div class="flex-1 min-w-0">

                                        <p
                                            class="text-sm font-semibold
                                            ${isUnread
                                    ? 'text-blue-900'
                                    : 'text-gray-800'}"
                                        >
                                            ${title}
                                        </p>


                                        <p
                                            class="text-xs text-gray-600 mt-1 leading-relaxed line-clamp-2"
                                        >
                                            ${message}
                                        </p>


                                        <div
                                            class="flex items-center gap-1.5 mt-2 text-[11px] text-blue-400"
                                        >

                                            <span>
                                                ${createdAt}
                                            </span>

                                        </div>

                                    </div>


                                    ${isUnread
                                    ? `
                                                <div class="mt-2">

                                                    <span
                                                        class="w-2.5 h-2.5 rounded-full bg-blue-500 block animate-pulse"
                                                    >
                                                    </span>

                                                </div>
                                            `
                                    : ''
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

    document.addEventListener(
        'DOMContentLoaded',
        function () {

            const btn =
                document.getElementById(
                    'vendorUserDropdownBtn'
                );

            const menu =
                document.getElementById(
                    'vendorUserDropdown'
                );


            if (!btn || !menu) {
                return;
            }


            btn.addEventListener(
                'click',
                function (e) {

                    e.preventDefault();

                    e.stopPropagation();

                    menu.classList.toggle(
                        'hidden'
                    );

                }
            );


            document.addEventListener(
                'click',
                function () {

                    menu.classList.add(
                        'hidden'
                    );

                }
            );

        }
    );

</script>