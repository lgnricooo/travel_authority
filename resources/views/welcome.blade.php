<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
  <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/favicon-32.png') }}">
  <link rel="icon" type="image/png" sizes="192x192" href="{{ asset('images/icon-192.png') }}">
  <link rel="apple-touch-icon" href="{{ asset('images/apple-touch-icon.png') }}">
  <title>Travel Authority System | SDO Tuguegarao City</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <style>
    body{background:#f4f6fa}
    .hero{background:linear-gradient(135deg,#0d47a1,#1976d2);color:#fff;padding:90px 0 110px}
    .step{width:44px;height:44px;border-radius:50%;background:#e3f2fd;color:#0d47a1;display:inline-flex;align-items:center;justify-content:center;font-size:1.3rem}
  </style>
</head>
<body>
<nav class="navbar navbar-dark" style="background:#0b3d91">
  <div class="container">
    <a class="navbar-brand fw-bold d-flex align-items-center" href="{{ url('/') }}"><img src="{{ asset('images/logo.png') }}" alt="" width="34" height="34" class="me-2"> SDO Tuguegarao City</a>
    <a href="{{ route('login') }}" class="btn btn-light btn-sm fw-semibold"><i class="bi bi-box-arrow-in-right"></i> Login</a>
  </div>
</nav>

<section class="hero text-center">
  <div class="container">
    <img src="{{ asset('images/logo.png') }}" alt="SDO Tuguegarao City seal" width="110" height="110" class="mb-3">
    <p class="text-uppercase small mb-2" style="letter-spacing:2px;opacity:.85">Department of Education • Schools Division of Tuguegarao City</p>
    <h1 class="display-5 fw-bold">Travel Authority System</h1>
    <p class="lead mb-4">File, recommend, approve, and print travel authorities online — fast, paperless, and tracked.</p>
    <a href="{{ route('login') }}" class="btn btn-warning btn-lg fw-semibold px-4">Get Started</a>
  </div>
</section>

<section class="container" style="margin-top:-60px">
  <div class="row g-3">
    @foreach([
      ['bi-pencil-square','File a request','Submit your travel details and attach the memo.'],
      ['bi-patch-check','Recommend & approve','Routed to your recommending officer, then the SDS.'],
      ['bi-vector-pen','E-signature','Sign once and it is applied at every step.'],
      ['bi-printer','Print','Print the approved authority together with its memo.'],
    ] as [$icon,$title,$text])
    <div class="col-md-6 col-lg-3">
      <div class="card h-100 shadow-sm border-0"><div class="card-body text-center p-4">
        <span class="step mb-3"><i class="bi {{ $icon }}"></i></span>
        <h6 class="fw-bold">{{ $title }}</h6><p class="text-muted small mb-0">{{ $text }}</p>
      </div></div>
    </div>
    @endforeach
  </div>
</section>

<footer class="text-center text-muted small py-5">© {{ date('Y') }} Schools Division Office of Tuguegarao City</footer>
</body>
</html>
