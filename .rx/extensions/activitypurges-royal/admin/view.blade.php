<!-- 
  Content on this page will be displayed on your extension's admin page.
-->
<div class="box box-info">
  <div class="box-header with-border">
    <h3 class="box-title">Information</h3>
  </div>
  <div class="box-body">
    <p>
      An Extension Used To <code>Clear/Purge</code> selected logs in order to clear database storage.
    </p>
  </div>
</div>

<div class="box box-info">
  <div class="box-header with-border">
    <h3 class="box-title">Management</h3>
  </div>
  <div class="box-body">
    <!-- Display success or error message if available -->
    @if(isset($message) && $message)
      <div class="alert alert-success">
        {{ $message }}
      </div>
    @endif
    <!-- The form posts to the same page -->
    <form method="POST" action="{{ $root }}">
      @csrf

      <!-- Multi-selection for tables -->
      <div class="form-group">
        <label>Selection Table Area</label>
        <div class="d-flex">
          <div class="form-check mr-3">
            <input class="form-check-input" type="checkbox" id="activity_logs" name="tables[]" value="activity_logs">
            <label class="form-check-label" for="activity_logs">Activity Logs</label>
          </div>
          <div class="form-check mr-3">
            <input class="form-check-input" type="checkbox" id="api_logs" name="tables[]" value="api_logs">
            <label class="form-check-label" for="api_logs">API Logs</label>
          </div>
          <div class="form-check">
            <input class="form-check-input" type="checkbox" id="audit_logs" name="tables[]" value="audit_logs">
            <label class="form-check-label" for="audit_logs">Audit Logs</label>
          </div>
          <div class="form-check">
            <input class="form-check-input" type="checkbox" id="failed_jobs" name="tables[]" value="failed_jobs">
            <label class="form-check-label" for="failed_jobs">Failed Logs</label>
          </div>
        </div>
      </div>

      <!-- Quick Delete Buttons -->
      <div class="form-group">
        <label>Quick Action</label>
        <div class="d-flex align-items-center">
          <button type="submit" name="quick" value="7" class="btn btn-danger mr-2">1 Week</button>
          <button type="submit" name="quick" value="14" class="btn btn-danger mr-2">2 Weeks</button>
          <button type="submit" name="quick" value="30" class="btn btn-danger mr-2">1 Month</button>
          <button type="submit" name="quick" value="90" class="btn btn-danger mr-2">3 Months</button>
          <button type="submit" name="quick" value="180" class="btn btn-danger mr-2">6 Months</button>
          <button type="submit" name="quick" value="365" class="btn btn-danger">1 Year</button>
        </div>
      </div>

      <!-- Manual Purge Button -->
      <div class="form-group">
        <label for="timestamp">Manual Purge</label>
        <input type="datetime-local" name="timestamp" id="timestamp" class="form-control">
      </div>
      <div class="form-group">
        <button type="submit" class="btn btn-danger">Purge</button>
      </div>
    </form>
  </div>
</div>
