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

## 7. Discovering All Videos and Episodes
There is no single static JSON file containing every video, but the following methods are used by the platform to list content:

### Web Interface:
- `https://netshort.com/all-episodes`: A paginated directory of all short dramas.
- `https://netshort.com/drama/all-plots`: Category-based listing.

### Hydrated JSON Data:
When visiting `/all-episodes`, the page source contains a JSON array in the `self.__next_f.push` scripts. Key fields include:
- `videoList`: An array of drama objects.
- `shortPlayId`: Unique identifier for each series.
- `shortPlayName`: Title of the drama.
- `fullEpisodeNameUrl`: Relative path to the episode list.

### Potential List APIs:
The frontend calls these endpoints to retrieve content lists:
- `/prod-web-api/web/web/v2/queryPopularLabelList/cascade_label` (POST)
- `/prod-web-api/web/web/v3/queryOnlineLabelList/cascade_label` (POST)
- `/prod-web-api/web/marketing/queryMarketingList` (POST)

These APIs typically return JSON objects with a `data` field containing lists of short dramas and their metadata.

## 8. Crawling Strategy
To automate the discovery and extraction of all video sources, the following crawling strategy is recommended:

### Phase 1: Series Discovery
1. **Initial Seed**: Start with `https://netshort.com/all-episodes`.
2. **Pagination Handling**: Iterate through the page numbers (1 to 367+). The URL pattern is likely `?page={n}` or handled via client-side state.
3. **Data Extraction**: For each page, parse the `self.__next_f.push` scripts in the HTML source. Specifically, look for the `videoList` array which contains the `shortPlayId` and `shortPlayNameUrl` for every drama in that batch.

### Phase 2: Episode Discovery
1. **Detail Page Visit**: For each `shortPlayNameUrl` discovered, visit the corresponding drama page (e.g., `https://netshort.com/episode/{slug}-{id}`).
2. **Hydration Parsing**: Extract the `VideoObject` from the page's hydration scripts. This provides the `embedUrl` for the first episode.
3. **Sequence Mapping**: Identify the total number of episodes. The page contains a list of episode numbers. Follow the link pattern (e.g., `{base-url}-ep-{n}`) to visit and extract the `embedUrl` for subsequent episodes.

### Phase 3: Automation Tools
- **Static Scraper (Requests + BeautifulSoup)**: Fast and efficient for extracting hydration data from the initial HTML.
- **Dynamic Scraper (Playwright/Selenium)**: Only necessary if specific interactions (like clicking "Load More") are required, though Next.js hydration data usually contains most information upfront.
- **JSON Extractors**: Use regex or customized JSON parsers to handle the `self.__next_f.push` format, which is a serialized representation of React components and props.
