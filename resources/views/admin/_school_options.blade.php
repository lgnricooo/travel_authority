@php
  $order = ['Elementary School', 'Integrated School', 'High School'];
  $groups = $schools->groupBy(fn($s) => $s->level ?: 'Other')
      ->sortBy(fn($g, $k) => ($i = array_search($k, $order)) === false ? 9 : $i);
@endphp
@foreach($groups as $level => $group)
  <optgroup label="{{ $level }}">
    @foreach($group as $s)<option value="{{ $s->id }}" @selected((int) ($selected ?? 0) === $s->id)>{{ $s->name }}</option>@endforeach
  </optgroup>
@endforeach
