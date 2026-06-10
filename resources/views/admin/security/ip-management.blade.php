@extends('layouts.admin')
@section('title') Security - IP Management @endsection
@section('content')
<div class="row">
    <div class="col-xs-12">
        <div class="box box-danger">
            <div class="box-header with-border">
                <h3 class="box-title">IP Management</h3>
                <div class="box-tools">
                    <a href="{{ route('admin.security.index') }}" class="btn btn-sm btn-default">Back</a>
                </div>
            </div>
            <form action="{{ route('admin.security.save') }}" method="POST">
                @csrf
                <div class="box-body">
                    <div class="form-group">
                        <label>Enable Country Blocking</label>
                        <input type="hidden" name="country_block_enabled" value="0">
                        <input type="checkbox" name="country_block_enabled" value="1" {{ config('security.ip_blocking.country_block.enabled') ? 'checked' : '' }}>
                    </div>
                </div>
                <div class="box-footer">
                    <button type="submit" class="btn btn-primary">Save Settings</button>
                </div>
            </form>

            <div class="box-header"><h3 class="box-title">Blocked IPs</h3></div>
            <div class="box-body table-responsive no-padding">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>IP</th>
                            <th>Reason</th>
                            <th>Type</th>
                            <th>Expires</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($blockedIps as $block)
                            <tr>
                                <td>{{ $block->ip }}</td>
                                <td>{{ $block->reason }}</td>
                                <td>{{ $block->type }}</td>
                                <td>{{ $block->expires_at ?? 'Never' }}</td>
                                <td>
                                    <form action="{{ route('admin.security.unblock', $block->ip) }}" method="POST" style="display:inline">
                                        @csrf
                                        <button type="submit" class="btn btn-xs btn-success">Unblock</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5">No blocked IPs.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
