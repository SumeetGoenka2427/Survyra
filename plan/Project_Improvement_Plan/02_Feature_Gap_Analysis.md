# 02 – Feature Gap Analysis

> Comparison of what exists vs. what is expected for a competitive SMB survey platform.

---

## Gap Summary Table

| Feature | Industry Standard | Our Platform | Gap | Priority | Difficulty | Business Value |
|---|---|---|---|---|---|---|
| Drag-and-drop question reorder | Yes (all major platforms) | No – up/down arrow buttons only | High | Critical | Medium | High |
| Survey duplication | Yes | No | High | High | Easy | High |
| Question duplication | Yes | No | Medium | High | Easy | Medium |
| Blank survey creation (no template) | Yes | No – template required | High | Critical | Easy | High |
| Survey description/intro page | Yes | No | Medium | High | Easy | Medium |
| Welcome screen | Yes (Typeform, Jotform) | No | Medium | High | Easy | High |
| Survey expiry / response limit | Yes | No | Medium | High | Easy | Medium |
| Password-protected surveys | Yes (Jotform, SurveyMonkey) | No | Medium | Medium | Easy | Medium |
| Embed code (iframe/widget) | Yes (all) | No | High | High | Easy | High |
| Survey link sharing UI | Yes | No dedicated UI | Medium | High | Easy | High |
| Social share buttons | Yes | No | Low | Medium | Easy | Low |
| File upload question type | Yes (Jotform, Formstack) | No | Medium | Medium | Hard | Medium |
| Image choice question type | Yes (Typeform, Jotform) | No | Medium | Medium | Medium | Medium |
| Signature question type | Yes (Jotform) | No | Low | Low | Hard | Low |
| Payment question type | Yes (Jotform, Formstack) | No | Low | Low | Hard | Medium |
| Conditional logic (jump-to-page) | Yes | Show/hide only | Medium | High | Medium | High |
| Multi-condition logic (AND/OR) | Yes | AND only | Medium | High | Medium | High |
| Logic: end survey on condition | Yes | No | Medium | High | Easy | High |
| Piping (insert answer into question) | Yes (SurveyMonkey, Qualtrics) | No | Medium | Medium | Hard | Medium |
| Randomize question order | Yes | No | Low | Medium | Easy | Low |
| Randomize answer options | Yes | No | Low | Low | Easy | Low |
| Survey sections/pages | Yes | Section wizard (fixed 3/page) | Medium | High | Medium | High |
| Custom section names | Yes | No | Low | Medium | Easy | Low |
| Progress bar (% or steps) | Yes | Theme setting only | Low | Medium | Easy | Low |
| Back button in multi-step | Yes | No | High | High | Medium | High |
| Save & continue later | Yes (SurveyMonkey, Jotform) | Cookie resume only | Medium | Medium | Medium | Medium |
| Anonymous response option | Yes | No explicit toggle | Medium | High | Easy | High |
| Response limit per respondent | Yes | No | Medium | Medium | Easy | Medium |
| Geo-blocking / allowed domains | Yes (Qualtrics) | No | Low | Low | Hard | Low |
| Real-time response notifications | Yes | Negative only | Medium | High | Easy | High |
| Webhook on response | Yes (Jotform, Formstack) | No | High | High | Medium | High |
| Zapier / Make integration | Yes (all major) | No | High | High | Medium | High |
| Google Sheets integration | Yes | No | High | High | Medium | High |
| Slack notification | Yes | No | Medium | Medium | Easy | Medium |
| HubSpot / Salesforce CRM | Yes (enterprise) | No | Medium | Low | Hard | Medium |
| Mailchimp / Brevo integration | Yes | No | Medium | Medium | Medium | Medium |
| Google Analytics / Meta Pixel | Yes | No | Medium | Medium | Easy | Medium |
| Stripe / Razorpay payments | Yes (Jotform) | No | Low | Low | Hard | Medium |
| REST API (public) | Yes (all major) | No | High | High | Medium | High |
| API key management | Yes | No | High | High | Easy | High |
| White-label (custom domain) | Yes (Jotform, Formstack) | No | High | Medium | Hard | High |
| Custom subdomain per client | Yes | No | Medium | Low | Hard | Medium |
| Multi-language surveys | Yes (SurveyMonkey, Qualtrics) | No | Medium | Medium | Hard | Medium |
| RTL language support | Yes | No | Low | Low | Medium | Low |
| GDPR consent checkbox | Yes | No | High | Critical | Easy | High |
| Cookie consent banner | Yes | No | Medium | High | Easy | Medium |
| Data retention policy | Yes | No | Medium | High | Easy | Medium |
| reCAPTCHA / bot protection | Yes | Throttle only | High | High | Easy | High |
| Audit log UI | Yes | Installed but no UI | Medium | Medium | Easy | Medium |
| Two-factor authentication | Yes | No | Medium | Medium | Medium | Medium |
| SSO / OAuth login | Yes (enterprise) | No | Low | Low | Hard | Low |
| Team collaboration (multi-user per client) | Partial | `ClientUser` model exists, no UI | High | High | Medium | High |
| Client user roles (owner/editor/viewer) | Yes | No | Medium | High | Medium | High |
| Survey collaboration (comments) | Yes (Qualtrics) | No | Low | Low | Hard | Low |
| Real-time analytics dashboard | Yes | Polling/manual refresh | Medium | High | Medium | High |
| Drop-off analysis | Yes | No | High | High | Medium | High |
| Geo analytics (country/city) | Yes | No | Medium | Medium | Medium | Medium |
| Device/browser analytics | Partial | Device/browser stored, no chart | Medium | Medium | Easy | Medium |
| Source analytics (QR/SMS/email/direct) | Partial | Source stored, no chart | Medium | Medium | Easy | Medium |
| Time-of-day heatmap | Yes | No | Low | Low | Medium | Low |
| Cross-filter analytics | Yes (Qualtrics) | No | Low | Low | Hard | Low |
| AI survey generator | Yes (Typeform, SurveyMonkey) | No | High | High | Hard | High |
| AI question suggestions | Yes | No | Medium | Medium | Hard | Medium |
| AI response summary | Yes | No | High | High | Hard | High |
| Sentiment analysis (NLP) | Yes | Rule-based score only | High | High | Hard | High |
| Keyword extraction | Yes | No | Medium | Medium | Hard | Medium |
| NPS benchmark comparison | Yes (SurveyMonkey) | No | Low | Low | Medium | Low |
| Predictive analytics | Yes (Qualtrics) | No | Low | Low | Hard | Low |
| Executive PDF report (rich) | Partial | Basic PDF exists | Medium | High | Medium | High |
| Scheduled report (daily) | Partial | Weekly/monthly/quarterly only | Low | Medium | Easy | Low |
| Subscription billing (Stripe) | Yes (SaaS) | Plan model only, no billing | High | High | Hard | High |
| Usage metering / enforcement | Partial | Limits defined, not enforced | High | Critical | Medium | High |
| Client onboarding wizard | Yes | No | Medium | Medium | Medium | Medium |
| In-app help / tooltips | Yes | No | Medium | Medium | Easy | Medium |
| Email verification | Yes | Commented out in User model | Medium | High | Easy | Medium |
| Redis caching | Yes | Not configured | High | High | Easy | High |
| Queue monitoring (Horizon) | Yes | No | Medium | Medium | Easy | Medium |
| S3 / CDN file storage | Yes | Local storage only | High | High | Easy | High |
| Database indexes (performance) | Yes | Minimal | High | High | Easy | High |
