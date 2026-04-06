# Email Service Setup Guide

This guide explains how to set up and test the PHPMailer email service for the forgot password feature.

## SMTP Configuration

The email service is configured with the following Hostinger SMTP settings:

- **SMTP Host**: `smtp.hostinger.com`
- **Port**: `465` (SSL encryption)
- **Username**: `mail@arzhizeon.com`
- **Password**: `Adnan@66202`
- **From Email**: `mail@archizeon.com`
- **From Name**: `BLine Boutique`

## Setup Steps

### 1. Update Database Settings

Run the setup script to configure SMTP settings in the database:

```bash
php setup-smtp-settings.php
```

This will update the `system_settings` table with the correct SMTP configuration.

### 2. Test SMTP Connection

Test the basic SMTP connection:

```bash
php test-smtp-connection.php
```

Or access via browser:
```
http://yourdomain.com/test-smtp-connection.php
```

This will:
- Connect to the SMTP server
- Send a test email to verify the connection works
- Display detailed debug information

### 3. Test Forgot Password Email

Test the forgot password email functionality:

```bash
php test-forgot-password-email.php your@email.com
```

Or access via browser:
```
http://yourdomain.com/test-forgot-password-email.php?email=your@email.com
```

This will:
- Initialize the EmailService
- Generate a test reset token
- Send a formatted forgot password email
- Verify the email template and functionality

## How It Works

### EmailService Class

The `EmailService` class (`includes/email/EmailService.php`) handles all email operations:

1. **Configuration**: Automatically loads SMTP settings from the database
2. **Port Detection**: Automatically detects encryption type based on port:
   - Port 465 → SSL (SMTPS)
   - Port 587 → TLS (STARTTLS)
3. **Error Handling**: Comprehensive error logging and graceful failure handling

### Forgot Password Flow

1. User requests password reset via `forgot-password.php`
2. Frontend calls `/api/v1/auth/forgot-password.php`
3. API endpoint:
   - Validates email address
   - Generates secure reset token (64-character hex string)
   - Stores token in database with 1-hour expiration
   - Calls `EmailService::sendForgotPasswordEmail()`
4. EmailService sends HTML email with reset link
5. User clicks link → `reset-password.php?token=...&email=...`
6. User sets new password
7. Confirmation email sent

## Email Templates

The service includes professionally designed HTML email templates:

- **Forgot Password Email**: Includes reset button, link, and expiry notice
- **Password Reset Confirmation**: Confirms successful password change

Both templates are:
- Mobile-responsive
- Branded with BLine Boutique styling
- Include plain text alternatives

## Troubleshooting

### Email Not Sending

1. **Check SMTP Settings**: Verify credentials in database
2. **Check Port**: Port 465 requires SSL, not TLS
3. **Check Firewall**: Ensure port 465 is not blocked
4. **Check Logs**: Review PHP error logs for detailed errors
5. **Test Connection**: Run `test-smtp-connection.php` with debug enabled

### Common Issues

**Issue**: "Connection timeout"
- **Solution**: Check firewall settings, verify SMTP host is correct

**Issue**: "Authentication failed"
- **Solution**: Verify username and password are correct

**Issue**: "SSL certificate problem"
- **Solution**: SMTPOptions are configured to handle this, but verify SSL settings

**Issue**: "Email sent but not received"
- **Solution**: Check spam folder, verify recipient email is valid

## Best Practices

1. **Security**:
   - Reset tokens expire after 1 hour
   - Tokens are cryptographically secure (random_bytes)
   - Tokens are single-use (cleared after password reset)

2. **Error Handling**:
   - Never reveal if email exists (security best practice)
   - Always return success message to user
   - Log errors for administrator review

3. **Testing**:
   - Always test in development first
   - Use real email addresses for testing
   - Check both inbox and spam folder

4. **Production**:
   - Set `SMTPDebug = 0` in production
   - Monitor error logs regularly
   - Set up email delivery monitoring

## API Endpoints

### Forgot Password
- **Endpoint**: `POST /api/v1/auth/forgot-password.php`
- **Body**: `{ "email": "user@example.com" }`
- **Response**: `{ "success": true, "message": "..." }`

### Reset Password
- **Endpoint**: `POST /api/v1/auth/reset-password.php`
- **Body**: `{ "email": "...", "token": "...", "password": "...", "confirm_password": "..." }`
- **Response**: `{ "success": true, "message": "..." }`

## Files Modified/Created

- `includes/email/EmailService.php` - Main email service class (updated)
- `test-smtp-connection.php` - SMTP connection test script (updated)
- `test-forgot-password-email.php` - Forgot password email test (new)
- `setup-smtp-settings.php` - Database configuration script (new)
- `api/v1/auth/forgot-password.php` - Forgot password API (already exists)
- `api/v1/auth/reset-password.php` - Reset password API (already exists)

## Support

For issues or questions:
1. Check PHP error logs
2. Run test scripts with debug enabled
3. Verify SMTP credentials with Hostinger
4. Review PHPMailer documentation: https://github.com/PHPMailer/PHPMailer
