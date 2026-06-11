@extends('layouts.admin')
@section('title') Security - Cloudflare @endsection
@section('content')
<div class="row">
    <div class="col-md-6">
        <div class="box box-info">
            <div class="box-header with-border">
                <h3 class="box-title">Cloudflare Configuration</h3>
            </div>
            <form action="{{ route('admin.security.save') }}" method="POST">
                @csrf
                <div class="box-body">
                    <div class="form-group">
                        <label class="control-label">Enable Cloudflare Integration</label>
                        <div>
                            <input type="hidden" name="cloudflare_enabled" value="0">
                            <input type="checkbox" name="cloudflare_enabled" value="1" {{ config('security.cloudflare.enabled') ? 'checked' : '' }} class="checkbox">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>API Token <small class="text-muted">(requires Zone:Read, Zone:Edit permissions)</small></label>
                        <input type="password" name="cloudflare_api_token" class="form-control" value="{{ config('security.cloudflare.api_token') }}" placeholder="Enter your Cloudflare API token">
                    </div>
                    <div class="form-group">
                        <label>Zone ID</label>
                        <input type="text" name="cloudflare_zone_id" class="form-control" value="{{ config('security.cloudflare.zone_id') }}" placeholder="Enter your Cloudflare Zone ID">
                    </div>
                </div>
                <div class="box-footer">
                    <button type="submit" class="btn btn-primary">Save Settings</button>
                </div>
            </form>
        </div>
        <div class="box box-default">
            <div class="box-header with-border">
                <h3 class="box-title">SSL/TLS Configuration</h3>
            </div>
            <div class="box-body">
                <div class="form-group">
                    <label>SSL Mode</label>
                    <select id="sslMode" class="form-control">
                        <option value="off">Off</option>
                        <option value="flexible">Flexible</option>
                        <option value="full">Full</option>
                        <option value="full_strict">Full (Strict)</option>
                        <option value="strict">Strict</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Minimum TLS Version</label>
                    <select id="minTls" class="form-control">
                        <option value="1.0">TLS 1.0</option>
                        <option value="1.1">TLS 1.1</option>
                        <option value="1.2">TLS 1.2</option>
                        <option value="1.3">TLS 1.3</option>
                    </select>
                </div>
                <div id="sslSubOptions">
                    <div class="checkbox"><label><input type="checkbox" id="toggleAlwaysHttps"> Always Use HTTPS</label></div>
                    <div class="checkbox"><label><input type="checkbox" id="toggleOppEncryption"> Opportunistic Encryption</label></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="box box-danger">
            <div class="box-header with-border">
                <h3 class="box-title">Actions <span id="cfStatusBadge" class="label pull-right" style="display:none"></span></h3>
            </div>
            <div class="box-body">
                <div id="cfActions"></div>
                <div id="cfLoading" class="text-center" style="padding:40px"><i class="fa fa-spinner fa-spin fa-2x"></i><br><br>Fetching Cloudflare status...</div>
            </div>
        </div>
    </div>
