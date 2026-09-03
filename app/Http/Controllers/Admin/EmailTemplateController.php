<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmailTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class EmailTemplateController extends Controller
{
    /**
     * Display all email templates.
     */
    public function index()
    {
        $templates = EmailTemplate::query()
            ->orderBy('name')
            ->get();

        return view('admin.email_templates.index', compact('templates'));
    }


    /**
     * Show the create email template page.
     */
    public function create()
    {
        return view('admin.email_templates.create');
    }


    /**
     * Store a new email template.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'key' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-z0-9_]+$/',
                'unique:email_templates,key',
            ],

            'subject' => [
                'required',
                'string',
                'max:255',
            ],

            'body' => [
                'required',
                'string',
            ],

            'variables' => [
                'nullable',
                'array',
            ],

            'is_active' => [
                'nullable',
                'boolean',
            ],
        ], [
            'key.regex' => 'The key may only contain lowercase letters, numbers, and underscores.',
            'key.unique' => 'This email template key already exists.',
        ]);


        /*
        |--------------------------------------------------------------------------
        | Normalize variables
        |--------------------------------------------------------------------------
        |
        | variables column is cast to array in the model.
        | If no variables are supplied, store an empty array.
        |
        */
        $validated['variables'] = $request->input('variables', []);


        /*
        |--------------------------------------------------------------------------
        | Active Status
        |--------------------------------------------------------------------------
        */
        $validated['is_active'] = $request->boolean('is_active');


        /*
        |--------------------------------------------------------------------------
        | Created By
        |--------------------------------------------------------------------------
        */
        $validated['created_by'] = auth()->id();


        /*
        |--------------------------------------------------------------------------
        | Updated By
        |--------------------------------------------------------------------------
        */
        $validated['updated_by'] = auth()->id();


        /*
        |--------------------------------------------------------------------------
        | Create Template
        |--------------------------------------------------------------------------
        */
        EmailTemplate::create($validated);


        return redirect()
            ->route('email_templates.index')
            ->with('success', 'Email template created successfully.');
    }


    /**
     * Show the email template edit page.
     */
    public function edit(EmailTemplate $emailTemplate)
    {
        return view(
            'admin.email_templates.edit',
            compact('emailTemplate')
        );
    }


    /**
     * Update an existing email template.
     */
    public function update(
        Request $request,
        EmailTemplate $emailTemplate
    ) {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'key' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-z0-9_]+$/',
                Rule::unique('email_templates', 'key')
                    ->ignore($emailTemplate->id),
            ],

            'subject' => [
                'required',
                'string',
                'max:255',
            ],

            'body' => [
                'required',
                'string',
            ],

            'variables' => [
                'nullable',
                'array',
            ],

            'is_active' => [
                'nullable',
                'boolean',
            ],
        ], [
            'key.regex' => 'The key may only contain lowercase letters, numbers, and underscores.',
            'key.unique' => 'This email template key already exists.',
        ]);


        $validated['variables'] = $request->input('variables', []);

        $validated['is_active'] = $request->boolean('is_active');

        $validated['updated_by'] = auth()->id();

        $emailTemplate->update($validated);


        return redirect()
            ->route('email_templates.index')
            ->with('success', 'Email template updated successfully.');
    }

    public function preview(EmailTemplate $emailTemplate)
    {
        $body = $emailTemplate->body;

        $sampleVariables = [
            'customer_name' => 'Saurabh Pandey',
            'customer_email' => 'dbm.laraveldeveloper5@gmail.com',
            'customer_phone' => '+91 9876543210',

            'vendor_name' => 'Demo Vendor',
            'vendor_email' => 'vendor@example.com',

            'enquiry_number' => 'ENQ-1001',

            'hoarding_type' => 'Unipole',
            'city' => 'Delhi',
            'preferred_locations' => 'Connaught Place, Rajouri Garden',
            'preferred_contact_mode' => 'Phone',
            'client_message' => 'Looking for hoardings for 30 days.',

            'quotation_number' => 'QUO-1001',
            'preferred_start_date' => '15 September 2026',

            'app_name' => config('app.name', 'OOHAPP'),
            'login_url' => url('/login'),
            'support_email' => config(
                'mail.from.address',
                'support@example.com'
            ),
        ];

        foreach ($sampleVariables as $key => $value) {
            $body = preg_replace(
                '/\{\{\s*' . preg_quote($key, '/') . '\s*\}\}/',
                e($value),
                $body
            );
        }

        return view(
            'admin.email_templates.preview',
            [
                'emailTemplate' => $emailTemplate,
                'body' => $body,
            ]
        );
    }
}