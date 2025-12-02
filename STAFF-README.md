# Booking Admin Panel – staff guide

This guide explains how front-of-house staff can manage reservations using the Booking Admin Panel page (no need to open the WordPress dashboard). A manager should create a page using the **Booking Admin Panel** template and share its URL with staff.

## Access and sign-in

- Open the shared Booking Admin Panel link.
- Enter your WordPress **username** and **password**, then click **Sign in**.
- If you see “No access”, your account is missing the **edit posts** permission; ask a manager to adjust your role. Trashing bookings requires the **delete posts** permission.
- Use the **Log out** button in the top-right to end your session.

## Create a booking

1. Go to the **Create booking** card.
2. Fill in **Guest name**, **Email**, **Phone**, and **Party size** (minimum 1).
3. Choose a **Menu** and **Section**. The dropdowns list the published Booking Menus and Table Sections set by managers.
4. Tick one or more **Tables** that suit the party size. Table labels show the section and number of seats.
5. Pick the **Date** and **Time** (24-hour format). Use the same slots customers see on the public form.
6. Add any **Notes** (allergies, accessibility, celebrations) and click **Save booking**.
7. The booking is saved immediately and marked **Confirmed** (no customer verification required for staff-created bookings).

## Update an existing booking

1. In **Existing bookings**, expand a reservation to view its details and status pill (Confirmed or Pending).
2. Edit any field inline: guest details, party size, menu/section, selected tables, date/time, or notes.
3. Click **Update booking** to save changes. Updates keep the booking confirmed and refresh the list count instantly.

## Trash a booking

1. Expand the booking in **Existing bookings**.
2. Click **Trash booking** and confirm the prompt. Trashed bookings free the time slot for that table.

## How the panel works (quick facts)

- The panel lists all Table Bookings ordered by date/time, including Pending customer bookings from the public form.
- Selecting tables ensures the right physical table(s) are reserved; staff can assign multiple tables for larger parties.
- The panel respects the site timezone configured in **Settings → General**.
- Changes made here are reflected in the main **Table Bookings** list inside wp-admin for managers.
- If options like menus, sections, or tables are missing, ask a manager to add them in WordPress; the panel only shows published records.
