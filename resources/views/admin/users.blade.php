@extends('layouts.app')
@section('title', 'Users')
@section('content')
@php $recommenders = $users->whereIn('role', ['unit_head', 'asds']); @endphp
<div class="d-flex justify-content-between mb-3"><h4>Users</h4><button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal"><i class="bi bi-person-plus"></i> Add User</button></div>
<div class="alert alert-info small">
  <b>Routing:</b> a <b>Teacher / school personnel</b> is routed automatically to the <b>School Head</b> of their school.
  For School Heads, SDO Personnel and Unit Heads, choose the recommending officer when editing.
</div>

<div class="card shadow-sm"><div class="table-responsive"><table class="table align-middle mb-0 js-dt">
  <thead class="table-light"><tr><th>Name</th><th>Role</th><th>Position</th><th>School / Office</th><th>Recommending officer</th><th class="text-end">Actions</th></tr></thead>
  <tbody>
  @foreach($users as $x)
    @php $rec = $x->resolveRecommender(); @endphp
    <tr>
      <td>{{ $x->name }}<div class="small text-muted">{{ $x->email }}</div></td>
      <td>{{ $x->roleLabel() }}</td>
      <td>{{ $x->position ?: '—' }}</td>
      <td>{{ $x->school?->name ?? ($x->office ?: '—') }}</td>
      <td>
        @if($x->role === 'teacher')
          {!! $rec ? e($rec->name) . ' <span class="small text-muted">(School Head)</span>' : '<span class="text-warning">No School Head yet</span>' !!}
        @else
          {{ $rec?->name ?? '—' }}
        @endif
      </td>
      <td class="text-end text-nowrap">
        <button type="button" class="btn btn-sm btn-outline-primary btn-edit"
          data-user="{{ json_encode(['url' => route('admin.users.update', $x), 'name' => $x->name, 'email' => $x->email, 'role' => $x->role,
            'position_id' => $x->position_id, 'school_id' => $x->school_id, 'office' => $x->office, 'recommender_id' => $x->recommender_id]) }}">Edit</button>
        <form class="delForm d-inline" action="{{ route('admin.users.destroy', $x) }}" data-t="Delete {{ $x->name }}?">
          <button class="btn btn-sm btn-outline-danger" @disabled($x->id === auth()->id())>Delete</button>
        </form>
      </td>
    </tr>
  @endforeach
  </tbody></table></div></div>

{{-- Add user --}}
<div class="modal fade" id="addModal" tabindex="-1"><div class="modal-dialog modal-lg"><form class="modal-content" id="addForm" action="{{ route('admin.users.store') }}">
  <div class="modal-header"><h5 class="modal-title">Add User</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
  <div class="modal-body">@include('admin._user_fields', ['p' => 'add', 'isNew' => true])</div>
  <div class="modal-footer"><button class="btn btn-light" type="button" data-bs-dismiss="modal">Cancel</button><button class="btn btn-primary" type="submit">Create</button></div>
</form></div></div>

{{-- Edit user --}}
<div class="modal fade" id="editModal" tabindex="-1"><div class="modal-dialog modal-lg"><form class="modal-content" id="editForm" action="#">
  <div class="modal-header"><h5 class="modal-title" id="editTitle">Edit User</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
  <div class="modal-body">@include('admin._user_fields', ['p' => 'edit', 'isNew' => false])</div>
  <div class="modal-footer"><button class="btn btn-light" type="button" data-bs-dismiss="modal">Cancel</button><button class="btn btn-primary" type="submit">Save changes</button></div>
</form></div></div>
@endsection

@include('partials.datatables')
@push('styles')
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">
<style>.ts-dropdown{z-index:2000}</style>
@endpush
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
<script>
// Searchable dropdowns (school, position)
const ts = {};
document.querySelectorAll('select.ts-select').forEach(el => ts[el.id] = new TomSelect(el, {
  allowEmptyOption: true, maxOptions: 200, placeholder: el.dataset.ph || 'Search…', dropdownParent: 'body',
}));

// Show school (teachers / school heads) or office (everyone else); teachers are routed automatically
function toggleRole(p) {
  const role = $('#' + p + '_role').val(), school = ['teacher', 'school_head'].includes(role);
  $('#' + p + '_fschool').toggle(school); $('#' + p + '_foffice').toggle(!school); $('#' + p + '_frec').toggle(role !== 'teacher');
}
$('#add_role').on('change', () => toggleRole('add'));
$('#edit_role').on('change', () => toggleRole('edit'));
toggleRole('add');

$(document).on('click', '.btn-edit', function () {
  const u = JSON.parse(this.dataset.user), f = $('#editForm');
  f.attr('action', u.url);
  f.find('[name=name]').val(u.name); f.find('[name=email]').val(u.email); f.find('[name=password]').val('');
  f.find('[name=role]').val(u.role); f.find('[name=recommender_id]').val(u.recommender_id || '');
  const o = u.office || '';
  if (o && !ts.edit_office.options[o]) ts.edit_office.addOption({ value: o, text: o + ' (current)' });
  ts.edit_office.setValue(o, true);
  ts.edit_school.setValue(u.school_id || '', true); ts.edit_position.setValue(u.position_id || '', true);
  toggleRole('edit');
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
