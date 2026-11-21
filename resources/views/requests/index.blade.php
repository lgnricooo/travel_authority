@extends('layouts.app')
@section('title', 'Requests')
@section('content')
@php $u = auth()->user(); @endphp
@if(!$u->signature_path && $u->role !== 'admin')
  <div class="alert alert-warning">You have not set your e-signature yet. <a href="{{ route('profile.edit') }}">Set it up now</a>.</div>
@elseif($u->signature_path && !$u->signature_verified_at && $u->role !== 'admin')
  <div class="alert alert-info">Your e-signature is awaiting verification by the administrator (focal person). You can sign requests once it is verified.</div>
@endif

<div class="d-flex justify-content-between align-items-center mb-3">
  <h4 class="mb-0">Travel Authority Requests</h4>
  @if($u->canRequest())
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newModal"><i class="bi bi-plus-lg"></i> New Request</button>
  @endif
</div>

<ul class="nav nav-tabs mb-3">
  @if($u->canRequest())<li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#mine">My Requests <span class="badge bg-secondary">{{ $mine->count() }}</span></button></li>@endif
  <li class="nav-item"><button class="nav-link {{ $u->canRequest() ? '' : 'active' }}" data-bs-toggle="tab" data-bs-target="#todo">For My Action <span class="badge bg-danger">{{ $forAction->count() }}</span></button></li>
</ul>

<div class="tab-content">
  <div class="tab-pane fade {{ $u->canRequest() ? 'show active' : '' }}" id="mine">
    @include('requests._table', ['rows' => $mine, 'showUser' => false])
  </div>
  <div class="tab-pane fade {{ $u->canRequest() ? '' : 'show active' }}" id="todo">
    @include('requests._table', ['rows' => $forAction, 'showUser' => true])
  </div>
</div>

@if($u->canRequest())
<div class="modal fade" id="newModal" tabindex="-1"><div class="modal-dialog modal-lg"><form class="modal-content" id="newForm" action="{{ route('requests.store') }}" enctype="multipart/form-data">
  <div class="modal-header"><h5 class="modal-title">New Travel Authority</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
  <div class="modal-body row g-3">
    <div class="col-12"><label class="form-label">Purpose of travel</label><textarea name="purpose" rows="3" class="form-control" required></textarea></div>
    <div class="col-12"><label class="form-label">Host of activity</label><input name="host_of_activity" class="form-control" placeholder="e.g. DepEd Region II" required></div>
    <div class="col-12"><label class="form-label">Destination</label><input name="destination" class="form-control" required></div>
    <div class="col-md-6"><label class="form-label">Date from</label><input type="date" name="date_from" min="{{ date('Y-m-d') }}" class="form-control" required></div>
    <div class="col-md-6"><label class="form-label">Date to</label><input type="date" name="date_to" min="{{ date('Y-m-d') }}" class="form-control" required></div>
    <div class="col-md-6"><label class="form-label">Fund source</label>
      <input name="fund_source" class="form-control" list="funds" value="No expense to the government" required>
      <datalist id="funds"><option value="No expense to the government"><option value="MOOE"><option value="Local fund"><option value="Registration fee"></datalist></div>
    <div class="col-md-6"><label class="form-label">Attach memo (PDF/JPG/PNG, max 5MB)</label><input type="file" name="memo" accept=".pdf,.jpg,.jpeg,.png" class="form-control"></div>
    <div class="col-12 small text-muted">Routing: <b>{{ $u->resolveRecommender()?->name ?? 'No recommender assigned yet' }}</b> (recommending) → <b>SDS</b> (approval)</div>
    <div class="col-12">@include('requests._sign_fields', ['id' => 'new', 'purpose' => 'file this travel authority'])</div>
  </div>
  <div class="modal-footer"><button class="btn btn-light" type="button" data-bs-dismiss="modal">Cancel</button><button class="btn btn-primary" type="submit">Submit</button></div>
</form></div></div>
@endif
@endsection
@include('partials.datatables')
@push('scripts')
<script>$('#newForm').on('submit', function (e) { e.preventDefault(); ajaxSubmit(this, { confirm: { title: 'Submit this request?', text: 'It will be routed for recommendation.' } }); });</script>
@endpush
