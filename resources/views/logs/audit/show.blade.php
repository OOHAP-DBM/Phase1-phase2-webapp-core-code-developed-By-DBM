@extends('layouts.admin')

@section('title', 'Audit Log Details')

@section('content')
<div class="p-6 max-w-6xl mx-auto space-y-6">

    <div class="flex items-center justify-between">

        <div>
            <h1 class="text-2xl font-bold text-gray-900">
                Audit Log Details
            </h1>

            <p class="mt-1 text-sm text-gray-500">
                Detailed information about this system event.
            </p>
        </div>

        <a
            href="{{ url()->previous() }}"
            class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
        >
            Back
        </a>

    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <div class="lg:col-span-2 bg-white border border-gray-200 rounded-xl p-6">

            <h2 class="text-lg font-semibold text-gray-900 mb-5">
                Event Information
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                <div>
                    <p class="text-xs text-gray-400">Action</p>
                    <p class="mt-1 font-medium text-gray-900">
                        {{ $auditLog->action_label }}
                    </p>
                </div>

                <div>
                    <p class="text-xs text-gray-400">Module</p>
                    <p class="mt-1 font-medium text-gray-900">
                        {{ $auditLog->module ?? '-' }}
                    </p>
                </div>

                <div>
                    <p class="text-xs text-gray-400">User</p>
                    <p class="mt-1 font-medium text-gray-900">
                        {{ $auditLog->user_name ?? 'System' }}
                    </p>
                </div>

                <div>
                    <p class="text-xs text-gray-400">Email</p>
                    <p class="mt-1 text-gray-700">
                        {{ $auditLog->user_email ?? '-' }}
                    </p>
                </div>

                <div>
                    <p class="text-xs text-gray-400">IP Address</p>
                    <p class="mt-1 text-gray-700">
                        {{ $auditLog->ip_address ?? '-' }}
                    </p>
                </div>

                <div>
                    <p class="text-xs text-gray-400">Date & Time</p>
                    <p class="mt-1 text-gray-700">
                        {{ optional($auditLog->created_at)->format('d M Y, h:i:s A') }}
                    </p>
                </div>

            </div>

            <div class="mt-6 pt-6 border-t border-gray-100">

                <p class="text-xs text-gray-400">Description</p>

                <p class="mt-2 text-sm text-gray-700">
                    {{ $auditLog->description ?? 'No description available.' }}
                </p>

            </div>

        </div>

        <div class="bg-white border border-gray-200 rounded-xl p-6">

            <h2 class="text-lg font-semibold text-gray-900 mb-5">
                Request Details
            </h2>

            <div class="space-y-4 text-sm">

                <div>
                    <p class="text-xs text-gray-400">Method</p>
                    <p class="mt-1 font-medium">
                        {{ $auditLog->request_method ?? '-' }}
                    </p>
                </div>

                <div>
                    <p class="text-xs text-gray-400">URL</p>
                    <p class="mt-1 break-all text-gray-700">
                        {{ $auditLog->request_url ?? '-' }}
                    </p>
                </div>

                <div>
                    <p class="text-xs text-gray-400">User Agent</p>
                    <p class="mt-1 break-words text-gray-700">
                        {{ $auditLog->user_agent ?? '-' }}
                    </p>
                </div>

            </div>

        </div>

    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <div class="bg-white border border-gray-200 rounded-xl p-6">

            <h2 class="text-lg font-semibold text-gray-900 mb-4">
                Old Values
            </h2>

            <pre class="overflow-auto rounded-lg bg-gray-50 p-4 text-xs text-gray-700">{{ json_encode($auditLog->old_values ?? [], JSON_PRETTY_PRINT) }}</pre>

        </div>

        <div class="bg-white border border-gray-200 rounded-xl p-6">

            <h2 class="text-lg font-semibold text-gray-900 mb-4">
                New Values
            </h2>

            <pre class="overflow-auto rounded-lg bg-gray-50 p-4 text-xs text-gray-700">{{ json_encode($auditLog->new_values ?? [], JSON_PRETTY_PRINT) }}</pre>

        </div>

    </div>

</div>
@endsection