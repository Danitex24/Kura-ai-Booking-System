# Kura-ai Booking System - New Features Guide (v1.1.0)

This document provides a comprehensive guide to all the new features added to the plugin.

---

## Table of Contents

1. [Recurring Events](#recurring-events)
2. [Email Reminders](#email-reminders)
3. [Waitlist Management](#waitlist-management)
4. [Cancellations & Refunds](#cancellations--refunds)
5. [Reviews & Ratings](#reviews--ratings)
6. [Event Categories](#event-categories)
7. [Frontend Shortcodes](#frontend-shortcodes)

---

## Recurring Events

### Admin Interface
**Location:** WordPress Admin → Kura-ai Booking → Recurring Events

### Features
- Create recurring event patterns (daily, weekly, monthly)
- Set custom intervals (e.g., every 2 weeks)
- Specify end date or maximum occurrences
- Weekly patterns: Select specific days of the week
- Monthly patterns: Set specific day of the month
- Automatic instance generation (up to 100 events)
- View and manage all recurring patterns

### Database Tables
- `kab_event_recurrence` - Stores recurrence patterns
- `kab_event_instances` - Stores generated event instances

### API Methods
```php
// Create recurring pattern
KAB_Recurring_Events::create_recurrence( $event_id, $recurrence_data );

// Generate instances
KAB_Recurring_Events::generate_instances( $recurrence_id );

// Delete pattern
KAB_Recurring_Events::delete_recurrence( $recurrence_id );
```

---

## Email Reminders

### Admin Interface
**Location:** WordPress Admin → Kura-ai Booking → Email Reminders

### Features
- Automated 24-hour reminder emails before bookings
- Hourly cron job processing
- Manual reminder sending
- View upcoming bookings without reminders
- Track sent, scheduled, and failed reminders
- Professional HTML email templates with brand colors

### Database Tables
- `kab_reminders` - Tracks all reminder emails

### Cron Jobs
- Hook: `kab_send_reminders`
- Frequency: Hourly
- Automatically scheduled on plugin activation

### API Methods
```php
// Schedule reminder for booking
KAB_Reminders::schedule_reminder( $booking_id );

// Send manual reminder
KAB_Reminders::send_manual_reminder( $booking_id );

// Cancel reminder
KAB_Reminders::cancel_reminder( $booking_id );
```

---

## Waitlist Management

### Admin Interface
**Location:** WordPress Admin → Kura-ai Booking → Waitlist

### Features
- Automatic capacity checking for services and events
- Priority-based queue (first-come, first-served)
- Automatic notifications when spots become available
- Manual notification triggering
- Position tracking in waitlist
- Date-specific or flexible waitlist entries

### Database Tables
- `kab_waitlist` - Stores waitlist entries with priority

### Frontend Shortcode
```
[kab_waitlist_form item_type="service" item_id="1"]
```

### API Methods
```php
// Check if at capacity
KAB_Waitlist::is_at_capacity( $type, $item_id, $date );

// Add to waitlist
KAB_Waitlist::add_to_waitlist( $data );

// Notify next person
KAB_Waitlist::notify_next_in_line( $type, $item_id, $date );
```

---

## Cancellations & Refunds

### Admin Interface
**Location:** WordPress Admin → Kura-ai Booking → Cancellations

### Features
- Customer cancellation requests
- Time-based refund policy:
  - More than 48 hours: 100% refund
  - 24-48 hours: 50% refund
  - Less than 24 hours: No refund
- Automatic waitlist notification on cancellation
- Refund tracking and processing
- Cancellation fee calculation

### Database Tables
- `kab_cancellations` - Stores cancellation requests and refund details

### Frontend Shortcode
```
[kab_cancel_booking]
```
*Note: User must be logged in*

### API Methods
```php
// Request cancellation
KAB_Cancellations::request_cancellation( $booking_id, $data );

// Process refund
KAB_Cancellations::process_refund( $cancellation_id );
```

---

## Reviews & Ratings

### Admin Interface
**Location:** WordPress Admin → Kura-ai Booking → Reviews

### Features
- 1-5 star rating system
- Review moderation (approved, pending, rejected)
- Automatic average rating calculation
- Star rating distribution visualization
- Review title and detailed comments
- One review per booking enforcement

### Database Tables
- `kab_reviews` - Stores customer reviews
- Enhanced `kab_services` and `kab_events` tables with avg_rating and review_count

### Frontend Shortcodes

**Submit Review:**
```
[kab_review_form booking_id="123"]
```

**Display Reviews:**
```
[kab_reviews_display item_type="service" item_id="1" limit="10"]
```

### API Methods
```php
// Submit review
KAB_Reviews::submit_review( $data );

// Moderate review
KAB_Reviews::moderate_review( $review_id, $status );

// Get rating stats
KAB_Reviews::get_rating_stats( $type, $item_id );

// Render stars HTML
KAB_Reviews::render_stars( $rating, $show_count, $count );
```

---

## Event Categories

### Admin Interface
**Location:** WordPress Admin → Kura-ai Booking → Event Categories

### Features
- Create and manage event categories
- Custom icons (Dashicons)
- Color coding for categories
- Many-to-many relationship with events
- Slug-based URLs
- Category descriptions

### Database Tables
- `kab_event_categories` - Category definitions
- `kab_event_category_relations` - Event-category relationships

---

## Frontend Shortcodes

### Available Shortcodes

#### 1. Waitlist Signup Form
```
[kab_waitlist_form item_type="service" item_id="1"]
```

**Parameters:**
- `item_type`: "service" or "event"
- `item_id`: ID of the service/event

**Features:**
- Customer name, email, phone
- Optional preferred date
- AJAX form submission
- Success/error messaging

---

#### 2. Review Submission Form
```
[kab_review_form booking_id="123"]
```

**Parameters:**
- `booking_id`: ID of the booking to review

**Features:**
- Interactive star rating (1-5)
- Review title (optional)
- Review comment (required)
- Customer name and email
- AJAX submission
- Prevents duplicate reviews

---

#### 3. Reviews Display
```
[kab_reviews_display item_type="service" item_id="1" limit="10"]
```

**Parameters:**
- `item_type`: "service" or "event"
- `item_id`: ID of the service/event
- `limit`: Maximum number of reviews to display (default: 10)

**Features:**
- Average rating summary
- Star rating distribution graph
- Individual review cards
- Star ratings visualization
- Responsive design

---

#### 4. Cancellation Form
```
[kab_cancel_booking]
```

**Requirements:**
- User must be logged in

**Features:**
- Booking ID input
- Cancellation reason (optional)
- Displays cancellation policy
- AJAX submission
- Confirmation dialog

---

## Custom Styling

All frontend forms use the plugin's brand colors from setup wizard settings:
- Primary Color: Used for buttons, headers, accents
- Secondary Color: Used for borders, highlights

### CSS File
`assets/css/frontend-forms.css`

### JavaScript File
`assets/js/frontend-forms.js`

### Customization
You can override styles in your theme:
```css
.kab-form-container { /* Your custom styles */ }
.kab-btn-primary { /* Custom button styles */ }
.kab-review-card { /* Custom review card styles */ }
```

---

## Database Schema

### New Tables (v1.1.0)

1. **kab_event_recurrence** - Recurring event patterns
2. **kab_event_instances** - Generated event instances
3. **kab_reminders** - Email reminder tracking
4. **kab_waitlist** - Priority-based waitlist
5. **kab_cancellations** - Cancellation and refund records
6. **kab_reviews** - Customer reviews and ratings
7. **kab_event_categories** - Event categorization
8. **kab_event_category_relations** - Event-category links

### Enhanced Tables

- `kab_services`: Added capacity, avg_rating, review_count
- `kab_events`: Added featured, image_url, avg_rating, review_count

---

## Admin Menu Structure

```
Kura-ai Booking
├── Dashboard
├── Services
├── Events
├── Bookings
├── Employees
├── Settings
├── Setup Wizard
├── Recurring Events (NEW)
├── Email Reminders (NEW)
├── Waitlist (NEW)
├── Cancellations (NEW)
├── Reviews (NEW)
└── Event Categories (NEW)
```

---

## Developer Hooks

### Actions
- `kab_send_reminders` - Triggered hourly to process reminders
- `kab_booking_cancelled` - Triggered when booking is cancelled
- `kab_review_submitted` - Triggered when review is submitted

### Filters
- `kab_reminder_email_subject` - Customize reminder email subject
- `kab_waitlist_notification_email` - Customize waitlist notification
- `kab_cancellation_refund_amount` - Modify refund calculation

---

## Automation Features

### Automatic Processes

1. **Email Reminders**
   - Runs hourly via WordPress cron
   - Sends reminders 24 hours before bookings
   - Prevents duplicate sends

2. **Waitlist Notifications**
   - Automatically triggered on cancellations
   - Notifies highest priority person in queue
   - Sends professional HTML emails

3. **Rating Calculations**
   - Automatically updates average ratings
   - Updates review counts
   - Recalculates on review approval/rejection

4. **Instance Generation**
   - Generates up to 100 instances per recurring pattern
   - Respects end dates and occurrence limits
   - Handles weekly/monthly day restrictions

---

## Best Practices

### For Administrators

1. **Recurring Events**
   - Test with short patterns first
   - Review generated instances before going live
   - Use end dates to prevent excessive generation

2. **Email Reminders**
   - Test email delivery with a personal booking
   - Check spam folders if emails not received
   - Monitor failed reminders in admin panel

3. **Waitlist**
   - Notify customers promptly when spots available
   - Keep priority order fair (FIFO)
   - Remove expired waitlist entries regularly

4. **Reviews**
   - Moderate reviews promptly
   - Respond to negative reviews
   - Use reviews to improve services

### For Developers

1. **Custom Integration**
   - Use provided API methods
   - Hook into WordPress actions/filters
   - Respect database schema

2. **Frontend Customization**
   - Override CSS in theme
   - Use shortcode parameters
   - Maintain responsive design

3. **Email Templates**
   - Customize in class files
   - Use brand colors from settings
   - Test across email clients

---

## Troubleshooting

### Reminders Not Sending
- Check cron is running: `wp cron event list`
- Verify email settings in WordPress
- Check error logs for failed sends

### Waitlist Not Working
- Ensure capacity is set on services/events
- Check waitlist entries in admin panel
- Verify email notifications are enabled

### Reviews Not Appearing
- Check moderation status (must be "approved")
- Verify item_type and item_id are correct
- Ensure reviews exist in database

---

## Version History

### v1.1.0 (Current)
- Added Recurring Events system
- Added Email Reminders with cron
- Added Waitlist Management
- Added Cancellations & Refunds workflow
- Added Reviews & Ratings system
- Added Event Categories
- Added Frontend Forms and Shortcodes
- Enhanced database schema
- Added 6 new admin pages

---

## Support & Documentation

For additional support:
- Plugin documentation: [Your docs URL]
- WordPress support forums
- GitHub issues (if applicable)

---

**Note:** All features are fully integrated and ready to use. Database tables are created automatically on plugin activation.
