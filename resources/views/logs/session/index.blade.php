@extends('layouts.admin')

@section('title', 'Session Logs')

@section('content')
    <div class="p-6 space-y-6">

        <div>
            <h1 class="text-2xl font-bold text-gray-900">
                Session Logs
            </h1>

            <p class="mt-1 text-sm text-gray-500">
                Track login, logout and session activity.
            </p>
        </div>

        <div class="bg-white border border-gray-200 rounded-xl p-4">

            <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">

                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search user..."
                    class="rounded-lg border-gray-300 text-sm">

                <select name="action" class="rounded-lg border-gray-300 text-sm">
                    <option value="">All Actions</option>

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

                <input type="date" name="from_date" value="{{ request('from_date') }}"
                    class="rounded-lg border-gray-300 text-sm">

                <button type="submit" class="rounded-lg bg-[#00995c] px-5 py-2 text-sm font-semibold text-white">
                    Filter
                </button>

            </form>

        </div>

        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white">

            <div class="overflow-x-auto">

                <table class="min-w-full text-sm">

                    <thead class="bg-gray-50 border-b border-gray-200">

                        <tr>
                            <th class="px-5 py-3 text-left font-semibold text-gray-600">Date</th>
                            <th class="px-5 py-3 text-left font-semibold text-gray-600">User</th>
                            <th class="px-5 py-3 text-left font-semibold text-gray-600">Role</th>
                            <th class="px-5 py-3 text-left font-semibold text-gray-600">Action</th>
                            <th class="px-5 py-3 text-left font-semibold text-gray-600">IP Address</th>
                            <th class="px-5 py-3 text-left font-semibold text-gray-600">Session</th>
                        </tr>

                    </thead>

                    <tbody class="divide-y divide-gray-100">

                        @forelse($logs as $log)

                            <tr class="hover:bg-gray-50">

                                <td class="px-5 py-4 whitespace-nowrap text-gray-500">
                                    {{ optional($log->created_at)->format('d M Y, h:i A') }}
                                </td>

                                <td class="px-5 py-4 font-medium text-gray-900">
                                    {{ $log->user?->name ?? 'System' }}
                                </td>

                                <td class="px-5 py-4">
                                    <span class="rounded-full bg-gray-100 px-2.5 py-1 text-xs font-medium text-gray-700">
                                        {{ ucfirst($log->user_role ?? 'system') }}
                                    </span>
                                </td>

                                <td class="px-5 py-4">
                                    <span class="font-medium text-gray-800">
                                        {{ ucfirst(str_replace('_', ' ', $log->event)) }}
                                    </span>
                                </td>

                                <td class="px-5 py-4 text-gray-600">
                                    {{ $log->ip_address ?? '-' }}
                                </td>

                                <td class="px-5 py-4 text-xs text-gray-500">
                                    {{ $log->session_id ?? '-' }}
                                </td>

                            </tr>

                        @empty

                            <tr>
                                <td colspan="6" class="px-5 py-12 text-center text-gray-400">
                                    No session logs found.
                                </td>
                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

            @if(method_exists($logs, 'links'))
                <div class="border-t border-gray-200 px-5 py-4">
                    {{ $logs->withQueryString()->links() }}
                </div>
            @endif

        </div>

    </div>
@endsection