<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Book Scanner</title>
    <style>
        .subscription-buttons {
            display: flex;
            gap: 10px;
        }
        .subscription-button {
            padding: 10px 20px;
            border: 1px solid #ccc;
            border-radius: 5px;
            cursor: pointer;
            background-color: #f0f0f0;
        }
        .subscription-button.selected {
            background-color: #007bff;
            color: white;
        }
    </style>
</head>
<body>
    <h1>Register</h1>
    <form method="POST" action="{{ route('register') }}">
        @csrf
        <input type="text" name="name" placeholder="Name" value="{{ old('name') }}" required>
        @error('name')
            <div style="color: red;">{{ $message }}</div>
        @enderror

        <input type="email" name="email" placeholder="Email" value="{{ old('email') }}" required>
        @error('email')
            <div style="color: red;">{{ $message }}</div>
        @enderror

        <input type="password" name="password" placeholder="Password" minlength="8" required>
        @error('password')
            <div style="color: red;">{{ $message }}</div>
        @enderror

        <input type="password" name="password_confirmation" placeholder="Confirm Password" required>
        @error('password_confirmation')
            <div style="color: red;">{{ $message }}</div>
        @enderror

        <div class="subscription-buttons">
            <div class="subscription-button" data-value="basic">Basic</div>
            <div class="subscription-button" data-value="pro">Pro</div>
        </div>
        <input type="hidden" name="subscription" value="{{ old('subscription', 'basic') }}">
        @error('subscription')
            <div style="color: red;">{{ $message }}</div>
        @enderror

        <button type="submit">Register</button>
    </form>
    <a href="{{ route('welcome') }}">Back</a>
    <a href="{{ route('login') }}">Login</a>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const buttons = document.querySelectorAll(".subscription-button");
            buttons.forEach(button => {
                button.addEventListener("click", function() {
                    buttons.forEach(btn => btn.classList.remove("selected"));
                    button.classList.add("selected");
                    document.querySelector('input[name="subscription"]').value = button.dataset.value;
                });
            });
        });
    </script>
</body>
</html>
