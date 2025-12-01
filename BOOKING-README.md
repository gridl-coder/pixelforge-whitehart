# Table Booking System

This theme ships with a reservation workflow built around custom post types and CMB2 fields. Use this guide to set up sections, tables, and menus, then expose the booking form on the front end.

## 1) Define bookable areas and tables
1. Go to **Table Sections** and add areas such as *Bar*, *Stage*, or *Function Room*. Optionally add internal notes.
2. Go to **Tables** and create a record for each table:
   - Choose its **Section**.
   - Enter the **Seats** (total seats available at that table).
   - Add **Table Notes** if staff need guidance when seating guests.

## 2) Add bookable menus and hours
1. Go to **Booking Menus** and add one entry per menu (e.g., *Breakfast Menu*, *Sunday Lunch*, *Christmas Menu*).
2. For each menu fill in:
   - **Start Time** and **End Time** in 24-hour format (bookings are enforced in 1-hour slots).
   - Optional **Available Days** to limit which weekdays the menu accepts bookings; leave empty to allow every day.
   - Use the **Content** or **Excerpt** fields to describe the menu for editors.

## 3) Place the booking form on a page
- Add the shortcode `[pixelforge_table_booking]` to any page or post (use the *Shortcode* block in the block editor if needed).
- In Blade templates you can render the form with `@shortcode('pixelforge_table_booking')`.
- The form reads all published Sections, Tables, and Booking Menus to present selectable areas and menu-specific time slots.

## 4) How bookings are processed
- Customers choose a menu, date, section, party size, and an hourly slot within the menu's availability window.
- On submission the system finds the first table in the chosen section with enough seats that is free for that hour. If no table is free, the user is prompted to try another time.
- Successful submissions create a **Table Booking** entry and automatically send a confirmation email to:
  - The admin/business email configured under *PixelForge Options* (falls back to the site admin email).
  - The customer email entered in the form.
- Booked tables are blocked for their hour, preventing double bookings of the same table and time.

## 5) Managing bookings
- View the **Table Bookings** list in the WordPress admin to see confirmed reservations.
- Booking details (customer info, menu, section, table, date/time, notes) are stored as read-only fields for staff reference.
- To free a slot, trash or delete the relevant Table Booking entry.

## Notes & tips
- The availability checker uses the site's timezone (`Settings → General`).
- Menus without valid start/end times or without any published tables/sections will not present usable slots.
- If confirmation emails are not received, verify the *PixelForge Options → Business Email* address and your WordPress mail transport configuration.
