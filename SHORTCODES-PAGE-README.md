# Shortcodes Reference Page

A beautiful, interactive admin page that displays all available plugin shortcodes with copy-to-clipboard functionality.

## Location

**WordPress Admin → Kura-ai Booking → Shortcodes**

## Features

### 🎨 Beautiful Design
- **Modern Card Layout** - Each shortcode displayed in an elegant card with gradient headers
- **Color-Coded Categories** - Different colors for booking, reviews, waitlist, and display shortcodes
- **Responsive Grid** - Adapts to all screen sizes from desktop to mobile
- **Smooth Animations** - Cards animate on scroll and hover with subtle transitions

### 🔍 Smart Search
- **Real-time Filtering** - Search by shortcode name, description, or parameters
- **Keyboard Shortcut** - Press `Ctrl/⌘ + K` to quickly focus the search
- **Highlight Matches** - Search terms are highlighted in yellow for easy scanning
- **Press Enter** - Auto-scroll to first matching result

### 📋 One-Click Copy
- **Copy to Clipboard** - Click any "Copy" button to instantly copy the shortcode
- **Visual Feedback** - Button turns green with checkmark on successful copy
- **Toast Notification** - Success message appears in bottom-right corner
- **No Manual Selection** - No need to highlight and copy manually

### 📖 Comprehensive Documentation
Each shortcode card includes:
- **Clear Description** - Explains what the shortcode does
- **Code Example** - Shows the shortcode with common parameters
- **Parameter List** - Details all available parameters with defaults
- **Requirements** - Indicates if login is required or parameters are mandatory
- **Status Badge** - Shows if shortcode is "Core" or "New" feature

### 💡 Tips & Best Practices
Bottom section provides:
- Dedicated page recommendations
- Mobile responsiveness info
- Styling customization notes
- Performance optimization tips

## Available Shortcodes

### Core Shortcodes
1. **[kab_booking_form]** - Main booking form
2. **[kab_services]** - Service list/grid
3. **[kab_events_calendar]** - Event calendar
4. **[kab_my_bookings]** - User dashboard (requires login)

### New Shortcodes (v1.1.0)
1. **[kab_waitlist_form]** - Customer waitlist signup
2. **[kab_review_form]** - Submit reviews and ratings
3. **[kab_reviews_display]** - Display reviews with stats
4. **[kab_cancel_booking]** - Self-service cancellation (requires login)

## Design Elements

### Card Headers
- **Gradient Backgrounds** - Each category has unique gradient
- **Icon Integration** - Dashicons for visual identification
- **Status Badges** - "Core" or "New" badges with animations
- **Bookmark-Style** - Professional card design

### Interactive Elements
- **Hover Effects** - Cards lift on hover with shadow
- **Button Animations** - Buttons scale and change color
- **Smooth Transitions** - All animations use cubic-bezier easing
- **Focus States** - Accessible keyboard navigation

### Color Scheme
Uses your plugin's brand colors from Setup Wizard:
- **Primary Color** - Used for main elements and buttons
- **Secondary Color** - Used for accents and highlights
- **System Colors** - Additional colors for different categories

### Typography
- **Headings** - Bold, large headings for clarity
- **Code Blocks** - Monospace font (Monaco/Menlo) for shortcodes
- **Body Text** - Clean, readable sans-serif
- **Icon Font** - WordPress Dashicons

## Technical Details

### Files Created
1. **includes/admin/class-kab-shortcodes-page.php** - Page controller
2. **assets/css/shortcodes-page.css** - Page styling (3.5KB)
3. **assets/js/shortcodes-page.js** - Interactive features (2.8KB)

### Dependencies
- WordPress Admin UI
- jQuery (bundled with WordPress)
- Dashicons (bundled with WordPress)

### Browser Support
- Chrome/Edge (latest)
- Firefox (latest)
- Safari (latest)
- Mobile browsers (iOS Safari, Chrome Mobile)

### Performance
- **Lightweight** - Styles and scripts load only on this page
- **Optimized** - No external dependencies
- **Fast Search** - Client-side filtering with no server requests
- **Lazy Assets** - Assets enqueued only when needed

