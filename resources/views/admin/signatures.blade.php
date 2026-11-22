@extends('layouts.app')
@section('title', 'E-Signature Verification')
@section('content')
<h4 class="mb-2">E-Signature Verification</h4>
<div class="alert alert-info small">
  As the designated focal person, check each person's identity (in person or by valid ID) and that the drawn signature is theirs, then click <b>Verify</b>.
  A user cannot file, recommend or approve until their signature is verified. Use <b>Revoke</b> if a signature is compromised, misused or the person leaves.
</div>
<div class="card shadow-sm"><div class="table-responsive"><table class="table align-middle mb-0 js-dt">
  <thead class="table-light"><tr><th>Person</th><th>Signature</th><th>Undertaking</th><th>Status</th><th></th></tr></thead>
  <tbody>
  @foreach($users as $x)
    <tr>
      <td>{{ $x->name }}<div class="small text-muted">{{ $x->roleLabel() }} • {{ $x->position }} • {{ $x->office }}</div></td>
      <td>@if($x->signature_path)<img src="{{ Storage::disk('s3')->url($x->signature_path) }}?v={{ $x->updated_at?->timestamp }}" class="border rounded bg-white p-1" style="max-height:60px">@endif</td>
      <td class="small">{!! $x->signature_undertaking_at ? $x->signature_undertaking_at->format('M d, Y') : '<span class="text-danger">None — must re-save</span>' !!}</td>
      <td>@if($x->signature_verified_at)<span class="badge text-bg-success">Verified</span><div class="small text-muted">{{ $x->signature_verified_at->format('M d, Y') }} by {{ $x->verifier?->name }}</div>
          @else<span class="badge text-bg-warning">Pending</span>@endif</td>
      <td class="text-end">
        @if(!$x->signature_verified_at)
          <form class="sigForm d-inline" action="{{ route('admin.signatures.verify', $x) }}" data-t="Verify {{ $x->name }}'s e-signature?"><button class="btn btn-sm btn-success" @disabled(!$x->signature_undertaking_at)>Verify</button></form>
        @else
          <form class="sigForm d-inline" action="{{ route('admin.signatures.revoke', $x) }}" data-t="Revoke {{ $x->name }}'s e-signature?"><button class="btn btn-sm btn-outline-danger">Revoke</button></form>
        @endif
      </td>
    </tr>
  @endforeach
  </tbody></table></div></div>
@endsection
@include('partials.datatables')
@push('scripts')
<script>$(document).on('submit', '.sigForm', function (e) { e.preventDefault(); ajaxSubmit(this, { confirm: { title: $(this).data('t') } }); });</script>
@endpush
