@extends('layouts.admin')

@section('title')
    New Server
@endsection

@section('content-header')
    <h1>Create Server<small>Add a new server to the panel.</small></h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('admin.index') }}">Admin</a></li>
        <li><a href="{{ route('admin.servers') }}">Servers</a></li>
        <li class="active">Create Server</li>
    </ol>
@endsection

@section('content')
<form action="{{ route('admin.servers.new') }}" method="POST" id="serverForm" data-template-form>
    {!! csrf_field() !!}

    <div class="draft-notice" id="draft-notice">
        <span><i class="fa fa-save"></i> A saved draft was found.</span>
        <div style="display:flex;gap:6px;">
            <button class="btn btn-primary btn-sm" data-action="restore"><i class="fa fa-undo"></i> Restore</button>
            <button class="btn btn-default btn-sm" data-action="discard"><i class="fa fa-trash"></i> Discard</button>
        </div>
    </div>

    <div class="template-bar">
        <span style="color:#94a3b8;font-size:13px;"><i class="fa fa-bookmark"></i> Templates:</span>
        <select id="template-select" onchange="loadTemplate('server-create')">
            <option value="">— Load Template —</option>
        </select>
        <input type="text" id="template-name" placeholder="Template name" style="width:150px;">
        <button class="btn btn-info btn-sm" onclick="saveTemplate('server-create', document.getElementById('template-name').value)"><i class="fa fa-save"></i> Save</button>
        <button class="btn btn-danger btn-sm" onclick="deleteTemplate('server-create')"><i class="fa fa-trash"></i> Delete</button>
    </div>

    <div class="step-indicator">
        <div class="step" id="step-indicator-1">Step 1 — Core &amp; Allocation</div>
        <div class="step" id="step-indicator-2">Step 2 — Limits &amp; Resources</div>
        <div class="step" id="step-indicator-3">Step 3 — Nest, Docker &amp; Startup</div>
        <div class="step" id="step-indicator-4">Step 4 — Review &amp; Submit</div>
    </div>

    <div class="step-error" id="step-error"></div>

    <div id="step-content-1">
        <div class="row">
            <div class="col-xs-12">
                <div class="panel-card">
                    <div class="panel-card-header" onclick="revToggle(this)" style="cursor:pointer;">
                        <h3 class="panel-card-title">
                            <span><i class="fa fa-server"></i></span>
                            <span>Core Details</span>
                        </h3>
                        <i class="fa fa-chevron-down" style="transition:transform 0.2s;color:#94a3b8;"></i>
                    </div>
                    <div class="panel-card-body row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="pName">Server Name <span style="color:#e74c3c;">*</span></label>
                                <input type="text" class="form-control" id="pName" name="name" value="{{ old('name') }}" placeholder="Server Name" data-review data-review-label="Server Name" data-review-section="Core Details" required>
                                <p class="small text-muted no-margin">Character limits: <code>a-z A-Z 0-9 _ - .</code> and <code>[Space]</code>.</p>
                            </div>
                            <div class="form-group">
                                <label for="pUserId">Server Owner <span style="color:#e74c3c;">*</span></label>
                                <select id="pUserId" name="owner_id" class="form-control" style="padding-left:0;" data-review data-review-label="Server Owner" data-review-section="Core Details"></select>
                                <p class="small text-muted no-margin">Email address of the Server Owner.</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="pDescription" class="control-label">Server Description</label>
                                <textarea id="pDescription" name="description" rows="3" class="form-control" data-review data-review-label="Description" data-review-section="Core Details">{{ old('description') }}</textarea>
                                <p class="text-muted small">A brief description of this server.</p>
                            </div>
                            <div class="form-group">
                                <div class="checkbox checkbox-primary no-margin-bottom">
                                    <input id="pStartOnCreation" name="start_on_completion" type="checkbox" value="1" {{ \RoyalPanel\Helpers\Utilities::checked('start_on_completion', 1) }} data-review data-review-label="Start on Completion" data-review-section="Core Details" />
                                    <label for="pStartOnCreation" class="strong">Start Server when Installed</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-xs-12">
                <div class="panel-card">
                    <div class="panel-card-header" onclick="revToggle(this)" style="cursor:pointer;">
                        <h3 class="panel-card-title">
                            <span><i class="fa fa-plug"></i></span>
                            <span>Allocation Management</span>
                        </h3>
                        <i class="fa fa-chevron-down" style="transition:transform 0.2s;color:#94a3b8;"></i>
                    </div>
                    <div class="panel-card-body row">
                        <div class="form-group col-sm-4">
                            <label for="pNodeId">Node <span style="color:#e74c3c;">*</span></label>
                            <select name="node_id" id="pNodeId" class="form-control" data-review data-review-label="Node" data-review-section="Allocation">
                                @foreach($locations as $location)
                                    <optgroup label="{{ $location->long }} ({{ $location->short }})">
                                    @foreach($location->nodes as $node)
                                    <option value="{{ $node->id }}"
                                        @if($location->id === old('location_id')) selected @endif
                                    >{{ $node->name }}</option>
                                    @endforeach
                                    </optgroup>
                                @endforeach
                            </select>
                            <p class="small text-muted no-margin">The node which this server will be deployed to.</p>
                        </div>
                        <div class="form-group col-sm-4">
                            <label for="pAllocation">Default Allocation</label>
                            <select id="pAllocation" name="allocation_id" class="form-control" data-review data-review-label="Default Allocation" data-review-section="Allocation"></select>
                            <p class="small text-muted no-margin">The main allocation that will be assigned to this server.</p>
                        </div>
                        <div class="form-group col-sm-4">
                            <label for="pAllocationAdditional">Additional Allocation(s)</label>
                            <select id="pAllocationAdditional" name="allocation_additional[]" class="form-control" multiple data-review data-review-label="Additional Allocations" data-review-section="Allocation"></select>
                            <p class="small text-muted no-margin">Additional allocations to assign to this server on creation.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="step-content-2" style="display:none;">
        <div class="row">
            <div class="col-xs-12">
                <div class="panel-card">
                    <div class="panel-card-header" onclick="revToggle(this)" style="cursor:pointer;">
                        <h3 class="panel-card-title">
                            <span><i class="fa fa-tachometer"></i></span>
                            <span>Application Feature Limits</span>
                        </h3>
                        <i class="fa fa-chevron-down" style="transition:transform 0.2s;color:#94a3b8;"></i>
                    </div>
                    <div class="panel-card-body row">
                        <div class="form-group col-xs-6">
                            <label for="pDatabaseLimit" class="control-label">Database Limit</label>
                            <div>
                                <input type="text" id="pDatabaseLimit" name="database_limit" class="form-control" value="{{ old('database_limit', 0) }}" data-review data-review-label="Database Limit" data-review-section="Feature Limits" />
                            </div>
                            <p class="text-muted small">The total number of databases a user is allowed to create for this server.</p>
                        </div>
                        <div class="form-group col-xs-6">
                            <label for="pAllocationLimit" class="control-label">Allocation Limit</label>
                            <div>
                                <input type="text" id="pAllocationLimit" name="allocation_limit" class="form-control" value="{{ old('allocation_limit', 0) }}" data-review data-review-label="Allocation Limit" data-review-section="Feature Limits" />
                            </div>
                            <p class="text-muted small">The total number of allocations a user is allowed to create for this server.</p>
                        </div>
                        <div class="form-group col-xs-6">
                            <label for="pBackupLimit" class="control-label">Backup Limit</label>
                            <div>
                                <input type="text" id="pBackupLimit" name="backup_limit" class="form-control" value="{{ old('backup_limit', 0) }}" data-review data-review-label="Backup Limit" data-review-section="Feature Limits" />
                            </div>
                            <p class="text-muted small">The total number of backups that can be created for this server.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-xs-12">
                <div class="panel-card">
                    <div class="panel-card-header" onclick="revToggle(this)" style="cursor:pointer;">
                        <h3 class="panel-card-title">
                            <span><i class="fa fa-cube"></i></span>
                            <span>Resource Management</span>
                        </h3>
                        <i class="fa fa-chevron-down" style="transition:transform 0.2s;color:#94a3b8;"></i>
                    </div>
                    <div class="panel-card-body row">
                        <div class="form-group col-xs-6">
                            <label for="pCPU">CPU Limit</label>
                            <div class="input-group">
                                <input type="text" id="pCPU" name="cpu" class="form-control" value="{{ old('cpu', 0) }}" data-review data-review-label="CPU Limit" data-review-section="Resources" />
                                <span class="input-group-addon">%</span>
                            </div>
                            <p class="text-muted small">If you do not want to limit CPU usage, set the value to <code>0</code>.</p>
                        </div>
                        <div class="form-group col-xs-6">
                            <label for="pThreads">CPU Pinning</label>
                            <div>
                                <input type="text" id="pThreads" name="threads" class="form-control" value="{{ old('threads') }}" data-review data-review-label="CPU Pinning" data-review-section="Resources" />
                            </div>
                            <p class="text-muted small"><strong>Advanced:</strong> Enter the specific CPU threads.</p>
                        </div>
                    </div>
                    <div class="panel-card-body row">
                        <div class="form-group col-xs-6">
                            <label for="pMemory">Memory</label>
                            <div class="input-group">
                                <input type="text" id="pMemory" name="memory" class="form-control" value="{{ old('memory') }}" data-review data-review-label="Memory" data-review-section="Resources" />
                                <span class="input-group-addon">MiB</span>
                            </div>
                            <p class="text-muted small">The maximum amount of memory allowed.</p>
                        </div>
                        <div class="form-group col-xs-6">
                            <label for="pSwap">Swap</label>
                            <div class="input-group">
                                <input type="text" id="pSwap" name="swap" class="form-control" value="{{ old('swap', 0) }}" data-review data-review-label="Swap" data-review-section="Resources" />
                                <span class="input-group-addon">MiB</span>
                            </div>
                            <p class="text-muted small">Setting to <code>-1</code> will allow unlimited swap.</p>
                        </div>
                    </div>
                    <div class="panel-card-body row">
                        <div class="form-group col-xs-6">
                            <label for="pDisk">Disk Space</label>
                            <div class="input-group">
                                <input type="text" id="pDisk" name="disk" class="form-control" value="{{ old('disk') }}" data-review data-review-label="Disk Space" data-review-section="Resources" />
                                <span class="input-group-addon">MiB</span>
                            </div>
                            <p class="text-muted small">Set to <code>0</code> to allow unlimited disk usage.</p>
                        </div>
                        <div class="form-group col-xs-6">
                            <label for="pIO">Block IO Weight</label>
                            <div>
                                <input type="text" id="pIO" name="io" class="form-control" value="{{ old('io', 500) }}" data-review data-review-label="Block IO Weight" data-review-section="Resources" />
                            </div>
                            <p class="text-muted small">Value should be between <code>10</code> and <code>1000</code>.</p>
                        </div>
                        <div class="form-group col-xs-12">
                            <div class="checkbox checkbox-primary no-margin-bottom">
                                <input type="checkbox" id="pOomDisabled" name="oom_disabled" value="0" {{ \RoyalPanel\Helpers\Utilities::checked('oom_disabled', 0) }} data-review data-review-label="OOM Killer" data-review-section="Resources" />
                                <label for="pOomDisabled" class="strong">Enable OOM Killer</label>
                            </div>
                            <p class="small text-muted no-margin">Terminates the server if it breaches the memory limits.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="step-content-3" style="display:none;">
        <div class="row">
            <div class="col-md-6">
                <div class="panel-card">
                    <div class="panel-card-header" onclick="revToggle(this)" style="cursor:pointer;">
                        <h3 class="panel-card-title">
                            <span><i class="fa fa-sitemap"></i></span>
                            <span>Nest Configuration</span>
                        </h3>
                        <i class="fa fa-chevron-down" style="transition:transform 0.2s;color:#94a3b8;"></i>
                    </div>
                    <div class="panel-card-body row">
                        <div class="form-group col-xs-12">
                            <label for="pNestId">Nest <span style="color:#e74c3c;">*</span></label>
                            <select id="pNestId" name="nest_id" class="form-control" data-review data-review-label="Nest" data-review-section="Nest &amp; Docker">
                                @foreach($nests as $nest)
                                    <option value="{{ $nest->id }}"
                                        @if($nest->id === old('nest_id')) selected="selected" @endif
                                    >{{ $nest->name }}</option>
                                @endforeach
                            </select>
                            <p class="small text-muted no-margin">Select the Nest that this server will be grouped under.</p>
                        </div>
                        <div class="form-group col-xs-12">
                            <label for="pEggId">Egg <span style="color:#e74c3c;">*</span></label>
                            <select id="pEggId" name="egg_id" class="form-control" data-review data-review-label="Egg" data-review-section="Nest &amp; Docker"></select>
                            <p class="small text-muted no-margin">Select the Egg that will define how this server should operate.</p>
                        </div>
                        <div class="form-group col-xs-12">
                            <div class="checkbox checkbox-primary no-margin-bottom">
                                <input type="checkbox" id="pSkipScripting" name="skip_scripts" value="1" {{ \RoyalPanel\Helpers\Utilities::checked('skip_scripts', 0) }} data-review data-review-label="Skip Install Script" data-review-section="Nest &amp; Docker" />
                                <label for="pSkipScripting" class="strong">Skip Egg Install Script</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="panel-card">
                    <div class="panel-card-header" onclick="revToggle(this)" style="cursor:pointer;">
                        <h3 class="panel-card-title">
                            <span><i class="fa fa-docker"></i></span>
                            <span>Docker Configuration</span>
                        </h3>
                        <i class="fa fa-chevron-down" style="transition:transform 0.2s;color:#94a3b8;"></i>
                    </div>
                    <div class="panel-card-body row">
                        <div class="form-group col-xs-12">
                            <label for="pDefaultContainer">Docker Image</label>
                            <select id="pDefaultContainer" name="image" class="form-control" data-review data-review-label="Docker Image" data-review-section="Nest &amp; Docker"></select>
                            <input id="pDefaultContainerCustom" name="custom_image" value="{{ old('custom_image') }}" class="form-control" placeholder="Or enter a custom image..." style="margin-top:1rem" data-review data-review-label="Custom Image" data-review-section="Nest &amp; Docker" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="panel-card">
                    <div class="panel-card-header" onclick="revToggle(this)" style="cursor:pointer;">
                        <h3 class="panel-card-title">
                            <span><i class="fa fa-play"></i></span>
                            <span>Startup Configuration</span>
                        </h3>
                        <i class="fa fa-chevron-down" style="transition:transform 0.2s;color:#94a3b8;"></i>
                    </div>
                    <div class="panel-card-body row">
                        <div class="form-group col-xs-12">
                            <label for="pStartup">Startup Command</label>
                            <input type="text" id="pStartup" name="startup" value="{{ old('startup') }}" class="form-control" data-review data-review-label="Startup Command" data-review-section="Startup" />
                        </div>
                    </div>
                    <div style="padding:12px 20px;border-top:1px solid #2a2a3e;display:flex;align-items:center;">
                        <h3 class="panel-card-title" style="margin:0;">
                            <span><i class="fa fa-cog"></i></span>
                            <span>Service Variables</span>
                        </h3>
                    </div>
                    <div class="panel-card-body row" id="appendVariablesTo"></div>
                </div>
            </div>
        </div>
    </div>

    <div id="step-content-4" style="display:none;">
        <div class="panel-card">
            <div class="panel-card-header" onclick="revToggle(this)" style="cursor:pointer;">
                <h3 class="panel-card-title">
                    <span><i class="fa fa-check-circle"></i></span>
                    <span>Review &amp; Submit</span>
                </h3>
                <i class="fa fa-chevron-down" style="transition:transform 0.2s;color:#94a3b8;"></i>
            </div>
            <div class="panel-card-body" id="review-content"></div>
        </div>
    </div>

    <div class="step-nav">
        <div>
            <button id="step-prev" class="btn btn-default" style="display:none;"><i class="fa fa-arrow-left"></i> Previous</button>
        </div>
        <div>
            <button id="step-next" class="btn btn-primary">Next <i class="fa fa-arrow-right"></i></button>
            <button id="step-submit" type="submit" class="btn btn-success" style="display:none;"><i class="fa fa-check"></i> Create Server</button>
        </div>
    </div>
