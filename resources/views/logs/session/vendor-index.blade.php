@extends('layouts.vendor')

@section('title', 'Session Logs')

@section('content')

    <div class="p-4 md:p-6 space-y-6">

        {{-- Header --}}
        <div>
            <h1 class="text-2xl font-bold text-gray-900">
                Session Logs
            </h1>

            <p class="mt-1 text-sm text-gray-500">
                Track your login, logout and session activity.
            </p>
        </div>


        {{-- Filters --}}
        <div class="bg-white border border-gray-200 rounded-xl p-4">

            <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">

                {{-- Action --}}
                <select name="action"
                    class="rounded-lg border-gray-300 text-sm focus:border-[#00995c] focus:ring-[#00995c]">

                    <option value="">
                        All Actions
                    </option>

                    <option value="login" @selected(request('action') === 'login')>
                        Login
                    </option>

                    <option value="logout" @selected(request('action') === 'logout')>
                        Logout
                    </option>

                    <option value="session_expired" @selected(request('action') === 'session_expired')>
                        Session Expired
                    </option>

                </select>


                {{-- From Date --}}
                <input type="date" name="from_date" value="{{ request('from_date') }}"
                    class="rounded-lg border-gray-300 text-sm focus:border-[#00995c] focus:ring-[#00995c]">


                {{-- To Date --}}
                <input type="date" name="to_date" value="{{ request('to_date') }}"
                    class="rounded-lg border-gray-300 text-sm focus:border-[#00995c] focus:ring-[#00995c]">


                {{-- Buttons --}}
                <div class="flex gap-2">

                    <button type="submit"
                        class="flex-1 rounded-lg bg-[#00995c] px-5 py-2 text-sm font-semibold text-white hover:bg-[#00834f] transition">
                        Filter
                    </button>

                    <a href="{{ route('vendor.logs.session.index') }}"
                        class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50 transition">
                        Reset
                    </a>

                </div>

            </form>

        </div>


        {{-- Session Logs Table --}}
        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white">

            <div class="overflow-x-auto">

                <table class="min-w-full text-sm">

                    <thead class="bg-gray-50 border-b border-gray-200">

                        <tr>

                            <th class="px-5 py-3 text-left font-semibold text-gray-600">
                                Date
                            </th>

                            <th class="px-5 py-3 text-left font-semibold text-gray-600">
                                Action
                            </th>

                            <th class="px-5 py-3 text-left font-semibold text-gray-600">
                                Login At
                            </th>

                            <th class="px-5 py-3 text-left font-semibold text-gray-600">
                                Logout At
                            </th>

                            <th class="px-5 py-3 text-left font-semibold text-gray-600">
                                IP Address
                            </th>

                            <th class="px-5 py-3 text-left font-semibold text-gray-600">
                                Session
                            </th>

                        </tr>

                    </thead>


                    <tbody class="divide-y divide-gray-100">

                        @forelse($logs as $log)

                            <tr class="hover:bg-gray-50 transition">

                                {{-- Date --}}
                                <td class="px-5 py-4 whitespace-nowrap text-gray-500">

                                    {{ optional($log->created_at)->format('d M Y, h:i A') }}

                                </td>


                                {{-- Action --}}
                                <td class="px-5 py-4">

                                    @php
                                        $action = $log->event ?? 'unknown';
                                    @endphp

                                    @if($action === 'login')

                                        <span
                                            class="inline-flex items-center rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700">
                                            Login
                                        </span>

                                    @elseif($action === 'logout')

                                        <span
                                            class="inline-flex items-center rounded-full bg-red-50 px-2.5 py-1 text-xs font-semibold text-red-700">
                                            Logout
                                        </span>

                                    @elseif($action === 'session_expired')

                                        <span
                                            class="inline-flex items-center rounded-full bg-amber-50 px-2.5 py-1 text-xs font-semibold text-amber-700">
                                            Session Expired
                                        </span>

                                    @else

                                        <span
                                            class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-1 text-xs font-semibold text-gray-600">
                                            {{ ucfirst(str_replace('_', ' ', $action)) }}
                                        </span>

                                    @endif

                                </td>


                                {{-- Login --}}
                                <td class="px-5 py-4 whitespace-nowrap text-gray-600">

                                    {{ optional($log->login_at)->format('d M Y, h:i A') ?? '-' }}

                                </td>

 
                                {{-- Logout --}}
                                <td class="px-5 py-4 whitespace-nowrap">
                                    @if($log->logout_at)
                                        <span class="text-gray-600">
                                            {{ $log->logout_at->format('d M Y, h:i A') }}
                                        </span>
                                    @else
                                        <span
                                            class="inline-flex items-center rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700">
                                            <span class="mr-1.5 h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                            Currently Active
                                        </span>
                                    @endif
                                </td>


                                {{-- IP --}}
                                <td class="px-5 py-4 text-gray-600">

                                    {{ $log->ip_address ?? '-' }}

                                </td>


                                {{-- Session --}}
                                <td class="px-5 py-4">

                                    <span class="block max-w-[220px] truncate text-xs text-gray-500"
                                        title="{{ $log->session_id }}">
                                        {{ $log->session_id ?? '-' }}
                                    </span>

                                </td>

                            </tr>

                        @empty

                            <tr>

                                <td colspan="6" class="px-5 py-12 text-center text-gray-400">

                                    <div class="flex flex-col items-center gap-2">

                                        <svg class="w-10 h-10 text-gray-300" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l4.414 4.414A1 1 0 0118 8.414V19a2 2 0 01-2 2z" />
                                        </svg>

                                        <span>
                                            No session logs found.
                                        </span>

                                    </div>

                                </td>

                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>


            {{-- Pagination --}}
            @if($logs->hasPages())

                <div class="border-t border-gray-200 px-5 py-4">

                    {{ $logs->withQueryString()->links() }}

                </div>

            @endif

        </div>

    </div>

@endsection