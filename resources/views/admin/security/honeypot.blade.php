@extends('layouts.admin')
@section('title') Security - Honeypot @endsection
@section('content')
<div class="row">
    <div class="col-xs-12">
        <div class="box box-success">
            <div class="box-header with-border">
                <h3 class="box-title">Honeypot Protection</h3>
                <div class="box-tools">
                    <a href="{{ route('admin.security.index') }}" class="btn btn-sm btn-default">Back</a>
                </div>
            </div>
            <div class="box-body">
                <p>Honeypot endpoints trap scanners and attackers. If an IP hits 3+ honeypot paths, it is automatically blocked for 24 hours.</p>
                <h4>Recent Honeypot Hits</h4>
                <table class="table table-hover">
                    <thead>
                        <tr><th>IP</th><th>Details</th><th>Action Taken</th><th>Time</th></tr>
                    </thead>
                    <tbody>
                        @forelse($hits as $hit)
                            <tr>
                                <td>{{ $hit->ip }}</td>
                                <td>{{ $hit->details }}</td>
                                <td>{{ $hit->action_taken }}</td>
                                <td>{{ $hit->detected_at }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4">No honeypot hits recorded.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
