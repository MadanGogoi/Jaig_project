<!DOCTYPE html>
<html>
<head>
    <title>Did you forget your password?</title>
</head>
<body>
    <h2>Hi {{$user->name}}.</h2>
    <div>
        <p>Did you forget your password? Please use the reset code below, to change your password from Teamaxis App.</p>
        <p>If you did not initiate this forgot password, you can continue using your existing password.</p>
        <p><b>Reset code:</b> {{$reset_code}}</p>
    </div>
    <br>
    <div>Thanks,</div>
    <div>Whizz Team</div>
</body>
</html>
 