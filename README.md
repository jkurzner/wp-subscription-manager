# WP Subscriber Manager v2.1

A complete WordPress plugin for managing newsletter subscribers with double opt-in, CSV import/export, and cookie-based form hiding.

## 🚀 Features

### Admin Interface
- **Dashboard Overview**: See total, confirmed, and pending subscribers at a glance
- **Subscriber Listing**: Full table with names, emails, status, and dates
- **Bulk Actions**: Select multiple subscribers and delete them at once
- **Individual Actions**: Delete subscribers one at a time
- **Export CSV**: Download all subscribers as a CSV file
- **Import CSV**: Upload a CSV file to bulk-add subscribers
- **Real-time Updates**: AJAX-powered for smooth interactions

### Front-end Features
- **Double Opt-in**: Subscribers must confirm via email
- **Cookie Tracking**: Form automatically hides for subscribed users
- **Duplicate Prevention**: Won't allow the same email twice
- **Styled Messages**: Professional success/error/info messages
- **Shortcode Support**: Easy placement anywhere on your site

### Security
- WordPress nonces for all AJAX requests
- Email validation and sanitization
- Capability checking (only admins can manage subscribers)
- SQL injection protection via prepared statements
- Unique email constraint in database

## 📦 Installation

1. Upload the `wp-subscriber-manager.php` file to `/wp-content/plugins/`
2. Activate the plugin through the 'Plugins' menu in WordPress
3. The database table will be created automatically

## 🎯 Usage

### Adding the Subscription Form

Use the shortcode on any page or post:

```
[wsm_subscribe_form]
```

**With custom title:**
```
[wsm_subscribe_form title="Join Our Community"]
```

**Always show form (even for subscribers):**
```
[wsm_subscribe_form hide_for_subscribers="no"]
```

### Managing Subscribers

1. Go to **WordPress Admin → Subscribers**
2. View your subscriber list with real-time stats
3. Use the bulk actions or individual delete buttons
4. Export or import as needed

### Exporting Subscribers

1. Click **"Export CSV"** button
2. Downloads a file named `subscribers-YYYY-MM-DD.csv`
3. Includes: Name, Email, Status (Confirmed/Pending), Date

### Importing Subscribers

**CSV Format:**
```
Name,Email,Status,Date
John Doe,john@example.com,Confirmed,2024-01-01
Jane Smith,jane@example.com,Confirmed,2024-01-02
```

**Steps:**
1. Click **"Import CSV"** button
2. Select your CSV file
3. Imported subscribers are automatically marked as "Confirmed"
4. Duplicates are skipped
5. See import results in the alert message

### How Cookie Tracking Works

**When a user subscribes:**
1. User fills out the form
2. Receives confirmation email
3. Clicks confirmation link
4. Cookie `wsm_subscribed` is set (valid for 10 years)
5. Form no longer displays for that browser

**Cookie Details:**
- Name: `wsm_subscribed`
- Value: MD5 hash of email (privacy-friendly)
- Duration: 10 years
- Path: `/` (site-wide)

**Benefits:**
- Prevents repeat subscriptions
- Improves user experience
- No login required
- Privacy-friendly (hashed email)

## 🗄️ Database Structure

Table: `wp_custom_subscribers`

| Column | Type | Description |
|--------|------|-------------|
| id | mediumint(9) | Auto-increment ID |
| name | varchar(200) | Subscriber name |
| email | varchar(200) | Email (unique) |
| confirmed | tinyint(1) | 0=pending, 1=confirmed |
| token | varchar(100) | Confirmation token |
| created_at | datetime | Subscription date/time |

## 🎨 Styling

The plugin includes default styles that work with most themes:

**Form Styling:**
- Clean, modern input fields
- Blue submit button
- Mobile-responsive
- 500px max width

**Message Styling:**
- Success: Green background
- Error: Red background
- Info: Blue background

**To customize**, add CSS to your theme:

```css
/* Override form styles */
.wsm-form {
    max-width: 600px;
}

.wsm-form button {
    background: #your-color;
}

/* Override message styles */
.wsm-message.success {
    background: #your-color;
}
```

## 🔧 Advanced Customization

### Email Confirmation Message

Edit the `wsm_init` function (around line 314):

```php
$subject = 'Your Custom Subject';
$message = "Your custom message with confirmation link: " . $confirm_link;
```

### Cookie Duration

Change cookie lifetime (currently 10 years) on line 35 and 304:

```php
// Change to 1 year
time() + (365 * 24 * 60 * 60)

// Change to 30 days
time() + (30 * 24 * 60 * 60)
```

### Auto-confirm Imports

By default, imported subscribers are auto-confirmed. To change this, edit line 253:

```php
'confirmed' => 0 // Instead of 1
```

## 🐛 Troubleshooting

**Form doesn't hide after subscription:**
- Check if cookies are enabled in browser
- Clear browser cache
- Check `hide_for_subscribers` shortcode parameter

**Email not sending:**
- Check WordPress email configuration
- Use an SMTP plugin (recommended)
- Check spam folder

**Import not working:**
- Ensure CSV is properly formatted
- Check for UTF-8 encoding
- Verify Name and Email are in first two columns

**Subscribers not appearing:**
- Check database table exists: `wp_custom_subscribers`
- Verify plugin is activated
- Check for JavaScript errors in browser console

## 📋 CSV Import Template

Download or create a CSV file with this format:

```csv
Name,Email,Status,Date
John Smith,john@example.com,Confirmed,2024-01-15
Sarah Johnson,sarah@example.com,Confirmed,2024-01-16
Mike Williams,mike@example.com,Confirmed,2024-01-17
```

**Notes:**
- Header row is required (but skipped during import)
- Status and Date columns are ignored (for export compatibility)
- Only Name and Email are imported
- All imported subscribers are auto-confirmed

## 🔒 Privacy & GDPR

**Cookie Usage:**
- Only stores hashed email (not personally identifiable)
- Used solely for form display logic
- Can be cleared by user
- No tracking or analytics

**Data Storage:**
- All data stored locally in your WordPress database
- No third-party services
- Full control over data

**Recommendations:**
- Add to privacy policy
- Include unsubscribe option in emails
- Regular data cleanup
- Inform users about cookie usage

## 📝 Changelog

### Version 2.1
- Added complete admin interface with subscriber listing
- Implemented CSV export functionality
- Implemented CSV import functionality
- Added cookie-based subscription tracking
- Added bulk delete actions
- Added individual delete buttons
- Added statistics dashboard
- Improved duplicate email handling
- Added email validation
- Added AJAX for smooth UX
- Enhanced security with nonces
- Added unique email constraint
- Improved error messaging

### Version 2.0
- Initial double opt-in implementation
- Basic form handling

## 🤝 Support

For issues or questions:
- Check the Troubleshooting section
- Review WordPress error logs
- Verify all requirements are met

## 📄 License

This plugin is provided as-is. Use at your own discretion.

---

**Author:** jkurzner  
**Website:** https://mindshaft.com  
**Version:** 2.1
