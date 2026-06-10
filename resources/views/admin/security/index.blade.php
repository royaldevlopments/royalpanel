@extends('layouts.admin')

@section('title')
    Security
@endsection

@section('content')
    <div class="row">
        <div class="col-xs-12">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">Security Overview</h3>
                    <div class="box-tools">
                        <a href="{{ route('admin.security.cloudflare') }}" class="btn btn-sm btn-info">Cloudflare</a>
                        <a href="{{ route('admin.security.rate_limiting') }}" class="btn btn-sm btn-info">Rate Limiting</a>
                        <a href="{{ route('admin.security.ip_management') }}" class="btn btn-sm btn-info">IP Management</a>
                        <a href="{{ route('admin.security.detection') }}" class="btn btn-sm btn-info">Detection</a>
                        <a href="{{ route('admin.security.honeypot') }}" class="btn btn-sm btn-info">Honeypot</a>
                        <a href="{{ route('admin.security.origin_protection') }}" class="btn btn-sm btn-warning">Origin</a>
                        <a href="{{ route('admin.security.server_protection') }}" class="btn btn-sm btn-danger">Server</a>
                    </div>
                </div>
                <div class="box-body">
                    <div class="row">
                        <div class="col-md-3 col-xs-6">
                            <div class="small-box bg-green">
                                <div class="inner">
                                    <h3>{{ $stats['total_attacks'] ?? 0 }}</h3>
                                    <p>Total Attacks Blocked</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 col-xs-6">
                            <div class="small-box bg-yellow">
                                <div class="inner">
                                    <h3>{{ $stats['attacks_today'] ?? 0 }}</h3>
                                    <p>Attacks Today</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 col-xs-6">
                            <div class="small-box bg-red">
                                <div class="inner">
                                    <h3>{{ $stats['active_blocks'] ?? 0 }}</h3>
                                    <p>Active IP Blocks</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 col-xs-6">
                            <div class="small-box bg-{{ $autoResponseStatus['under_attack'] ? 'red' : 'green' }}">
                                <div class="inner">
                                    <h3>{{ $autoResponseStatus['under_attack'] ? 'UNDER ATTACK' : 'Normal' }}</h3>
                                    <p>Attack Status</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-xs-12">
                            <div class="box">
                                <div class="box-header">
                                    <h3 class="box-title">Quick Actions</h3>
                                </div>
                                <div class="box-body">
                                    <form action="{{ route('admin.security.cleanup') }}" method="POST" style="display:inline">
                                        @csrf
                                        <button type="submit" class="btn btn-default">Cleanup Logs</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-xs-12">
                            <div class="box">
                                <div class="box-header">
                                    <h3 class="box-title">Recent Attacks</h3>
                                </div>
                                <div class="box-body table-responsive no-padding">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Type</th>
                                                <th>Severity</th>
                                                <th>IP</th>
                                                <th>Action Taken</th>
                                                <th>Time</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse(($stats['recent_attacks'] ?? []) as $attack)
                                                <tr>
                                                    <td>{{ $attack->type }}</td>
                                                    <td>
                                                        <span class="label label-{{ $attack->severity === 'critical' ? 'danger' : ($attack->severity === 'high' ? 'warning' : 'info') }}">
                                                            {{ $attack->severity }}
                                                        </span>
                                                    </td>
                                                    <td>{{ $attack->ip }}</td>
                                                    <td>{{ $attack->action_taken }}</td>
                                                    <td>{{ $attack->detected_at }}</td>
                                                </tr>
                                            @empty
                                                <tr><td colspan="5">No attacks recorded yet.</td></tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-xs-12">
                            <div class="box">
                                <div class="box-header">
                                    <h3 class="box-title">Attack by Type</h3>
                                </div>
                                <div class="box-body table-responsive no-padding">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Type</th>
                                                <th>Count</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse(($stats['attack_types'] ?? []) as $type)
                                                <tr>
                                                    <td>{{ $type->type }}</td>
                                                    <td>{{ $type->count }}</td>
                                                </tr>
                                            @empty
                                                <tr><td colspan="2">No data available.</td></tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
