@component('mail::message')
    <h1>We have received your request to reset your account password</h1>
    <p>You can use the following code to recover your account:</p>
    <h1>{!! $code !!}</h1>
@endcomponent
