@extends('layouts.admin')

@section('title')
    Royal Feature Toggle
@endsection

@section('content-header')
    <div class="col-sm-6">
        <h1>Royal Feature Toggle</h1>
        <p class="text-muted">Enable or disable experimental features</p>
    </div>
    <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">Admin</a></li>
            <li class="breadcrumb-item"><span>Extensions</span></li>
            <li class="breadcrumb-item active">Feature Toggle</li>
        </ol>
    </div>
@endsection

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">Available Features</h3>
            </div>
            <div class="box-body">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                        <p><strong>Success!</strong> {{ session('success') }}</p>
                    </div>
                @endif

                <form method="POST" action="{{ route('admin.extensions.royalfeaturetoggle.update') }}">
                    {{ csrf_field() }}
                    <div class="box-body">
                        <div class="form-group">
                            <label>Enable Advanced Analytics</label>
                            <div class="checkbox">
                                <label>
                                    <input type="checkbox" name="features[]" value="advanced_analytics" @if(in_array('advanced_analytics', $features ?? [])) checked @endif>
                                    Track detailed usage analytics
                                </label>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Enable Developer Mode</label>
                            <div class="checkbox">
                                <label>
                                    <input type="checkbox" name="features[]" value="developer_mode" @if(in_array('developer_mode', $features ?? [])) checked @endif>
                                    Show debug information and developer tools
                                </label>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Enable Maintenance Mode</label>
                            <div class="checkbox">
                                <label>
                                    <input type="checkbox" name="features[]" value="maintenance_mode" @if(in_array('maintenance_mode', $features ?? [])) checked @endif>
                                    Display maintenance page to users
                                </label>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Enable Experimental UI</label>
                            <div class="checkbox">
                                <label>
                                    <input type="checkbox" name="features[]" value="experimental_ui" @if(in_array('experimental_ui', $features ?? [])) checked @endif>
                                    Test new interface designs
                                </label>
                            </div>
                        </div>
                    </div>
                    <!-- /.box-body -->
                    <div class="box-footer">
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
            <!-- /.box-body -->
        </div>
        <!-- /.box -->
    </div>
    <!-- /.col -->
</div>
<!-- /.row -->
@endsection