@extends('layouts.admin')

@section('title')
    Create User
@endsection

@section('content-header')
    <h1>Create User<small>Add a new user to the system.</small></h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('admin.index') }}">Admin</a></li>
        <li><a href="{{ route('admin.users') }}">Users</a></li>
        <li class="active">Create</li>
    </ol>
@endsection

@section('content')
<div class="row">
    <form method="post">
        {!! csrf_field() !!}

        <div class="step-indicator">
            <div class="step" id="step-indicator-1">Step 1 — Identity</div>
            <div class="step" id="step-indicator-2">Step 2 — Permissions &amp; Password</div>
        </div>

        <div id="step-content-1">
            <div class="panel-card">
                <div class="panel-card-header" onclick="revToggle(this)" style="cursor:pointer;">
                    <h3 class="panel-card-title">
                        <span><i class="fa fa-id-card"></i></span>
                        <span>Identity</span>
                    </h3>
                    <i class="fa fa-chevron-down" style="transition:transform 0.2s;color:#94a3b8;"></i>
                </div>
                <div class="panel-card-body">
                    <div class="form-group">
                        <label for="email" class="control-label">Email</label>
                        <div>
                            <input type="text" autocomplete="off" name="email" value="{{ old('email') }}" class="form-control" />
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="username" class="control-label">Username</label>
                        <div>
                            <input type="text" autocomplete="off" name="username" value="{{ old('username') }}" class="form-control" />
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="name_first" class="control-label">Client First Name</label>
                        <div>
                            <input type="text" autocomplete="off" name="name_first" value="{{ old('name_first') }}" class="form-control" />
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="name_last" class="control-label">Client Last Name</label>
                        <div>
                            <input type="text" autocomplete="off" name="name_last" value="{{ old('name_last') }}" class="form-control" />
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label">Default Language</label>
                        <div>
                            <select name="language" class="form-control">
                                @foreach($languages as $key => $value)
                                    <option value="{{ $key }}" @if(config('app.locale') === $key) selected @endif>{{ $value }}</option>
                                @endforeach
                            </select>
                            <p class="text-muted"><small>The default language to use when rendering the Panel for this user.</small></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div id="step-content-2" style="display:none;">
            <div class="panel-card">
                <div class="panel-card-header" onclick="revToggle(this)" style="cursor:pointer;">
                    <h3 class="panel-card-title">
                        <span><i class="fa fa-shield"></i></span>
                        <span>Permissions</span>
                    </h3>
                    <i class="fa fa-chevron-down" style="transition:transform 0.2s;color:#94a3b8;"></i>
                </div>
                <div class="panel-card-body">
                    <div class="form-group col-md-12">
                        <label for="root_admin" class="control-label">Administrator</label>
                        <div>
                            <select name="root_admin" class="form-control">
                                <option value="0">@lang('strings.no')</option>
                                <option value="1">@lang('strings.yes')</option>
                            </select>
                            <p class="text-muted"><small>Setting this to 'Yes' gives a user full administrative access.</small></p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="panel-card">
                <div class="panel-card-header" onclick="revToggle(this)" style="cursor:pointer;">
                    <h3 class="panel-card-title">
                        <span><i class="fa fa-lock"></i></span>
                        <span>Password</span>
                    </h3>
                    <i class="fa fa-chevron-down" style="transition:transform 0.2s;color:#94a3b8;"></i>
                </div>
                <div class="panel-card-body">
                    <div class="alert alert-info">
                        <p>Providing a user password is optional. New user emails prompt users to create a password the first time they login. If a password is provided here you will need to find a different method of providing it to the user.</p>
                    </div>
                    <div id="gen_pass" class=" alert alert-success" style="display:none;margin-bottom: 10px;"></div>
                    <div class="form-group">
                        <label for="pass" class="control-label">Password</label>
                        <div>
                            <input type="password" name="password" class="form-control" />
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="step-nav">
            <div>
                <button id="step-prev" class="btn btn-default" style="display:none;"><i class="fa fa-arrow-left"></i> Previous</button>
            </div>
            <div>
                <button id="step-next" class="btn btn-primary">Next <i class="fa fa-arrow-right"></i></button>
                <button id="step-submit" type="submit" class="btn btn-success" style="display:none;"><i class="fa fa-check"></i> Create User</button>
            </div>
        </div>
    </form>
</div>
@endsection

@section('footer-scripts')
    @parent
    <script>
        initStepWizard(2);
        $("#gen_pass_bttn").click(function (event) {
            event.preventDefault();
            $.ajax({
                type: "GET",
                url: "/password-gen/12",
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
               },
                success: function(data) {
                    $("#gen_pass").html('<strong>Generated Password:</strong> ' + data).slideDown();
                    $('input[name="password"], input[name="password_confirmation"]').val(data);
                    return false;
                }
            });
            return false;
        });
    </script>
@endsection
