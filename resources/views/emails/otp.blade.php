@component('mail::message')
# OTP for Password Reset

Your OTP for password reset is: {{ $otp }}

Thanks,<br>
{{ config('app.name') }}
@endcomponent