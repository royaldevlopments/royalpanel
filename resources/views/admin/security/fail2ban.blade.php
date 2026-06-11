@extends('layouts.admin')
@section('title') Security - Fail2Ban @endsection
@section('content')
<div class="row">
    <div class="col-xs-12">
        <div class="box box-default">
            <div class="box-header with-border">
                <h3 class="box-title">Fail2Ban Integration</h3>
                <div class="box-tools">
                    <a href="{{ route('admin.security.index') }}" class="btn btn-sm btn-default">Back</a>
                </div>
            </div>
            <form action="{{ route('admin.security.fail2ban.save') }}" method="POST">
                @csrf
                <div class="box-body">
                    <div class="form-group">
                        <label>Enabled</label>
                        <input type="hidden" name="enabled" value="0">
                        <input type="checkbox" name="enabled" value="1" {{ config('security.fail2ban.enabled', false) ? 'checked' : '' }}>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Max Retries</label>
                                <input type="number" name="max_retries" class="form-control" value="{{ config('security.fail2ban.max_retries', 5) }}" min="1">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Find Time (seconds)</label>
                                <input type="number" name="find_time" class="form-control" value="{{ config('security.fail2ban.find_time', 600) }}" min="1">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Ban Time (seconds)</label>
                                <input type="number" name="ban_time" class="form-control" value="{{ config('security.fail2ban.ban_time', 3600) }}" min="1">
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Jail Log Path (e.g. /var/www/pterodactyl/storage/logs/laravel.log)</label>
                        <input type="text" name="log_path" class="form-control" value="{{ config('security.fail2ban.log_path', storage_path('logs/laravel.log')) }}">
                    </div>
                </div>
                <div class="box-footer">
                    <button type="submit" class="btn btn-primary">Save Fail2Ban Settings</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
