@php
  $groups = config('offices.groups');
  $all = collect($groups)->flatten()->all();
@endphp
{{-- keep an older free-text value selectable so it is not lost --}}
@if(!empty($selected) && !in_array($selected, $all))<option value="{{ $selected }}" selected>{{ $selected }} (current)</option>@endif
@foreach($groups as $group => $items)
  <optgroup label="{{ $group }}">
    @foreach($items as $o)<option value="{{ $o }}" @selected(($selected ?? null) === $o)>{{ $o }}</option>@endforeach
  </optgroup>
@endforeach
