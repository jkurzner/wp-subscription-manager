# WP Subscriber Manager v3.0 - Installation Instructions

## 🚀 Quick Install via WordPress Admin

### Method 1: Upload via WordPress Dashboard (Recommended)

1. **Download** the `wp-subscriber-manager-3.0.zip` file
2. **Login** to your WordPress admin panel
3. Navigate to **Plugins → Add New**
4. Click the **Upload Plugin** button at the top
5. Click **Choose File** and select `wp-subscriber-manager-3.0.zip`
6. Click **Install Now**
7. Once installed, click **Activate Plugin**
8. Done! Look for **Subscribers** in your admin menu

### Method 2: FTP/File Manager Upload

1. **Download** and **extract** `wp-subscriber-manager-3.0.zip`
2. Upload the entire `wp-subscriber-manager` folder to `/wp-content/plugins/`
3. Go to **WordPress Admin → Plugins**
4. Find "WP Subscriber Manager" and click **Activate**

---

## ✅ Verify Installation

After activation, you should see:
- **"Subscribers"** menu item in WordPress admin (left sidebar with group icon)
- A success message confirming activation
- Database table automatically created: `wp_custom_subscribers`

---

## 🎯 First Steps

### 1. Check the Admin Interface
- Click **Subscribers** in the admin menu
- You'll see the dashboard with stats (currently all zeros)
- This is where you'll manage your subscribers

### 2. Add the Subscription Form
Edit any page or post and add this shortcode:
```
[wsm_subscribe_form]
```

**Shortcode Options:**
```
[wsm_subscribe_form title="Join Our Newsletter"]
[wsm_subscribe_form hide_for_subscribers="no"]
```

### 3. Test the Form
1. Visit the page with the form
2. Enter a test name and email
3. Submit and check your email inbox
4. Click the confirmation link
5. Go to **WordPress Admin → Subscribers** to see your test subscriber

---

## 📦 What's Included in the Zip

```
wp-subscriber-manager/
├── wp-subscriber-manager.php    (Main plugin file)
├── README.md                    (Full documentation)
├── INSTALL.md                   (Detailed setup guide)
├── CHANGELOG.md                 (Version history)
└── sample-subscribers.csv       (CSV import template)
```

---

## 🔧 Configuration

### Email Settings
The plugin uses WordPress's built-in email system. For better deliverability:
- Install an SMTP plugin (WP Mail SMTP, Post SMTP, etc.)
- Configure your SMTP settings
- Test email sending

### Cookie Settings
Cookies are automatically set when users confirm their subscription:
- **Name:** `wsm_subscribed`
- **Duration:** 10 years
- **Purpose:** Hide form for subscribed users

---

## 📋 Quick Feature Overview

✅ **Admin Interface**
- View all subscribers in a table
- See confirmed vs pending counts
- Delete individual or multiple subscribers
- Export subscribers to CSV
- Import subscribers from CSV

✅ **Front-end**
- Double opt-in confirmation
- Cookie prevents re-subscription
- Duplicate email prevention
- Professional styled messages
- Mobile responsive

---

## 🆘 Troubleshooting

**Plugin won't activate:**
- Check PHP version (requires 7.0+)
- Verify file permissions
- Check for plugin conflicts

**Can't see admin menu:**
- Only administrators can access it
- Try clearing browser cache
- Deactivate and reactivate plugin

**Emails not sending:**
- Check WordPress email settings
- Install an SMTP plugin
- Check spam folder
- Verify server can send emails

**Form not displaying:**
- Make sure you used the shortcode correctly
- Check if there are any JavaScript errors
- Verify theme compatibility

---

## 📚 Next Steps

1. Read the full **README.md** for detailed documentation
2. Review **CHANGELOG.md** to see what's new in v3.0
3. Use **sample-subscribers.csv** as a template for imports
4. Customize the form title and styling as needed

---

## 🔄 Updating from Previous Version

If you have the old version (2.0) installed:

1. **Backup your database** (the `wp_custom_subscribers` table)
2. **Deactivate** the old plugin
3. **Delete** the old plugin files
4. **Install** this new version (v3.0)
5. **Activate** the new plugin

**Note:** Your subscriber data will be preserved as it's stored in the database!

---

## ✨ What's New in v3.0

- Complete admin interface with subscriber management
- CSV export functionality
- CSV import functionality
- Cookie-based form hiding
- Bulk delete actions
- Statistics dashboard
- Improved security
- Better duplicate handling
- Enhanced user experience

---

## 📞 Support

For help:
1. Check the README.md file
2. Review the troubleshooting section
3. Check WordPress error logs
4. Verify all system requirements

---

**Enjoy your new subscriber management system!**

Version: 3.0  
Author: jkurzner  
Website: https://mindshaft.com
