@extends('layouts.admin')
@section('title') Security - Cloudflare @endsection
@section('content')
<div class="row">
    <div class="col-xs-12">
        <div class="box box-info">
            <div class="box-header with-border">
                <h3 class="box-title">Cloudflare Configuration</h3>
                <div class="box-tools">
                    <span id="cfStatusBadge" class="label" style="display:none;font-size:12px;padding:4px 10px;margin-right:8px"></span>
                    <a href="{{ route('admin.security.index') }}" class="btn btn-sm btn-default">Back</a>
                </div>
            </div>
            <form action="{{ route('admin.security.save') }}" method="POST">
                @csrf
                <div class="box-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Enable Cloudflare Integration</label>
                                <div><input type="hidden" name="cloudflare_enabled" value="0"><input type="checkbox" name="cloudflare_enabled" value="1" {{ config('security.cloudflare.enabled') ? 'checked' : '' }}></div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>API Token <small>(Zone:Read, Zone:Edit)</small></label>
                                <input type="password" name="cloudflare_api_token" class="form-control" value="{{ config('security.cloudflare.api_token') }}" placeholder="CF API Token">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Zone ID</label>
                                <input type="text" name="cloudflare_zone_id" class="form-control" value="{{ config('security.cloudflare.zone_id') }}" placeholder="CF Zone ID">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="box-footer">
                    <button type="submit" class="btn btn-primary">Save Settings</button>
                    <button type="button" id="refreshStatus" class="btn btn-default pull-right"><i class="fa fa-refresh"></i> Refresh Status</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="nav-tabs-custom">
    <ul class="nav nav-tabs">
        <li class="active"><a href="#tab-security" data-toggle="tab">Security</a></li>
        <li><a href="#tab-ssl" data-toggle="tab">SSL/TLS</a></li>
        <li><a href="#tab-performance" data-toggle="tab">Performance</a></li>
        <li><a href="#tab-dns" data-toggle="tab">DNS</a></li>
        <li><a href="#tab-waf" data-toggle="tab">WAF Rules</a></li>
        <li><a href="#tab-rate" data-toggle="tab">Rate Limiting</a></li>
    </ul>
    <div class="tab-content">
        <div class="tab-pane active" id="tab-security"><div class="cf-loading text-center" style="padding:40px"><i class="fa fa-spinner fa-spin fa-2x"></i></div></div>
        <div class="tab-pane" id="tab-ssl"><div class="cf-loading text-center" style="padding:40px"><i class="fa fa-spinner fa-spin fa-2x"></i></div></div>
        <div class="tab-pane" id="tab-performance"><div class="cf-loading text-center" style="padding:40px"><i class="fa fa-spinner fa-spin fa-2x"></i></div></div>
        <div class="tab-pane" id="tab-dns"><div class="cf-loading text-center" style="padding:40px"><i class="fa fa-spinner fa-spin fa-2x"></i></div></div>
        <div class="tab-pane" id="tab-waf"><div class="cf-loading text-center" style="padding:40px"><i class="fa fa-spinner fa-spin fa-2x"></i></div></div>
        <div class="tab-pane" id="tab-rate"><div class="cf-loading text-center" style="padding:40px"><i class="fa fa-spinner fa-spin fa-2x"></i></div></div>
    </div>
</div>

