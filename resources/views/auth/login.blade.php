@extends('layouts.app')
@section('title', 'Login')
@section('content')
<div class="row justify-content-center mt-5"><div class="col-md-5 col-lg-4">
  <div class="card shadow-sm"><div class="card-body p-4">
    <div class="text-center mb-3">
      <img src="{{ asset('images/logo.png') }}" alt="SDO Tuguegarao City" width="92" height="92" class="mb-2">
      <h5 class="brand mb-0">Travel Authority System</h5>
      <small class="text-muted">SDO Tuguegarao City</small>
    </div>
    <form id="loginForm" action="{{ route('login') }}">
      <div class="mb-3"><label class="form-label">Email</label><input type="email" name="email" class="form-control" required autofocus></div>
      <div class="mb-3"><label class="form-label">Password</label><input type="password" name="password" class="form-control" required></div>
      <div class="form-check mb-3"><input type="checkbox" name="remember" value="1" class="form-check-input" id="rem"><label for="rem" class="form-check-label">Remember me</label></div>
      <button class="btn btn-primary w-100" type="submit">Sign in</button>
    </form>
  </div></div>
</div></div>
@endsection
@push('scripts')
<script>$('#loginForm').on('submit', function (e) { e.preventDefault(); ajaxSubmit(this); });</script>
@endpush
