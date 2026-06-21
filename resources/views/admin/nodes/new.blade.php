@extends('layouts.admin')

@section('title')
    Nodes &rarr; New
@endsection

@section('content-header')
    <h1>New Node<small>Create a new local or remote node for servers to be installed to.</small></h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('admin.index') }}">Admin</a></li>
        <li><a href="{{ route('admin.nodes') }}">Nodes</a></li>
        <li class="active">New</li>
    </ol>
@endsection

@section('content')
<form action="{{ route('admin.nodes.new') }}" method="POST" id="nodeForm" data-template-form>
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
        <select id="template-select" onchange="loadTemplate('node-create')">
            <option value="">— Load Template —</option>
        </select>
        <input type="text" id="template-name" placeholder="Template name" style="width:150px;">
        <button class="btn btn-info btn-sm" onclick="saveTemplate('node-create', document.getElementById('template-name').value)"><i class="fa fa-save"></i> Save</button>
        <button class="btn btn-danger btn-sm" onclick="deleteTemplate('node-create')"><i class="fa fa-trash"></i> Delete</button>
    </div>

    <div class="step-indicator">
        <div class="step" id="step-indicator-1">Step 1 — Basic Details</div>
        <div class="step" id="step-indicator-2">Step 2 — Configuration</div>
        <div class="step" id="step-indicator-3">Step 3 — Review &amp; Submit</div>
    </div>

    <div class="step-error" id="step-error"></div>

    <div id="step-content-1">
        <div class="row">
            <div class="col-sm-12">
                <div class="panel-card">
                    <div class="panel-card-header" onclick="revToggle(this)" style="cursor:pointer;">
                        <h3 class="panel-card-title">
                            <span><i class="fa fa-info-circle"></i></span>
                            <span>Basic Details</span>
                        </h3>
                        <i class="fa fa-chevron-down" style="transition:transform 0.2s;color:#94a3b8;"></i>
                    </div>
                    <div class="panel-card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="pName" class="form-label">Name <span style="color:#e74c3c;">*</span></label>
                                    <input type="text" name="name" id="pName" class="form-control" value="{{ old('name') }}" data-review data-review-label="Name" data-review-section="Basic Details" required />
                                    <p class="text-muted small">Character limits: <code>a-zA-Z0-9_.-</code> and <code>[Space]</code> (min 1, max 100 characters).</p>
                                </div>
                                <div class="form-group">
                                    <label for="daemon_text" class="control-label">Daemon text <b style="background-color:#17078D;font-size:1rem;padding:2px 7px;border-radius:100px;font-weight:600;">ROYAL</b></label>
                                    <div>
                                        <input type="text" autocomplete="off" name="daemon_text" class="form-control" value="{{ old('daemon_text' ) }}" data-review data-review-label="Daemon Text" data-review-section="Basic Details" />
                                        <p class="text-muted"><small>Change the "Royal Panel:" text.</small></p>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="pFQDN" class="form-label">FQDN <span style="color:#e74c3c;">*</span></label>
                                    <input type="text" name="fqdn" id="pFQDN" class="form-control" value="{{ old('fqdn') }}" data-review data-review-label="FQDN" data-review-section="Basic Details" required />
                                    <p class="text-muted small">Please enter domain name (e.g <code>node.example.com</code>) to be used for connecting to the daemon. An IP address may be used <em>only</em> if you are not using SSL for this node.</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="container_text" class="control-label">Container text <b style="background-color:#17078D;font-size:1rem;padding:2px 7px;border-radius:100px;font-weight:600;">ROYAL</b></label>
                                    <div>
                                        <input type="text" autocomplete="off" name="container_text" class="form-control" value="{{ old('container_text' ) }}" data-review data-review-label="Container Text" data-review-section="Basic Details" />
                                        <p class="text-muted"><small>Change the "container@royalpanel~" text.</small></p>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="pDescription" class="form-label">Description</label>
                                    <textarea name="description" id="pDescription" rows="4" class="form-control" data-review data-review-label="Description" data-review-section="Basic Details">{{ old('description') }}</textarea>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="pLocationId" class="form-label">Location <span style="color:#e74c3c;">*</span></label>
                                    <select name="location_id" id="pLocationId" data-review data-review-label="Location" data-review-section="Basic Details">
                                        @foreach($locations as $location)
                                            <option value="{{ $location->id }}" {{ $location->id != old('location_id') ?: 'selected' }}>{{ $location->short }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Node Visibility</label>
                                    <div>
                                        <div class="radio radio-success radio-inline">
                                            <input type="radio" id="pPublicTrue" value="1" name="public" checked data-review data-review-label="Visibility" data-review-section="Basic Details">
                                            <label for="pPublicTrue"> Public </label>
                                        </div>
                                        <div class="radio radio-danger radio-inline">
                                            <input type="radio" id="pPublicFalse" value="0" name="public" data-review data-review-label="Visibility" data-review-section="Basic Details">
                                            <label for="pPublicFalse"> Private </label>
                                        </div>
                                    </div>
                                    <p class="text-muted small">By setting a node to <code>private</code> you will be denying the ability to auto-deploy to this node.
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Communicate Over SSL</label>
                                    <div>
                                        <div class="radio radio-success radio-inline">
                                            <input type="radio" id="pSSLTrue" value="https" name="scheme" checked data-review data-review-label="SSL" data-review-section="Basic Details">
                                            <label for="pSSLTrue"> Use SSL Connection</label>
                                        </div>
                                        <div class="radio radio-danger radio-inline">
                                            <input type="radio" id="pSSLFalse" value="http" name="scheme" @if(request()->isSecure()) disabled @endif data-review data-review-label="SSL" data-review-section="Basic Details">
                                            <label for="pSSLFalse"> Use HTTP Connection</label>
                                        </div>
                                    </div>
                                    @if(request()->isSecure())
                                        <p class="text-danger small">Your Panel is currently configured to use a secure connection. In order for browsers to connect to your node it <strong>must</strong> use a SSL connection.</p>
                                    @else
                                        <p class="text-muted small">In most cases you should select to use a SSL connection. If using an IP Address or you do not wish to use SSL at all, select a HTTP connection.</p>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Behind Proxy</label>
                                    <div>
                                        <div class="radio radio-success radio-inline">
                                            <input type="radio" id="pProxyFalse" value="0" name="behind_proxy" checked data-review data-review-label="Behind Proxy" data-review-section="Basic Details">
                                            <label for="pProxyFalse"> Not Behind Proxy </label>
                                        </div>
                                        <div class="radio radio-info radio-inline">
                                            <input type="radio" id="pProxyTrue" value="1" name="behind_proxy" data-review data-review-label="Behind Proxy" data-review-section="Basic Details">
                                            <label for="pProxyTrue"> Behind Proxy </label>
                                        </div>
                                    </div>
                                    <p class="text-muted small">If you are running the daemon behind a proxy such as Cloudflare, select this to have the daemon skip looking for certificates on boot.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="step-content-2" style="display:none;">
        <div class="row">
            <div class="col-sm-12">
                <div class="panel-card">
                    <div class="panel-card-header" onclick="revToggle(this)" style="cursor:pointer;">
                        <h3 class="panel-card-title">
                            <span><i class="fa fa-wrench"></i></span>
                            <span>Configuration</span>
                        </h3>
                        <i class="fa fa-chevron-down" style="transition:transform 0.2s;color:#94a3b8;"></i>
                    </div>
                    <div class="panel-card-body">
                        <div class="row">
                            <div class="form-group col-md-6">
                                <label for="pDaemonBase" class="form-label">Daemon Server File Directory</label>
                                <input type="text" name="daemonBase" id="pDaemonBase" class="form-control" value="/var/lib/royalpanel/volumes" data-review data-review-label="File Directory" data-review-section="Configuration" />
                                <p class="text-muted small">Enter the directory where server files should be stored. <strong>If you use OVH you should check your partition scheme. You may need to use <code>/home/daemon-data</code> to have enough space.</strong></p>
                            </div>
                            <div class="form-group col-md-6">
                                <label for="pMemory" class="form-label">Total Memory <span style="color:#e74c3c;">*</span></label>
                                <div class="input-group">
                                    <input type="text" name="memory" data-multiplicator="true" class="form-control" id="pMemory" value="{{ old('memory') }}" data-review data-review-label="Total Memory" data-review-section="Configuration" required />
                                    <span class="input-group-addon">MiB</span>
                                </div>
                            </div>
                            <div class="form-group col-md-6">
                                <label for="pMemoryOverallocate" class="form-label">Memory Over-Allocation</label>
                                <div class="input-group">
                                    <input type="text" name="memory_overallocate" class="form-control" id="pMemoryOverallocate" value="{{ old('memory_overallocate') }}" data-review data-review-label="Memory Over-Allocation" data-review-section="Configuration" />
                                    <span class="input-group-addon">%</span>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <p class="text-muted small">Enter the total amount of memory available for new servers. If you would like to allow overallocation of memory enter the percentage that you want to allow. To disable checking for overallocation enter <code>-1</code> into the field. Entering <code>0</code> will prevent creating new servers if it would put the node over the limit.</p>
                            </div>
                        </div>
                        <div class="row">
                            <div class="form-group col-md-6">
                                <label for="pDisk" class="form-label">Total Disk Space <span style="color:#e74c3c;">*</span></label>
                                <div class="input-group">
                                    <input type="text" name="disk" data-multiplicator="true" class="form-control" id="pDisk" value="{{ old('disk') }}" data-review data-review-label="Total Disk Space" data-review-section="Configuration" required />
                                    <span class="input-group-addon">MiB</span>
                                </div>
                            </div>
                            <div class="form-group col-md-6">
                                <label for="pDiskOverallocate" class="form-label">Disk Over-Allocation</label>
                                <div class="input-group">
                                    <input type="text" name="disk_overallocate" class="form-control" id="pDiskOverallocate" value="{{ old('disk_overallocate') }}" data-review data-review-label="Disk Over-Allocation" data-review-section="Configuration" />
                                    <span class="input-group-addon">%</span>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <p class="text-muted small">Enter the total amount of disk space available for new servers. If you would like to allow overallocation of disk space enter the percentage that you want to allow. To disable checking for overallocation enter <code>-1</code> into the field. Entering <code>0</code> will prevent creating new servers if it would put the node over the limit.</p>
                            </div>
                        </div>
                        <div class="row">
                            <div class="form-group col-md-6">
                                <label for="pDaemonListen" class="form-label">Daemon Port</label>
                                <input type="text" name="daemonListen" class="form-control" id="pDaemonListen" value="8080" data-review data-review-label="Daemon Port" data-review-section="Configuration" />
                            </div>
                            <div class="form-group col-md-6">
                                <label for="pDaemonSFTP" class="form-label">Daemon SFTP Port</label>
                                <input type="text" name="daemonSFTP" class="form-control" id="pDaemonSFTP" value="2022" data-review data-review-label="Daemon SFTP Port" data-review-section="Configuration" />
                            </div>
                            <div class="col-md-12">
                                <p class="text-muted small">The daemon runs its own SFTP management container and does not use the SSHd process on the main physical server. <Strong>Do not use the same port that you have assigned for your physical server's SSH process.</strong> If you will be running the daemon behind CloudFlare&reg; you should set the daemon port to <code>8443</code> to allow websocket proxying over SSL.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="step-content-3" style="display:none;">
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
            <button id="step-submit" type="submit" class="btn btn-success" style="display:none;"><i class="fa fa-check"></i> Create Node</button>
        </div>
    </div>
</form>
@endsection

@section('footer-scripts')
    @parent
    <script>
        initStepWizard(3, {
            1: function() {
                var name = document.querySelector('[name="name"]');
                var fqdn = document.querySelector('[name="fqdn"]');
                if (!name.value.trim()) { name.classList.add('field-error'); return 'Node name is required.'; }
                name.classList.remove('field-error');
                if (!fqdn.value.trim()) { fqdn.classList.add('field-error'); return 'FQDN is required.'; }
                fqdn.classList.remove('field-error');
                return null;
            },
            2: function() {
                var memory = document.querySelector('[name="memory"]');
                var disk = document.querySelector('[name="disk"]');
                if (!memory.value.trim()) { memory.classList.add('field-error'); return 'Total Memory is required.'; }
                memory.classList.remove('field-error');
                if (!disk.value.trim()) { disk.classList.add('field-error'); return 'Total Disk Space is required.'; }
                disk.classList.remove('field-error');
                return null;
            }
        });
        initExitConfirmation('nodeForm');
        initAutoSave('node-create-draft', 'nodeForm');

        var templates = JSON.parse(localStorage.getItem('node-create-templates') || '{}');
        var sel = document.getElementById('template-select');
        for (var name in templates) {
            var opt = document.createElement('option');
            opt.value = name; opt.textContent = name;
            sel.appendChild(opt);
        }

        $('#pLocationId').select2();
    </script>
@endsection
