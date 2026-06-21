@extends('layouts.admin')

@section('title')
    @lang('admin/index.title')
@endsection

@section('content-header')
@endsection

@section('content')
<div class="page-header"><h1><i class="fa fa-dashboard text-primary"></i> Dashboard</h1></div>

<div class="row">
    <div class="col-xs-12">
        {{-- UPDATE STATUS --}}
        <div class="panel-card">
            <div class="panel-card-header">
                <div style="display:flex;align-items:center;gap:8px;font-size:14px;font-weight:600;color:#e2e8f0;">
                    @if($version->isLatestPanel())
                        <span>&#10003;</span>
                        <span>@lang('admin/index.uptodate-header')</span>
                        <span class="badge-pill badge-success">Up to date</span>
                    @else
                        <span>&#9888;</span>
                        <span>@lang('admin/index.notuptodate-header')</span>
                        <span class="badge-pill badge-warning">Update available</span>
                    @endif
                </div>
            </div>
            <div class="panel-card-body">
                @if($version->isLatestPanel())
                    {!! __("admin/index.uptodate-body", ["version" => config("app.version")]) !!}
                @else
                    {!! __("admin/index.notuptodate-body", ["version" => config("app.version"), "latest" => $version->getPanel()]) !!}
                @endif
            </div>
        </div>
    </div>
</div>

{{-- FEEDBACK + SPONSOR --}}
<div class="row">
    <div class="col-md-6">
        <div class="panel-card">
            <div class="panel-card-header" onclick="revToggle(this)" style="cursor:pointer;">
                <h3 class="panel-card-title">
                    <span>&lt;/&gt;</span>
                    <span>@lang('admin/index.feedback-header')</span>
                </h3>
                <div style="display:flex;align-items:center;gap:8px;">
                    <a href="https://github.com/royaldevlopments/royalpanel/issues" target="_blank" class="btn btn-primary btn-sm"><i class="fa fa-bug"></i> @lang('admin/index.feedback-btn')</a>
                    <i class="fa fa-chevron-down" style="transition:transform 0.2s;color:#94a3b8;"></i>
                </div>
            </div>
            <div class="panel-card-body">@lang('admin/index.feedback-body')</div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="panel-card">
            <div class="panel-card-header" onclick="revToggle(this)" style="cursor:pointer;">
                <h3 class="panel-card-title">
                    <span>&#9829;</span>
                    <span>@lang('admin/index.sponsor-header')</span>
                </h3>
                <div style="display:flex;align-items:center;gap:8px;">
                    <a href="{{ $version->getDonations() }}" target="_blank" class="btn btn-success btn-sm"><i class="fa fa-heart"></i> @lang('admin/index.sponsor-btn')</a>
                    <i class="fa fa-chevron-down" style="transition:transform 0.2s;color:#94a3b8;"></i>
                </div>
            </div>
            <div class="panel-card-body">@lang('admin/index.sponsor-body')</div>
        </div>
    </div>
</div>

{{-- USER ACTIVITY + RECENT ACTIVITY --}}
<div class="row">
    <div class="col-md-6">
        <div class="panel-card">
            <div class="panel-card-header" onclick="revToggle(this)" style="cursor:pointer;">
                <h3 class="panel-card-title">
                    <span>&#127760;</span>
                    <span>User Activity Metrics</span>
                </h3>
                <i class="fa fa-chevron-down" style="transition:transform 0.2s;color:#94a3b8;"></i>
            </div>
            <div class="panel-card-body">
                @if(empty($topCountries))
                    <p style="color:#64748b;">No activity data yet.</p>
                @else
                    @php $maxCount = $topCountries[0]['count']; @endphp
                    @foreach($topCountries as $i => $data)
                        @php
                            $code = strtolower($data['code']);
                            $pct  = $maxCount > 0 ? round(($data['count'] / $maxCount) * 100, 1) : 0;
                        @endphp
                        <div style="display:flex;align-items:center;gap:12px;margin-bottom:14px;">
                            <div style="display:flex;align-items:center;gap:8px;min-width:160px;">
                                @if(!in_array($code, ['un','local']))
                                    <img src="https://flagcdn.com/{{ $code }}.svg" style="width:20px;height:14px;border-radius:3px;object-fit:cover;">
                                @endif
                                <span style="font-size:13px;color:#e2e8f0;">{{ $data['country'] }} ({{ $pct }}%)</span>
                            </div>
                            <div style="flex:1;background:#2a2a3e;border-radius:8px;height:12px;overflow:hidden;">
                                <div style="height:12px;border-radius:8px;width:{{ $pct }}%;{{ $i === 0 ? 'background:linear-gradient(to right,#22c55e,#16a34a);' : 'background:linear-gradient(to right,#3b82f6,#6366f1);' }}"></div>
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="panel-card">
            <div class="panel-card-header" onclick="revToggle(this)" style="cursor:pointer;">
                <h3 class="panel-card-title">
                    <span>&#128203;</span>
                    <span>Recent Activity</span>
                </h3>
                <i class="fa fa-chevron-down" style="transition:transform 0.2s;color:#94a3b8;"></i>
            </div>
            <div class="panel-card-body">
                @forelse($recentLogs as $log)
                    @php
                        $actor = $log->actor;
                        $actorName = ($actor && isset($actor->username)) ? $actor->username : 'System';
                    @endphp
                    <div style="padding:6px 0;border-bottom:1px solid #2a2a3e;font-size:13px;color:#94a3b8;">{{ $actorName }} &mdash; {{ $log->event }} &mdash; {{ $log->timestamp->diffForHumans() }}</div>
                @empty
                    <p style="color:#64748b;">No recent activity.</p>
                @endforelse
                
            </div>
        </div>
    </div>
</div>

<script>
</script>
@endsection
