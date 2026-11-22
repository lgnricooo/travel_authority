<div class="row g-3">
  <div class="col-md-6"><label class="form-label">Full name</label><input name="name" class="form-control" required></div>
  <div class="col-md-6"><label class="form-label">Email</label><input type="email" name="email" class="form-control" required></div>
  <div class="col-md-6"><label class="form-label">{{ $isNew ? 'Temporary password (min 8)' : 'New password' }}</label>
    <input type="password" name="password" class="form-control" autocomplete="new-password" placeholder="{{ $isNew ? '' : 'Leave blank to keep the current one' }}" @required($isNew)></div>
  <div class="col-md-6"><label class="form-label">Role</label>
    <select name="role" id="{{ $p }}_role" class="form-select">@foreach($roles as $k => $v)<option value="{{ $k }}">{{ $v }}</option>@endforeach</select></div>
  <div class="col-12"><label class="form-label">Position / Designation</label>
    <select name="position_id" id="{{ $p }}_position" class="ts-select" data-ph="Search position…"><option value="">Search position…</option>
      @include('partials.position_options', ['selected' => null])</select></div>
  <div class="col-12" id="{{ $p }}_fschool"><label class="form-label">School</label>
    <select name="school_id" id="{{ $p }}_school" class="ts-select" data-ph="Search school…"><option value="">Search school…</option>
      @include('admin._school_options', ['selected' => null])</select></div>
  <div class="col-12" id="{{ $p }}_foffice"><label class="form-label">Office / Unit</label>
    <select name="office" id="{{ $p }}_office" class="ts-select" data-ph="Search office / unit…"><option value="">Search office / unit…</option>
      @include('partials.office_options', ['selected' => null])</select></div>
  <div class="col-12" id="{{ $p }}_frec"><label class="form-label">Recommending officer</label>
    <select name="recommender_id" class="form-select"><option value="">— none —</option>
      @foreach($recommenders as $o)<option value="{{ $o->id }}">{{ $o->name }} ({{ $o->roleLabel() }})</option>@endforeach</select></div>
</div>
