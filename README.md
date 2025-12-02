# PixelForge table booking playbook (manager edition)

This document explains how to run the PixelForge table booking system day to day. It is written for business owners and venue managers rather than developers.

## What the system does

- Publishes a public booking form you can place on any page.
- Prevents double bookings by assigning guests to specific tables and time slots.
- Sends verification links to customers (email via Brevo + SMS via FrontlineSMS) so only confirmed bookings stay on the calendar.
- Lets staff manage bookings from either WordPress admin or a simplified Booking Admin Panel.

## Daily tasks at a glance

1) **Check availability** – the public form shows available times based on your tables, sections, and menus.
2) **Review new bookings** – customers submit a request, receive a verification link, and you get an email notification.
3) **Track confirmations** – when customers click the link, the booking is marked **Confirmed** and a follow-up email is sent to both sides.
4) **Manage changes** – staff can add, edit, or trash bookings from the Booking Admin Panel without entering wp-admin.

## Initial setup

Complete these steps once when the site launches or whenever the floor plan changes.

### 1) Define areas and tables
- In WordPress, go to **Table Sections** and add areas such as *Bar*, *Window*, or *Function Room*. Optional internal notes help staff seat guests correctly.
- Next, open **Tables** and create one entry per table:
  - Select its **Section**.
  - Fill in **Seats** (total capacity).
  - Add **Table Notes** if staff need extra context (e.g., high-top, wheelchair-friendly).

### 2) Set booking windows (menus)
- Go to **Booking Menus** and add one per service (e.g., *Breakfast*, *Evening*, *Christmas Menu*).
- For each menu set:
  - **Start Time** and **End Time** in 24-hour format; bookings are enforced in 90-minute slots.
  - Optional **Available Days** to limit which weekdays the menu accepts bookings. Leave empty to allow every day.
  - Use the content/excerpt to describe the menu for editors (not shown on the public form).

### 3) Publish the booking form
- Edit any page and add the shortcode `[pixelforge_table_booking]` (use the *Shortcode* block in the block editor if needed).
- The form reads your published Sections, Tables, and Booking Menus to present valid options and time slots.

### 4) Choose who gets notified
- Go to **PixelForge Options → Business Email** to set where admin notifications are sent (falls back to the WordPress admin email).
- Configure **Brevo Email** for outgoing emails and **FrontlineSMS (SMS)** for text messages.

## How bookings flow

1) **Customer submits the form** with a menu, date, section, party size, and hour slot.
2) The system finds the first table in that section with enough seats that is free for the chosen hour.
3) A booking is saved as *pending verification* and an email (plus SMS if enabled) is sent with a confirmation link.
4) The admin/business email receives the booking summary noting it awaits verification.
5) When the customer clicks the link, the booking is marked **Confirmed** and both customer and admin receive confirmation emails.
6) Booked tables are blocked for their 90-minute window to prevent double bookings.

If the system cannot find an available table, it suggests the next available slot (up to 7 days ahead) when possible.

## Managing bookings (WordPress dashboards)

- **Table Bookings list** – shows every reservation with menu, section, tables, and timestamps. Trash or delete an entry to free the slot.
- **Booking Admin Panel** – a page template that provides a simplified interface for staff (see the staff guide for details). Staff accounts need the ability to edit posts; deleting bookings requires the delete capability.

## Controlling the booking form

- Toggle public bookings under **PixelForge Options → Enable Table Bookings**. When unchecked, the front-end form and availability calendar are hidden and AJAX availability checks return unavailable.
- The availability calendar respects the site timezone in **Settings → General**.

## PixelForge Options (business settings)

Access these under **PixelForge Options** in WordPress:

- **Business Logo** – used as the header/hero fallback image.
- **Business Email** – where admin booking notifications are delivered.
- **Business Telephone** and **Business Address** – surfaced in theme templates.
- **Enable Table Bookings** – hides/shows the public form and availability checks.
- **Booking Data Tools** – one-click, permanent deletion of all booking data (sections, tables, menus, bookings). Use with caution.
- **Brevo Email** – configure API key, sender email/name for confirmations. If Brevo is missing, the system falls back to standard WordPress email.
- **FrontlineSMS (SMS)** – configure token, API URL, optional channel and sender ID, plus the default country code used to normalise phone numbers for SMS confirmations.
- **SEO & Social Defaults** – default meta description, keywords, Open Graph title/description/image, and Twitter username used across the site when individual pages do not override them.

## Tips and troubleshooting

- **Menus need times** – menus without valid start/end times or without any published tables/sections will not present usable slots.
- **Customer already booked** – the same email/phone cannot hold overlapping active bookings.
- **Delivery issues** – if confirmations do not arrive, check the Business Email value and your mail transport. For SMS, confirm the FrontlineSMS token, channel, and sender settings are valid.
- **Timezone** – booking times and availability use the site timezone; update it in **Settings → General** if needed.
- **Data cleanup** – use **Booking Data Tools** only when resetting the system; it removes all booking-related records and cannot be undone.

## Quick reference: where to go

- Publish/hide form: **PixelForge Options → Enable Table Bookings**
- Notification recipients: **PixelForge Options → Business Email**
- Email sender: **PixelForge Options → Brevo Email**
- SMS sender: **PixelForge Options → FrontlineSMS (SMS)**
- Manage bookings (full WordPress): **Table Bookings** post type
- Manage bookings (staff-friendly): page using **Booking Admin Panel** template
- Define seating: **Table Sections** and **Tables** post types
- Define service windows: **Booking Menus** post type
