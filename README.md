# PixelForge White Hart Theme

PixelForge White Hart is a custom WordPress theme built for pubs that need simple content publishing plus a full booking workflow. The theme disables the block editor in favor of classic editing, registers preset pages (Home, About, News, Contact), and ships with booking tools, events, SEO defaults, and seasonal styling toggles.

## Requirements
- WordPress with PHP 8.1+.
- CMB2 plugin (for theme options and metaboxes).
- Brevo API key if you want booking confirmation emails.

## Installation & Activation
1. Upload the theme folder to your WordPress `/wp-content/themes` directory or install the packaged ZIP via **Appearance → Themes → Add New**.
2. Activate the theme. On activation, the theme creates the Home, About Us, News (blog), and Contact pages and assigns the Home page as the front page. It also seeds a “mastnav” menu and maps it to the Masthead location.
3. In **Settings → Reading**, confirm “Your homepage displays” is set to the Home page if you changed it later.
4. Install and activate the CMB2 plugin so the theme’s option pages and metaboxes appear.

## Theme Options (Appearance → PixelForge Options)
- **Business Logo / Email / Telephone / Address**: Stored once and reused across templates.
- **Homepage Seasonal Theme**: Switch to “Christmas” to add festive styling to the homepage; leave “Off” for the default look.
- **Enable Table Bookings**: Uncheck to hide the booking form and availability checks on the front end.
- **Booking Data Tools**: “Delete all booking data” wipes booking records, tables, sections, and menus.
- **Brevo Email**: Add your Brevo transactional API key plus sender email/name to send confirmation emails to guests.
- **SEO Defaults**: Site-wide meta description/keywords plus Open Graph title/description/image and Twitter username used when pages don’t define their own values.

## Content & Navigation
- **Pages**: Edit the auto-created Home, About Us, News, and Contact pages under **Pages**. Use classic editor content plus featured images as needed.
- **Menus**: Manage the “Masthead Navigation” menu under **Appearance → Menus** and assign it to the Masthead location. A Primary Navigation location is also available for secondary links.
- **Blog/News**: Publish standard Posts under **Posts**; the News page is set as the posts index.
- **Events**: Use the **Events** custom post type for promotions or live acts. Add a featured image, excerpt, and body content; categories/tags are available.

## Booking System
The booking workflow is built from four custom post types:
- **Table Sections** (`Booking Section`): Create areas such as Bar, Garden, or Function Room.
- **Tables** (`Booking Table`): Assign each table to a section, set seat count, and add internal notes.
- **Booking Menus**: Define bookable menus (e.g., Sunday Roast) with start/end times and available days, plus optional imagery and descriptions.
- **Table Bookings**: Customer reservations saved privately; viewable in WP Admin and via the Booking Admin page template.

### Front-End Booking & Availability
- Add the booking form to any page or template with the `[pixelforge_table_booking]` shortcode. Guests choose a menu, date/time, section, and party size; availability checks run via AJAX before saving.
- Use `[pixelforge_booking_menus]` to output a slider of menus showing their available days and time window.
- If bookings are disabled in Theme Options, the form is replaced with a notice that bookings are unavailable.

### Booking Admin Panel (Front-End)
- Create a new page and assign the **Booking Admin Panel** template. Staff with `edit_posts` capability can sign in to add, list, and calendar-view bookings from the front end. Non-staff see a login form.
- Actions include creating a booking (guest details, party size, menu, section, tables, date/time, notes), editing or deleting existing bookings, and viewing the calendar tab for quick date navigation.
- Customers receive confirmation links; staff can also log out directly from the panel header.

### Booking Notifications & Data
- Confirmation emails use your Brevo credentials. The sender defaults to site name if no custom sender is set.
- Bookings and availability rely on a 90-minute slot length. Slots are generated per menu using each menu’s start/end time and allowed days.
- Table bookings are stored as private posts; details (customer info, menu/section/table choices, time, notes) are view-only in the editor to preserve data integrity.

## Managing SEO & Performance
- The theme injects classic-editor styles and loads Vite-built assets. Avoid editing files in `public/build`; run `npm run build` if you modify assets.
- SEO defaults from Theme Options populate meta tags and Open Graph data. Individual pages can override these values via their SEO metabox.

## Typical Pub Manager Workflow
1. **Initial setup**: Activate the theme, install CMB2, and fill out PixelForge Options (logo, contact info, booking toggle, Brevo details, SEO defaults).
2. **Set up booking data**: Create Sections, Tables, and Booking Menus with times/days. Add a Booking Admin Panel page for staff.
3. **Publish content**: Edit Home/About/Contact, publish blog posts to News, and add Events for promotions.
4. **Embed booking form**: Add `[pixelforge_table_booking]` to the Contact page (or another page) and publish; optionally add `[pixelforge_booking_menus]` near it.
5. **Operate bookings**: Staff use the Booking Admin Panel to add/update bookings and check the calendar; customers submit via the public form and receive Brevo-powered confirmations.
6. **Maintenance**: Temporarily disable bookings from Theme Options if capacity is limited, or use the delete tool before re-opening with fresh data.
