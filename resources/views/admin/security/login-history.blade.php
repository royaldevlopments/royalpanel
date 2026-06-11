@extends('layouts.admin')
@section('title') Security - Login History @endsection
@section('content')
<div class="row">
    <div class="col-xs-12">
        <div class="box box-default">
            <div class="box-header with-border">
                <h3 class="box-title">Login History</h3>
                <div class="box-tools">
                </div>
            </div>
            <div class="box-body">
                <table class="table table-hover">
                    <thead><tr><th>IP</th><th>Route</th><th>Status</th><th>Timestamp</th></tr></thead>
                    <tbody>
                        @forelse($logins ?? [] as $login)
                            <tr>
                                <td>{{ $login->ip }}</td>
                                <td>{{ $login->route ?? 'N/A' }}</td>
                                <td><span class="label label-{{ $login->blocked ? 'danger' : 'success' }}">{{ $login->blocked ? 'Blocked' : 'Allowed' }}</span></td>
                                <td>{{ $login->logged_at }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4">No login events recorded.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