<style>
.cf-row { display:flex; align-items:center; justify-content:space-between; padding:10px 0; border-bottom:1px solid #eee; }
.cf-row:last-child { border-bottom:none; }
.cf-row .cf-label { display:flex; align-items:center; gap:8px; }
.cf-row .cf-label i { width:20px; text-align:center; font-size:16px; }
.cf-row .cf-label strong { font-size:13px; }
.cf-row .cf-label small { color:#888; font-size:11px; display:block; }
.cf-switch { position:relative; display:inline-block; width:44px; height:22px; vertical-align:middle; }
.cf-switch input { opacity:0; width:0; height:0; }
.cf-slider { position:absolute; cursor:pointer; top:0; left:0; right:0; bottom:0; background:#ccc; transition:.3s; border-radius:22px; }
.cf-slider:before { position:absolute; content:""; height:16px; width:16px; left:3px; bottom:3px; background:#fff; transition:.3s; border-radius:50%; }
.cf-switch input:checked + .cf-slider { background:#5cb85c; }
.cf-switch input:checked + .cf-slider:before { transform:translateX(22px); }
.cf-badge { display:inline-block; padding:1px 7px; border-radius:9px; font-size:10px; font-weight:700; vertical-align:middle; }
.cf-badge.on { background:#dff0d8; color:#3c763d; }
.cf-badge.off { background:#f2dede; color:#a94442; }
.cf-select { min-width:180px; }
.dns-proxied { color:#5cb85c; } .dns-unproxied { color:#d9534f; }
.waf-row, .rate-row { padding:8px 0; border-bottom:1px solid #eee; font-size:13px; }
</style>
@endsection
@section('footer-scripts')
@parent
<script>
$(function() {
    var configured = {{ config('security.cloudflare.enabled') && config('security.cloudflare.api_token') && config('security.cloudflare.zone_id') ? 'true' : 'false' }};
    if (!configured) { $('.cf-loading').html('<span class="text-muted">Save your API token and Zone ID first.</span>'); return; }

    function showMsg(m, t) { if (typeof swal!=='undefined') swal({title:t==='error'?'Error':'Success',text:m,type:t,timer:3000,showConfirmButton:false}); else alert(m); }

    function updateSetting(setting, value, cb) {
        $.post('{{ route("admin.security.cloudflare.set_setting") }}', { _token:'{{ csrf_token() }}', setting:setting, value:value })
            .done(function(r) { if (r.success) { if (cb) cb(); else loadAll(); } else showMsg(r.error||'Failed','error'); })
            .fail(function() { showMsg('Request failed','error'); });
    }

    function loadAll() {
        $.get('{{ route("admin.security.cloudflare.status") }}').done(function(r) {
            $('#cfStatusBadge').show().removeClass('label-danger label-success').addClass(r.success?'label-success':'label-danger').text(r.success?'Connected':'Failed');
            if (!r.success) return;
            var s = r.settings || {};
            renderSecurity(s); renderSsl(s); renderPerformance(s);
        });
        $.get('{{ route("admin.security.cloudflare.dns") }}').done(function(r) { renderDns(r); });
        $.get('{{ route("admin.security.cloudflare.waf") }}').done(function(r) { renderWaf(r); });
        $.get('{{ route("admin.security.cloudflare.rate") }}').done(function(r) { renderRate(r); });
    }

    function renderSecurity(s) {
        var items = [
            { k:'security_level', l:'Security Level', d:'Low / Medium / High / Under Attack', i:'fa-shield', c:'text-red', t:'select', o:{under_attack:'Under Attack',high:'High',medium:'Medium',low:'Low'} },
            { k:'browser_check', l:'Browser Integrity Check', d:'Block suspicious browsers', i:'fa-firefox', c:'text-primary', t:'toggle', onVal:'on' },
            { k:'underAttack', l:'Under Attack Mode', d:'Challenge all visitors with JS', i:'fa-exclamation-triangle', c:'text-red', t:'toggle', onVal:'under_attack', cfg:'security_level' },
            { k:'bot_fight_mode', l:'Bot Fight Mode', d:'Block malicious bots', i:'fa-robot', c:'text-warning', t:'toggle', onVal:true },
            { k:'challenge_ttl', l:'Challenge Passage', d:'Minutes before re-challenge', i:'fa-clock-o', c:'text-info', t:'text', suffix:' sec' },
            { k:'ip_geolocation', l:'IP Geolocation', d:'Pass visitor location header', i:'fa-globe', c:'text-info', t:'toggle', onVal:'on' },
            { k:'privacy_pass', l:'Privacy Pass', d:'Reduce CAPTCHA challenges', i:'fa-user-secret', c:'text-purple', t:'toggle', onVal:'on' },
        ];
        var h = '';
        $.each(items, function(i, it) {
            var val = s[it.k];
            if (it.k === 'underAttack') val = s[it.cfg];
            var isOn = val == it.onVal || val === true || val === 'on' || val === 'true';
            if (it.t === 'toggle') {
                var badge = isOn ? '<span class="cf-badge on">ON</span>' : '<span class="cf-badge off">OFF</span>';
                h += '<div class="cf-row"><div class="cf-label"><i class="fa '+it.i+' '+it.c+'"></i><div><strong>'+it.l+'</strong><small>'+it.d+'</small></div></div><div>'+badge+' <label class="cf-switch"><input type="checkbox" class="cf-toggle" data-setting="'+it.k+'" '+(isOn?'checked':'')+'><span class="cf-slider"></span></label></div></div>';
            } else if (it.t === 'select') {
                var opts = '';
                $.each(it.o, function(v,lab) { opts += '<option value="'+v+'" '+(val==v?'selected':'')+'>'+lab+'</option>'; });
                h += '<div class="cf-row"><div class="cf-label"><i class="fa '+it.i+' '+it.c+'"></i><div><strong>'+it.l+'</strong><small>'+it.d+'</small></div></div><select class="form-control cf-select cf-dropdown" data-setting="'+it.k+'">'+opts+'</select></div>';
            } else {
                h += '<div class="cf-row"><div class="cf-label"><i class="fa '+it.i+' '+it.c+'"></i><div><strong>'+it.l+'</strong><small>'+it.d+'</small></div></div><div><input type="number" class="form-control cf-text" data-setting="'+it.k+'" value="'+(val||'')+'" style="width:100px;display:inline">'+(it.suffix||'')+'</div></div>';
            }
        });
        $('#tab-security').html(h);
        bindEvents();
    }

    function renderSsl(s) {
        var h = '';
        h += '<div class="cf-row"><div class="cf-label"><i class="fa fa-lock text-green"></i><div><strong>SSL Mode</strong><small>Off / Flexible / Full / Full (Strict) / Strict</small></div></div><select class="form-control cf-select cf-dropdown" data-setting="ssl"><option value="off" '+(s.ssl=='off'?'selected':'')+'>Off</option><option value="flexible" '+(s.ssl=='flexible'?'selected':'')+'>Flexible</option><option value="full" '+(s.ssl=='full'?'selected':'')+'>Full</option><option value="full_strict" '+(s.ssl=='full_strict'?'selected':'')+'>Full (Strict)</option><option value="strict" '+(s.ssl=='strict'?'selected':'')+'>Strict</option></select></div>';
        h += '<div class="cf-row"><div class="cf-label"><i class="fa fa-shield text-green"></i><div><strong>Minimum TLS Version</strong><small>1.0 / 1.1 / 1.2 / 1.3</small></div></div><select class="form-control cf-select cf-dropdown" data-setting="min_tls_version"><option value="1.0" '+(s.min_tls_version=='1.0'?'selected':'')+'>TLS 1.0</option><option value="1.1" '+(s.min_tls_version=='1.1'?'selected':'')+'>TLS 1.1</option><option value="1.2" '+(s.min_tls_version=='1.2'?'selected':'')+'>TLS 1.2</option><option value="1.3" '+(s.min_tls_version=='1.3'?'selected':'')+'>TLS 1.3</option></select></div>';
        h += '<div class="cf-row"><div class="cf-label"><i class="fa fa-link text-green"></i><div><strong>Always Use HTTPS</strong><small>Redirect all HTTP to HTTPS</small></div></div>' + (s.always_use_https?'<span class="cf-badge on">ON</span>':'<span class="cf-badge off">OFF</span>')+' <label class="cf-switch"><input type="checkbox" class="cf-toggle" data-setting="always_use_https" '+(s.always_use_https=='on'?'checked':'')+'><span class="cf-slider"></span></label></div>';
        h += '<div class="cf-row"><div class="cf-label"><i class="fa fa-shield text-green"></i><div><strong>Opportunistic Encryption</strong><small>Encrypt between CF and origin</small></div></div>' + (s.opportunistic_encryption?'<span class="cf-badge on">ON</span>':'<span class="cf-badge off">OFF</span>')+' <label class="cf-switch"><input type="checkbox" class="cf-toggle" data-setting="opportunistic_encryption" '+(s.opportunistic_encryption=='on'?'checked':'')+'><span class="cf-slider"></span></label></div>';
        $('#tab-ssl').html(h);
        bindEvents();
    }

    function renderPerformance(s) {
        var h = '';
        h += '<div class="cf-row"><div class="cf-label"><i class="fa fa-flask text-warning"></i><div><strong>Development Mode</strong><small>Bypass cache for 30 min</small></div></div>' + (s.development_mode=='on'?'<span class="cf-badge on">ON</span>':'<span class="cf-badge off">OFF</span>')+' <label class="cf-switch"><input type="checkbox" class="cf-toggle" data-setting="development_mode" '+(s.development_mode=='on'?'checked':'')+'><span class="cf-slider"></span></label></div>';
        h += '<div class="cf-row"><div class="cf-label"><i class="fa fa-database text-info"></i><div><strong>Cache Level</strong><small>No Query String / Ignore / Standard / Cache Everything</small></div></div><select class="form-control cf-select cf-dropdown" data-setting="cache_level"><option value="basic" '+(s.cache_level=='basic'?'selected':'')+'>No Query String</option><option value="simplified" '+(s.cache_level=='simplified'?'selected':'')+'>Ignore Query String</option><option value="standard" '+(s.cache_level=='standard'?'selected':'')+'>Standard</option><option value="cache_everything" '+(s.cache_level=='cache_everything'?'selected':'')+'>Cache Everything</option></select></div>';
        h += '<div class="cf-row"><div class="cf-label"><i class="fa fa-globe text-green"></i><div><strong>Always Online</strong><small>Serve cached pages when origin offline</small></div></div>' + (s.always_online=='on'?'<span class="cf-badge on">ON</span>':'<span class="cf-badge off">OFF</span>')+' <label class="cf-switch"><input type="checkbox" class="cf-toggle" data-setting="always_online" '+(s.always_online=='on'?'checked':'')+'><span class="cf-slider"></span></label></div>';
        h += '<div class="cf-row"><div class="cf-label"><i class="fa fa-rocket text-warning"></i><div><strong>Rocket Loader</strong><small>Async JS loading for faster pages</small></div></div>' + (s.rocket_loader=='on'?'<span class="cf-badge on">ON</span>':'<span class="cf-badge off">OFF</span>')+' <label class="cf-switch"><input type="checkbox" class="cf-toggle" data-setting="rocket_loader" '+(s.rocket_loader=='on'?'checked':'')+'><span class="cf-slider"></span></label></div>';
        var mJs = s.minify_js=='on', mCss = s.minify_css=='on', mHtml = s.minify_html=='on';
        h += '<div class="cf-row"><div class="cf-label"><i class="fa fa-compress text-primary"></i><div><strong>Auto Minify</strong><small>Minify JS / CSS / HTML</small></div></div><div><label style="font-weight:400;margin:0 10px 0 0"><input type="checkbox" class="cf-minify" data-ext="js" '+(mJs?'checked':'')+'> JS</label><label style="font-weight:400;margin:0 10px 0 0"><input type="checkbox" class="cf-minify" data-ext="css" '+(mCss?'checked':'')+'> CSS</label><label style="font-weight:400;margin:0"><input type="checkbox" class="cf-minify" data-ext="html" '+(mHtml?'checked':'')+'> HTML</label></div></div>';
        $('#tab-performance').html(h);
        bindEvents();
        $('.cf-minify').on('change', function() {
            var exts = [];
            $('.cf-minify:checked').each(function() { exts.push($(this).data('ext')); });
            updateSetting('auto_minify', exts, loadAll);
        });
    }

    function renderDnsModal(title, record) {
        var isEdit = record && record.id;
        var id = isEdit ? record.id : '';
        var name = isEdit ? record.name : '';
        var type = isEdit ? record.type : 'A';
        var content = isEdit ? record.content : '';
        var ttl = isEdit ? (record.ttl === 1 ? '1' : record.ttl) : '1';
        var proxied = isEdit ? record.proxied : false;
        var h = '<div class="modal fade" id="dnsModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content"><form id="dnsForm"><input type="hidden" name="id" value="'+id+'">';
        h += '<div class="modal-header"><button type="button" class="close" data-dismiss="modal">&times;</button><h4 class="modal-title">'+title+'</h4></div>';
        h += '<div class="modal-body"><div class="form-group"><label>Name</label><input type="text" name="name" class="form-control" value="'+name+'" placeholder="sub.domain.com"></div>';
        h += '<div class="form-group"><label>Type</label><select name="type" class="form-control"><option value="A" '+(type=='A'?'selected':'')+'>A</option><option value="AAAA" '+(type=='AAAA'?'selected':'')+'>AAAA</option><option value="CNAME" '+(type=='CNAME'?'selected':'')+'>CNAME</option><option value="MX" '+(type=='MX'?'selected':'')+'>MX</option><option value="TXT" '+(type=='TXT'?'selected':'')+'>TXT</option><option value="SRV" '+(type=='SRV'?'selected':'')+'>SRV</option></select></div>';
        h += '<div class="form-group"><label>Content / Value</label><input type="text" name="content" class="form-control" value="'+content+'" placeholder="IP address or target"></div>';
        h += '<div class="row"><div class="col-md-6"><div class="form-group"><label>TTL</label><select name="ttl" class="form-control"><option value="1" '+(ttl=='1'?'selected':'')+'>Auto</option><option value="60" '+(ttl==60?'selected':'')+'>1 min</option><option value="300" '+(ttl==300?'selected':'')+'>5 min</option><option value="3600" '+(ttl==3600?'selected':'')+'>1 hour</option><option value="86400" '+(ttl==86400?'selected':'')+'>1 day</option></select></div></div>';
        h += '<div class="col-md-6"><div class="form-group"><label>Proxy</label><div class="checkbox"><label><input type="checkbox" name="proxied" value="1" '+(proxied?'checked':'')+'> Cloudflare Proxy (orange cloud)</label></div></div></div></div>';
        h += '</div><div class="modal-footer"><button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary">'+(isEdit?'Update':'Create')+' Record</button></div></form></div></div></div>';
        if ($('#dnsModal').length) $('#dnsModal').remove();
        $('body').append(h);
        $('#dnsModal').modal('show');
        $('#dnsForm').off('submit').on('submit', function(e) {
            e.preventDefault();
            var fd = $(this).serializeArray(), data = {};
            $.each(fd, function(i, f) { data[f.name] = f.value; });
            data.proxied = data.proxied ? true : false;
            var url = isEdit ? '{{ route("admin.security.cloudflare.dns.update", "") }}/' + id : '{{ route("admin.security.cloudflare.dns.create") }}';
            $.post(url, $.extend(data, { _token: '{{ csrf_token() }}' }))
                .done(function(r2) { if (r2.success) { $('#dnsModal').modal('hide'); loadAll(); showMsg('DNS record '+(isEdit?'updated':'created')+' successfully','success'); } else showMsg(r2.error||'Failed','error'); })
                .fail(function(x) { showMsg(x.responseJSON?.error||'Request failed','error'); });
        });
    }

    function renderDns(r) {
        if (!r.success) { $('#tab-dns').html('<span class="text-danger">Failed to fetch DNS records.</span>'); return; }
        var records = r.records || [];
        if (!records.length) { $('#tab-dns').html('<span class="text-muted">No DNS records found.</span>'); return; }
        var h = '<div style="margin-bottom:10px"><button class="btn btn-sm btn-success" id="addDnsBtn"><i class="fa fa-plus"></i> Add Record</button></div>';
        h += '<div class="row" style="font-weight:700;padding:8px 15px;border-bottom:2px solid #ddd"><div class="col-md-3">Name</div><div class="col-md-2">Type</div><div class="col-md-3">Content</div><div class="col-md-2">Proxy</div><div class="col-md-2">Actions</div></div>';
        $.each(records, function(i, rec) {
            var proxied = rec.proxied;
            h += '<div class="row" style="padding:8px 15px;border-bottom:1px solid #eee;font-size:13px"><div class="col-md-3">'+rec.name+'</div><div class="col-md-2">'+rec.type+'</div><div class="col-md-3" style="word-break:break-all">'+rec.content+'</div><div class="col-md-2"><label class="cf-switch"><input type="checkbox" class="dns-proxy-toggle" data-id="'+rec.id+'" '+(proxied?'checked':'')+'><span class="cf-slider" style="'+(proxied?'background:#5cb85c':'background:#ccc')+'"></span></label> <span class="cf-badge '+(proxied?'on':'off')+' dns-status">'+(proxied?'Proxied':'DNS Only')+'</span></div><div class="col-md-2"><button class="btn btn-xs btn-info dns-edit" data-record=\''+JSON.stringify(rec).replace(/'/g, '&apos;')+'\'><i class="fa fa-pencil"></i></button> <button class="btn btn-xs btn-danger dns-delete" data-id="'+rec.id+'"><i class="fa fa-trash"></i></button></div></div>';
        });
        $('#tab-dns').html(h);
        $('#addDnsBtn').on('click', function() { renderDnsModal('Add DNS Record', null); });
        $('.dns-edit').on('click', function() {
            try { var rec = JSON.parse($(this).data('record')); renderDnsModal('Edit DNS Record', rec); } catch(e) { showMsg('Error parsing record','error'); }
        });
        $('.dns-delete').on('click', function() {
            var id = $(this).data('id'), btn = $(this);
            if (!confirm('Delete this DNS record?')) return;
            btn.prop('disabled', true);
            $.ajax({ url: '{{ route("admin.security.cloudflare.dns.delete", "") }}/' + id, method: 'DELETE', data: { _token: '{{ csrf_token() }}' } })
                .done(function(r2) { if (r2.success) { loadAll(); showMsg('DNS record deleted','success'); } else showMsg(r2.error||'Failed','error'); })
                .fail(function(x) { showMsg(x.responseJSON?.error||'Delete failed','error'); btn.prop('disabled', false); });
        });
        $('.dns-proxy-toggle').on('change', function() {
            var id = $(this).data('id'), proxied = $(this).is(':checked'), row = $(this).closest('.row');
            $.post('{{ route("admin.security.cloudflare.dns.toggle") }}', { _token:'{{ csrf_token() }}', record_id:id, proxied:proxied })
                .done(function(r2) { if (r2.success) { row.find('.dns-status').text(proxied?'Proxied':'DNS Only').removeClass('on off').addClass(proxied?'on':'off'); } else showMsg(r2.error||'Failed','error'); })
                .fail(function() { showMsg('Toggle failed','error'); });
        });
    }

    function renderWaf(r) {
        if (!r.success || !r.rulesets) { $('#tab-waf').html('<span class="text-muted">No WAF rulesets found or API error.</span>'); return; }
        var h = '<div style="margin-bottom:10px"><button class="btn btn-sm btn-success" id="addWafBtn"><i class="fa fa-plus"></i> Add Custom Rule</button></div>';
        $.each(r.rulesets, function(i, rs) {
            var rules = rs.rules || [];
            h += '<div style="padding:8px 0;font-weight:700;border-bottom:1px solid #ddd;margin-top:8px">' + (rs.name || 'Unknown') + ' <small class="text-muted">(' + rs.kind + ')</small></div>';
            if (!rules.length) { h += '<div class="waf-row text-muted">No rules in this ruleset</div>'; }
            $.each(rules, function(j, rule) {
                var action = rule.action || 'N/A';
                var desc = rule.description || rule.expression || '-';
                h += '<div class="waf-row"><strong>' + action + '</strong> ' + desc + '</div>';
            });
        });
        if (!h) h = '<span class="text-muted">No WAF rulesets found.</span>';
        $('#tab-waf').html(h);
        $('#addWafBtn').on('click', function() {
            var e = '<div class="modal fade" id="wafModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content"><form id="wafForm"><div class="modal-header"><button type="button" class="close" data-dismiss="modal">&times;</button><h4 class="modal-title">Add Custom WAF Rule</h4></div><div class="modal-body">';
            e += '<div class="alert alert-info"><i class="fa fa-info-circle"></i> Adding WAF rules requires <strong>Rulesets:Edit</strong> permission on your Cloudflare API token.</div>';
            e += '<div class="form-group"><label>Description</label><input type="text" name="description" class="form-control" placeholder="e.g. Block admin paths"></div>';
            e += '<div class="form-group"><label>Expression</label><textarea name="expression" class="form-control" rows="2" placeholder=\'(http.request.uri.path contains "/wp-admin")\'></textarea><small class="text-muted">Cloudflare expression syntax</small></div>';
            e += '<div class="form-group"><label>Action</label><select name="action" class="form-control"><option value="block">Block</option><option value="challenge">Challenge (CAPTCHA)</option><option value="js_challenge">JS Challenge</option><option value="allow">Allow</option><option value="log">Log</option></select></div>';
            e += '</div><div class="modal-footer"><button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary">Add Rule</button></div></form></div></div></div>';
            if ($('#wafModal').length) $('#wafModal').remove();
            $('body').append(e); $('#wafModal').modal('show');
            $('#wafForm').off('submit').on('submit', function(ev) {
                ev.preventDefault();
                var data = { _token: '{{ csrf_token() }}', description: $('[name=description]').val(), expression: $('[name=expression]').val(), action: $('[name=action]').val() };
                $.post('{{ route("admin.security.cloudflare.waf.create") }}', data)
                    .done(function(r2) { if (r2.success) { $('#wafModal').modal('hide'); loadAll(); showMsg('WAF rule added','success'); } else showMsg(r2.error||'Failed','error'); })
                    .fail(function(x) { showMsg(x.responseJSON?.error||'Request failed','error'); });
            });
        });
    }

    function renderRate(r) {
        if (!r.success || !r.rules) { $('#tab-rate').html('<span class="text-muted">No rate limiting rules found or API error.</span>'); return; }
        var h = '<div style="margin-bottom:10px"><button class="btn btn-sm btn-success" id="addRateBtn"><i class="fa fa-plus"></i> Add Rate Limit</button></div>';
        $.each(r.rules, function(i, rule) {
            var desc = rule.description || '-';
            var threshold = rule.threshold || '-';
            var period = rule.period || '-';
            var action = rule.action?.mode || rule.action || '-';
            var disabled = rule.disabled ? '<span class="cf-badge off">Disabled</span>' : '<span class="cf-badge on">Active</span>';
            h += '<div class="rate-row"><div style="display:flex;justify-content:space-between"><div><strong>' + desc + '</strong></div><div><button class="btn btn-xs btn-danger rate-delete" data-id="'+rule.id+'"><i class="fa fa-trash"></i></button></div></div><div class="text-muted" style="font-size:12px">Threshold: ' + threshold + ' | Period: ' + period + 's | Action: ' + action + ' ' + disabled + '</div></div>';
        });
        if (!h) h = '<span class="text-muted">No rate limiting rules found.</span>';
        $('#tab-rate').html(h);
        $('#addRateBtn').on('click', function() {
            var e = '<div class="modal fade" id="rateModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content"><form id="rateForm"><div class="modal-header"><button type="button" class="close" data-dismiss="modal">&times;</button><h4 class="modal-title">Add Rate Limiting Rule</h4></div><div class="modal-body">';
            e += '<div class="alert alert-info"><i class="fa fa-info-circle"></i> Rate limiting may require <strong>WAF:Rate Limiting:Edit</strong> permission on your API token.</div>';
            e += '<div class="form-group"><label>URL Pattern</label><input type="text" name="pattern" class="form-control" value="*" placeholder="e.g. /api/*"></div>';
            e += '<div class="row"><div class="col-md-6"><div class="form-group"><label>Max Requests</label><input type="number" name="max_requests" class="form-control" value="100" min="1"></div></div>';
            e += '<div class="col-md-6"><div class="form-group"><label>Period (seconds)</label><input type="number" name="period" class="form-control" value="60" min="1"></div></div></div>';
            e += '<div class="form-group"><label>Action</label><select name="action" class="form-control"><option value="block">Block</option><option value="challenge">Challenge (CAPTCHA)</option><option value="js_challenge">JS Challenge</option><option value="managed_challenge">Managed Challenge</option><option value="log">Log Only</option></select></div>';
            e += '</div><div class="modal-footer"><button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary">Add Rule</button></div></form></div></div></div>';
            if ($('#rateModal').length) $('#rateModal').remove();
            $('body').append(e); $('#rateModal').modal('show');
            $('#rateForm').off('submit').on('submit', function(ev) {
                ev.preventDefault();
                var data = { _token: '{{ csrf_token() }}', pattern: $('[name=pattern]').val(), max_requests: $('[name=max_requests]').val(), period: $('[name=period]').val(), action: $('[name=action]').val() };
                $.post('{{ route("admin.security.cloudflare.rate.create") }}', data)
                    .done(function(r2) { if (r2.success) { $('#rateModal').modal('hide'); loadAll(); showMsg('Rate limit added','success'); } else showMsg(r2.error||'Failed','error'); })
                    .fail(function(x) { showMsg(x.responseJSON?.error||'Request failed','error'); });
            });
        });
        $('.rate-delete').on('click', function() {
            var id = $(this).data('id'), btn = $(this);
            if (!confirm('Delete this rate limiting rule?')) return;
            btn.prop('disabled', true);
            $.ajax({ url: '{{ route("admin.security.cloudflare.rate.delete", "") }}/' + id, method: 'DELETE', data: { _token: '{{ csrf_token() }}' } })
                .done(function(r2) { if (r2.success) { loadAll(); showMsg('Rate limit deleted','success'); } else showMsg(r2.error||'Failed','error'); })
                .fail(function(x) { showMsg(x.responseJSON?.error||'Delete failed','error'); btn.prop('disabled', false); });
        });
    }

    function bindEvents() {    function bindEvents() {
        $('.cf-toggle').off('change').on('change', function() {
            var setting = $(this).data('setting'), state = $(this).is(':checked');
            $(this).prop('disabled', true);
            updateSetting(setting, state ? 'on' : 'off', function() { loadAll(); });
        });
        $('.cf-dropdown').off('change').on('change', function() {
            var setting = $(this).data('setting'), val = $(this).val();
            updateSetting(setting, val, function() { setTimeout(loadAll, 1000); });
        });
        $('.cf-text').off('change').on('change', function() {
            var setting = $(this).data('setting'), val = $(this).val();
            updateSetting(setting, val);
        });
    }

    $('#refreshStatus').on('click', loadAll);
    loadAll();
    setInterval(loadAll, 30000);
});
</script>
@endsection
