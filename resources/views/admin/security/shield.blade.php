@extends('layouts.admin')

@section('title')
    Codenest Shield
@endsection

@section('content-header')
    <h1>Codenest Shield<small>Unified security protection suite</small></h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('admin.index') }}">Admin</a></li>
        <li><a href="{{ route('admin.security.index') }}">Security</a></li>
        <li class="active">Codenest Shield</li>
    </ol>
@endsection

@section('content')
<style>
    .shield-status { text-align:center; padding:30px; border-radius:12px; margin-bottom:25px; }
    .shield-armed { background:linear-gradient(135deg,#1a1a2e,#16213e); border:2px solid #22c55e; }
    .shield-disarmed { background:linear-gradient(135deg,#1a1a2e,#16213e); border:2px solid #ef4444; }
    .shield-status-icon { font-size:64px; margin-bottom:10px; }
    .shield-status-label { font-size:28px; font-weight:700; text-transform:uppercase; letter-spacing:3px; }
    .shield-status-sub { font-size:14px; color:#94a3b8; margin-top:5px; }
    .shield-btn { padding:12px 40px; font-size:16px; font-weight:600; border-radius:8px; border:none; cursor:pointer; transition:all .2s; }
    .shield-btn-armed { background:#22c55e; color:#fff; }
    .shield-btn-armed:hover { background:#16a34a; }
    .shield-btn-disarmed { background:#ef4444; color:#fff; }
    .shield-btn-disarmed:hover { background:#dc2626; }
    .layer-card { background:#1e1e32; border:1px solid #2a2a3e; border-radius:10px; padding:18px 20px; display:flex; align-items:center; gap:15px; transition:all .2s; }
    .layer-card:hover { border-color:#3b82f6; }
    .layer-icon { width:42px; height:42px; border-radius:8px; display:flex; align-items:center; justify-content:center; font-size:20px; flex-shrink:0; }
    .layer-icon-active { background:rgba(34,197,94,.15); color:#22c55e; }
    .layer-icon-inactive { background:rgba(239,68,68,.15); color:#ef4444; }
    .layer-icon-warning { background:rgba(245,158,11,.15); color:#f59e0b; }
    .layer-info { flex:1; }
    .layer-name { font-size:14px; font-weight:600; color:#e2e8f0; }
    .layer-desc { font-size:12px; color:#94a3b8; }
    .layer-badge { padding:3px 10px; border-radius:20px; font-size:11px; font-weight:600; }
    .badge-on { background:rgba(34,197,94,.15); color:#22c55e; }
    .badge-off { background:rgba(239,68,68,.15); color:#ef4444; }
    .badge-partial { background:rgba(245,158,11,.15); color:#f59e0b; }
    .stat-card { background:#1e1e32; border:1px solid #2a2a3e; border-radius:10px; padding:20px; text-align:center; }
    .stat-value { font-size:32px; font-weight:700; color:#e2e8f0; }
    .stat-label { font-size:12px; color:#94a3b8; text-transform:uppercase; letter-spacing:1px; margin-top:4px; }
    .stat-card-red .stat-value { color:#ef4444; }
    .stat-card-green .stat-value { color:#22c55e; }
    .stat-card-blue .stat-value { color:#3b82f6; }
    .stat-card-yellow .stat-value { color:#f59e0b; }
</style>

{{-- SHIELD STATUS --}}
<div class="row">
    <div class="col-xs-12">
        <div class="shield-status {{ $shieldArmed ? 'shield-armed' : 'shield-disarmed' }}">
            <div class="shield-status-icon">{{ $shieldArmed ? '🛡️' : '⚠️' }}</div>
            <div class="shield-status-label" style="color:{{ $shieldArmed ? '#22c55e' : '#ef4444' }}">{{ $shieldArmed ? 'SHIELD ARMED' : 'SHIELD DISARMED' }}</div>
            <div class="shield-status-sub">{{ $shieldArmed ? 'All protection layers are active. Your infrastructure is secure.' : 'Protection layers are inactive. Your infrastructure may be vulnerable.' }}</div>
            <div style="margin-top:18px;">
                @if($shieldArmed)
                    <form action="{{ route('admin.security.shield.disarm') }}" method="POST" style="display:inline">
                        @csrf
                        <button class="shield-btn shield-btn-disarmed">DISARM SHIELD</button>
                    </form>
                @else
                    <form action="{{ route('admin.security.shield.arm') }}" method="POST" style="display:inline">
                        @csrf
                        <button class="shield-btn shield-btn-armed">ARM SHIELD</button>
                    </form>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- STATS ROW --}}
<div class="row" style="margin-bottom:20px;">
    <div class="col-md-3 col-xs-6">
        <div class="stat-card stat-card-red">
            <div class="stat-value">{{ $stats['blocked_ips'] }}</div>
            <div class="stat-label">Blocked IPs</div>
        </div>
    </div>
    <div class="col-md-3 col-xs-6">
        <div class="stat-card stat-card-yellow">
            <div class="stat-value">{{ $stats['attacks_detected'] }}</div>
            <div class="stat-label">Attacks Detected</div>
        </div>
    </div>
    <div class="col-md-3 col-xs-6">
        <div class="stat-card stat-card-blue">
            <div class="stat-value">{{ $stats['rate_limits_hit'] }}</div>
            <div class="stat-label">Rate Limits Hit</div>
        </div>
    </div>
    <div class="col-md-3 col-xs-6">
        <div class="stat-card stat-card-green">
            <div class="stat-value">{{ $stats['honeypot_hits'] }}</div>
            <div class="stat-label">Honeypot Hits</div>
        </div>
    </div>
</div>

{{-- PROTECTION LAYERS --}}
<div class="row">
    <div class="col-xs-12">
        <div class="box box-primary">
            <div class="box-header with-border"><h3 class="box-title">Protection Layers</h3></div>
            <div class="box-body">
                <div class="row" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(320px,1fr));gap:12px;">
                    @foreach($layers as $layer)
                        <div class="layer-card">
                            <div class="layer-icon layer-icon-{{ $layer['status_class'] }}">{{ $layer['icon'] }}</div>
                            <div class="layer-info">
                                <div class="layer-name">{{ $layer['name'] }}</div>
                                <div class="layer-desc">{{ $layer['desc'] }}</div>
                            </div>
                            <span class="layer-badge badge-{{ $layer['badge_class'] }}">{{ $layer['badge'] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

{{-- RECENT EVENTS --}}
<div class="row">
    <div class="col-xs-12">
        <div class="box box-default">
            <div class="box-header with-border"><h3 class="box-title">Recent Security Events</h3></div>
            <div class="box-body table-responsive no-padding">
                <table class="table table-hover">
                    <thead>
                        <tr><th>Type</th><th>Severity</th><th>IP</th><th>Action</th><th>Time</th></tr>
                    </thead>
                    <tbody>
                        @forelse($recentEvents as $event)
                        <tr>
                            <td>{{ $event->type }}</td>
                            <td><span class="label label-{{ $event->severity === 'high' ? 'danger' : ($event->severity === 'medium' ? 'warning' : 'info') }}">{{ $event->severity }}</span></td>
                            <td><code>{{ $event->ip ?? '-' }}</code></td>
                            <td>{{ $event->action_taken ?? '-' }}</td>
                            <td>{{ $event->detected_at ? \Carbon\Carbon::parse($event->detected_at)->diffForHumans() : '-' }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="5" style="text-align:center;color:#94a3b8;">No security events recorded yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="box-footer">
                <a href="{{ route('admin.security.detection') }}" class="btn btn-sm btn-primary">View Full Logs</a>
                <a href="{{ route('admin.security.ip_management') }}" class="btn btn-sm btn-default">Manage Blocked IPs</a>
            </div>
        </div>
    </div>
</div>
@endsection
