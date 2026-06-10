@extends('layouts.admin')
@section('title') Security - Origin Protection @endsection
@section('content')
<div class="row">
    <div class="col-xs-12">
        <div class="box box-warning">
            <div class="box-header with-border">
                <h3 class="box-title">Origin Protection & DNS Stealth</h3>
                <div class="box-tools">
                    <a href="{{ route('admin.security.index') }}" class="btn btn-sm btn-default">Back</a>
                </div>
            </div>
            <div class="box-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="small-box bg-green">
                            <div class="inner">
                                <h3>{{ $status['protection_score'] }}/{{ $status['max_score'] }}</h3>
                                <p>Protection Score</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="small-box bg-{{ $stealth['real_ip_exposed'] ? 'red' : 'green' }}">
                            <div class="inner">
                                <h3>{{ $stealth['stealth_level'] }}</h3>
                                <p>Stealth Level ({{ $stealth['stealth_score'] }}/{{ $stealth['max_score'] }})</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="small-box bg-{{ $dnsCheck['real_ip_exposed'] ? 'red' : 'green' }}">
                            <div class="inner">
                                <h3>{{ $dnsCheck['real_ip_exposed'] ? 'EXPOSED' : 'Hidden' }}</h3>
                                <p>DNS Leak Status</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-xs-12">
                        <h4>DNS Records</h4>
                        <table class="table table-bordered">
                            <tr>
                                <td><strong>A Records</strong></td>
                                <td>
                                    @forelse(($stealth['records']['A'] ?? []) as $a)
                                        <span class="label label-danger">{{ $a }}</span>
                                    @empty
                                        <span class="label label-success">None (stealth)</span>
                                    @endforelse
                                </td>
                            </tr>
                            <tr>
                                <td><strong>AAAA Records</strong></td>
                                <td>
                                    @forelse(($stealth['records']['AAAA'] ?? []) as $a)
                                        <span class="label label-danger">{{ $a }}</span>
                                    @empty
                                        <span class="label label-success">None</span>
                                    @endforelse
                                </td>
                            </tr>
                            <tr>
                                <td><strong>CNAME</strong></td>
                                <td>{{ implode(', ', $stealth['records']['CNAME'] ?? []) ?: 'None' }}</td>
                            </tr>
                        </table>
                    </div>
                </div>

                <div class="row">
                    <div class="col-xs-12">
                        <h4>Stealth Checks</h4>
                        <table class="table table-hover">
                            @foreach(($stealth['checks'] ?? []) as $key => $check)
                                <tr>
                                    <td>{{ $check['pass'] ? '✅' : '🔴' }}</td>
                                    <td>{{ $check['detail'] }}</td>
                                </tr>
                            @endforeach
                        </table>
                    </div>
                </div>

                <div class="row">
                    <div class="col-xs-12">
                        <h4>Actions</h4>
                        <form action="{{ route('admin.security.orange_cloud') }}" method="POST" style="display:inline">@csrf<button type="submit" class="btn btn-warning btn-sm">Orange Cloud (Proxy ON)</button></form>
                        <form action="{{ route('admin.security.cf_iptables') }}" method="POST" style="display:inline">@csrf<button type="submit" class="btn btn-success btn-sm">CF-Only IPTables</button></form>
                        <form action="{{ route('admin.security.block_direct_ip') }}" method="POST" style="display:inline">@csrf<button type="submit" class="btn btn-danger btn-sm">Block Direct IP</button></form>
                        <form action="{{ route('admin.security.real_ip') }}" method="POST" style="display:inline">@csrf<button type="submit" class="btn btn-info btn-sm">Real-IP Config</button></form>
                        <form action="{{ route('admin.security.ssl_strict') }}" method="POST" style="display:inline">@csrf<button type="submit" class="btn btn-success btn-sm">SSL Strict</button></form>
                        <form action="{{ route('admin.security.stealth_on') }}" method="POST" style="display:inline">@csrf<button type="submit" class="btn btn-danger btn-sm">DNS Stealth ON</button></form>
                        <form action="{{ route('admin.security.stealth_off') }}" method="POST" style="display:inline">@csrf<button type="submit" class="btn btn-default btn-sm">DNS Stealth OFF</button></form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
