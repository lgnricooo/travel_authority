@extends('layouts.app')
@section('title', 'Verify document')
@section('content')
<div class="row justify-content-center mt-4"><div class="col-lg-7">
@if(!$t)
  <div class="alert alert-danger"><b>Not found.</b> No travel authority matches this code. The printed copy may not be genuine.</div>
@else
  @php $req = $t->party('requester'); @endphp
  <div class="card shadow-sm"><div class="card-body">
    <h5 class="mb-1">Travel Authority {{ $t->tracking_no }}</h5>
    <div class="mb-3">
      <span class="badge text-bg-{{ $t->statusColor() }}">{{ $t->statusLabel() }}</span>
      @if($ok)<span class="badge text-bg-success">Integrity verified</span>@else<span class="badge text-bg-danger">Integrity check FAILED</span>@endif
    </div>
    <dl class="row mb-3">
      <dt class="col-sm-4">Employee</dt><dd class="col-sm-8">{{ $req['name'] }} — {{ $req['position'] }}, {{ $req['office'] }}</dd>
      <dt class="col-sm-4">Destination</dt><dd class="col-sm-8">{{ $t->destination }}</dd>
      <dt class="col-sm-4">Inclusive dates</dt><dd class="col-sm-8">{{ $t->inclusiveDates() }}</dd>
    </dl>
    <h6>Electronic signature log</h6>
    <table class="table table-sm small"><thead><tr><th>Action</th><th>By</th><th>Date &amp; time</th></tr></thead><tbody>
    @foreach($t->logs as $log)
      <tr><td>{{ ['requested'=>'Filed & e-signed','recommended'=>'Recommended & e-signed','approved'=>'Approved & e-signed','rejected'=>'Rejected'][$log->event] ?? $log->event }}</td>
          <td>{{ $log->user->name }}</td><td>{{ $log->signed_at->format('M d, Y h:i A') }}</td></tr>
    @endforeach
    </tbody></table>
    <p class="small text-muted mb-0">The original of this document is in electronic format. A printout is a secondary copy. If the integrity check fails or the details do not match the printout, do not rely on the printout.</p>
  </div></div>
@endif
</div></div>
@endsection
