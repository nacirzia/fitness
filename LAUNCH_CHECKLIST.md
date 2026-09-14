# ✅ UPTOWN FITNESS - INSTALLATION & LAUNCH CHECKLIST

## Pre-Installation (Before Starting)

- [ ] Verify XAMPP is installed and configured
- [ ] MySQL/MariaDB is installed and accessible
- [ ] phpMyAdmin is accessible at http://localhost/phpmyadmin/
- [ ] PHP version is 7.4 or higher
- [ ] .htaccess support is enabled (mod_rewrite)

---

## Installation Phase 1: Database Setup

- [ ] Create MySQL database named "uptownfitness"
- [ ] Verify database was created successfully
- [ ] Check database is empty and ready for WordPress

**SQL Command:**
```sql
CREATE DATABASE uptownfitness;
```

---

## Installation Phase 2: WordPress Setup

- [ ] Navigate to http://localhost/uptownfitness/
- [ ] WordPress installer loads (should see setup page)
- [ ] Complete WordPress installation:
  - [ ] Site Title: "Uptown Fitness"
  - [ ] Tagline: "Transform Your Body, Transform Your Life"
  - [ ] Admin Username: (create custom username)
  - [ ] Admin Email: (your email)
  - [ ] Admin Password: (strong password)
  - [ ] Finish installation

---

## Installation Phase 3: Theme Activation