## Usage Examples

### For Administrators
1. Navigate to **Kura-ai Booking → Shortcodes**
2. Browse available shortcodes or use search
3. Click "Copy" button on desired shortcode
4. Paste into WordPress page/post editor
5. Customize parameters as needed

### For Developers
```php
// Access the page programmatically
do_action('admin_menu'); // Triggers page registration

// Filter shortcode list
add_filter('kab_shortcodes_list', function($shortcodes) {
    // Add custom shortcode to the list
    $shortcodes[] = array(
        'name' => 'My Custom Shortcode',
        'code' => '[my_shortcode]',
        'description' => 'Description here',
    );
    return $shortcodes;
});
```

## Accessibility Features

- **Keyboard Navigation** - Full keyboard support
- **Focus Indicators** - Visible focus states
- **ARIA Labels** - Proper labeling for screen readers
- **Color Contrast** - WCAG AA compliant contrast ratios
- **Semantic HTML** - Proper heading hierarchy

## Mobile Experience

### Responsive Breakpoints
- **Desktop** (1024px+) - 3-column grid
- **Tablet** (768px-1023px) - 2-column grid
- **Mobile** (<768px) - Single column stack

### Mobile Optimizations
- Larger touch targets for buttons
- Simplified layout for small screens
- Full-width search bar
- Scrollable parameter lists
- Toast notifications adjust to screen size

## Print Friendly

Page is optimized for printing:
- Search bar hidden
- Copy buttons hidden
- Cards stack vertically
- Page breaks avoid splitting cards
- Clean, ink-friendly layout

## Future Enhancements

Potential future additions:
- [ ] Export shortcodes as PDF
- [ ] Favorite/bookmark shortcodes
- [ ] Filter by category badges
- [ ] Live preview of shortcodes
- [ ] Direct page insertion (create page with shortcode)
- [ ] Video tutorials for each shortcode
- [ ] Code syntax highlighting
- [ ] Dark mode support

## Screenshots

### Desktop View
```
┌────────────────────────────────────────────────────────────┐
│  📋 Available Shortcodes                                   │
│  Copy and paste these shortcodes into your pages...        │
│                                                             │
│  ┌──────────────────────────────────────────────┐         │
│  │  🔍 Search shortcodes... (Ctrl/⌘ + K)        │         │
│  └──────────────────────────────────────────────┘         │
│                                                             │
│  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐       │
│  │ Booking     │  │ Waitlist    │  │ Review      │       │
│  │ Form        │  │ Form        │  │ Form        │       │
│  │             │  │             │  │             │       │
│  │ [Copy]      │  │ [Copy]      │  │ [Copy]      │       │
│  └─────────────┘  └─────────────┘  └─────────────┘       │
│                                                             │
│  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐       │
│  │ Reviews     │  │ Cancel      │  │ Service     │       │
│  │ Display     │  │ Booking     │  │ List        │       │
│  │             │  │             │  │             │       │
│  │ [Copy]      │  │ [Copy]      │  │ [Copy]      │       │
│  └─────────────┘  └─────────────┘  └─────────────┘       │
└────────────────────────────────────────────────────────────┘
```

### Card Detail
```
┌─────────────────────────────────────────────────┐
│ 📅 Waitlist Form                          NEW   │ ← Gradient Header
├─────────────────────────────────────────────────┤
│ Allow customers to join a waitlist when a       │
│ service or event is at full capacity.           │
│                                                  │
│ ┌───────────────────────────────────────┐       │
│ │ [kab_waitlist_form item_type="s...    │ Copy │ ← One-Click Copy
│ └───────────────────────────────────────┘       │
│                                                  │
│ Parameters:                                      │
│ • item_type - "service" or "event" *required    │
│ • item_id - ID of the service/event *required   │
└─────────────────────────────────────────────────┘
```

## Support

For questions or issues:
- Check the [Features Guide](FEATURES-GUIDE.md)
- Visit WordPress admin help sections
- Review shortcode documentation

---

**Built with ❤️ for Kura-ai Booking System v1.1.0**
