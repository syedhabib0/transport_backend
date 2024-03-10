@component('mail::message')
    <h1>Welcome to our platform!</h1>
    <p>'Congratulations {!!$user_data->first_name!!} {!!$user_data->last_name!!}! You are successfully registered on IWS Transport Service'</p>
    <p>'Here is your password for login (remember to reset your password on first login): {!!$password!!}</p>
    <h5>Thank you for using our application!</h5>
@endcomponent