@extends('layouts.app')
@section('title', 'Schools')
@section('content')
<div class="d-flex justify-content-between mb-3"><h4>Schools</h4><button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal"><i class="bi bi-building-add"></i> Add Schools</button></div>

<div class="card shadow-sm"><div class="table-responsive"><table class="table align-middle mb-0 js-dt">
  <thead class="table-light"><tr><th>School</th><th>Level</th><th>School ID</th><th>District</th><th>School Head</th><th>Staff</th><th class="text-end">Actions</th></tr></thead>
  <tbody>
  @foreach($schools as $s)
    <tr>
      <td>{{ $s->name }}</td>
      <td>{{ $s->level ?: '—' }}</td>
      <td>{{ $s->school_code ?: '—' }}</td>
      <td>{{ $s->district ?: '—' }}</td>
      <td>@if($s->head){{ $s->head->name }}@else<span class="text-warning">No School Head yet</span>@endif</td>
      <td>{{ $s->users_count }}</td>
      <td class="text-end text-nowrap">
        <button type="button" class="btn btn-sm btn-outline-primary btn-edit"
          data-school="{{ json_encode(['url' => route('admin.schools.update', $s), 'name' => $s->name, 'level' => $s->level, 'school_code' => $s->school_code, 'district' => $s->district]) }}">Edit</button>
        <form class="delForm d-inline" action="{{ route('admin.schools.destroy', $s) }}" data-t="Delete {{ $s->name }}?">
          <button class="btn btn-sm btn-outline-danger">Delete</button>
        </form>
      </td>
    </tr>
  @endforeach
  </tbody></table></div></div>

{{-- Add schools --}}
<div class="modal fade" id="addModal" tabindex="-1"><div class="modal-dialog"><form class="modal-content" id="addForm" action="{{ route('admin.schools.store') }}">
  <div class="modal-header"><h5 class="modal-title">Add Schools</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
  <div class="modal-body">
    <label class="form-label">School name(s) <span class="text-muted small">— one per line</span></label>
    <textarea name="names" rows="6" class="form-control mb-3" placeholder="Atulayan Elementary School&#10;Tuguegarao North Central School" required></textarea>
    <div class="row g-3">
      <div class="col-md-6"><label class="form-label">Level <span class="text-muted small">(optional)</span></label>
        <select name="level" class="form-select"><option value="">—</option>@foreach($levels as $l)<option>{{ $l }}</option>@endforeach</select></div>
      <div class="col-md-6"><label class="form-label">District <span class="text-muted small">(optional)</span></label>
        <input name="district" class="form-control" placeholder="e.g. Tuguegarao North District"></div>
    </div>
    <div class="form-text mt-2">Level and district are applied to every school you add here.</div>
  </div>
  <div class="modal-footer"><button class="btn btn-light" type="button" data-bs-dismiss="modal">Cancel</button><button class="btn btn-primary" type="submit">Add</button></div>
</form></div></div>

{{-- Edit school --}}
<div class="modal fade" id="editModal" tabindex="-1"><div class="modal-dialog"><form class="modal-content" id="editForm" action="#">
  <div class="modal-header"><h5 class="modal-title" id="editTitle">Edit School</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
  <div class="modal-body row g-3">
    <div class="col-12"><label class="form-label">School name</label><input name="name" class="form-control" required></div>
    <div class="col-md-6"><label class="form-label">Level</label>
      <select name="level" class="form-select"><option value="">—</option>@foreach($levels as $l)<option>{{ $l }}</option>@endforeach</select></div>
    <div class="col-md-6"><label class="form-label">School ID</label><input name="school_code" class="form-control"></div>
    <div class="col-12"><label class="form-label">District</label><input name="district" class="form-control"></div>
  </div>
  <div class="modal-footer"><button class="btn btn-light" type="button" data-bs-dismiss="modal">Cancel</button><button class="btn btn-primary" type="submit">Save changes</button></div>
</form></div></div>
@endsection

@include('partials.datatables')
@push('scripts')
<script>
$(document).on('click', '.btn-edit', function () {
  const u = JSON.parse(this.dataset.school), f = $('#editForm');
  f.attr('action', u.url);
  f.find('[name=name]').val(u.name); f.find('[name=level]').val(u.level || ''); f.find('[name=school_code]').val(u.school_code || ''); f.find('[name=district]').val(u.district || '');
  $('#editTitle').text('Edit ' + u.name);
  bootstrap.Modal.getOrCreateInstance('#editModal').show();
});
$('#addForm, #editForm').on('submit', function (e) { e.preventDefault(); ajaxSubmit(this); });
$(document).on('submit', '.delForm', function (e) {
  e.preventDefault();
  ajaxSubmit(this, { confirm: { icon: 'warning', title: $(this).data('t'), text: 'This cannot be undone.', confirmButtonText: 'Yes, delete' } });
});
</script>
@endpush
