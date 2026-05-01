# FERPA Compliance Policy - SchoolConnect AI

## Data Retention & Deletion
- **Student Data Deletion:** All student identifiable information (PII) is automatically purged from the primary database within 30 days of contract termination. We use Laravel's `Pruneable` trait to automate this.
- **Data Portability:** Schools can export their full data set in JSON/CSV format.

## AI Privacy & FERPA
- **Gemini 2.0 Flash:** We use Google Cloud Vertex AI with FERPA-compliant configurations. Data sent for translation or summarization is NOT used to train foundation models.
- **Translation Transparency:** All AI-translated messages are stored alongside the original. A mandatory disclaimer is appended: "AI-generated translation. Contact school for official version."

## Technical Safeguards
- **Multi-Tenancy:** Isolation is enforced via `stancl/tenancy` which uses separate databases/schemas per school district, preventing cross-tenant data leakage.
- **Audit Logs:** All access to student behavior records is logged with timestamp and user ID.