- [ ] Log in to WordPress Dashboard (http://localhost/uptownfitness/wp-admin/)
- [ ] Go to Appearance → Themes
- [ ] Locate "Uptown Fitness" theme
- [ ] Click Activate
- [ ] Verify theme is active (check homepage)
- [ ] Homepage displays with hero section

---

## Content Creation Phase 1: Menu Setup

- [ ] Go to Appearance → Menus
- [ ] Create new menu "Main Navigation"
- [ ] Add menu items:
  - [ ] Home (homepage)
  - [ ] About (create page first)
  - [ ] Services (create page first)
  - [ ] Trainers (create page first)
  - [ ] Pricing (create page first)
  - [ ] Contact (create page first)
  - [ ] Blog (create page first)
- [ ] Assign menu to "Primary Menu" location
- [ ] Save and verify menu appears on site

---

## Content Creation Phase 2: Core Pages

### About Us Page
- [ ] Create page titled "About Us"
- [ ] Slug: /about
- [ ] Add content (use CONTENT_TEMPLATE.md as reference)
- [ ] Add featured image (optional)
- [ ] Publish

### Services Page
- [ ] Create page titled "Our Services"
- [ ] Slug: /services
- [ ] Add intro content
- [ ] Publish

### Service Posts (Create 3+)
For each service:
- [ ] Go to Posts → Services (or Services in menu)
- [ ] Create new service post
- [ ] Title: (Service name)
- [ ] Add description (300+ words)
- [ ] Add featured image
- [ ] Publish
- [ ] Services: Personal Training, Group Classes, Nutrition

### Trainers Page
- [ ] Create page titled "Meet Our Team"
- [ ] Slug: /trainers
- [ ] Add intro content
- [ ] Publish

### Trainer Posts (Create 2+)
For each trainer:
- [ ] Go to Posts → Trainers (or Trainers in menu)
- [ ] Create new trainer post
- [ ] Name: (Trainer name)
- [ ] Add bio/credentials (200+ words)
- [ ] Add professional image
- [ ] Publish

### Pricing Page
- [ ] Create page titled "Membership Plans"
- [ ] Slug: /pricing
- [ ] Add pricing tiers and details
- [ ] Add CTA button
- [ ] Publish

### Contact Page
- [ ] Create page titled "Get In Touch"
- [ ] Slug: /contact
- [ ] Add contact info (phone, email, address, hours)
- [ ] Install Contact Form 7 plugin
- [ ] Create contact form in Contact Form 7
- [ ] Add form shortcode to page
- [ ] Test form submission
- [ ] Publish

### Blog (Optional - Create 3+)
- [ ] Go to Posts → Add New
- [ ] Create blog post: "Getting Started at the Gym"
- [ ] Add content (800+ words)
- [ ] Add featured image
- [ ] Publish
- [ ] Repeat for 2-3 more posts

---

## Content Creation Phase 3: Homepage Configuration

- [ ] Go to Settings → Reading
- [ ] Set "Your homepage displays" to "A static page"
- [ ] Set "Homepage:" to your Home page
- [ ] Set "Posts page:" to your Blog page
- [ ] Save changes
- [ ] Verify homepage loads with hero and features

---

## Media & Images Phase

- [ ] Download free images from:
  - [ ] Unsplash (unsplash.com)
  - [ ] Pexels (pexels.com)
  - [ ] Pixabay (pixabay.com)
  
- [ ] For each page/post:
  - [ ] Download appropriate image
  - [ ] Optimize image (TinyPNG.com or similar)
  - [ ] Upload as featured image
  - [ ] Add alt text
  - [ ] Save

---

## Customization Phase 1: Basic Setup

- [ ] Go to Appearance → Customize
- [ ] Site Identity:
  - [ ] Upload site logo
  - [ ] Set site title
  - [ ] Set tagline
  - [ ] Upload site icon (favicon)
  - [ ] Save changes

- [ ] Colors (optional advanced):
  - [ ] Edit style.css to change primary/secondary colors
  - [ ] Edit footer.php to update social links

---

## Customization Phase 2: Footer Configuration

- [ ] Edit footer.php to update:
  - [ ] Phone number
  - [ ] Email address
  - [ ] Physical address
  - [ ] Business hours
  - [ ] Social media links
  
- [ ] Test all footer links work correctly

---

## Plugin Installation Phase

### Contact Form 7 (if not using pre-built form)
- [ ] Go to Plugins → Add New
- [ ] Search "Contact Form 7"
- [ ] Install and Activate
- [ ] Create contact form
- [ ] Add shortcode to Contact page

### Yoast SEO
- [ ] Go to Plugins → Add New
- [ ] Search "Yoast SEO"
- [ ] Install and Activate
- [ ] Run setup wizard
- [ ] For each page:
  - [ ] Set focus keyword
  - [ ] Optimize title
  - [ ] Optimize meta description

### WP Super Cache
- [ ] Go to Plugins → Add New
- [ ] Search "WP Super Cache"
- [ ] Install and Activate
- [ ] Go to Settings → WP Super Cache
- [ ] Enable caching
- [ ] Test site loads faster

### Jetpack (Optional - for backup)
- [ ] Go to Plugins → Add New
- [ ] Search "Jetpack"
- [ ] Install and Activate
- [ ] Connect WordPress.com account
- [ ] Enable backups

---

## Testing Phase

### Functionality Testing
- [ ] Homepage loads correctly
- [ ] All navigation links work
- [ ] All pages display properly
- [ ] Contact form submits successfully
- [ ] Email form submissions work
- [ ] Images load on all pages
- [ ] Footer links are active

### Mobile Testing
- [ ] Site displays correctly on mobile
- [ ] Navigation hamburger menu works (if applicable)
- [ ] Buttons are clickable/sized properly
- [ ] Images are responsive
- [ ] Text is readable
- [ ] Forms work on mobile

### Cross-Browser Testing
- [ ] Chrome
- [ ] Firefox
- [ ] Safari
- [ ] Edge

### Performance Testing
- [ ] Site loads under 3 seconds
- [ ] Images are optimized
- [ ] Caching is working
- [ ] No console errors

---

## SEO & Analytics Phase

### Google Search Console
- [ ] Go to search.google.com/search-console
- [ ] Add property
- [ ] Verify site ownership
- [ ] Submit sitemap
- [ ] Monitor search performance

### Google Analytics
- [ ] Go to analytics.google.com
- [ ] Create new property
- [ ] Get tracking ID
- [ ] Install MonsterInsights plugin
- [ ] Add tracking ID
- [ ] Verify tracking is working

### Search Engine Submissions
- [ ] Submit sitemap to Google
- [ ] Submit sitemap to Bing
- [ ] Submit to relevant directories

---

## Security Phase

- [ ] Update WordPress to latest version
- [ ] Update all plugins to latest versions
- [ ] Change default admin username (create new, delete old)
- [ ] Set strong admin password (20+ chars, mixed case, symbols)
- [ ] Install iThemes Security plugin
- [ ] Enable two-factor authentication
- [ ] Setup regular backups (daily)
- [ ] Disable file editing (add to wp-config.php):
  ```php
  define('DISALLOW_FILE_EDIT', true);
  ```

---

## Backup Phase

- [ ] Install UpdraftPlus or Jetpack
- [ ] Setup automated daily backups
- [ ] Configure backup retention (30+ days)
- [ ] Test backup restoration
- [ ] Store backup location info securely

---

## Final QA Phase

### Content Quality Check
- [ ] All pages have meaningful content (200+ words minimum)
- [ ] All spelling and grammar is correct
- [ ] Contact info is accurate and current
- [ ] Hours of operation are correct
- [ ] All links are working

### Technical Check
- [ ] All images display correctly
- [ ] No broken links
- [ ] No console errors
- [ ] Site loads in under 3 seconds
- [ ] Mobile layout is perfect
- [ ] Form validation works

### Visual Check
- [ ] Colors look professional
- [ ] Typography is readable
- [ ] Spacing and alignment are correct
- [ ] Buttons are properly styled
- [ ] Navigation is intuitive

---

## Deployment/Launch Preparation

If going to production hosting:

- [ ] Purchase domain name
- [ ] Purchase hosting account
- [ ] Configure DNS records
- [ ] SSL certificate installed (HTTPS)
- [ ] Database backup created
- [ ] All files backed up
- [ ] Staging site tested
- [ ] Ready for migration

---

## Post-Launch Phase

### First Week
- [ ] Monitor site for errors
- [ ] Check analytics for traffic
- [ ] Respond to any contact form submissions
- [ ] Monitor backup completion
- [ ] Check server performance

### First Month
- [ ] Create and publish 2-3 blog posts
- [ ] Promote on social media
- [ ] Monitor analytics
- [ ] Optimize underperforming pages
- [ ] Gather feedback

### Ongoing Maintenance
- [ ] Weekly: Monitor for errors and performance
- [ ] Bi-weekly: Check for plugin updates
- [ ] Monthly: Backup verification
- [ ] Quarterly: Content audit and updates
- [ ] Annually: Security audit

---

## Troubleshooting Checklist

If site is not loading:
- [ ] Check database connection (wp-config.php)
- [ ] Verify database name, user, password
- [ ] Check file permissions (755 for folders, 644 for files)
- [ ] Review error logs
- [ ] Check server error log

If theme not showing:
- [ ] Clear cache (Settings → WP Super Cache → Delete Cache)
- [ ] Verify theme is activated
- [ ] Check for console errors (F12)
- [ ] Verify style.css is loaded
- [ ] Check theme functions.php for errors

If forms not working:
- [ ] Verify Contact Form 7 plugin is installed
- [ ] Verify form shortcode is on page
- [ ] Test SMTP configuration
- [ ] Check spam folder for test emails
- [ ] Verify contact form settings

If images not loading:
- [ ] Check file permissions
- [ ] Verify image URLs are correct
- [ ] Clear cache
- [ ] Re-upload images
- [ ] Check server disk space

---

## Success Indicators ✅

Your site is successful when:
- ✅ WordPress dashboard loads without errors
- ✅ Theme displays correctly on all pages
- ✅ All navigation and links work
- ✅ Images display properly
- ✅ Forms submit successfully
- ✅ Site is mobile responsive
- ✅ Page load time under 3 seconds
- ✅ SEO is configured
- ✅ Analytics is tracking
- ✅ Backup system is working

---

## Final Notes

**Total Setup Time**: 4-6 hours
**Content Creation Time**: 4-8 hours
**Total Time to Launch**: 1-2 days

**Remember**:
- Always backup before making changes
- Test in staging before updating
- Update plugins regularly
- Monitor performance
- Engage with your community

---

**Status**: Ready for Launch
**Last Updated**: September 13, 2026
**Version**: 1.0

**Good luck with your Uptown Fitness website! 💪**
