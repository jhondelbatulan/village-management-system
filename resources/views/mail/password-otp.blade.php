<x-mail::message>
# Password Reset Code

Hello {{ $user->full_name }},

We received a request to reissue the login credentials for your account. Enter the verification code below to confirm it was you:

# {{ $code }}

This code expires in {{ $minutes }} minutes.

Once you enter the code, we will email your username and temporary password.

<x-mail::button :url="route('password.otp.show')">
Enter Verification Code
</x-mail::button>

If you did not request this, you can safely ignore this email — your password will not change.

Thanks,<br>
{{ config('app_settings.name', config('app.name', 'Multi-Village Management System')) }}
</x-mail::message>
