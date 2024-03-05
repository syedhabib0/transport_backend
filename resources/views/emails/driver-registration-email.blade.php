@component('mail::message')
    <h1>Welcome to our platform!</h1>
    <p>'Congratulations! '. $this->userData['first_name'] . ' ' . $this->userData['last_name'] .' You are successfully registered on IWS Transport Service'</p>
    <p>'Here is your password for login (remember to reset your password on first login): ' . $this->password</p>
    <p>'<strong>Reset Password</strong>', url('/password/reset')</p>
    <h5>Thank you for using our application!</h5>
@endcomponent