@extends('layouts.royal', ['navbar' => 'email-templates', 'sideEditor' => false])

@section('title')
    Edit Template: {{ str_replace('_', ' ', ucfirst($template->template_key)) }}
@endsection

@php
    $ph = ['{{name}}', '{{username}}', '{{email}}', '{{app_name}}', '{{server_name}}', '{{setup_url}}', '{{reset_url}}', '{{server_url}}'];
    list($ph_name, $ph_uname, $ph_email, $ph_app, $ph_srv, $ph_setup, $ph_reset, $ph_srvurl) = $ph;
@endphp
@section('content')
    <form action="{{ route('admin.royal.email-templates.update', $template) }}" method="POST" class="content-box">
        <div class="header">
            <p>Edit: {{ str_replace('_', ' ', ucfirst($template->template_key)) }}</p>
            <span class="description-text">
                Available placeholders:
                <code>{!! $ph_name !!}</code>
                <code>{!! $ph_uname !!}</code>
                <code>{!! $ph_email !!}</code>
                <code>{!! $ph_app !!}</code>
                <code>{!! $ph_srv !!}</code>
                <code>{!! $ph_setup !!}</code>
                <code>{!! $ph_reset !!}</code>
                <code>{!! $ph_srvurl !!}</code>
            </span>
        </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <x-royal.form-wrapper
            title="Content"
            description="Customize the email content. Leave fields empty to use the default."
        >
            <x-royal.input-field
                id="subject"
                :value="old('subject', $template->subject)"
                label="Subject"
                helpText="Email subject line. Leave empty for default."
            />
            <x-royal.input-field
                id="greeting"
                :value="old('greeting', $template->greeting)"
                label="Greeting"
                :helpText="'e.g. Hello ' . $ph_name . '!'"
            />
            <div class="input-field">
                <label for="body">Body</label>
                <textarea
                    id="body"
                    name="body"
                    rows="6"
                    style="width:100%;padding:10px;border-radius:6px;border:1px solid rgba(255,255,255,0.1);background:rgba(0,0,0,0.3);color:#e2e8f0;font-size:13px;font-family:inherit;resize:vertical;"
                >{{ old('body', $template->body) }}</textarea>
                <span style="font-size:0.8rem">One line per paragraph. Use <code>{!! $ph_uname !!}</code>, <code>{!! $ph_email !!}</code>, etc.</span>
            </div>
            <x-royal.input-field
                id="action_text"
                :value="old('action_text', $template->action_text)"
                label="Action Button Text"
                helpText="e.g. Visit Server. Leave empty to remove the button."
            />
            <x-royal.input-field
                id="action_url"
                :value="old('action_url', $template->action_url)"
                label="Action Button URL"
                :helpText="'e.g. ' . $ph_srvurl . ' or ' . $ph_setup"
            />
            <div class="input-field">
                <label for="level">Button Style</label>
                <select id="level" name="level">
                    <option value="primary" @if(old('level', $template->level) === 'primary') selected @endif>Primary</option>
                    <option value="error" @if(old('level', $template->level) === 'error') selected @endif>Error / Danger</option>
                </select>
            </div>
            <div class="input-field">
                <label for="outro">Outro / Closing</label>
                <textarea
                    id="outro"
                    name="outro"
                    rows="3"
                    style="width:100%;padding:10px;border-radius:6px;border:1px solid rgba(255,255,255,0.1);background:rgba(0,0,0,0.3);color:#e2e8f0;font-size:13px;font-family:inherit;resize:vertical;"
                >{{ old('outro', $template->outro) }}</textarea>
            </div>
            <x-royal.switch
                id="enabled"
                name="enabled"
                :value="old('enabled', $template->enabled)"
                label="Override default email"
                helpText="Enable to use these custom settings instead of the default email content."
            />
        </x-royal.form-wrapper>

        <div class="floating-button">
            {!! csrf_field() !!}
            {!! method_field('PATCH') !!}
            <a href="{{ route('admin.royal.email-templates') }}" class="button" style="text-decoration:none;margin-right:8px;">Cancel</a>
            <button type="submit" class="button button-primary">Save Template</button>
        </div>
    </form>
@endsection
