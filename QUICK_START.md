# Uptown Fitness - Quick Start Guide

## 🚀 Getting Started in 5 Steps

### Step 1: Access WordPress
Navigate to: `http://localhost/uptownfitness/wp-admin/`

### Step 2: Complete Installation (First Time Only)
Follow the WordPress setup wizard if not already installed.

### Step 3: Activate Theme
- Go to **Appearance → Themes**
- Activate **Uptown Fitness**

### Step 4: Create Menu
1. Go to **Appearance → Menus**
2. Create menu named "Main Navigation"
3. Add pages:
   - Home
   - About
   - Services
   - Trainers
   - Pricing
   - Contact
   - Blog
4. Set to display in "Primary Menu" location

### Step 5: Publish Pages
Create pages for each menu item using the Content Template provided.

---

## 📋 Content Checklist

- [ ] Homepage (auto-generated from front-page.php)
- [ ] About Us page
- [ ] Services (3+ service posts)
- [ ] Trainer profiles (2+ trainers)
- [ ] Pricing page
- [ ] Contact page (with Contact Form 7)
- [ ] Blog (initial 3-4 posts)
- [ ] Navigation menu created and assigned
- [ ] Site logo added
- [ ] Tagline set in Settings
- [ ] Homepage & blog page set in Reading settings

---

## 🎨 Customization Quick Links

**Colors**: Edit `/wp-content/themes/uptown-fitness/style.css`
- Lines 7-14: Change color variables

**Fonts**: Edit `/wp-content/themes/uptown-fitness/functions.php`
- Update Google Fonts import for different typefaces

**Logo**: 
- Go to Appearance → Customize → Site Identity → Upload Logo

**Social Links**: Edit `/wp-content/themes/uptown-fitness/footer.php`
- Lines 30-36: Update social media URLs

---

## 📸 Image Guidelines

**Hero Image**: 1920×600px (Homepage)
- Suggested: Fitness equipment or gym interior
- Source: Unsplash, Pexels, Pixabay

**Service Images**: 400×300px
- Suggested: Different workout activities
- Source: Free stock photo sites

**Trainer Images**: 300×300px
- Suggested: Professional headshots or action shots
- Source: Free stock photo sites

**Blog Images**: 800×400px minimum
- Suggested: Related to article topic
- Source: Free stock photo sites

**Recommended Free Image Sites**:
- unsplash.com
- pexels.com
- pixabay.com
- burst.shopify.com
- unsplash.com/napi/photos/search/fitness

---

## 🔧 Installing Plugins

1. Go to **Plugins → Add New**
2. Search for plugin name
3. Click **Install Now** → **Activate**

### Essential Plugins

**Contact Form 7**
- For contact forms

**Yoast SEO**
- For search engine optimization

**WP Super Cache**
- For performance

**Jetpack**
- For backups and security

---

## 📱 Testing Responsive Design

- Desktop: Full width
- Tablet: 768px width
- Mobile: 480px width

Test using:
- Chrome DevTools (F12)
- Actual devices
- Browser extensions: Responsive Viewer

---

## 🔐 Initial Security Setup

1. Change admin username:
   - Create new admin user
   - Delete "admin" user

2. Set strong password:
   - Users → Your Profile → Generate Password

3. Install iThemes Security plugin

4. Enable two-factor authentication

5. Regular backups:
   - Use Jetpack or UpdraftPlus

---

## 📊 Analytics Setup

1. Create Google Analytics account
2. Get tracking ID
3. Install MonsterInsights plugin
4. Add tracking ID in plugin settings
5. View reports in Dashboard → Analytics

---

## 🚀 Going Live (Production)

1. **Backup Everything**
   - Database
   - All files
   - Media library

2. **Security Audit**
   - Update WordPress
   - Update all plugins
   - Remove unused plugins
   - Set proper permissions

3. **Performance Check**
   - Test page speed (GTmetrix)
   - Optimize images
   - Enable caching

4. **SEO Verification**
   - Submit sitemap to Google Search Console
   - Add to Google Analytics
   - Verify structured data

5. **Testing**
   - Test all forms
   - Check links
   - Verify email delivery
   - Test on multiple devices

---

## 🆘 Common Issues & Solutions

### Theme Not Showing
- Clear cache: Settings → WP Super Cache → Delete Cache
- Reload page: Ctrl+Shift+R (or Cmd+Shift+R on Mac)

### Forms Not Working
- Install Contact Form 7
- Create a form and add shortcode to page

### Images Not Loading
- Check file permissions
- Verify correct image URLs
- Reupload images

### Site Slow
- Enable caching plugin
- Optimize images
- Check server load
- Install WP Super Cache

### Emails Not Sending
- Test SMTP configuration
- Check spam folder
- Install WP Mail SMTP plugin
- Verify email settings

---

## 📚 Useful Resources

**WordPress Official Docs**: wordpress.org/support/
**Theme Development**: developer.wordpress.org/
**Plugin Directory**: wordpress.org/plugins/
**Codex Reference**: codex.wordpress.org/

---

## 💡 Pro Tips

1. **Backup regularly** - Use scheduled backups
2. **Update immediately** - Keep WordPress and plugins current
3. **Monitor performance** - Check site speed monthly
4. **Review analytics** - Track user behavior
5. **Engage community** - Respond to comments and inquiries
6. **Create content regularly** - Post blog articles weekly
7. **Test before publishing** - Always preview changes
8. **Use search console** - Monitor search rankings

---

**Need Help?** 
- Check SETUP_GUIDE.md for detailed documentation
- Review CONTENT_TEMPLATE.md for content examples
- Visit wordpress.org/support for community help

---

Version: 1.0
Last Updated: September 2026