</div>
<style>
#cfActions .action-item { display:flex; align-items:center; justify-content:space-between; padding:12px 0; border-bottom:1px solid #f0f0f0; }
#cfActions .action-item:last-child { border-bottom:none; }
#cfActions .action-info { display:flex; align-items:center; gap:10px; }
#cfActions .action-info i { font-size:20px; width:24px; text-align:center; }
#cfActions .action-info strong { display:block; font-size:14px; }
#cfActions .action-info small { color:#888; font-size:12px; }
.cf-switch { position:relative; display:inline-block; width:50px; height:26px; }
.cf-switch input { opacity:0; width:0; height:0; }
.cf-slider { position:absolute; cursor:pointer; top:0; left:0; right:0; bottom:0; background-color:#ccc; transition:.3s; border-radius:26px; }
.cf-slider:before { position:absolute; content:""; height:20px; width:20px; left:3px; bottom:3px; background-color:white; transition:.3s; border-radius:50%; }
.cf-switch input:checked + .cf-slider { background-color:#5cb85c; }
.cf-switch input:checked + .cf-slider:before { transform:translateX(24px); }
.cf-badge { display:inline-block; padding:2px 8px; border-radius:10px; font-size:11px; font-weight:600; }
.cf-badge.on { background:#dff0d8; color:#3c763d; }
.cf-badge.off { background:#f2dede; color:#a94442; }
</style>
@endsection
@section('footer-scripts')
@parent
<script>
$(function() {
    var cfConfigured = {{ config('security.cloudflare.enabled') && config('security.cloudflare.api_token') && config('security.cloudflare.zone_id') ? 'true' : 'false' }};

    function showMsg(msg, type) {
        if (typeof swal !== 'undefined') {
            swal({ title: type === 'error' ? 'Error' : 'Success', text: msg, type: type, timer: 3000, showConfirmButton: false });
        } else { alert(msg); }
    }

    function doAction(url, data, cb) {
        $.post(url, Object.assign({ _token: '{{ csrf_token() }}' }, data || {}))
            .done(function(r) { if (cb) cb(null, r); else showMsg('Done', 'success'); })
            .fail(function(x) { var m = x.responseJSON ? (x.responseJSON.error || x.responseJSON.message) : 'Request failed.'; if (cb) cb(m); else showMsg(m, 'error'); });
    }

    function loadStatus() {
        if (!cfConfigured) {
            $('#cfLoading').html('<span class="text-muted">Save your Cloudflare API token and Zone ID first.</span>');
            return;
        }
        $.get('{{ route("admin.security.cloudflare.status") }}')
            .done(function(r) {
                $('#cfLoading').hide();
                $('#cfStatusBadge').show().removeClass('label-danger label-success').addClass('label-success').text('Connected');
                var s = r.settings || {};
                renderActions(s);
                renderSsl(s);
            })
            .fail(function() {
                $('#cfLoading').html('<span class="text-danger">Failed to reach Cloudflare. Check your API token and Zone ID.</span>');
                $('#cfStatusBadge').show().removeClass('label-danger label-success').addClass('label-danger').text('Disconnected');
            });
    }

    function renderActions(s) {
        var items = [
            { key: 'security_level', label: 'Under Attack Mode', desc: 'Challenge all visitors with JS', icon: 'fa-shield', color: 'text-red', type: 'toggle', onVal: 'under_attack', offVal: 'medium', action: 'under_attack' },
            { key: 'bot_fight_mode', label: 'Bot Fight Mode', desc: 'Block malicious bots', icon: 'fa-robot', color: 'text-warning', type: 'toggle', onVal: true, offVal: false, action: 'bot_fight' },
            { key: 'browser_check', label: 'Browser Integrity Check', desc: 'Blocks access from suspicious browsers', icon: 'fa-firefox', color: 'text-primary', type: 'toggle', onVal: 'on', offVal: 'off', action: 'browser_check' },
            { key: 'always_use_https', label: 'Always Use HTTPS', desc: 'Redirect all HTTP to HTTPS', icon: 'fa-lock', color: 'text-green', type: 'toggle', onVal: 'on', offVal: 'off', action: 'always_https' },
            { key: 'ip_geolocation', label: 'IP Geolocation', desc: 'Pass visitor location to your server', icon: 'fa-globe', color: 'text-info', type: 'toggle', onVal: 'on', offVal: 'off', action: 'ip_geolocation' },
            { key: 'privacy_pass', label: 'Privacy Pass', desc: 'Reduce CAPTCHA challenges', icon: 'fa-user-secret', color: 'text-purple', type: 'toggle', onVal: 'on', offVal: 'off', action: 'privacy_pass' },
            { key: 'opportunistic_encryption', label: 'Opportunistic Encryption', desc: 'Encrypt between CF and origin', icon: 'fa-shield', color: 'text-green', type: 'toggle', onVal: 'on', offVal: 'off', action: 'opp_encryption' },
        ];
        var html = '';
        $.each(items, function(i, item) {
            var val = s[item.key];
            var isOn = val == item.onVal || val === true || val === 'on' || val === 'true';
            var checked = isOn ? 'checked' : '';
            var badge = isOn ? '<span class="cf-badge on">ON</span>' : '<span class="cf-badge off">OFF</span>';
            html += '<div class="action-item" data-key="'+item.key+'">' +
                '<div class="action-info"><i class="fa '+item.icon+' '+item.color+'"></i><div><strong>'+item.label+'</strong><small>'+item.desc+'</small></div></div>' +
                '<div class="action-control">' + badge + ' ' +
                '<label class="cf-switch" style="margin:0 0 0 8px"><input type="checkbox" class="cf-toggle" data-action="'+item.action+'" '+checked+'><span class="cf-slider"></span></label></div></div>';
        });
        $('#cfActions').html(html);

        $('.cf-toggle').on('change', function() {
            var action = $(this).data('action');
            var state = $(this).is(':checked');
            $(this).prop('disabled', true);
            updateCfSetting(action, state, $(this));
        });
    }

    function renderSsl(s) {
        if (s.ssl) $('#sslMode').val(s.ssl);
        if (s.min_tls_version) $('#minTls').val(s.min_tls_version);
        if (s.always_use_https) $('#toggleAlwaysHttps').prop('checked', s.always_use_https === 'on');
        if (s.opportunistic_encryption) $('#toggleOppEncryption').prop('checked', s.opportunistic_encryption === 'on');
    }

    function updateCfSetting(action, state, el) {
        var urlMap = {
            under_attack: state ? 'under-attack/on' : 'under-attack/off',
            bot_fight: 'bot-fight/on',
            always_https: 'ssl-strict',
        };
        var url = '{{ url("/admin/security") }}/' + (urlMap[action] || action);
        if (!urlMap[action] && url.indexOf('/admin/security/') > 0) {
            url = '{{ url("/admin/security") }}/' + action + '/' + (state ? 'on' : 'off');
        }
        doAction(url, {}, function(err) {
            if (err) {
                showMsg(err, 'error');
                if (el) el.prop('checked', !state);
            } else {
                loadStatus();
            }
            if (el) el.prop('disabled', false);
        });
    }

    $('#sslMode').on('change', function() {
        var val = $(this).val();
        doAction('{{ route("admin.security.ssl_strict") }}', { mode: val }, function(err) {
            if (err) showMsg(err, 'error');
            else { showMsg('SSL mode updated to ' + val, 'success'); loadStatus(); }
        });
    });

    $('#minTls').on('change', function() {});

    $('#toggleAlwaysHttps').on('change', function() {
        doAction('{{ url("/admin/security") }}/ssl-strict', { always_https: $(this).is(':checked') ? 'on' : 'off' });
    });

    $('#toggleOppEncryption').on('change', function() {});

    loadStatus();
    setInterval(loadStatus, 30000);
});
</script>
@endsection
