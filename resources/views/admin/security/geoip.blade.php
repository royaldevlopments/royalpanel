@extends('layouts.admin')
@section('title') Security - Country Blocking @endsection
@section('content')
<div class="row">
    <div class="col-xs-12">
        <div class="box box-warning">
            <div class="box-header with-border">
                <h3 class="box-title">Country Geo-Blocking</h3>
                <div class="box-tools">
                    <a href="{{ route('admin.security.index') }}" class="btn btn-sm btn-default">Back</a>
                </div>
            </div>
            <form action="{{ route('admin.security.geoip.save') }}" method="POST">
                @csrf
                <div class="box-body">
                    <div class="form-group">
                        <label>Enable Country Blocking</label>
                        <input type="hidden" name="enabled" value="0">
                        <input type="checkbox" name="enabled" value="1" {{ config('security.ip_blocking.country_block.enabled') ? 'checked' : '' }}>
                    </div>
                    <div class="form-group">
                        <label>Mode</label>
                        <select name="mode" class="form-control">
                            <option value="block" {{ config('security.ip_blocking.country_block.mode') === 'block' ? 'selected' : '' }}>Block selected countries</option>
                            <option value="allow" {{ config('security.ip_blocking.country_block.mode') === 'allow' ? 'selected' : '' }}>Only allow selected countries</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Countries (comma-separated ISO codes, e.g. US,CN,RU)</label>
                        <textarea name="countries" class="form-control" rows="5">{{ implode(',', config('security.ip_blocking.country_block.blocked_countries', [])) ?: implode(',', config('security.ip_blocking.country_block.allowed_countries', [])) }}</textarea>
                    </div>
                </div>
                <div class="box-footer">
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
