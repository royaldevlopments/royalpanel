@extends('layouts.admin')

@section('title')
    System Info
@endsection

@section('content-header')
    <div class="col-sm-6">
        <h1>Royal System Info</h1>
        <p class="text-muted">Server environment overview</p>
    </div>
    <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">Admin</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.extensions') }}">Extensions</a></li>
            <li class="breadcrumb-item active">System Info</li>
        </ol>
    </div>
@endsection

@section('content')
<div class="row">
    <div class="col-md-6">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">PHP & Laravel</h3>
            </div>
            <div class="box-body table-responsive no-padding">
                <table class="table table-hover">
                    <tr><th>PHP Version</th><td>{{ $phpVersion }}</td></tr>
                    <tr><th>Laravel Version</th><td>{{ $laravelVersion }}</td></tr>
                    <tr><th>Environment</th><td><span class="label label-{{ $environment === 'production' ? 'success' : 'warning' }}">{{ $environment }}</span></td></tr>
                    <tr><th>Debug Mode</th><td><span class="label label-{{ $debugMode ? 'danger' : 'default' }}">{{ $debugMode ? 'ON' : 'OFF' }}</span></td></tr>
                    <tr><th>Server Software</th><td>{{ $serverSoftware }}</td></tr>
                    <tr><th>Timezone</th><td>{{ $timezone }}</td></tr>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">Database & Cache</h3>
            </div>
            <div class="box-body table-responsive no-padding">
                <table class="table table-hover">
                    <tr><th>Database Driver</th><td>{{ $databaseDriver }}</td></tr>
                    <tr><th>Cache Driver</th><td>{{ $cacheDriver }}</td></tr>
                    <tr><th>Session Driver</th><td>{{ $sessionDriver }}</td></tr>
                    <tr><th>Queue Driver</th><td>{{ $queueDriver }}</td></tr>
                    <tr><th>Mail Driver</th><td>{{ $mailDriver }}</td></tr>
                </table>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-6">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">Resource Limits</h3>
            </div>
            <div class="box-body table-responsive no-padding">
                <table class="table table-hover">
                    <tr><th>Memory Limit</th><td>{{ $memoryLimit }}</td></tr>
                    <tr><th>Max Upload Size</th><td>{{ $maxUploadSize }}</td></tr>
                    <tr><th>Max POST Size</th><td>{{ $maxPostSize }}</td></tr>
                    <tr><th>Max Execution Time</th><td>{{ $maxExecTime }}s</td></tr>
                    <tr><th>Disk Free</th><td>{{ number_format($diskFree / 1073741824, 2) }} GB / {{ number_format($diskTotal / 1073741824, 2) }} GB</td></tr>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">Loaded PHP Extensions</h3>
            </div>
            <div class="box-body">
                @foreach(array_chunk($extensions, 6) as $chunk)
                    <div class="row">
                        @foreach($chunk as $ext)
                            <div class="col-md-4"><code>{{ $ext }}</code></div>
                        @endforeach
                    </div>
                @endforeach
                <p class="text-muted" style="margin-top:10px;">{{ count($extensions) }} extensions loaded</p>
            </div>
        </div>
    </div>
</div>
@endsection
