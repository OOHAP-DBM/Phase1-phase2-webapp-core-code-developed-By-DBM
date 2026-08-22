@extends('layouts.vendor')

@section('title', 'Get Started')

@section('content')

    <div class="min-h-[calc(100vh-80px)] bg-gray-50 flex items-center justify-center px-4 py-10">

        <div class="w-full max-w-5xl">

            {{-- Heading --}}
            <div class="text-center mb-10">

                <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-[#009A5C]/10 mb-5">
                    <svg class="w-8 h-8 text-[#009A5C]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 3v18m9-9H3" />
                    </svg>
                </div>

                <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">
                    Welcome to OOHAPP
                </h1>

                <p class="mt-3 text-sm sm:text-base text-gray-500 max-w-xl mx-auto">
                    Let's get your inventory started. Choose how you want to
                    add your hoardings to OOHAPP.
                </p>

            </div>


            {{-- Action Cards --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 max-w-4xl mx-auto">

                {{-- Import Inventory --}}
                {{-- Import Inventory --}}
                <a href="{{ url('/vendor/import') }}" class="group bg-white border-2 border-gray-200 rounded-2xl p-8
                   hover:border-[#009A5C] hover:shadow-xl
                   transition-all duration-300">

                    <div class="w-16 h-16 rounded-xl bg-[#009A5C]/10
                                       flex items-center justify-center mb-6
                                       group-hover:bg-[#009A5C] transition-all duration-300">

                        <svg class="w-8 h-8 text-[#009A5C] group-hover:text-white transition-colors" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                d="M12 16V4m0 0l-4 4m4-4l4 4M5 20h14" />
                        </svg>

                    </div>

                    <h2 class="text-xl font-bold text-gray-900">
                        Import Inventory
                    </h2>

                    <p class="mt-2 text-sm text-gray-500 leading-relaxed">
                        Upload your existing hoarding inventory in bulk
                        and get started quickly.
                    </p>

                    <div class="mt-6 inline-flex items-center gap-2
                                       text-sm font-semibold text-[#009A5C]">
                        Import Inventory

                        <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>

                    </div>

                </a>


                {{-- Add Hoardings --}}
                <a href="{{ url('/vendor/hoardings/add') }}" class="group bg-white border-2 border-gray-200 rounded-2xl p-8
               hover:border-[#009A5C] hover:shadow-xl
               transition-all duration-300">

                    <div class="w-16 h-16 rounded-xl bg-[#009A5C]/10
                                       flex items-center justify-center mb-6
                                       group-hover:bg-[#009A5C] transition-all duration-300">

                        <svg class="w-8 h-8 text-[#009A5C] group-hover:text-white transition-colors" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 5v14M5 12h14" />
                        </svg>

                    </div>

                    <h2 class="text-xl font-bold text-gray-900">
                        Add Hoardings
                    </h2>

                    <p class="mt-2 text-sm text-gray-500 leading-relaxed">
                        Add your hoardings manually one by one with
                        complete details.
                    </p>

                    <div class="mt-6 inline-flex items-center gap-2
                                       text-sm font-semibold text-[#009A5C]">
                        Add Hoardings

                        <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>

                    </div>

                </a>

            </div>


            {{-- Bottom Information --}}
            <div class="text-center mt-8">

                <p class="text-xs text-gray-400">
                    You can manage and update your inventory anytime
                    from your vendor dashboard.
                </p>

            </div>

        </div>

    </div>

@endsection