@extends('layouts.admin')
@section('title') Security - Brute Force Protection @endsection
@section('content')
<div class="row">
    <div class="col-xs-12">
        <div class="box box-danger">
            <div class="box-header with-border">
                <h3 class="box-title">Brute Force Protection</h3>
                <div class="box-tools">
                </div>
            </div>
            <form action="{{ route('admin.security.brute_force.save') }}" method="POST">
                @csrf
                <div class="box-body">
                    <div class="form-group">
                        <label>Enabled</label>
                        <input type="hidden" name="enabled" value="0">
                        <input type="checkbox" name="enabled" value="1" {{ config('security.brute_force.enabled', true) ? 'checked' : '' }}>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Max Failed Attempts</label>
                                <input type="number" name="max_attempts" class="form-control" value="{{ config('security.brute_force.max_attempts', 10) }}" min="1">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Lockout Duration (minutes)</label>
                                <input type="number" name="lockout_duration" class="form-control" value="{{ config('security.brute_force.lockout_duration', 60) }}" min="1">
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
<div class="row">
    <div class="col-xs-12">
        <div class="box box-solid">
            <div class="box-header"><h3 class="box-title">Recent Failed Login Attempts</h3></div>
            <div class="box-body">
                <table class="table table-hover">
                    <thead><tr><th>IP</th><th>Route</th><th>Timestamp</th></tr></thead>
                    <tbody>
                        @forelse($failedAttempts ?? [] as $attempt)
                            <tr><td>{{ $attempt->ip }}</td><td>{{ $attempt->route ?? 'N/A' }}</td><td>{{ $attempt->logged_at }}</td></tr>
                        @empty
                            <tr><td colspan="3">No failed attempts recorded.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
