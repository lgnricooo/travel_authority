@extends('layouts.app')
@section('title', 'My E-Signature')
@section('content')
<div class="row justify-content-center"><div class="col-lg-6">
  <div class="card shadow-sm"><div class="card-body">
    <h5>My E-Signature</h5>
    <p class="text-muted small">Draw your signature below. It is stamped on travel authorities you file, recommend, or approve.</p>
    @if($user->signature_path)
      <div class="mb-3"><div class="small text-muted">Current signature</div>
        <img src="{{ Storage::disk('s3')->url($user->signature_path) }}?v={{ time() }}" class="border rounded bg-white p-2" style="max-height:100px"></div>
    @endif
    <canvas id="pad" class="border rounded bg-white w-100" height="200"></canvas>
    <div class="mt-3 d-flex gap-2">
      <button class="btn btn-outline-secondary" id="clear" type="button">Clear</button>
      <button class="btn btn-primary" id="save" type="button">Save signature</button>
    </div>
  </div></div>
</div></div>
@endsection
@push('scripts')
<script src="https://jsdelivr.net"></script>
<script>
const canvas = document.getElementById('pad');
function resize() { const r = Math.max(window.devicePixelRatio || 1, 1); canvas.width = canvas.offsetWidth * r; canvas.height = 200 * r; canvas.getContext('2d').scale(r, r); }
resize();
const pad = new SignaturePad(canvas, { penColor: '#000' });
$('#clear').on('click', () => pad.clear());
$('#save').on('click', () => {
  if (pad.isEmpty()) return Swal.fire({ icon: 'warning', title: 'Please draw your signature first.' });
  $.post('{{ route('signature.store') }}', { signature: pad.toDataURL('image/png') })
    .done(r => Swal.fire({ icon: 'success', title: r.message, timer: 1400, showConfirmButton: false }).then(() => location.href = r.redirect))
    .fail(x => Swal.fire({ icon: 'error', title: 'Oops', text: x.responseJSON?.message || 'Could not save.' }));
});
</script>
@endpush
