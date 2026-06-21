@extends('layouts.admin')

@section('title')
    List Servers
@endsection

@section('content-header')
    <h1>Servers<small>All servers available on the system.</small></h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('admin.index') }}">Admin</a></li>
        <li class="active">Servers</li>
    </ol>
@endsection

@section('content')
<div class="row">
    <div class="col-xs-12">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">Server List</h3>
                <div class="box-tools search01">
                    <form action="{{ route('admin.servers') }}" method="GET">
                        <div class="input-group input-group-sm">
                            <input type="text" name="filter[*]" class="form-control pull-right" value="{{ request()->input()['filter']['*'] ?? '' }}" placeholder="Search Servers">
                            <div class="input-group-btn">
                                <button type="submit" class="btn btn-default"><i class="fa fa-search"></i></button>
                                <a href="{{ route('admin.servers.new') }}"><button type="button" class="btn btn-sm btn-primary" style="border-radius: 0 3px 3px 0;margin-left:-1px;">Create New</button></a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <div class="box-body table-responsive no-padding">
                <table class="table table-hover">
                    <tbody>
                        <tr>
                            <th width="30"><input type="checkbox" class="select-all" onchange="toggleSelectAll(this)"></th>
                            <th>Server Name</th>
                            <th>UUID</th>
                            <th>Owner</th>
                            <th>Node</th>
                            <th>Connection</th>
                            <th></th>
                            <th></th>
                        </tr>
                        @foreach ($servers as $server)
                            <tr data-server="{{ $server->uuidShort }}" data-id="{{ $server->id }}">
                                <td><input type="checkbox" class="select-row" value="{{ $server->id }}" onchange="updateBulkActions()"></td>
                                <td><a href="{{ route('admin.servers.view', $server->id) }}">{{ $server->name }}</a></td>
                                <td><code title="{{ $server->uuid }}">{{ $server->uuid }}</code></td>
                                <td><a href="{{ route('admin.users.view', $server->user->id) }}">{{ $server->user->username }}</a></td>
                                <td><a href="{{ route('admin.nodes.view', $server->node->id) }}">{{ $server->node->name }}</a></td>
                                <td>
                                    <code>{{ $server->allocation->alias }}:{{ $server->allocation->port }}</code>
                                </td>
                                <td class="text-center">
                                    @if($server->isSuspended())
                                        <span class="label bg-maroon">Suspended</span>
                                    @elseif(! $server->isInstalled())
                                        <span class="label label-warning">Installing</span>
                                    @else
                                        <span class="label label-success">Active</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <a class="btn btn-xs btn-default" href="/server/{{ $server->uuidShort }}"><i class="fa fa-wrench"></i></a>
                                </td>
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
                            <li><a href="#" onclick="return bulkAction('suspend')"><i class="fa fa-pause text-warning"></i> Suspend</a></li>
                            <li><a href="#" onclick="return bulkAction('unsuspend')"><i class="fa fa-play text-success"></i> Unsuspend</a></li>
                            <li><a href="#" onclick="return bulkAction('reinstall')"><i class="fa fa-refresh text-info"></i> Reinstall</a></li>
                            <li><a href="#" onclick="return bulkAction('toggle-install')"><i class="fa fa-toggle-on text-primary"></i> Toggle Install</a></li>
                        </ul>
                    </div>
                </div>
                <div>
                    @if($servers->hasPages())
                        {!! $servers->appends(['filter' => Request::input('filter')])->render() !!}
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
                'delete': 'Are you sure you want to delete the selected servers?',
                'suspend': 'Are you sure you want to suspend the selected servers?',
                'unsuspend': 'Are you sure you want to unsuspend the selected servers?',
                'reinstall': 'Are you sure you want to reinstall the selected servers?',
                'toggle-install': 'Are you sure you want to toggle the install status of the selected servers?',
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
                    url: '{{ route('admin.servers.bulk') }}',
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
