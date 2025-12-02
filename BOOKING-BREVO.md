# Brevo-powered booking confirmations

This theme now uses [Brevo](https://www.brevo.com/) for email and [FrontlineSMS](https://www.frontlinesms.com/) for SMS to deliver table-booking confirmations. Customers receive a link to verify their booking; once clicked, the booking is marked as confirmed and both the customer and admin receive final confirmation emails.

## What happens automatically
- On submission, a booking is saved and marked as "pending verification" with a unique token.
- The customer receives an email (and SMS when enabled) containing the verification link.
- The admin/business email receives the booking details and a note that the booking is awaiting verification.
- When the customer clicks the link, the booking is marked as verified and both the admin and customer receive confirmed booking emails.

## One-time setup in WordPress
1. Go to **PixelForge Options → Brevo Email** and fill in:
   - **Brevo API Key**: your Brevo v3/Transactional API key.
   - **Brevo Sender Email**: a validated sender/domain in Brevo (recommended to match your site domain).
   - **Brevo Sender Name**: how the "from" name should appear (defaults to the site name if left blank).
2. Next, open **PixelForge Options → FrontlineSMS (SMS)** and fill in:
   - **FrontlineSMS API Token**: token from your Frontline workspace (or set `FRONTLINESMS_API_TOKEN`).
   - **FrontlineSMS API URL**: keep the default for Frontline Cloud or point to your self-hosted instance.
   - **FrontlineSMS Channel ID (optional)**: restricts outbound SMS to a specific channel when required.
   - **SMS Sender ID (optional)**: custom “from” label when supported by your channel.
   - **SMS Default Country Code**: optional; prepend a country code (e.g., `+44`) when customers enter local numbers without one.
3. Save the options.

## Brevo account preparation (outside WordPress)
- Verify the sender email/domain you intend to use (Brevo → **Senders & Domains**).
- Ensure your API key has permission for transactional email (Brevo → **SMTP & API**).
- For SMS, configure a Frontline channel with connectivity (Android bridge, modem, or aggregator) and note its channel ID when needed.
- If you prefer, set an environment variable `BREVO_API_KEY` on the server; it is used when the option is blank.

## Ongoing usage notes
- The confirmation link has the format `https://yoursite.test/?pixelforge_booking_confirm=<id>&token=<token>`; customers must click it to finalize their booking.
- Pending (unverified) bookings still reserve their table slot to prevent double bookings.
- Admin emails go to the **Business Email** option (fallback: site admin email).
- If Brevo is misconfigured, the system falls back to WordPress mail for emails; SMS will silently skip if the Frontline token, channel (when provided), or phone number is missing.

## Testing the flow
1. Submit the booking form with your own email/phone.
2. Verify the message arrives from the configured sender and that the link marks the booking as confirmed (you should see the success banner after clicking).
3. Confirm that both admin and customer receive the post-verification emails.