</form>
@endsection

@section('footer-scripts')
    @parent
    {!! Theme::js('vendor/lodash/lodash.js') !!}

    <script type="application/javascript">
        function serviceVariablesUpdated(eggId, ids) {
            @if (old('egg_id'))
                if (eggId != '{{ old('egg_id') }}') { return; }
                @if (old('environment'))
                    @foreach (old('environment') as $key => $value)
                        $('#' + ids['{{ $key }}']).val('{{ $value }}');
                    @endforeach
                @endif
            @endif
            @if(old('image'))
                $('#pDefaultContainer').val('{{ old('image') }}');
            @endif
        }
    </script>

    {!! Theme::js('js/admin/new-server.js?v=20220530') !!}

    <script type="application/javascript">
        initStepWizard(4, {
            1: function() {
                var name = document.querySelector('[name="name"]');
                if (!name.value.trim()) { name.classList.add('field-error'); return 'Server Name is required.'; }
                name.classList.remove('field-error');
                return null;
            },
            2: function() {
                var cpu = document.querySelector('[name="cpu"]');
                if (cpu.value && isNaN(cpu.value)) { cpu.classList.add('field-error'); return 'CPU Limit must be a number.'; }
                cpu.classList.remove('field-error');
                return null;
            }
        });
        initExitConfirmation('serverForm');
        initAutoSave('server-create-draft', 'serverForm');

        var templates = JSON.parse(localStorage.getItem('server-create-templates') || '{}');
        var sel = document.getElementById('template-select');
        for (var name in templates) {
            var opt = document.createElement('option');
            opt.value = name; opt.textContent = name;
            sel.appendChild(opt);
        }

        $(document).ready(function() {
            @if (old('owner_id'))
                $.ajax({
                    url: '/admin/users/accounts.json?user_id={{ old('owner_id') }}',
                    dataType: 'json',
                }).then(function (data) { initUserIdSelect([ data ]); });
            @else
                initUserIdSelect();
            @endif

            @if (old('node_id'))
                $('#pNodeId').val('{{ old('node_id') }}').change();
                @if (old('allocation_id'))
                    $('#pAllocation').val('{{ old('allocation_id') }}').change();
                @endif
                @if (old('allocation_additional'))
                    const additional = [];
                    @for ($i = 0; $i < count(old('allocation_additional')); $i++)
                        additional.push('{{ old('allocation_additional.'.$i)}}');
                    @endfor
                    $('#pAllocationAdditional').val(additional).change();
                @endif
            @endif

            @if (old('nest_id'))
                $('#pNestId').val('{{ old('nest_id') }}').change();
                @if (old('egg_id'))
                    $('#pEggId').val('{{ old('egg_id') }}').change();
                @endif
            @endif
        });
    </script>
@endsection
