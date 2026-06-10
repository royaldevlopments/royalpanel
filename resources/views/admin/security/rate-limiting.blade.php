@extends('layouts.admin')
@section('title') Security - Rate Limiting @endsection
@section('content')
<div class="row">
    <div class="col-xs-12">
        <div class="box box-warning">
            <div class="box-header with-border">
                <h3 class="box-title">Rate Limiting</h3>
                <div class="box-tools">
                    <a href="{{ route('admin.security.index') }}" class="btn btn-sm btn-default">Back</a>
                </div>
            </div>
            <form action="{{ route('admin.security.save') }}" method="POST">
                @csrf
                <div class="box-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="box box-solid">
                                <div class="box-header"><h4>Panel</h4></div>
                                <div class="box-body">
                                    <div class="form-group">
                                        <label>Enabled</label>
                                        <input type="hidden" name="rate_limit_panel_enabled" value="0">
                                        <input type="checkbox" name="rate_limit_panel_enabled" value="1" {{ $config['panel']['enabled'] ? 'checked' : '' }}>
                                    </div>
                                    <div class="form-group">
                                        <label>Max Requests</label>
                                        <input type="number" name="rate_limit_panel_max" class="form-control" value="{{ $config['panel']['max_requests'] }}">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="box box-solid">
                                <div class="box-header"><h4>API</h4></div>
                                <div class="box-body">
                                    <div class="form-group">
                                        <label>Enabled</label>
                                        <input type="hidden" name="rate_limit_api_enabled" value="0">
                                        <input type="checkbox" name="rate_limit_api_enabled" value="1" {{ $config['api']['enabled'] ? 'checked' : '' }}>
                                    </div>
                                    <div class="form-group">
                                        <label>Max Requests</label>
                                        <input type="number" name="rate_limit_api_max" class="form-control" value="{{ $config['api']['max_requests'] }}">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="box box-solid">
                                <div class="box-header"><h4>Server</h4></div>
                                <div class="box-body">
                                    <div class="form-group">
                                        <label>Enabled</label>
                                        <input type="hidden" name="rate_limit_server_enabled" value="0">
                                        <input type="checkbox" name="rate_limit_server_enabled" value="1" {{ $config['server']['enabled'] ? 'checked' : '' }}>
                                    </div>
                                    <div class="form-group">
                                        <label>Max Requests</label>
                                        <input type="number" name="rate_limit_server_max" class="form-control" value="{{ $config['server']['max_requests'] }}">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-xs-12">
                            <div class="box box-solid">
                                <div class="box-header"><h4>Auto-Ban Settings</h4></div>
                                <div class="box-body">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>Auto-Ban Offending IPs</label>
                                                <input type="hidden" name="auto_ban_enabled" value="0">
                                                <input type="checkbox" name="auto_ban_enabled" value="1" {{ config('security.ip_blocking.auto_ban.enabled') ? 'checked' : '' }}>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>Max Failed Attempts</label>
                                                <input type="number" name="auto_ban_max_attempts" class="form-control" value="{{ config('security.ip_blocking.auto_ban.max_failed_attempts') }}">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>Ban Duration (minutes)</label>
                                                <input type="number" name="auto_ban_duration" class="form-control" value="{{ config('security.ip_blocking.auto_ban.ban_duration_minutes') }}">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="box-footer">
                    <button type="submit" class="btn btn-primary">Save Settings</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
