@extends('layouts.royal', ['navbar' => 'email-templates', 'sideEditor' => false])

@section('title')
    Email Templates
@endsection

@section('content')
    <div class="content-box">
        <div class="header">
            <p>Email Templates</p>
            <span class="description-text">Customize the subject line, greeting, body text, and action button for each email sent by the panel. Leave blank to use the default. Placeholders: <code>@{{name}}</code>, <code>@{{username}}</code>, <code>@{{email}}</code>, <code>@{{app_name}}</code>, <code>@{{server_name}}</code>, <code>@{{setup_url}}</code>, <code>@{{reset_url}}</code>, <code>@{{server_url}}</code>.</span>
        </div>

        @if(session('success'))
            <div class="alert alert-success" style="padding:12px;margin-bottom:16px;border-radius:8px;background:rgba(34,197,94,0.15);color:#22c55e;font-size:14px;">
                {{ session('success') }}
            </div>
        @endif

        <div style="display:grid;gap:12px;">
            @foreach($templates as $template)
                <div style="display:flex;align-items:center;justify-content:space-between;padding:16px;border-radius:8px;background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.06);">
                    <div>
                        <strong style="font-size:14px;color:#e2e8f0;">{{ str_replace('_', ' ', ucfirst($template->template_key)) }}</strong>
                        <div style="font-size:12px;color:#94a3b8;margin-top:4px;">
                            Key: <code>{{ $template->template_key }}</code>
                            &middot;
                            Subject: <em>{{ $template->subject ?: '(default)' }}</em>
                            &middot;
                            Status: {!! $template->enabled ? '<span style="color:#22c55e;">Custom</span>' : '<span style="color:#94a3b8;">Default</span>' !!}
                        </div>
                    </div>
                    <a href="{{ route('admin.royal.email-templates.edit', $template) }}" class="button button-primary" style="text-decoration:none;padding:8px 16px;border-radius:6px;font-size:13px;">
                        Edit
                    </a>
                </div>
            @endforeach
        </div>
    </div>
@endsection
