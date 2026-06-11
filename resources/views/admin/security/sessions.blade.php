@extends('layouts.admin')
@section('title') Security - Session Manager @endsection
@section('content')
<div class="row">
    <div class="col-xs-12">
        <div class="box box-default">
            <div class="box-header with-border">
                <h3 class="box-title">Active Sessions</h3>
                <div class="box-tools">
                </div>
            </div>
            <div class="box-body">
                <table class="table table-hover">
                    <thead><tr><th>User</th><th>IP</th><th>Last Activity</th><th>Action</th></tr></thead>
                    <tbody>
                        @forelse($sessions ?? [] as $session)
                            <tr>
                                <td>{{ $session->user_email ?? 'N/A' }}</td>
                                <td>{{ $session->ip_address ?? $session->ip }}</td>
                                <td>{{ $session->last_activity ? date('Y-m-d H:i:s', $session->last_activity) : 'N/A' }}</td>
                                <td>
                                    <form action="{{ route('admin.security.sessions.terminate', $session->id) }}" method="POST" style="display:inline">
                                        @csrf
                                        <button type="submit" class="btn btn-danger btn-xs">Terminate</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4">No active sessions.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
