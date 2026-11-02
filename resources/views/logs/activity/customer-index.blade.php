@extends('layouts.customer')

@section('title', 'Activity Logs')

@section('content')

    <div class="p-6 space-y-6">

        {{-- Header --}}
        <div>
            <h1 class="text-2xl font-bold text-gray-900">
                Activity Logs
            </h1>

            <p class="mt-1 text-sm text-gray-500">
                Track your account activity and recent actions.
            </p>
        </div>

        {{-- Activity Logs Table --}}
        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">

            <div class="overflow-x-auto">

                <table class="min-w-full text-sm">

                    {{-- Table Header --}}
                    <thead class="bg-gray-50 border-b border-gray-200">

                        <tr>

                            <th class="px-5 py-3 text-left font-semibold text-gray-600 whitespace-nowrap">
                                Date
                            </th>

                            <th class="px-5 py-3 text-left font-semibold text-gray-600 whitespace-nowrap">
                                Action
                            </th>

                            <th class="px-5 py-3 text-left font-semibold text-gray-600 whitespace-nowrap">
                                Module
                            </th>

                            <th class="px-5 py-3 text-left font-semibold text-gray-600">
                                Description
                            </th>

                            <th class="px-5 py-3 text-left font-semibold text-gray-600 whitespace-nowrap">
                                IP Address
                            </th>

                            <th class="px-5 py-3 text-left font-semibold text-gray-600 w-[180px]">
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
                                        $action = $log->action ?? 'unknown';

                                        $actionLabel = ucfirst(
                                            str_replace('_', ' ', $action)
                                        );
                                    @endphp

                                    @if(
                                            in_array($action, [
                                                'login',
                                                'otp_verified',
                                                'email_verified',
                                                'phone_verified'
                                            ])
                                        )

                                        <span
                                            class="inline-flex items-center rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700">

                                            <span class="mr-1.5 h-1.5 w-1.5 rounded-full bg-emerald-500">
                                            </span>

                                            {{ $actionLabel }}

                                        </span>

                                    @elseif(
                                            in_array($action, [
                                                'logout',
                                                'otp_failed',
                                                'login_failed'
                                            ])
                                        )

                                        <span
                                            class="inline-flex items-center rounded-full bg-red-50 px-2.5 py-1 text-xs font-semibold text-red-700">

                                            <span class="mr-1.5 h-1.5 w-1.5 rounded-full bg-red-500">
                                            </span>

                                            {{ $actionLabel }}

                                        </span>

                                    @elseif(str_contains($action, 'failed'))

                                        <span
                                            class="inline-flex items-center rounded-full bg-red-50 px-2.5 py-1 text-xs font-semibold text-red-700">

                                            <span class="mr-1.5 h-1.5 w-1.5 rounded-full bg-red-500">
                                            </span>

                                            {{ $actionLabel }}

                                        </span>

                                    @elseif(str_contains($action, 'created') || str_contains($action, 'updated'))

                                        <span
                                            class="inline-flex items-center rounded-full bg-blue-50 px-2.5 py-1 text-xs font-semibold text-blue-700">

                                            <span class="mr-1.5 h-1.5 w-1.5 rounded-full bg-blue-500">
                                            </span>

                                            {{ $actionLabel }}

                                        </span>

                                    @else

                                        <span
                                            class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-1 text-xs font-semibold text-gray-600">

                                            {{ $actionLabel }}

                                        </span>

                                    @endif

                                </td>

                                {{-- Module --}}
                                <td class="px-5 py-4 whitespace-nowrap">

                                    @if($log->module)

                                        <span
                                            class="inline-flex items-center rounded-md bg-gray-50 border border-gray-200 px-2.5 py-1 text-xs font-medium text-gray-600">

                                            {{ ucfirst(str_replace('_', ' ', $log->module)) }}

                                        </span>

                                    @else

                                        <span class="text-gray-400">
                                            -
                                        </span>

                                    @endif

                                </td>

                                {{-- Description --}}
                                <td class="px-5 py-4">

                                    <div class="max-w-[420px] text-gray-700" title="{{ $log->description ?? '' }}">

                                        <span class="block truncate">
                                            {{ $log->description ?? 'No description available' }}
                                        </span>

                                    </div>

                                </td>

                                {{-- IP Address --}}
                                <td class="px-5 py-4 whitespace-nowrap text-gray-600">

                                    {{ $log->ip_address ?? '-' }}

                                </td>

                                {{-- Browser --}}
                                <td class="px-5 py-4 w-[180px] max-w-[180px]">

                                    <div class="flex flex-col gap-1 min-w-0">

                                        <span class="block w-full truncate text-sm font-medium text-gray-700"
                                            title="{{ $log->user_agent ?? 'Unknown Device' }}">

                                            {{ $log->user_agent ?? 'Unknown Device' }}

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
                                            No activity history found.
                                        </span>

                                    </div>

                                </td>

                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>


            @if($logs->hasPages())

                <div class="border-t border-gray-200 px-5 py-4">
                    {{ $logs->withQueryString()->links() }}
                </div>

            @endif



        </div>

    </div>

@endsection