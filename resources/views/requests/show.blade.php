@extends('layouts.app')
@section('title', $t->tracking_no)
@section('content')
@php
  $canRecommend = $t->status === 'pending_recommendation' && (int)$t->recommender_id === $u->id;
  $canApprove   = $t->status === 'pending_approval' && (int)$t->approver_id === $u->id;
  $hasMemo = (bool) $t->memo_path;
@endphp
<a href="{{ route('requests.index') }}" class="text-decoration-none">&larr; Back</a>

<div class="row g-3 mt-1">
  {{-- Request details --}}
  <div class="{{ $hasMemo ? 'col-lg-5' : 'col-12' }}">
    <div class="card shadow-sm"><div class="card-body">
      <div class="d-flex justify-content-between flex-wrap gap-2">
        <div><h5 class="mb-0">{{ $t->tracking_no }}</h5><small class="text-muted">Filed {{ $t->created_at->format('M d, Y h:i A') }}</small></div>
        <div class="d-flex gap-2 align-items-start">
          <span class="badge text-bg-{{ $t->statusColor() }} fs-6">{{ $t->statusLabel() }}</span>
          @if($t->status === 'approved')<a class="btn btn-sm btn-success" target="_blank" href="{{ route('requests.print', $t) }}"><i class="bi bi-printer"></i> Print</a>@endif
        </div>
      </div>
      <hr>
      @if($t->verification_code)
      <div class="d-flex align-items-center gap-3 mb-3">
        <div id="qr" style="width:96px;height:96px;border:1px solid #dee2e6;border-radius:4px;flex:none"></div>
        <div class="small text-muted">Scan this QR code to check the status and authenticity of this travel authority.<br>Verification code: <b>{{ $t->verification_code }}</b></div>
      </div>
      @endif
      <dl class="row mb-0">
        <dt class="col-sm-4">Requester</dt><dd class="col-sm-8">{{ $t->user->name }} — {{ $t->user->position }}, {{ $t->user->office }}</dd>
        <dt class="col-sm-4">Purpose</dt><dd class="col-sm-8">{!! nl2br(e($t->purpose)) !!}</dd>
        <dt class="col-sm-4">Host of activity</dt><dd class="col-sm-8">{{ $t->host_of_activity }}</dd>
        <dt class="col-sm-4">Destination</dt><dd class="col-sm-8">{{ $t->destination }}</dd>
        <dt class="col-sm-4">Inclusive dates</dt><dd class="col-sm-8">{{ $t->inclusiveDates() }}</dd>
        <dt class="col-sm-4">Fund source</dt><dd class="col-sm-8">{{ $t->fund_source }}</dd>
        <dt class="col-sm-4">Memo</dt><dd class="col-sm-8">{!! $hasMemo ? '<i class="bi bi-paperclip"></i> Attached' : '<span class="text-muted">None attached</span>' !!}</dd>
      </dl>
      <hr>
      <h6>Approval trail</h6>
      <ul class="list-group mb-3">
        <li class="list-group-item"><b>Recommending:</b> {{ $t->recommender->name }}
          @if($t->recommended_at) <span class="badge text-bg-success">Recommended {{ $t->recommended_at->format('M d, Y') }}</span>@endif
          @if($t->recommender_remarks)<div class="small text-muted">“{{ $t->recommender_remarks }}”</div>@endif</li>
        <li class="list-group-item"><b>Approving:</b> {{ $t->approver->name }}
          @if($t->status==='approved') <span class="badge text-bg-success">Approved {{ $t->decided_at->format('M d, Y') }}</span>@endif
          @if($t->approver_remarks)<div class="small text-muted">“{{ $t->approver_remarks }}”</div>@endif</li>
        @if($t->status==='rejected')<li class="list-group-item text-danger"><b>Rejected</b> {{ $t->decided_at?->format('M d, Y') }}</li>@endif
      </ul>

      @if($t->logs->count())
        @php $ok = $t->integrityOk(); @endphp
        <h6>E-signature log <span class="badge text-bg-{{ $ok ? 'success' : 'danger' }}">{{ $ok ? 'Integrity verified' : 'Integrity check failed' }}</span></h6>
        <div class="table-responsive mb-2"><table class="table table-sm small mb-0">
          <thead><tr><th>Action</th><th>By</th><th>Date &amp; time</th><th>Hash</th></tr></thead>
          <tbody>@foreach($t->logs as $log)
            <tr><td>{{ ['requested'=>'Filed & e-signed','recommended'=>'Recommended & e-signed','approved'=>'Approved & e-signed','rejected'=>'Rejected'][$log->event] ?? $log->event }}</td>
                <td>{{ $log->user->name }}</td><td>{{ $log->signed_at->format('M d, Y h:i A') }}</td><td><code>{{ substr($log->document_hash, 0, 10) }}…</code></td></tr>
          @endforeach</tbody></table></div>
        @if($t->verification_code)<div class="small text-muted mb-3">Verification code: <b>{{ $t->verification_code }}</b> — <a href="{{ route('verify', $t->verification_code) }}" target="_blank">public verification page</a></div>@endif
      @endif

      @if($canRecommend || $canApprove)
      <form id="actForm" action="{{ route('requests.action', $t) }}">
        <input type="hidden" name="decision" id="decision">
        <label class="form-label">Remarks <span class="text-muted small">(required when rejecting)</span></label>
        <textarea name="remarks" class="form-control mb-3" rows="2"></textarea>
        <div class="mb-3">@include('requests._sign_fields', ['id' => 'act', 'purpose' => 'recommend or approve this travel authority'])<div class="form-text">Needed for Recommend / Approve. Not needed to reject.</div></div>
        <div class="d-flex gap-2">
          @if($canRecommend)<button class="btn btn-primary act" data-d="recommend" data-t="Recommend approval?">Recommend</button>@endif
          @if($canApprove)<button class="btn btn-success act" data-d="approve" data-t="Approve this travel authority?">Approve</button>@endif
          <button class="btn btn-outline-danger act" data-d="reject" data-t="Reject this request?">Reject</button>
        </div>
      </form>
      @endif
    </div></div>
  </div>

  {{-- Memo viewer: loads automatically so approvers can read it while deciding --}}
  @if($hasMemo)
  <div class="col-lg-7">
    <div class="card shadow-sm"><div class="card-body">
      <div class="d-flex justify-content-between align-items-center mb-2">
        <h6 class="mb-0"><i class="bi bi-file-earmark-text"></i> Memo</h6>
        <div class="d-flex gap-2">
          <a href="{{ $t->memoUrl() }}" target="_blank" class="btn btn-sm btn-outline-primary"><i class="bi bi-box-arrow-up-right"></i> Open in new tab</a>
          <a href="{{ $t->memoUrl(true) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-download"></i> Download</a>
        </div>
      </div>
      @if($t->memoIsPdf())
        <iframe src="{{ $t->memoUrl() }}#view=FitH" title="Memo" style="width:100%;height:80vh;border:1px solid #dee2e6;border-radius:6px"></iframe>
        <div class="small text-muted mt-2">Can't see the memo? Your browser may block PDF previews — use <b>Open in new tab</b>.</div>
      @else
        <img src="{{ $t->memoUrl() }}" alt="Memo" class="img-fluid border rounded">
      @endif
    </div></div>
  </div>
  @endif
</div>
@endsection
@push('scripts')
<script>
$('.act').on('click', function (e) {
  e.preventDefault();
  $('#decision').val($(this).data('d'));
  ajaxSubmit('#actForm', { confirm: { title: $(this).data('t'), text: 'Your e-signature will be applied where applicable.' } });
});
</script>
@endpush

@if($t->verification_code)
@push('scripts')
@include('partials.qr_script')
<script>makeQr(document.getElementById('qr'), @json(route('verify', $t->verification_code)));</script>
@endpush
@endif
