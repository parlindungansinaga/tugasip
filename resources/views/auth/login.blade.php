@extends('layouts.app')

@section('title', 'Masuk ke Sistem - Toko Retail')

@section('content')
<div class="row justify-content-center align-items-center" style="min-height: 80vh;">
    <div class="col-12 col-sm-10 col-md-8 col-lg-5 col-xl-4">
        <div class="card card-custom shadow-lg border-0">
            <div class="card-body p-4 p-sm-5">
                <div class="text-center mb-4">
                    <div class="d-inline-flex align-items-center justify-content-center bg-primary bg-opacity-10 text-primary rounded-circle mb-3" style="width: 68px; height: 68px;">
                        <i class="bi bi-shop fs-2"></i>
                    </div>
                    <h4 class="fw-bold text-dark mb-1">Toko Retail Modern</h4>
                    <p class="text-muted small">Silakan masuk menggunakan akun Anda</p>
                </div>

                <form action="{{ route('login.post') }}" method="POST" id="loginForm">
                    @csrf

                    <div class="mb-3">
                        <label for="login" class="form-label small fw-semibold text-secondary">Username atau Email</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-person"></i></span>
                            <input type="text" 
                                   class="form-control border-start-0 ps-0 @error('login') is-invalid @enderror" 
                                   id="login" 
                                   name="login" 
                                   value="{{ old('login') }}" 
                                   placeholder="admin / gudang / kasir" 
                                   required 
                                   autofocus>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <label for="password" class="form-label small fw-semibold text-secondary">Kata Sandi</label>
                        </div>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-lock"></i></span>
                            <input type="password" 
                                   class="form-control border-start-0 border-end-0 ps-0 @error('password') is-invalid @enderror" 
                                   id="password" 
                                   name="password" 
                                   placeholder="••••••••" 
                                   required>
                            <button class="btn btn-outline-secondary border-start-0 bg-light text-muted" type="button" id="togglePasswordBtn">
                                <i class="bi bi-eye" id="toggleIcon"></i>
                            </button>
                        </div>
                    </div>

                    <div class="mb-4 form-check">
                        <input type="checkbox" class="form-check-input" id="remember" name="remember">
                        <label class="form-check-label small text-muted" for="remember">Ingat Saya di Perangkat Ini</label>
                    </div>

                    <div class="d-grid mb-4">
                        <button type="submit" class="btn btn-primary-custom py-2 fw-semibold">
                            <i class="bi bi-box-arrow-in-right me-1"></i> Masuk Sekarang
                        </button>
                    </div>
                </form>

                <!-- Quick Demo Login Selection -->
                <div class="mt-4 pt-3 border-top">
                    <p class="small text-muted fw-semibold text-center mb-2">
                        <i class="bi bi-lightning-charge-fill text-warning me-1"></i> Akun Demo Cepat (Klik untuk Isi):
                    </p>
                    <div class="d-flex flex-wrap gap-2 justify-content-center">
                        <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3 py-1 demo-btn" 
                                data-user="admin" data-pass="password123">
                            <i class="bi bi-shield-lock me-1"></i> Admin
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-info rounded-pill px-3 py-1 demo-btn" 
                                data-user="gudang" data-pass="password123">
                            <i class="bi bi-box-seam me-1"></i> Gudang
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-success rounded-pill px-3 py-1 demo-btn" 
                                data-user="kasir" data-pass="password123">
                            <i class="bi bi-cart me-1"></i> Kasir
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-2 py-1 demo-btn" 
                                data-user="kasir_nonaktif" data-pass="password123" title="Uji penolakan akun nonaktif">
                            <i class="bi bi-slash-circle me-1"></i> Nonaktif
                        </button>
                    </div>
                </div>

            </div>
        </div>

        <p class="text-center text-muted small mt-4">
            &copy; {{ date('Y') }} Sistem Informasi Manajemen Toko Retail. All rights reserved.
        </p>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Toggle password visibility
        const toggleBtn = document.getElementById('togglePasswordBtn');
        const passwordInput = document.getElementById('password');
        const toggleIcon = document.getElementById('toggleIcon');

        if (toggleBtn) {
            toggleBtn.addEventListener('click', function () {
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);
                toggleIcon.classList.toggle('bi-eye');
                toggleIcon.classList.toggle('bi-eye-slash');
            });
        }

        // Quick demo filler
        const demoBtns = document.querySelectorAll('.demo-btn');
        demoBtns.forEach(btn => {
            btn.addEventListener('click', function () {
                const user = this.getAttribute('data-user');
                const pass = this.getAttribute('data-pass');
                document.getElementById('login').value = user;
                document.getElementById('password').value = pass;
            });
        });
    });
</script>
@endpush
