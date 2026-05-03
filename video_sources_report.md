# NetShort Video Sources Report

## Overview
The investigation into `https://appsecapi.netshort.com/` and the associated `netshort.com` platform revealed that video content is served through a Content Delivery Network (CDN).

## Findings
- **Primary Video Host:** `cfcdn.netshort.com`
- **Subdomain Analysis:** `appsecapi.netshort.com` appears to be a restricted backend for the mobile application (`com.netshort.abroad`). It returns 404 for standard web requests and likely requires mobile-specific authentication (App-Signature, Device-ID, etc.).
- **Scraped Video Sources:** Direct video links (MP4/TS) were extracted from the platform's web frontend.

## Verified Video URLs
The following URLs were verified to be active and serve video content (requires standard browser headers or the specific Referer `https://netshort.com/`):

1. **The Wolfless Carpenter Rules the World (EP 1):**
   `https://cfcdn.netshort.com/o8QzqpqqeCWEnqNLkMIf3FEAl0D4n9hlVfIMww?a=0`
2. **The Wolfless Carpenter Rules the World (EP 2):**
   `https://cfcdn.netshort.com/o8G1GVM40IsW1WA3aAfibvoiEYBQVxv1EKPhD5`
3. **The Wolfless Carpenter Rules the World (EP 3):**
   `https://cfcdn.netshort.com/oEENjwpfW1MnvAx1DAIfG9FTUYQCUqfzEaq6bO`
4. **The Wolfless Carpenter Rules the World (EP 4):**
   `https://cfcdn.netshort.com/okpqwS1VlCGIrfiN3A1E5X4QUeqYqrfTkSaBiu`
5. **Ancient Bullies? Don't Mess With a CEO:**
   `https://cfcdn.netshort.com/o8wDzKMNEnqQEbFqpTzf1FBfeWpNCWq90IAn1N`
6. **Baby You're So Wet:**
   `https://cfcdn.netshort.com/oU4eEAfFXQY3rNgqNmq9o8IZy4npDfRCw1Up31`

## Technical Details
The web player uses a custom implementation based on `VePlayer`. While API endpoints like `/web/v4/short_play/episode_info` exist, they are protected with RSA/AES encryption. The video URLs themselves are often embedded in the initial page load data (`self.__next_f.push`).
