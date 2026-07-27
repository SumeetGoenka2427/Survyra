# Instagram Name Suggestions + Business Account Setup

Fills in Day 2 of [phase-1-foundation.md](../phase-1-foundation.md) ("Brand Identity") in full detail.

---

## 1. Naming Decision — Survyra

**Locked: the business goes to market as `Survyra`** — one unified brand across the website, survey/feedback, and review-growth offer. No separate agency name; drop the earlier "keep Survyra backend-only" idea.

### Why this won over the invented English names
A round of English name candidates (LoopVine, TrustBloom, BloomLoop, StarLoop, TrustLoop, ReputeHQ, NudgeLocal, RaveLoop, WeaveGrowth, LocalRaise, GrowCircle, GlowReach) was checked two ways — Instagram/web search for an exact-match existing account, and a live domain-registry (RDAP) lookup for the `.com`. Nearly every plausible two-word English `.com` turned out to be squatted by a domain reseller, and a couple had direct real-business collisions (`TrustLoop` is an existing review-management SaaS; `GlowReach` an existing marketing agency). `Survyra` sidesteps all of it:

| Check | Result |
|---|---|
| `survyra.com` (live RDAP registry lookup) | **404 — genuinely unregistered** |
| Instagram / web search for an existing "Survyra" account or business | **No match found** |
| Already in use | Yes — it's your existing product name, nothing new to introduce |

### Domain & handle
- [ ] Register `survyra.com` and/or `survyra.in` now, while it's confirmed open — don't wait
- [ ] Claim `@survyra` on Instagram; if the bare handle is gone by the time you check, fall back to a close variant (`getsurvyra`, `survyra.hq`) rather than a different name
- [ ] Re-confirm both directly (Instagram search + registrar) immediately before signing up — automated checks are a strong signal, not a 100% guarantee

### Naming checklist
- [ ] `survyra.com` / `survyra.in` registered
- [ ] `@survyra` (or close variant) claimed on Instagram
- [ ] Say "Survyra" out loud on a call — confirm it's easy to spell/pronounce for a salon/restaurant/clinic owner over the phone

---

## 2. Business Account Setup (step-by-step)

