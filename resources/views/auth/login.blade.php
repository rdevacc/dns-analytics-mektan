<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Login | DNS Analytics</title>

    @vite([
        'resources/css/app.css',
        'resources/js/app.js'
    ])
</head>

<body class="bg-body-tertiary">

<div class="container">

    <div class="row justify-content-center align-items-center vh-100">

        <div class="col-md-5 col-lg-4">

            <div class="card shadow-sm">

                <div class="card-body p-4">

                    <div class="text-center mb-4">

                        <h3 class="fw-bold mb-1">
                            DNS Analytics
                        </h3>

                        <p class="text-muted mb-0">
                            Silakan login untuk melanjutkan
                        </p>

                    </div>

                    @if ($errors->any())

                        <div class="alert alert-danger">

                            {{ $errors->first() }}

                        </div>

                    @endif

                    <form
                        method="POST"
                        action="{{ route('login.store') }}"
                    >

                        @csrf

                        <div class="mb-3">

                            <label class="form-label">
                                Email
                            </label>

                            <input
                                type="email"
                                name="email"
                                class="form-control"
                                value="{{ old('email') }}"
                                required
                                autofocus
                            >

                        </div>

                        <div class="mb-4">

                            <label class="form-label">
                                Password
                            </label>

                            <input
                                type="password"
                                name="password"
                                class="form-control"
                                required
                            >

                        </div>

                        <button
                            class="btn btn-primary w-100"
                            type="submit"
                        >
                            Login
                        </button>

                    </form>

                </div>

            </div>

            <p class="text-center text-muted mt-3 small">
                &copy; {{ date('Y') }} DNS Analytics
            </p>

        </div>

    </div>

</div>

</body>
</html>