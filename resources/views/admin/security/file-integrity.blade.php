@extends('layouts.admin')
@section('title') Security - File Integrity @endsection
@section('content')
<div class="row">
    <div class="col-xs-12">
        <div class="box box-success">
            <div class="box-header with-border">
                <h3 class="box-title">File Integrity Monitor</h3>
                <div class="box-tools">
                </div>
            </div>
            <div class="box-body">
                <div class="row">
                    <div class="col-md-3"><strong>Total:</strong> {{ $stats['total'] ?? 0 }}</div>
                    <div class="col-md-3 text-green"><strong>Clean:</strong> {{ $stats['clean'] ?? 0 }}</div>
                    <div class="col-md-3 text-yellow"><strong>New:</strong> {{ $stats['new'] ?? 0 }}</div>
                    <div class="col-md-3 text-red"><strong>Modified:</strong> {{ $stats['modified'] ?? 0 }}</div>
                </div>
                <form action="{{ route('admin.security.file_integrity.scan') }}" method="POST" class="mt-3">
                    @csrf
                    <button type="submit" class="btn btn-warning">Run Scan</button>
                </form>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-xs-12">
        <div class="box box-solid">
            <div class="box-header"><h3 class="box-title">Scan Results</h3></div>
            <div class="box-body">
                <table class="table table-hover">
                    <thead><tr><th>File</th><th>Status</th><th>Last Checked</th></tr></thead>
                    <tbody>
                        @forelse($results ?? [] as $r)
                            <tr>
                                <td>{{ $r->file_path }}</td>
                                <td><span class="label label-{{ $r->status === 'clean' ? 'success' : ($r->status === 'new' ? 'warning' : 'danger') }}">{{ $r->status }}</span></td>
                                <td>{{ $r->last_checked_at }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3">No scans run yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
