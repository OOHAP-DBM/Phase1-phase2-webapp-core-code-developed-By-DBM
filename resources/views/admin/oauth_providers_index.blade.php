@extends('layouts.admin')

@section('title', 'OAuth Providers')

@section('breadcrumb')
<x-breadcrumb :items="[
    ['label' => 'Home', 'route' => route('admin.dashboard')],
    ['label' => 'Settings', 'route' => route('admin.settings.razorpay')],
    ['label' => 'OAuth Providers']
]" />
@endsection

@section('content')
<div class="bg-[#F7F7F7] w-full min-h-screen p-6">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-bold text-gray-900">OAuth Providers</h1>
            <p class="text-sm text-gray-500 mt-1">Manage third-party OAuth providers (enable/disable and configure credentials)</p>
        </div>
        <div>
            <a href="{{ route('admin.oauth_providers.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-[#00995c] text-white rounded-lg">+ Add Provider</a>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-4 p-4 bg-green-50 border border-green-200 text-green-700 rounded-xl">{{ session('success') }}</div>
    @endif

    <div class="bg-white rounded-xl p-4 shadow-sm">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-xs text-gray-500 border-b">
                    <th class="py-3">Provider</th>
                    <th class="py-3">Name</th>
                    <th class="py-3">Redirect URI</th>
                    <th class="py-3">Active</th>
                    <th class="py-3">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($providers as $p)
                    <tr class="border-b">
                        <td class="py-3">{{ $p->provider }}</td>
                        <td class="py-3">{{ $p->name }}</td>
                        <td class="py-3">{{ $p->redirect }}</td>
                        <td class="py-3">
                            @if($p->active)
                                <span class="px-2 py-1 rounded-full text-xs bg-green-100 text-green-700">Active</span>
                            @else
                                <span class="px-2 py-1 rounded-full text-xs bg-gray-100 text-gray-700">Inactive</span>
                            @endif
                        </td>
                        <td class="py-3">
                            <a href="{{ route('admin.oauth_providers.edit', $p->id) }}" class="text-sm text-blue-600 mr-3">Edit</a>
                            <form action="{{ route('admin.oauth_providers.destroy', $p->id) }}" method="POST" style="display:inline" onsubmit="return confirm('Delete provider?');">
                                @csrf
                                @method('DELETE')
                                <button class="text-sm text-red-600">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="py-6 text-center text-gray-500">No providers yet</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
