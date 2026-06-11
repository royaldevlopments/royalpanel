@extends('layouts.admin')
@section('title') Security - Cloudflare @endsection
@section('content')
<div class="row">
    <div class="col-xs-12">
        <div class="box box-info">
            <div class="box-header with-border">
                <h3 class="box-title">Cloudflare Configuration</h3>
                <div class="box-tools">
                    <a href="{{ route('admin.security.index') }}" class="btn btn-sm btn-default">Back</a>
                </div>
            </div>
            <form action="{{ route('admin.security.save') }}" method="POST">
                @csrf
                <div class="box-body">
                    <div class="form-group">
                        <label class="control-label">Enable Cloudflare Integration</label>
                        <div>
                            <input type="hidden" name="cloudflare_enabled" value="0">
                            <input type="checkbox" name="cloudflare_enabled" value="1" {{ config('security.cloudflare.enabled') ? 'checked' : '' }} class="checkbox">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>API Token</label>
                        <input type="password" name="cloudflare_api_token" class="form-control" value="{{ config('security.cloudflare.api_token') }}" placeholder="Enter your Cloudflare API token">
                    </div>
                    <div class="form-group">
                        <label>Zone ID</label>
                        <input type="text" name="cloudflare_zone_id" class="form-control" value="{{ config('security.cloudflare.zone_id') }}" placeholder="Enter your Cloudflare Zone ID">
                    </div>
                </div>
                <div class="box-footer">
                    <button type="submit" class="btn btn-primary">Save Settings</button>
                </div>
            </form>
            <div class="row">
                <div class="col-xs-12">
                    <h4>Cloudflare Actions</h4>
                    <form action="{{ route('admin.security.under_attack_on') }}" method="POST" style="display:inline">@csrf<button type="submit" class="btn btn-danger btn-sm">Under Attack ON</button></form>
                    <form action="{{ route('admin.security.under_attack_off') }}" method="POST" style="display:inline">@csrf<button type="submit" class="btn btn-default btn-sm">Under Attack OFF</button></form>
                    <form action="{{ route('admin.security.bot_fight_on') }}" method="POST" style="display:inline">@csrf<button type="submit" class="btn btn-warning btn-sm">Bot Fight ON</button></form>
                    <form action="{{ route('admin.security.lockdown_on') }}" method="POST" style="display:inline">@csrf<button type="submit" class="btn btn-danger btn-sm">Lockdown ON</button></form>
                    <form action="{{ route('admin.security.lockdown_off') }}" method="POST" style="display:inline">@csrf<button type="submit" class="btn btn-default btn-sm">Lockdown OFF</button></form>
                    <form action="{{ route('admin.security.ssl_strict') }}" method="POST" style="display:inline">@csrf<button type="submit" class="btn btn-success btn-sm">SSL Strict</button></form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
