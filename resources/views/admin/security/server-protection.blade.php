@extends('layouts.admin')
@section('title') Security - Server Protection @endsection
@section('content')
<div class="row">
    <div class="col-xs-12">
        <div class="box box-danger">
            <div class="box-header with-border">
                <h3 class="box-title">Server Protection (iptables)</h3>
                <div class="box-tools">
                    <a href="{{ route('admin.security.index') }}" class="btn btn-sm btn-default">Back</a>
                </div>
            </div>
            <div class="box-body">
                <div class="callout callout-info">
                    <h4>OVH-Level Protection Stack</h4>
                    <p>Apply the full OVH-grade iptables protection. Requires root/sudo access on the server.</p>
                </div>

                <div class="row">
                    <div class="col-xs-12">
                        <h4>Protection Layers</h4>
                        <ul class="list-group">
                            <li class="list-group-item">🛡️ Default DROP policy on INPUT/FORWARD</li>
                            <li class="list-group-item">✅ Allow established/related connections</li>
                            <li class="list-group-item">✅ Allow loopback</li>
                            <li class="list-group-item">🔧 Kernel tuning (BBR, buffers, FIN timeout, TW reuse)</li>
                            <li class="list-group-item">🛡️ SYNPROXY + syncookies + backlog 65536</li>
                            <li class="list-group-item">🛡️ Invalid packet drop (XMAS, NULL, SYN-RST, SYN-FIN scans)</li>
                            <li class="list-group-item">🛡️ Fragment drop + tiny MSS block</li>
                            <li class="list-group-item">🛡️ Port scan detection (10 hits in 10s = DROP)</li>
                            <li class="list-group-item">🛡️ connlimit: 20 connections per IP</li>
                            <li class="list-group-item">🛡️ hashlimit: 30 req/s per IP with burst 50</li>
                            <li class="list-group-item">🛡️ recent module: 60 hits in 30s = DROP</li>
                            <li class="list-group-item">🛡️ Ping flood: hashlimit 5/s per IP</li>
                            <li class="list-group-item">🛡️ UDP flood: hashlimit 50/s per IP</li>
                            <li class="list-group-item">🛡️ SSH brute-force: 5 attempts in 60s = DROP</li>
                        </ul>
                    </div>
                </div>

                <div class="row">
                    <div class="col-xs-12">
                        <h4>Actions</h4>
                        <form action="{{ route('admin.security.ovh_level') }}" method="POST" style="display:inline">@csrf<button type="submit" class="btn btn-danger">Apply OVH-Level Protection</button></form>
                        <form action="{{ route('admin.security.kernel_tune') }}" method="POST" style="display:inline">@csrf<button type="submit" class="btn btn-warning btn-sm">Kernel Tuning</button></form>
                        <form action="{{ route('admin.security.flush_iptables') }}" method="POST" style="display:inline">@csrf<button type="submit" class="btn btn-default btn-sm">Flush iptables</button></form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
