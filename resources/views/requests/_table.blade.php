<div class="card shadow-sm"><div class="table-responsive">
<table class="table table-hover align-middle mb-0 js-dt">
  <thead class="table-light"><tr><th>Tracking No.</th>@if($showUser)<th>Requester</th>@endif<th>Destination</th><th>Dates</th><th style="min-width:210px">Status</th><th></th></tr></thead>
  <tbody>
  @foreach($rows as $r)
    @php
      $stage = $r->stage(); $rej = $r->status === 'rejected';
      $labels = ['Filed', 'Recommended', 'Approved'];
    @endphp
    <tr>
      <td class="fw-semibold">{{ $r->tracking_no }}<div class="small text-muted fw-normal">{{ $r->created_at->format('M d, Y') }}</div></td>
      @if($showUser)<td>{{ $r->user->name }}<div class="small text-muted">{{ $r->user->office }}</div></td>@endif
      <td>{{ $r->destination }}</td>
      <td>{{ $r->date_from->format('M d, Y') }} – {{ $r->date_to->format('M d, Y') }}</td>
      <td>
        <span class="badge text-bg-{{ $r->statusColor() }}">{{ $r->statusLabel() }}</span>
        <div class="d-flex gap-1 mt-2" title="Filed → Recommended → Approved">
          @foreach($labels as $i => $label)
            @php
              $done = ($i + 1) <= $stage;
              $color = $done ? ($rej && ($i + 1) == $stage ? 'bg-danger' : 'bg-success') : 'bg-secondary-subtle';
            @endphp
            <div class="flex-fill"><div class="rounded {{ $color }}" style="height:6px"></div><div class="text-muted" style="font-size:.65rem">{{ $label }}</div></div>
          @endforeach
        </div>
        <div class="small mt-1 text-muted">
          @if($w = $r->pendingWith())
            Waiting for: <b>{{ $w->name }}</b>
          @elseif($r->status === 'approved')
            Approved {{ $r->decided_at?->format('M d, Y') }}
          @elseif($rej)
            Rejected by <b>{{ $r->rejectedBy()?->name }}</b> {{ $r->decided_at?->format('M d, Y') }}
          @endif
        </div>
      </td>
      <td class="text-end"><a href="{{ route('requests.show', $r) }}" class="btn btn-sm btn-outline-primary">Open</a></td>
    </tr>
  @endforeach
  </tbody>
</table></div></div>
