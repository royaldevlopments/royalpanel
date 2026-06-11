@extends('layouts.admin')
@section('title') Security - Two-Factor Authentication @endsection
@section('content')
<div class="row">
    <div class="col-xs-12">
        <div class="box box-info">
            <div class="box-header with-border">
                <h3 class="box-title">Two-Factor Authentication Enforcement</h3>
                <div class="box-tools">
                </div>
            </div>
            <form action="{{ route('admin.security.two_factor.save') }}" method="POST">
                @csrf
                <div class="box-body">
                    <div class="form-group">
                        <label>Enforcement Level</label>
                        <select name="enforce_level" class="form-control">
                            <option value="none" {{ config('security.two_factor.enforce_level', 'none') === 'none' ? 'selected' : '' }}>Disabled (no enforcement)</option>
                            <option value="admin" {{ config('security.two_factor.enforce_level', 'none') === 'admin' ? 'selected' : '' }}>Require for admins only</option>
                            <option value="all" {{ config('security.two_factor.enforce_level', 'none') === 'all' ? 'selected' : '' }}>Require for all users</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Grace Period (days before forced setup)</label>
                        <input type="number" name="grace_period" class="form-control" value="{{ config('security.two_factor.grace_period', 7) }}" min="0" max="90">
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
