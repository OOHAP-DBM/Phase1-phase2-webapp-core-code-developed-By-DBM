@extends('layouts.customer')

@section('title', 'Session Logs')

@section('content')

    <div class="p-6 space-y-6">

        {{-- Header --}}
        <div>
            <h1 class="text-2xl font-bold text-gray-900">
                Session Logs
            </h1>

            <p class="mt-1 text-sm text-gray-500">
                Track your login, logout and session activity.
            </p>
        </div>

        {{-- Session Logs Table --}}
        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">

            <div class="overflow-x-auto">

                <table class="min-w-full text-sm">

                    {{-- Table Header --}}
                    <thead class="bg-gray-50 border-b border-gray-200">

                        <tr>

                            <th class="px-5 py-3 text-left font-semibold text-gray-600">
                                Date
                            </th>

                            <th class="px-5 py-3 text-left font-semibold text-gray-600">
                                Action
                            </th>

                            <th class="px-5 py-3 text-left font-semibold text-gray-600">
                                Login
                            </th>

                            <th class="px-5 py-3 text-left font-semibold text-gray-600">
                                Logout
                            </th>

                            <th class="px-5 py-3 text-left font-semibold text-gray-600">
                                IP Address
                            </th>

                            <th class="px-5 py-3 text-left font-semibold text-gray-600  ">
                                Browser
                            </th>

                        </tr>

                    </thead>

                    {{-- Table Body --}}
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

                                            <span class="mr-1.5 h-1.5 w-1.5 rounded-full bg-emerald-500"></span>

                                            Login

                                        </span>

                                    @elseif($action === 'logout')

                                        <span
                                            class="inline-flex items-center rounded-full bg-red-50 px-2.5 py-1 text-xs font-semibold text-red-700">

                                            <span class="mr-1.5 h-1.5 w-1.5 rounded-full bg-red-500"></span>

                                            Logout

                                        </span>

                                    @elseif($action === 'session_expired')

                                        <span
                                            class="inline-flex items-center rounded-full bg-amber-50 px-2.5 py-1 text-xs font-semibold text-amber-700">

                                            <span class="mr-1.5 h-1.5 w-1.5 rounded-full bg-amber-500"></span>

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

                                    {{ optional($log->login_at)->format('d M Y, h:i A') ?: '-' }}

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

                                            <span class="mr-1.5 h-1.5 w-1.5 rounded-full bg-emerald-500">
                                            </span>

                                            Currently Active

                                        </span>

                                    @endif

                                </td>

                                
                                <td class="px-5 py-4 text-gray-600">
                                    {{ $log->ip_address ?? '-' }}
                                </td>

                                <td class="px-5 py-4  w-[180px] max-w-[180px]">
                                    <div class="flex flex-col items-end gap-1 min-w-0">

                                        <span class="block w-full truncate text-sm font-medium text-gray-700"
                                            title="{{ $log->user_agent ?? 'Unknown Device' }}">
                                            {{ $log->user_agent ?? 'Unknown Device' }}
                                        </span>

                                        <span class="block w-full truncate text-xs text-gray-400"
                                            title="{{ ($log->platform ?? '-') . ($log->user_agent ? ' • ' . $log->user_agent : '') }}">
                                            {{ $log->platform ?? '-' }}

                                            @if($log->user_agent)
                                                • {{ $log->user_agent }}
                                            @endif
                                        </span>

                                    </div>
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
                                            No session history found.
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