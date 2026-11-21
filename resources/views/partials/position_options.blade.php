@foreach($positions->groupBy('group') as $group => $items)
  <optgroup label="{{ $group }}">
    @foreach($items as $p)<option value="{{ $p->id }}" @selected((int) ($selected ?? 0) === $p->id)>{{ $p->label() }}</option>@endforeach
  </optgroup>
@endforeach
