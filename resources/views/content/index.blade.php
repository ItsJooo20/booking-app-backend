@extends('layout.navbar')

@section('title', 'Login')

@section('content')
<div class="d-flex justify-content-center align-items-center min-vh-100" style="background: #f8f9fa;">
    <div class="card shadow-sm border-0 p-4" style="width: 100%; max-width: 400px;">
        <h2 class="mb-4 text-center text-dark">Login</h2>

        @if(session('success'))
            <div class="alert alert-success text-center">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger text-center">
                {{ session('error') }}
            </div>
        @endif

        <form action="{{ route('home') }}" method="POST">
            @csrf

            <div class="mb-3">
                <label for="email" class="form-label text-muted">Email</label>
                <input type="email" id="email" name="email" class="form-control" placeholder="you@example.com" required>
            </div>

            <div class="mb-3">
                <label for="password" class="form-label text-muted">Password</label>
                <input type="password" id="password" name="password" class="form-control" placeholder="••••••••" required>
            </div>

            <div class="d-grid">
                <button type="submit" class="btn btn-dark">Login</button>
            </div>
        </form>
    </div>
</div>
@endsection
