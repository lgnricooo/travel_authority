<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
  <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/favicon-32.png') }}">
  <link rel="icon" type="image/png" sizes="192x192" href="{{ asset('images/icon-192.png') }}">
  <link rel="apple-touch-icon" href="{{ asset('images/apple-touch-icon.png') }}">
  <title>@yield('title', 'Travel Authority') | SDO Tuguegarao City</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <style>body{background:#f4f6fa}.brand{font-weight:700;letter-spacing:.3px}</style>
  @stack('styles')
</head>
<body>
@auth
<nav class="navbar navbar-expand-md navbar-dark bg-primary mb-4">
  <div class="container">
    <a class="navbar-brand brand d-flex align-items-center" href="{{ route('requests.index') }}"><img src="{{ asset('images/logo.png') }}" alt="" width="34" height="34" class="me-2"> SDO Tuguegarao • Travel Authority</a>
    <button class="navbar-toggler" data-bs-toggle="collapse" data-bs-target="#nav"><span class="navbar-toggler-icon"></span></button>
    <div id="nav" class="collapse navbar-collapse">
      <ul class="navbar-nav me-auto">
        <li class="nav-item"><a class="nav-link" href="{{ route('requests.index') }}">Requests</a></li>
        <li class="nav-item"><a class="nav-link" href="{{ route('profile.edit') }}">My Profile</a></li>
        @if(auth()->user()->role === 'admin')
          @php $pendingSig = \App\Models\User::whereNotNull('signature_path')->whereNull('signature_verified_at')->count(); @endphp
          <li class="nav-item"><a class="nav-link" href="{{ route('admin.signatures') }}">E-Signatures @if($pendingSig)<span class="badge bg-warning text-dark">{{ $pendingSig }}</span>@endif</a></li>
          <li class="nav-item"><a class="nav-link" href="{{ route('admin.schools') }}">Schools</a></li>
          <li class="nav-item"><a class="nav-link" href="{{ route('admin.users') }}">Users</a></li>
        @endif
      </ul>
      <span class="navbar-text me-3 small">{{ auth()->user()->name }} <span class="badge bg-light text-primary">{{ auth()->user()->roleLabel() }}</span></span>
      <form method="POST" action="{{ route('logout') }}">@csrf<button class="btn btn-sm btn-outline-light">Logout</button></form>
    </div>
  </div>
</nav>
@endauth
<main class="container pb-5">@yield('content')</main>

<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.14.5/dist/sweetalert2.all.min.js"></script>
<script>
$.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name=csrf-token]').attr('content') } });

// Generic AJAX submit with SweetAlert feedback. opts.confirm = {title,text} asks first.
function ajaxSubmit(form, opts = {}) {
  const $f = $(form);
  const run = () => {
    const $btn = $f.find('[type=submit]').prop('disabled', true);
    $.ajax({ url: $f.attr('action'), method: 'POST', data: new FormData($f[0]), processData: false, contentType: false })
      .done(r => Swal.fire({ icon: 'success', title: r.message || 'Done', timer: 1400, showConfirmButton: false })
        .then(() => r.redirect ? (location.href = r.redirect) : location.reload()))
      .fail(x => {
        let m = x.responseJSON?.message || 'Something went wrong.';
        if (x.responseJSON?.errors) m = Object.values(x.responseJSON.errors).flat().join('<br>');
        Swal.fire({ icon: 'error', title: 'Oops', html: m });
      })
      .always(() => $btn.prop('disabled', false));
  };
  if (opts.confirm) {
    Swal.fire({ icon: 'question', showCancelButton: true, confirmButtonText: 'Yes, continue', ...opts.confirm })
      .then(res => res.isConfirmed && run());
  } else run();
}

// If the browser restores this page from its back/forward cache, reload so the server re-checks the login.
window.addEventListener('pageshow', e => { if (e.persisted) location.reload(); });
</script>
@stack('scripts')
</body>
</html>
