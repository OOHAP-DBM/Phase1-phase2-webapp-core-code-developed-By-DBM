@extends('layouts.admin')

@section('title', 'Edit OAuth Provider')

@section('content')
<div class="bg-[#F7F7F7] w-full min-h-screen p-6">
    <div class="bg-white rounded-xl p-6 shadow-sm max-w-3xl">
        <h1 class="text-lg font-bold mb-4">Edit OAuth Provider</h1>

        @if($errors->any())
            <div class="mb-4 p-3 bg-red-50 border border-red-200 text-red-700 rounded">Please fix the errors below</div>
        @endif

        <form method="POST" action="{{ route('admin.oauth_providers.update', $provider->id) }}">
            @csrf
            @method('PUT')

            <div class="mb-4">
                <label class="block text-sm font-medium">Provider (unique key)</label>
                <input type="text" name="provider" value="{{ old('provider', $provider->provider) }}" class="w-full border rounded px-3 py-2" placeholder="google">
                @error('provider')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium">Display Name</label>
                <input type="text" name="name" value="{{ old('name', $provider->name) }}" class="w-full border rounded px-3 py-2" placeholder="Google">
                @error('name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium">Client ID</label>
                <input type="text" name="client_id" value="{{ old('client_id', $provider->client_id) }}" class="w-full border rounded px-3 py-2">
                @error('client_id')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium">Client Secret</label>
                <input type="text" name="client_secret" value="{{ old('client_secret', $provider->client_secret) }}" class="w-full border rounded px-3 py-2">
                @error('client_secret')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium">Redirect URI</label>
                <input type="url" name="redirect" value="{{ old('redirect', $provider->redirect) }}" class="w-full border rounded px-3 py-2" placeholder="https://your-domain/auth/callback/google">
                @error('redirect')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div class="mb-4">
                <label class="inline-flex items-center gap-2">
                    <input type="checkbox" name="active" value="1" {{ $provider->active ? 'checked' : '' }}> <span>Active</span>
                </label>
            </div>

            <div class="flex gap-2">
                <button class="px-4 py-2 bg-[#00995c] text-white rounded">Update</button>
                <a href="{{ route('admin.oauth_providers.index') }}" class="px-4 py-2 border rounded">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
