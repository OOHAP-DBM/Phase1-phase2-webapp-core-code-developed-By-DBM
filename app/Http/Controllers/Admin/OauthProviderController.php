<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\OauthProvider;

class OauthProviderController extends Controller
{
    public function index()
    {
        $providers = OauthProvider::orderBy('provider')->get();
        return view('admin.oauth_providers_index', compact('providers'));
    }

    public function create()
    {
        return view('admin.oauth_providers_create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'nullable|string|max:191',
            'provider' => 'required|string|max:100|unique:oauth_providers,provider',
            'client_id' => 'nullable|string|max:255',
            'client_secret' => 'nullable|string|max:255',
            'redirect' => 'nullable|url|max:255',
            'active' => 'nullable|in:0,1',
        ]);

        $data['active'] = $request->has('active') ? 1 : 0;

        OauthProvider::create($data);

        return redirect()->route('admin.oauth_providers.index')->with('success', 'OAuth provider created.');
    }

    public function edit(OauthProvider $oauth_provider)
    {
        return view('admin.oauth_providers_edit', ['provider' => $oauth_provider]);
    }

    public function update(Request $request, OauthProvider $oauth_provider)
    {
        $data = $request->validate([
            'name' => 'nullable|string|max:191',
            'provider' => 'required|string|max:100|unique:oauth_providers,provider,' . $oauth_provider->id,
            'client_id' => 'nullable|string|max:255',
            'client_secret' => 'nullable|string|max:255',
            'redirect' => 'nullable|url|max:255',
            'active' => 'nullable|in:0,1',
        ]);

        $data['active'] = $request->has('active') ? 1 : 0;

        $oauth_provider->update($data);

        return redirect()->route('admin.oauth_providers.index')->with('success', 'OAuth provider updated.');
    }

    public function destroy(OauthProvider $oauth_provider)
    {
        $oauth_provider->delete();
        return redirect()->route('admin.oauth_providers.index')->with('success', 'OAuth provider deleted.');
    }
}
