# NetShort Scrape and Discovery Report

## 1. Overview
The target `https://appsecapi.netshort.com/` appears to be a backend API endpoint for the NetShort platform. While the root and common paths return 404, its existence and relation to the main `netshort.com` domain were analyzed.

## 2. Discovered Domains and Subdomains
- **netshort.com**: Main website (Next.js frontend).
- **appsecapi.netshort.com**: Potential security or private API subdomain.
- **cfcdn.netshort.com**: Content Delivery Network for video files.
- **cover.netshort.com**: Delivery of drama cover images.
- **awscover.netshort.com**: Alternative cover/image delivery (likely S3-backed).
- **collect.netshort.net**: Analytics collection endpoint.

## 3. Video Sources and Extensions
- **Video Extension**: `.mp4` (MIME type: `video/mp4`).
- **Video Host**: `cfcdn.netshort.com`.
- **Sample Video URL**: `https://cfcdn.netshort.com/o8QzqpqqeCWEnqNLkMIf3FEAl0D4n9hlVfIMww?a=0&auth_key=...&br=1313&bt=1313&...&mime_type=video_mp4&...`
- **Other Extensions**: `.js` (Next.js chunks), `.png`, `.jpg`, `.webp` (images), `.gif` (playing indicators).

## 4. API Endpoints and Structure
Discovered internal API paths used by the frontend:
- `/prod-web-api/web/v4/short_play/episode_info`
- `/prod-web-api/web/v4/short_play/detail_info/cascade_label`
- `/prod-web-api/web/v4/short_play/recommend_info/cascade_label`
- `/prod-web-api/base/checkstand/memberEquityV2`
- `/prod-web-api/order/confirm_order_v3`
- `/prod-web-api/web/auth/visitor_login`

**Request Format**:
- Method: `POST`
- Content-Type: `application/json`
- Sample Body: `{"shortPlayId": "2050068409881722882", "episodeNo": 1}`

## 5. Special JSON Data Structures
The platform embeds video metadata in the HTML source using Next.js hydration scripts (`self.__next_f.push`).

### VideoObject JSON-LD:
```json
{
  "@context": "https://schema.org",
  "@type": "VideoObject",
  "name": "Drama Title",
  "description": "...",
  "keywords": "...",
  "thumbnailUrl": "https://cover.netshort.com/...",
  "uploadDate": "...",
  "embedUrl": "https://cfcdn.netshort.com/..."
}
```

### Open Graph Metadata:
- `og:video`: Points directly to the `.mp4` source on `cfcdn.netshort.com`.
- `og:video:type`: `video/mp4`.
- `og:image`: Points to the cover image.

## 6. Security Observations
- API requests often include custom headers like `encrypt-key`, `Device-Code`, and `Authorization` (Bearer token).
- Video URLs are protected by `auth_key` parameters, preventing unauthorized access without valid session-derived signatures.
- Root access to `appsecapi.netshort.com` is restricted (404/403).
