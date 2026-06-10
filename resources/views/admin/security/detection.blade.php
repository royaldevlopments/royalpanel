@extends('layouts.admin')
@section('title') Security - Attack Detection @endsection
@section('content')
<div class="row">
    <div class="col-xs-12">
        <div class="box box-danger">
            <div class="box-header with-border">
                <h3 class="box-title">Attack Detection</h3>
                <div class="box-tools">
                    <a href="{{ route('admin.security.index') }}" class="btn btn-sm btn-default">Back</a>
                </div>
            </div>
            <form action="{{ route('admin.security.save') }}" method="POST">
                @csrf
                <div class="box-body">
                    <div class="form-group">
                        <label>Enable Attack Detection</label>
                        <input type="hidden" name="detection_enabled" value="0">
                        <input type="checkbox" name="detection_enabled" value="1" {{ $detectionConfig['enabled'] ? 'checked' : '' }}>
                    </div>
                    <h4>Thresholds</h4>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Max Requests/IP/Minute</label>
                                <input type="number" name="detection_threshold_requests" class="form-control" value="{{ $detectionConfig['threshold']['requests_per_ip_per_minute'] }}">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Max Concurrent/IP</label>
                                <input type="number" name="detection_threshold_concurrent" class="form-control" value="{{ $detectionConfig['threshold']['concurrent_connections_per_ip'] }}">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Max Unique IPs/Minute</label>
                                <input type="number" name="detection_threshold_unique_ips" class="form-control" value="{{ $detectionConfig['threshold']['unique_ips_per_minute'] }}">
                            </div>
                        </div>
                    </div>
                    <h4>Auto Actions</h4>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Enable Under Attack Mode</label>
                                <input type="hidden" name="auto_under_attack" value="0">
                                <input type="checkbox" name="auto_under_attack" value="1" {{ $detectionConfig['auto_actions']['enable_under_attack_mode'] ? 'checked' : '' }}>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Enable Bot Fight Mode</label>
                                <input type="hidden" name="auto_bot_fight" value="0">
                                <input type="checkbox" name="auto_bot_fight" value="1" {{ $detectionConfig['auto_actions']['enable_bot_fight_mode'] ? 'checked' : '' }}>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Block Offending IPs</label>
                                <input type="hidden" name="auto_block_ips" value="0">
                                <input type="checkbox" name="auto_block_ips" value="1" {{ $detectionConfig['auto_actions']['block_offending_ips'] ? 'checked' : '' }}>
                            </div>
                        </div>
                    </div>
                    <h4>Auto Attack Response</h4>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Auto Response (Attack triggers full protection)</label>
                                <input type="hidden" name="auto_response_enabled" value="0">
                                <input type="checkbox" name="auto_response_enabled" value="1" {{ $autoResponseConfig['enabled'] ? 'checked' : '' }}>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Grace Period (minutes)</label>
                                <input type="number" name="auto_response_grace" class="form-control" value="{{ $autoResponseConfig['grace_minutes'] }}" min="1" max="60">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-xs-12">
                            <form action="{{ route('admin.security.auto_response_on') }}" method="POST" style="display:inline">@csrf<button type="submit" class="btn btn-success btn-sm">Enable Auto Response</button></form>
                            <form action="{{ route('admin.security.auto_response_off') }}" method="POST" style="display:inline">@csrf<button type="submit" class="btn btn-default btn-sm">Disable Auto Response</button></form>
                        </div>
                    </div>
                </div>
                <div class="box-footer">
                    <button type="submit" class="btn btn-primary">Save Settings</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
