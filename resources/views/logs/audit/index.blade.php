@extends('layouts.admin')

@section('title', 'Audit Logs')

@section('content')
<div class="p-6 space-y-6">

    <div>
        <h1 class="text-2xl font-bold text-gray-900">Audit Logs</h1>
        <p class="mt-1 text-sm text-gray-500">
            Detailed record of important system and user changes.
        </p>
    </div>

    <div class="bg-white border border-gray-200 rounded-xl p-4">

        <form method="GET" class="grid grid-cols-1 md:grid-cols-5 gap-4">

            <input
                type="text"
                name="search"
                value="{{ request('search') }}"
                placeholder="Search logs..."
                class="rounded-lg border-gray-300 text-sm"
            >

            <select name="action" class="rounded-lg border-gray-300 text-sm">
                <option value="">All Actions</option>

                @foreach($actions ?? [] as $action)
                    <option value="{{ $action }}" @selected(request('action') === $action)>
                        {{ ucfirst(str_replace('_', ' ', $action)) }}
                    </option>
                @endforeach
            </select>

            <select name="module" class="rounded-lg border-gray-300 text-sm">
                <option value="">All Modules</option>

                @foreach($modules ?? [] as $module)
                    <option value="{{ $module }}" @selected(request('module') === $module)>
                        {{ ucfirst($module) }}
                    </option>
                @endforeach
            </select>

            <input
                type="date"
                name="from_date"
                value="{{ request('from_date') }}"
                class="rounded-lg border-gray-300 text-sm"
            >

            <button
                type="submit"
                class="rounded-lg bg-[#00995c] px-5 py-2 text-sm font-semibold text-white"
            >
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
                        <th class="px-5 py-3 text-left font-semibold text-gray-600">Action</th>
                        <th class="px-5 py-3 text-left font-semibold text-gray-600">Module</th>
                        <th class="px-5 py-3 text-left font-semibold text-gray-600">Description</th>
                        <th class="px-5 py-3 text-right font-semibold text-gray-600">View</th>
                    </tr>

                </thead>

                <tbody class="divide-y divide-gray-100">

                    @forelse($logs as $log)

                        <tr class="hover:bg-gray-50">

                            <td class="px-5 py-4 whitespace-nowrap text-gray-500">
                                {{ optional($log->created_at)->format('d M Y, h:i A') }}
                            </td>

                            <td class="px-5 py-4">
                                <div class="font-medium text-gray-900">
                                    {{ $log->user_name ?? 'System' }}
                                </div>

                                @if($log->user_email)
                                    <div class="text-xs text-gray-400">
                                        {{ $log->user_email }}
                                    </div>
                                @endif
                            </td>

                            <td class="px-5 py-4">
                                <span class="rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-medium text-emerald-700">
                                    {{ $log->action_label }}
                                </span>
                            </td>

                            <td class="px-5 py-4 text-gray-600">
                                {{ $log->module ?? '-' }}
                            </td>

                            <td class="px-5 py-4 text-gray-600 max-w-md">
                                {{ $log->description ?? '-' }}
                            </td>

                            <td class="px-5 py-4 text-right">

                                <a
                                    href="{{ route('admin.logs.audit.show', $log) }}"
                                    class="text-sm font-semibold text-[#00995c] hover:underline"
                                >
                                    View
                                </a>

                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td colspan="6" class="px-5 py-12 text-center text-gray-400">
                                No audit logs found.
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