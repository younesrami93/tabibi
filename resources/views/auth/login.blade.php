<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Tabibi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body class="bg-light d-flex align-items-center justify-content-center vh-100">

    <div class="card shadow-sm" style="width: 400px;">
        <div class="card-body p-4">
            <h3 class="text-center mb-4">Doctor Login</h3>
            
            <div id="error-alert" class="alert alert-danger d-none"></div>

            <form id="loginForm">
                <div class="mb-3">
                    <label for="email" class="form-label">Email Address</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                    <div class="invalid-feedback" id="email-error"></div>
                </div>
                
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>

                <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input" id="remember">
                    <label class="form-check-label" for="remember">Remember me</label>
                </div>

                <button type="submit" id="submitBtn" class="btn btn-primary w-100">Sign In</button>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        const loginForm = document.getElementById('loginForm');
        const submitBtn = document.getElementById('submitBtn');
        const errorAlert = document.getElementById('error-alert');

        loginForm.addEventListener('submit', function(e) {
            e.preventDefault(); // Stop page reload

            // Reset errors
            errorAlert.classList.add('d-none');
            submitBtn.disabled = true;
            submitBtn.innerText = 'Logging in...';

            // Prepare Data
            const formData = {
                email: document.getElementById('email').value,
                password: document.getElementById('password').value,
                remember: document.getElementById('remember').checked
            };

            // Send Request via Axios
            axios.post('/login', formData)
                .then(response => {
                    // Success! Redirect to dashboard
                    window.location.href = response.data.redirect;
                })
                .catch(error => {
                    submitBtn.disabled = false;
                    submitBtn.innerText = 'Sign In';
                    
                    if (error.response && error.response.status === 422) {
                        // Validation Error (Wrong password/email)
                        const errors = error.response.data.errors;
                        if(errors.email) {
                            errorAlert.innerText = errors.email[0];
                            errorAlert.classList.remove('d-none');
                        }
                    } else {
                        // System Error
                        errorAlert.innerText = 'Something went wrong. Please try again.';
                        errorAlert.classList.remove('d-none');
                    }
                });
        });
    </script>
</body>
</html>