# Quick Start: Email Service Verification

## 🚀 Step-by-Step Verification

### Step 1: Configure SMTP Settings
Open in your browser:
```
http://yourdomain.com/setup-smtp-web.php
```
Click "Update SMTP Settings" to configure the database.

### Step 2: Verify Configuration
Open in your browser:
```
http://yourdomain.com/verify-email-service.php
```
This will check:
- ✅ Database connection
- ✅ SMTP settings configuration
- ✅ EmailService class availability
- ✅ PHPMailer installation
- ✅ API endpoints existence

### Step 3: Test Email Sending
On the verification page, enter your email address and click "Send Test Email" to test the forgot password email functionality.

### Alternative: Direct Testing

**Test SMTP Connection:**
```
http://yourdomain.com/test-smtp-connection.php
```

**Test Forgot Password Email:**
```
http://yourdomain.com/test-forgot-password-email.php?email=your@email.com
```

**Test Full Flow:**
1. Go to: `http://yourdomain.com/forgot-password.php`
2. Enter an email address
3. Check inbox/spam for reset email
4. Click reset link and set new password

## 📧 SMTP Configuration

The service is configured with:
- **Host**: smtp.hostinger.com
- **Port**: 465 (SSL)
- **Username**: mail@arzhizeon.com
- **From**: mail@archizeon.com

## ✅ What's Implemented

1. ✅ EmailService class with PHPMailer integration
2. ✅ Port 465 SSL configuration
3. ✅ Forgot password email template
4. ✅ Password reset confirmation email
5. ✅ Secure token generation (64-char hex)
6. ✅ 1-hour token expiration
7. ✅ API endpoints for forgot/reset password
8. ✅ Error handling and logging
9. ✅ Test scripts for verification

## 🔧 Files Created/Modified

**New Files:**
- `setup-smtp-web.php` - Web-based SMTP configuration
- `verify-email-service.php` - Complete verification script
- `test-forgot-password-email.php` - Forgot password email test
- `EMAIL_SERVICE_SETUP.md` - Detailed documentation
- `QUICK_START_EMAIL.md` - This file

**Modified Files:**
- `includes/email/EmailService.php` - Updated for port 465 SSL
- `test-smtp-connection.php` - Updated with correct defaults

**Existing Files (Already Working):**
- `api/v1/auth/forgot-password.php` - Forgot password API
- `api/v1/auth/reset-password.php` - Reset password API
- `forgot-password.php` - Frontend forgot password page
- `reset-password.php` - Frontend reset password page

## 🎯 Next Steps

1. Run `setup-smtp-web.php` to configure database
2. Run `verify-email-service.php` to verify everything
3. Test with a real email address
4. Check spam folder if email doesn't arrive
5. Monitor error logs if issues occur

## 📝 Notes

- The email service loads settings from the database
- If settings are missing, it uses the hardcoded defaults
- Port 465 automatically uses SSL encryption
- All emails include HTML and plain text versions
- Error logging is enabled for troubleshooting
