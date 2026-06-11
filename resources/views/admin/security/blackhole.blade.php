@extends('layouts.admin')

@section('title')
    Blackhole Protection
@endsection

@section('content-header')
    <h1>Blackhole Protection<small>Null-route server IPs under attack at the network level</small></h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('admin.index') }}">Admin</a></li>
        <li><a href="{{ route('admin.security.index') }}">Security</a></li>
        <li class="active">Blackhole Protection</li>
    </ol>
@endsection

@section('content')
<div class="row">
    <div class="col-xs-12">
        <div class="box box-info">
            <div class="box-header with-border">
                <h3 class="box-title">About Blackhole Protection</h3>
            </div>
            <div class="box-body">
                <p>Blackhole protection null-routes traffic to a server IP at the network level by pointing DNS to <code>192.0.2.1</code> (a reserved blackhole IP) via Cloudflare and dropping all non-Cloudflare traffic via iptables.</p>
                <p>This is an <strong>extreme measure</strong> — the server becomes unreachable during the blackhole period. Use this during severe DDoS attacks to protect your origin infrastructure.</p>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="box box-danger">
            <div class="box-header with-border">
                <h3 class="box-title">Activate Blackhole</h3>
            </div>
            <form action="{{ route('admin.security.blackhole.enable') }}" method="POST">
                @csrf
                <div class="box-body">
                    <div class="form-group">
                        <label for="domain">Server Domain</label>
                        <input type="text" id="domain" name="domain" class="form-control" placeholder="e.g. server.example.com" required>
                        <p class="help-block">The domain pointing to the server IP you want to blackhole.</p>
                    </div>
                    <div class="form-group">
                        <label for="duration">Duration (minutes)</label>
                        <input type="number" id="duration" name="duration" class="form-control" value="30" min="1" max="1440">
                        <p class="help-block">Auto-disable after this many minutes. Max 1440 (24 hours).</p>
                    </div>
                </div>
                <div class="box-footer">
                    <button type="submit" class="btn btn-danger">Activate Blackhole</button>
                </div>
            </form>
        </div>
    </div>
    <div class="col-md-6">
        <div class="box box-success">
            <div class="box-header with-border">
                <h3 class="box-title">Deactivate Blackhole</h3>
            </div>
            <form action="{{ route('admin.security.blackhole.disable') }}" method="POST">
                @csrf
                <div class="box-body">
                    <div class="form-group">
                        <label for="domain-disable">Server Domain</label>
                        <input type="text" id="domain-disable" name="domain" class="form-control" placeholder="e.g. server.example.com" required>
                        <p class="help-block">The domain to restore from blackhole.</p>
                    </div>
                </div>
                <div class="box-footer">
                    <button type="submit" class="btn btn-success">Deactivate Blackhole</button>
                </div>
            </form>
        </div>
    </div>
</div>

@if(isset($activeBlackholes) && $activeBlackholes->isNotEmpty())
<div class="row">
    <div class="col-xs-12">
        <div class="box box-warning">
            <div class="box-header with-border">
                <h3 class="box-title">Active Blackholes</h3>
            </div>
            <div class="box-body table-responsive no-padding">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Domain</th>
                            <th>Activated</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($activeBlackholes as $bh)
                        <tr>
                            <td>{{ $bh->domain }}</td>
                            <td>{{ $bh->activated_at }}</td>
                            <td>
                                <form action="{{ route('admin.security.blackhole.disable') }}" method="POST" style="display:inline">
                                    @csrf
                                    <input type="hidden" name="domain" value="{{ $bh->domain }}">
                                    <button class="btn btn-xs btn-success">Deactivate</button>
                                </form>
                            </td>
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
