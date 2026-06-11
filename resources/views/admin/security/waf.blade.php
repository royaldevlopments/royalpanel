@extends('layouts.admin')
@section('title') Security - Web Application Firewall @endsection
@section('content')
<div class="row">
    <div class="col-xs-12">
        <div class="box box-danger">
            <div class="box-header with-border">
                <h3 class="box-title">Web Application Firewall Rules</h3>
                <div class="box-tools">
                    <a href="{{ route('admin.security.index') }}" class="btn btn-sm btn-default">Back</a>
                </div>
            </div>
            <form action="{{ route('admin.security.waf.save') }}" method="POST">
                @csrf
                <div class="box-body">
                    <div class="form-group">
                        <label>Enable WAF</label>
                        <input type="hidden" name="enabled" value="0">
                        <input type="checkbox" name="enabled" value="1" {{ config('security.waf.enabled', false) ? 'checked' : '' }}>
                    </div>
                    <div class="form-group">
                        <label>Block SQL Injection</label>
                        <input type="hidden" name="block_sqli" value="0">
                        <input type="checkbox" name="block_sqli" value="1" {{ config('security.waf.block_sqli', true) ? 'checked' : '' }}>
                    </div>
                    <div class="form-group">
                        <label>Block XSS</label>
                        <input type="hidden" name="block_xss" value="0">
                        <input type="checkbox" name="block_xss" value="1" {{ config('security.waf.block_xss', true) ? 'checked' : '' }}>
                    </div>
                    <div class="form-group">
                        <label>Block Directory Traversal</label>
                        <input type="hidden" name="block_path_traversal" value="0">
                        <input type="checkbox" name="block_path_traversal" value="1" {{ config('security.waf.block_path_traversal', true) ? 'checked' : '' }}>
                    </div>
                </div>
                <div class="box-footer">
                    <button type="submit" class="btn btn-primary">Save WAF Settings</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