1. **Create/convert to a Professional Account**
   - Instagram app → Settings → Account type and tools → Switch to Professional Account → choose **Business** (not Creator — Business unlocks WhatsApp/contact buttons and ad tools you'll want later)
   - Category (locked): **"Digital Marketing Agency"** — closest fit to the "Customer Growth & Digital Experience" positioning from [branding-guide.md](branding-guide.md), and reads as a growth partner rather than "freelancer" or "web designer"

2. **Connect to Meta Business Suite / Business Manager**
   - business.facebook.com → Create a Business Portfolio → connect this Instagram account + a Facebook Page (Instagram Business accounts require a linked Page, even a minimal one)
   - This connection is required later for the auto-reply automation (see [auto-reply-setup.md](auto-reply-setup.md)) and for running ads if you go that route

3. **Profile fields**
   - [ ] Name field: `Survyra` + a searchable keyword, e.g. `Survyra | Websites & Reviews`
   - [ ] Username: `@survyra` (or the close variant locked in the naming section above)
   - [ ] Profile photo: logo on a clean/solid background (from [branding-guide.md](branding-guide.md))
   - [ ] Bio: see template in [content-prompts.md](content-prompts.md) — bio prompt section
   - [ ] Contact options: add WhatsApp Business number + email; enable the "Contact" button
   - [ ] Website link: use a link-in-bio tool (Linktree/Beacons or a single page on your own site) pointing to: Demo request / WhatsApp / Portfolio / Pricing — not just one raw URL

4. **Business settings to configure immediately**
   - [ ] **Quick Replies** (Settings → Business tools → Saved Replies) — pre-write 3–5 canned DM responses (demo link, pricing, "what's included") so replies go out fast and consistent
   - [ ] **Away Message** (Settings → Messages → Away mode) — auto-response outside working hours, pointing people to WhatsApp for urgent replies
   - [ ] **Message Controls** — allow message requests from everyone (don't restrict — you want cold DMs from prospects to land, and you want to be able to send the first DM in outreach)
   - [ ] **Branded content / partnership tools** — leave off for now, not relevant at this stage
   - [ ] **Insights** — enabled by default on Business accounts; check weekly against the scoreboard in [execution-tracker.md](../execution-tracker.md)
   - [ ] **Ad account** — don't set this up yet; the 30-day plan is organic + direct outreach, not paid ads (see Rule 2 in [00-overview.md](../00-overview.md))

5. **Highlights covers** — once Day 7 content exists, build highlight covers matching the brand colors (Services / Websites / Surveys / Reviews / Demos / Pricing / About, per [phase-2-instagram-presence.md](../phase-2-instagram-presence.md))

### Exit checklist
- [ ] Professional (Business) account live, correct category
- [ ] Linked to a Meta Business Suite portfolio + Facebook Page
- [ ] Bio, contact button, and link-in-bio all pointing somewhere useful
- [ ] Quick Replies + Away Message configured
- [ ] Ready to hand off to [auto-reply-setup.md](auto-reply-setup.md) for the comment/DM automation layer

---

## 3. WhatsApp Business Setup

WhatsApp is the fallback channel referenced everywhere — the IG "Away Message," Quick Replies, and every outreach script in [phase-3-sales-machine.md](../phase-3-sales-machine.md) point prospects here for anything urgent. Set it up as its own properly configured business presence, not just a personal number.

1. **Install WhatsApp Business** (separate app from regular WhatsApp) on a number dedicated to Survyra — don't run it off a personal number if avoidable, since the business profile, catalog, and automation below live on this number
2. **Business Profile**
   - [ ] Name: `Survyra`
   - [ ] Category: Business Services / Marketing Agency
   - [ ] Description: *"Websites, customer feedback surveys, and review growth for small businesses. Reply 'DEMO' to see it in action."*
   - [ ] Business hours set (matches the Away Message hours configured in Instagram)
   - [ ] Address/location (if applicable) and website link (survyra.com / link-in-bio)
   - [ ] Profile photo: same wordmark logo used on Instagram (from [content-prompts.md](content-prompts.md) → Logo Generation Prompts) — never a different logo across channels
3. **Quick Replies / Greeting Message** (Settings → Business tools → Quick replies / Away message, native to WhatsApp Business — separate from the Instagram ones set up in §2 above, but should use identical wording for consistency)
   - Greeting message (sent automatically to first-time chatters): *"Hi! 👋 Thanks for reaching out to Survyra. Send 'DEMO' and I'll share a quick walkthrough of how it works for your business."*
   - Quick reply shortcuts mirroring the Instagram set: `/demo`, `/pricing`, `/included` (same copy as the Instagram Quick Replies in [auto-reply-setup.md](auto-reply-setup.md))
4. **Click-to-chat link (`wa.me` link)**
   - [ ] Generate at `wa.me/<your number with country code, no +/spaces>` — optionally pre-fill a starter message via `wa.me/<number>?text=Hi%2C%20I%27d%20like%20to%20see%20the%20demo`
   - [ ] This is the `[wa.me link]` referenced throughout [content-prompts.md](content-prompts.md), [auto-reply-setup.md](auto-reply-setup.md), and the link-in-bio setup in §2 above — generate it once here and reuse everywhere, don't create multiple links
5. **Catalog (optional, skip for Month 1)** — WhatsApp Business supports a product catalog; not worth setting up until the Starter/Growth/Pro tiers in [pricing-offers.md](pricing-offers.md) are stable, since catalog entries would need updating every time pricing changes

### Exit checklist
- [ ] WhatsApp Business app installed on the dedicated Survyra number
- [ ] Business profile complete (name, category, description, hours, photo)
- [ ] Greeting message + Quick Replies configured, wording matching the Instagram ones
- [ ] `wa.me` click-to-chat link generated and dropped into the Instagram link-in-bio, Quick Replies, and outreach scripts
