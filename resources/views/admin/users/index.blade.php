@extends('layouts.admin')

@section('title')
    List Users
@endsection

@section('content-header')
    <h1>Users<small>All registered users on the system.</small></h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('admin.index') }}">Admin</a></li>
        <li class="active">Users</li>
    </ol>
@endsection

@section('content')
<div class="row">
    <div class="col-xs-12">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">User List</h3>
                <div class="box-tools search01">
                    <form action="{{ route('admin.users') }}" method="GET">
                        <div class="input-group input-group-sm">
                            <input type="text" name="filter[email]" class="form-control pull-right" value="{{ request()->input('filter.email') }}" placeholder="Search">
                            <div class="input-group-btn">
                                <button type="submit" class="btn btn-default"><i class="fa fa-search"></i></button>
                                <a href="{{ route('admin.users.new') }}"><button type="button" class="btn btn-sm btn-primary" style="border-radius: 0 3px 3px 0;margin-left:-1px;">Create New</button></a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <div class="box-body table-responsive no-padding">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th width="30"><input type="checkbox" class="select-all" onchange="toggleSelectAll(this)"></th>
                            <th>ID</th>
                            <th>Email</th>
                            <th>Client Name</th>
                            <th>Username</th>
                            <th class="text-center">2FA</th>
                            <th class="text-center"><span data-toggle="tooltip" data-placement="top" title="Servers that this user is marked as the owner of.">Servers Owned</span></th>
                            <th class="text-center"><span data-toggle="tooltip" data-placement="top" title="Servers that this user can access because they are marked as a subuser.">Can Access</span></th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($users as $user)
                            <tr class="align-middle" data-id="{{ $user->id }}">
                                <td><input type="checkbox" class="select-row" value="{{ $user->id }}" onchange="updateBulkActions()"></td>
                                <td><code>{{ $user->id }}</code></td>
                                <td><a href="{{ route('admin.users.view', $user->id) }}">{{ $user->email }}</a> @if($user->root_admin)<i class="fa fa-star text-yellow"></i>@endif</td>
                                <td>{{ $user->name_last }}, {{ $user->name_first }}</td>
                                <td>{{ $user->username }}</td>
                                <td class="text-center">
                                    @if($user->use_totp)
                                        <i class="fa fa-lock text-green"></i>
                                    @else
                                        <i class="fa fa-unlock text-red"></i>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('admin.servers', ['filter[owner_id]' => $user->id]) }}">{{ $user->servers_count }}</a>
                                </td>
                                <td class="text-center">{{ $user->subuser_of_count }}</td>
                                <td class="text-center"><img src="https://www.gravatar.com/avatar/{{ md5(strtolower($user->email)) }}?s=100" style="height:20px;" class="img-circle" /></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="box-footer with-border" style="display:flex;align-items:center;justify-content:space-between;">
                <div>
                    <div class="btn-group" style="display:none;" id="bulk-actions">
                        <button class="btn btn-sm btn-default dropdown-toggle" data-toggle="dropdown" disabled id="bulk-toggle">
                            <i class="fa fa-tasks"></i> Mass Actions <span class="caret"></span>
                        </button>
                        <ul class="dropdown-menu">
                            <li><a href="#" onclick="return bulkAction('delete')"><i class="fa fa-trash text-danger"></i> Delete</a></li>
                        </ul>
                    </div>
                </div>
                <div>
                    @if($users->hasPages())
                        {!! $users->appends(['query' => Request::input('query')])->render() !!}
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('footer-scripts')
    @parent
    <script>
        function toggleSelectAll(master) {
            document.querySelectorAll('.select-row').forEach(function(cb) {
                cb.checked = master.checked;
            });
            updateBulkActions();
        }

        function updateBulkActions() {
            var checked = document.querySelectorAll('.select-row:checked').length;
            var container = document.getElementById('bulk-actions');
            var toggle = document.getElementById('bulk-toggle');
            container.style.display = checked > 0 ? '' : 'none';
            toggle.disabled = checked === 0;
        }

        function bulkAction(action) {
            var checked = document.querySelectorAll('.select-row:checked');
            if (!checked.length) return false;

            var labels = {
                'delete': 'Are you sure you want to delete the selected users?',
            };

            swal({
                title: '',
                type: 'warning',
                text: labels[action] || 'Are you sure?',
                showCancelButton: true,
                confirmButtonText: 'Yes',
                confirmButtonColor: '#d9534f',
                closeOnConfirm: false,
                showLoaderOnConfirm: true,
            }, function () {
                var ids = [];
                checked.forEach(function(cb) { ids.push(parseInt(cb.value)); });

                $.ajax({
                    method: 'POST',
                    url: '{{ route('admin.users.bulk') }}',
                    headers: { 'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content') },
                    data: JSON.stringify({ action: action, ids: ids }),
                    contentType: 'application/json',
                    processData: false,
                }).done(function (res) {
                    swal.close();
                    if (res.errors && res.errors.length) {
                        swal({ type: 'error', title: 'Errors', text: res.errors.length + ' operation(s) failed.' });
                    } else {
                        location.reload();
                    }
                }).fail(function () {
                    swal({ type: 'error', title: 'Whoops!', text: 'Something went wrong.' });
                });
            });
            return false;
        }
    </script>
@endsection
