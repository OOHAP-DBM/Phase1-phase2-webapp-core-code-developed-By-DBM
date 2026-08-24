@extends('layouts.vendor')

@section('title', 'Activity Logs')

@section('content')
    <div class="p-4 md:p-6 space-y-6">

        {{-- Header --}}
        <div>
            <h1 class="text-2xl font-bold text-gray-900">
                Activity Logs
            </h1>

            <p class="mt-1 text-sm text-gray-500">
                Track your account activities and actions performed across the system.
            </p>
        </div>

        {{-- Filters --}}
        <div class="bg-white border border-gray-200 rounded-xl p-4">

            <form method="GET"
                class="grid grid-cols-1 md:grid-cols-4 lg:grid-cols-5 gap-4">

                {{-- Action --}}
                <select name="action"
                    class="rounded-lg border-gray-300 text-sm focus:border-[#00995c] focus:ring-[#00995c]">

                    <option value="">
                        All Actions
                    </option>

                    @foreach ($actions as $action)
                        <option value="{{ $action }}"
                            @selected(request('action') === $action)>
                            {{ ucfirst(str_replace('_', ' ', $action)) }}
                        </option>
                    @endforeach

                </select>

                {{-- Module --}}
                <select name="module"
                    class="rounded-lg border-gray-300 text-sm focus:border-[#00995c] focus:ring-[#00995c]">

                    <option value="">
                        All Modules
                    </option>

                    @foreach ($modules as $module)
                        <option value="{{ $module }}"
                            @selected(request('module') === $module)>
                            {{ ucfirst(str_replace('_', ' ', $module)) }}
                        </option>
                    @endforeach

                </select>

                {{-- From Date --}}
                <input
                    type="date"
                    name="from"
                    value="{{ request('from') }}"
                    class="rounded-lg border-gray-300 text-sm focus:border-[#00995c] focus:ring-[#00995c]"
                >

                {{-- To Date --}}
                <input
                    type="date"
                    name="to"
                    value="{{ request('to') }}"
                    class="rounded-lg border-gray-300 text-sm focus:border-[#00995c] focus:ring-[#00995c]"
                >

                {{-- Buttons --}}
                <div class="flex gap-2">

                    <button
                        type="submit"
                        class="flex-1 rounded-lg bg-[#00995c] px-5 py-2 text-sm font-semibold text-white hover:bg-[#00834f] transition">
                        Filter
                    </button>

                    <a
                        href="{{ route('vendor.logs.activity.index') }}"
                        class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50 transition">
                        Reset
                    </a>

                </div>

            </form>

        </div>


        {{-- Activity Logs Table --}}
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
                                Module
                            </th>

                            <th class="px-5 py-3 text-left font-semibold text-gray-600">
                                Description
                            </th>

                            <th class="px-5 py-3 text-left font-semibold text-gray-600">
                                IP Address
                            </th>

                            <th class="px-5 py-3 text-left font-semibold text-gray-600">
                                Device
                            </th>

                        </tr>

                    </thead>


                    <tbody class="divide-y divide-gray-100">

                        @forelse ($logs as $log)

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

                                    @if (
                                        str_contains(strtolower($action), 'failed') ||
                                        str_contains(strtolower($action), 'deleted') ||
                                        str_contains(strtolower($action), 'rejected')
                                    )

                                        <span class="inline-flex items-center rounded-full bg-red-50 px-2.5 py-1 text-xs font-semibold text-red-700">
                                            {{ $actionLabel }}
                                        </span>

                                    @elseif (
                                        str_contains(strtolower($action), 'created') ||
                                        str_contains(strtolower($action), 'success') ||
                                        str_contains(strtolower($action), 'verified') ||
                                        str_contains(strtolower($action), 'approved')
                                    )

                                        <span class="inline-flex items-center rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700">
                                            {{ $actionLabel }}
                                        </span>

                                    @elseif (
                                        str_contains(strtolower($action), 'updated') ||
                                        str_contains(strtolower($action), 'changed')
                                    )

                                        <span class="inline-flex items-center rounded-full bg-blue-50 px-2.5 py-1 text-xs font-semibold text-blue-700">
                                            {{ $actionLabel }}
                                        </span>

                                    @else

                                        <span class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-1 text-xs font-semibold text-gray-600">
                                            {{ $actionLabel }}
                                        </span>

                                    @endif

                                </td>


                                {{-- Module --}}
                                <td class="px-5 py-4 whitespace-nowrap">

                                    @if ($log->module)

                                        <span class="inline-flex items-center rounded-full bg-indigo-50 px-2.5 py-1 text-xs font-semibold text-indigo-700">
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

                                    <div class="max-w-[360px]">

                                        <p
                                            class="truncate text-sm text-gray-700"
                                            title="{{ $log->description ?? '-' }}"
                                        >
                                            {{ $log->description ?? '-' }}
                                        </p>

                                    </div>

                                </td>


                                {{-- IP Address --}}
                                <td class="px-5 py-4 whitespace-nowrap text-gray-600">

                                    {{ $log->ip_address ?? '-' }}

                                </td>


                                {{-- Device --}}
                                <td class="px-5 py-4 w-[220px] max-w-[220px]">

                                    <div class="flex flex-col min-w-0">

                                        <span
                                            class="block w-full truncate text-sm font-medium text-gray-700"
                                            title="{{ $log->user_agent ?? 'Unknown Device' }}"
                                        >
                                            {{ $log->user_agent ?? 'Unknown Device' }}
                                        </span>

                                        <span class="text-xs text-gray-400">
                                            {{ $log->user_role ?? '-' }}
                                        </span>

                                    </div>

                                </td>

                            </tr>

                        @empty

                            <tr>

                                <td
                                    colspan="6"
                                    class="px-5 py-12 text-center text-gray-400"
                                >

                                    <div class="flex flex-col items-center gap-2">

                                        <svg
                                            class="w-10 h-10 text-gray-300"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="1.5"
                                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l4.414 4.414A1 1 0 0118 8.414V19a2 2 0 01-2 2z"
                                            />
                                        </svg>

                                        <span>
                                            No activity logs found.
                                        </span>

                                    </div>

                                </td>

                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>


            {{-- Pagination --}}
            @if ($logs->hasPages())

                <div class="border-t border-gray-200 px-5 py-4">

                    {{ $logs->withQueryString()->links() }}

                </div>

            @endif

        </div>

    </div>
@endsection