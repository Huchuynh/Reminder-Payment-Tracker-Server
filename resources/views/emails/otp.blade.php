<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OTP Verification</title>
</head>
<body style="background-color: #f8fafc; font-family: Arial, sans-serif; color: #333; margin: 0; padding: 40px 0;">
    <table align="center" width="100%" style="max-width: 600px; background: #fff; border-radius: 8px; padding: 30px; box-shadow: 0 0 10px rgba(0,0,0,0.05);">
        <tr>
            <td align="center" style="padding-bottom: 20px;">
                <h2 style="margin: 0; color: #1e293b;">OTP for {{ $appName }} Login</h2>
            </td>
        </tr>

        <tr>
            <td>
                <p>Hello,</p>
                <p>Enter the following verification code when prompted to securely sign in to your <strong>{{ $appName }}</strong> account.</p>
                <p>This code will expire in <strong>{{ $expires }} minutes</strong>.</p>
            </td>
        </tr>

        <tr>
            <td align="center" style="padding: 20px 0;">
                <div style="
                    display: inline-block;
                    padding: 15px 30px;
                    background-color: #f1f5f9;
                    border: 1px solid #cbd5e1;
                    border-radius: 8px;
                    font-size: 32px;
                    font-weight: bold;
                    letter-spacing: 5px;
                    color: #1e293b;">
                    {{ $otp }}
                </div>
            </td>
        </tr>

        <tr>
            <td style="padding-top: 20px; color: #64748b; font-size: 14px;">
                <p>This sign-in was requested using <strong>UNKNOWN Windows</strong>. If you didn’t request this, you can safely ignore this email.</p>
            </td>
        </tr>

        <tr>
            <td align="center" style="padding-top: 20px; font-size: 12px; color: #94a3b8;">
                <p>© {{ date('Y') }} {{ $appName }}. All rights reserved.</p>
            </td>
        </tr>
    </table>
</body>
</html>
