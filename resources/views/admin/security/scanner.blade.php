@extends('layouts.admin')
@section('title') Security - Scanner @endsection
@section('content')
<div class="row">
    <div class="col-xs-12">
        <div class="box box-danger">
            <div class="box-header with-border">
                <h3 class="box-title">Security Scanner</h3>
                <div class="box-tools">
                </div>
            </div>
            <div class="box-body">
                <form action="{{ route('admin.security.scanner.run') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-danger">Run Security Audit</button>
                </form>
            </div>
        </div>
    </div>
</div>
@if(isset($audit))
<div class="row">
    <div class="col-xs-12">
        <div class="box box-solid">
            <div class="box-header"><h3 class="box-title">Audit Results</h3></div>
            <div class="box-body">
                <table class="table table-hover">
                    <thead><tr><th>Check</th><th>Status</th><th>Detail</th></tr></thead>
                    <tbody>
                        @foreach($audit as $item)
                            <tr>
                                <td>{{ $item['check'] }}</td>
                                <td><span class="label label-{{ $item['status'] === 'pass' ? 'success' : ($item['status'] === 'warn' ? 'warning' : 'danger') }}">{{ strtoupper($item['status']) }}</span></td>
                                <td>{{ $item['detail'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endif
@endsection
