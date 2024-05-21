<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Settings - Book Scanner</title>
</head>
<body>
    <div class="account-settings">
        <h1>Account Settings</h1>

        <!-- Error and success message display -->
        @if(session('error'))
            <div style="color: red;">{{ session('error') }}</div>
        @endif
        @if(session('success'))
            <div style="color: green;">{{ session('success') }}</div>
        @endif

        <ul>
            <li><button onclick="toggleVisibility('change-email')">Change Email</button></li>
            <li><button onclick="toggleVisibility('change-password')">Change Password</button></li>
            <li><button onclick="toggleVisibility('change-name')">Change Name</button></li>
            <li><button onclick="toggleVisibility('delete-account')">Delete Account</button></li>
        </ul>

        <div id="change-email" style="display:none;">
            <form action="{{ route('account.email') }}" method="POST">
                @csrf
                <input type="email" name="email" placeholder="New email" value="{{ auth()->user()->email }}">
                <button type="submit">Update Email</button>
            </form>
        </div>

        <div id="change-password" style="display:none;">
            <form action="{{ route('account.password') }}" method="POST">
                @csrf
                <input type="password" name="current_password" placeholder="Current Password" required>
                <input type="password" name="new_password" placeholder="New Password" required>
                <input type="password" name="new_password_confirmation" placeholder="Confirm New Password" required>
                <button type="submit">Update Password</button>
            </form>

        </div>

        <div id="change-name" style="display:none;">
            <form action="{{ route('account.name') }}" method="POST">
                @csrf
                <input type="text" name="name" placeholder="New name" value="{{ auth()->user()->name }}">
                <button type="submit">Update Name</button>
            </form>
        </div>

        <div id="delete-account" style="display:none;">
            <form action="{{ route('account.delete') }}" method="POST">
                @csrf
                <button type="submit" onclick="return confirm('Are you sure you want to delete your account?');">Delete Account</button>
            </form>
        </div>
        <button type="button" onclick="window.history.back();">Back</button>
    </div>

    <script>
        function toggleVisibility(id) {
            var element = document.getElementById(id);
            var isDisplayed = element.style.display === 'block';
            element.style.display = isDisplayed ? 'none' : 'block';
        }
    </script>
</body>
</html>
