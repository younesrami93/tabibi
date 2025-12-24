<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Page Not Found - Tabibi</title>

    {{-- FONTS --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    {{-- ICONS --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    {{-- BOOTSTRAP CSS --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    {{-- YOUR CUSTOM CSS --}}
    <link href="{{ asset('css/custom.css') }}" rel="stylesheet">

    <style>
        body {
            background-color: #f8fafc;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Inter', sans-serif;
        }

        .error-card {
            max-width: 500px;
            width: 100%;
            border: none;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08);
            border-radius: 1rem;
            text-align: center;
            padding: 3rem 2rem;
            background: white;
        }

        .icon-box {
            width: 80px;
            height: 80px;
            background: #fee2e2;
            color: #ef4444;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            margin: 0 auto 1.5rem;
        }
    </style>
</head>

<body>

    <div class="container">
        <div class="d-flex justify-content-center">
            <div class="error-card animate-fade-in-up">

                <div class="icon-box">
                    <i class="fa-solid fa-file-circle-xmark"></i>
                </div>

                <h2 class="fw-bold text-dark mb-2">404 Not Found</h2>

                {{-- Display Custom Message from Controller if available, else default --}}
                <p class="text-muted mb-4">
                    {{ $message ?? "Oops! The page or resource you are looking for could not be found." }}
                </p>

                <div class="d-grid gap-2 col-8 mx-auto">
                    <a href="{{ url()->previous() }}" class="btn btn-primary fw-bold shadow-sm rounded-pill">
                        <i class="fa-solid fa-arrow-left me-2"></i> Go Back
                    </a>
                    <a href="{{ route('dashboard') }}" class="btn btn-light text-muted fw-bold rounded-pill">
                        Return to Dashboard
                    </a>
                </div>

            </div>
        </div>
    </div>

    {{-- BOOTSTRAP JS --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>