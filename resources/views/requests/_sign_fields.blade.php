<div class="border rounded p-3 bg-light">
  <div class="fw-semibold small mb-2"><i class="bi bi-vector-pen"></i> E-signature authentication</div>
  <div class="form-check mb-2">
    <input type="checkbox" class="form-check-input" name="affirm" value="1" id="affirm_{{ $id }}">
    <label class="form-check-label small" for="affirm_{{ $id }}">
      I affirm that the e-signature on file is mine, that I am affixing it on purpose to {{ $purpose }}, and that I take responsibility and accountability that this document stays unchanged until it is submitted to the auditor.
    </label>
  </div>
  <label class="form-label small mb-1">Confirm your password to sign</label>
  <input type="password" name="sign_password" class="form-control" autocomplete="current-password">
</div>
