@extends('layouts.app')
@section('title', 'My Profile')
@section('content')
@php $schoolBased = in_array($user->role, ['teacher', 'school_head']); @endphp
<div class="row justify-content-center"><div class="col-lg-7">
  <h4 class="mb-3">My Profile</h4>

  {{-- Details --}}
  <div class="card shadow-sm mb-4"><div class="card-body">
    <h6 class="mb-3">Details</h6>
    <form id="profileForm" action="{{ route('profile.update') }}">
      <div class="mb-3"><label class="form-label">Full name</label><input name="name" class="form-control" value="{{ $user->name }}" required></div>
      <div class="mb-3"><label class="form-label">Email</label><input type="email" name="email" class="form-control" value="{{ $user->email }}" required></div>

      <div class="mb-3"><label class="form-label">Position / Designation</label>
        <select name="position_id" class="ts-select" data-ph="Search your position…"><option value="">Search your position…</option>
          @include('partials.position_options', ['selected' => $user->position_id])</select>
        @if(!$user->position_id && $user->position)<div class="form-text text-warning">Current: {{ $user->position }} — please choose your position from the list.</div>@endif</div>

      <div class="mb-3"><label class="form-label">{{ $schoolBased ? 'School (Permanent Station)' : 'Office / Unit (Permanent Station)' }}</label>
        @if($schoolBased)
          <select name="school_id" class="ts-select" data-ph="Search your school…"><option value="">Search your school…</option>
            @include('admin._school_options', ['selected' => $user->school_id])</select>
          @if($user->role === 'teacher')<div class="form-text">Your travel requests go to the School Head of the school you choose.</div>@endif
        @else
          <select name="office" class="ts-select" data-ph="Search your office / unit…"><option value="">Search your office / unit…</option>
            @include('partials.office_options', ['selected' => $user->office])</select>
        @endif</div>

      <div class="mb-3"><label class="form-label">Current password <span class="text-muted small">(only needed if you change your email)</span></label>
        <input type="password" name="current_password" class="form-control" autocomplete="current-password"></div>
      <button class="btn btn-primary" type="submit">Save changes</button>
    </form>
  </div></div>

  {{-- Password --}}
  <div class="card shadow-sm mb-4"><div class="card-body">
    <h6 class="mb-3">Change password</h6>
    <form id="passwordForm" action="{{ route('profile.password') }}">
      <div class="mb-3"><label class="form-label">Current password</label><input type="password" name="current_password" class="form-control" autocomplete="current-password" required></div>
      <div class="row g-3 mb-3">
        <div class="col-md-6"><label class="form-label">New password <span class="text-muted small">(min 8)</span></label><input type="password" name="password" class="form-control" autocomplete="new-password" required></div>
        <div class="col-md-6"><label class="form-label">Confirm new password</label><input type="password" name="password_confirmation" class="form-control" autocomplete="new-password" required></div>
      </div>
      <button class="btn btn-primary" type="submit">Update password</button>
    </form>
  </div></div>

  {{-- E-signature --}}
  <div class="card shadow-sm"><div class="card-body">
    <h6>E-Signature</h6>
    <p class="text-muted small">Draw your signature below. It is stamped on travel authorities you file, recommend, or approve. Requests already signed keep their original signature.</p>
    @if($user->signature_path)
      <div class="mb-3"><div class="small text-muted">Current signature —
          @if($user->signature_verified_at)<span class="badge text-bg-success">Verified {{ $user->signature_verified_at->format('M d, Y') }}</span>
          @elseif($user->signature_undertaking_at)<span class="badge text-bg-warning">Awaiting verification by the administrator</span>
          @else<span class="badge text-bg-secondary">Not yet verified — please re-save it with the undertaking</span>@endif</div>
        <img src="{{ Storage::disk('s3')->url($user->signature_path) }}?v={{ time() }}" class="border rounded bg-white p-2" style="max-height:100px"></div>
    @endif
    <canvas id="pad" class="border rounded bg-white w-100" height="200"></canvas>
    <div class="form-check mt-3">
      <input type="checkbox" class="form-check-input" id="undertaking">
      <label class="form-check-label small" for="undertaking">
        <b>Undertaking:</b> I declare that this e-signature is my own and that I will use it only for my own official documents. I take full responsibility and accountability for every document bearing it, I will report any suspected misuse or compromise to the administrator right away, and I understand that misuse may be sanctioned under existing laws and DepEd/SDO rules.
      </label>
    </div>
    <div class="form-text">After saving, the administrator verifies your signature before you can sign. Changing it later requires verification again.</div>
    <div class="mt-3 d-flex gap-2">
      <button class="btn btn-outline-secondary" id="clear" type="button">Clear</button>
      <button class="btn btn-primary" id="save" type="button">{{ $user->signature_path ? 'Replace signature' : 'Save signature' }}</button>
    </div>
  </div></div>
</div></div>
@endsection
@push('styles')
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">
<style>.ts-dropdown{z-index:2000}</style>
@endpush
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js"></script>
<script>
document.querySelectorAll('select.ts-select').forEach(el => new TomSelect(el, {
  allowEmptyOption: true, maxOptions: 200, placeholder: el.dataset.ph || 'Search…',
}));
$('#profileForm, #passwordForm').on('submit', function (e) { e.preventDefault(); ajaxSubmit(this); });

const canvas = document.getElementById('pad');
function resize() { const r = Math.max(window.devicePixelRatio || 1, 1); canvas.width = canvas.offsetWidth * r; canvas.height = 200 * r; canvas.getContext('2d').scale(r, r); }
resize();
const pad = new SignaturePad(canvas, { penColor: '#000' });
$('#clear').on('click', () => pad.clear());
$('#save').on('click', () => {
  if (pad.isEmpty()) return Swal.fire({ icon: 'warning', title: 'Please draw your signature first.' });
  if (!$('#undertaking').is(':checked')) return Swal.fire({ icon: 'warning', title: 'Please tick the undertaking first.' });
  Swal.fire({ icon: 'question', title: 'Save this signature?', showCancelButton: true, confirmButtonText: 'Yes, save' }).then(res => {
    if (!res.isConfirmed) return;
    $.post('{{ route('signature.store') }}', { signature: pad.toDataURL('image/png'), undertaking: 1 })
      .done(r => Swal.fire({ icon: 'success', title: r.message, timer: 1400, showConfirmButton: false }).then(() => location.reload()))
      .fail(x => Swal.fire({ icon: 'error', title: 'Oops', text: x.responseJSON?.message || 'Could not save.' }));
  });
});
</script>
@endpush
