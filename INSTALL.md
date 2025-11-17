# Quick Installation Guide

## Step 1: Upload Plugin

1. Download `wp-subscriber-manager.php`
2. Go to your WordPress admin panel
3. Navigate to **Plugins → Add New → Upload Plugin**
4. Click **Choose File** and select `wp-subscriber-manager.php`
5. Click **Install Now**
6. Click **Activate Plugin**

## Step 2: Verify Installation

1. Look for **Subscribers** in the left WordPress admin menu (with a group icon)
2. Click it to see the admin interface
3. Database table is created automatically on activation

## Step 3: Add Subscription Form

1. Edit any page or post
2. Add this shortcode where you want the form:
   ```
   [wsm_subscribe_form]
   ```
3. Publish/update the page

## Step 4: Test It

1. Visit the page with the form
2. Enter a test name and email
3. Submit the form
4. Check your email for the confirmation link
5. Click the confirmation link
6. Verify the subscriber appears in **WordPress Admin → Subscribers**

## Common Shortcode Variations

**Basic (default):**
```
[wsm_subscribe_form]
```

**Custom title:**
```
[wsm_subscribe_form title="Join Our Newsletter"]
```

**Always show form (ignore cookie):**
```
[wsm_subscribe_form hide_for_subscribers="no"]
```

## Features Overview

### Admin Panel
- View all subscribers
- See confirmed vs pending count
- Delete individual or multiple subscribers
- Export all subscribers to CSV
- Import subscribers from CSV

### Front-end
- Double opt-in confirmation via email
- Cookie prevents re-subscription
- Duplicate email prevention
- Clean, styled messages
- Mobile responsive

## Need Help?

See the full README.md for:
- Detailed feature explanations
- Troubleshooting guide
- Customization options
- GDPR/Privacy information
- Database structure

---

That's it! Your subscriber management system is ready to use.
