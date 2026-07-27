# Instagram Auto-Reply Setup

Full setup for automating replies to the "DM 'DEMO'" CTA used throughout [content-prompts.md](content-prompts.md) and the bio in [instagram-setup.md](instagram-setup.md). Two layers: native Meta tools (free, basic) and a third-party automation tool (free tier, more powerful — comment-to-DM).

**Before setting this up, resolve the two placeholders every message below uses:**
- **`[wa.me link]`** — generate this once in [instagram-setup.md](instagram-setup.md) → WhatsApp Business Setup, then reuse the same link everywhere (don't generate a new one per message).
- **`[demo link]`** — not yet a real asset in the plan. [phase-1-foundation.md](../phase-1-foundation.md) Day 3 gets Demo 1/2/3 rehearsed as *live, presenter-led* walkthroughs (5 minutes, shown by you on a call/screen-share) — it doesn't produce a single self-serve URL a cold DM can just hand over. Before turning on automation, record a 60–90 second screen-capture walkthrough (Loom or similar) covering the same flow (Website → QR → Survey → Rating → Review Request → Dashboard) and host it somewhere link-able (Loom's own share link, or a YouTube unlisted link). That recording's URL is `[demo link]`.

---

## Layer 1 — Native Meta Tools (set up first, takes 10 minutes)

These require the Professional/Business account + Meta Business Suite connection from [instagram-setup.md](instagram-setup.md).

### Quick Replies (canned DM responses)
Path: Instagram app → Settings → Business tools and controls → Saved Replies (or Meta Business Suite → Inbox → Automations → Saved Replies)

Set up these 4 shortcuts so replying is one tap:
- **`/demo`** → "Here's a quick demo of how it works: [demo link]. Want me to show you how it'd look for your business specifically?"
- **`/pricing`** → "Our Growth Package starts at ₹3,000/month — website + customer feedback + review growth, all included. Right now it's ₹2,000/month for your first 3 months under our Founding Client offer. Want the full breakdown?"
- **`/whatsapp`** → "You can reach us faster on WhatsApp here: [wa.me link]"
- **`/included`** → "It includes: a modern business website, a customer feedback survey with QR code, and a review-growth system that turns happy customers into 5-star reviews. Monthly support included too."

### Instagram Automated Replies (Meta Business Suite → Automations)
Path: business.facebook.com → your Page/Instagram → Inbox → Automations

Set up these automation rules:
- [ ] **Instant Reply** — triggers the moment someone sends their first DM ever. Message: *"Thanks for reaching out! 👋 If you're here about the demo, reply 'DEMO' and I'll send it right over."*
- [ ] **Away Message** — outside working hours (set your hours). Message: *"We're offline right now but will reply first thing. For anything urgent, WhatsApp us: [wa.me link]"*
- [ ] **FAQ automation** — Meta lets you set up to 4 canned Q&A buttons that appear automatically in new conversations. Use:
  - "What's included?" → included answer above
  - "How much does it cost?" → pricing answer above
  - "Can I see a demo?" → demo answer above
  - "How do I get started?" → *"Just reply here and I'll set up a quick 5-minute demo call or send you a recorded walkthrough — whichever's easier for you."*

This layer alone covers "someone DMs cold" and "someone DMs after seeing a post." It does **not** cover comment-based triggers (someone commenting "DEMO" on a post/reel) — that needs Layer 2.

---

## Layer 2 — Comment-to-DM Automation (ManyChat, free tier is enough)

This is what makes the "DM 'DEMO'" CTA actually scale: when someone comments "DEMO" (or any keyword) on a post/reel, they automatically get a DM with the demo link — no manual replying needed. Also handles keyword-triggered auto-DM.

### Setup
1. [ ] Sign up at manychat.com (free tier supports 1 Instagram automation + up to 1,000 contacts — enough for this 30-day sprint)
2. [ ] Connect your Instagram Business account (requires the Meta Business Suite link from [instagram-setup.md](instagram-setup.md))
3. [ ] Create an automation: **Trigger** = "Comment on any post/reel contains keyword" → keyword = `DEMO` (case-insensitive, also add `demo`, `Demo`)
4. [ ] **Action** = Public reply to the comment (e.g. *"Sent you a DM! 📩"*) + Private DM:
   > Hey! Thanks for your interest 🙌 Here's a quick look at how we help businesses like yours get more customers: [demo link]
   > Want me to show you how it'd work specifically for your business? Just reply here.
5. [ ] Add a second automation for Story replies: **Trigger** = "Someone replies to a Story" containing keyword `DEMO` → same DM action as above
6. [ ] Add a fallback keyword set for the DM opening line prospects might use when they reply to your cold outreach: `interested`, `yes`, `tell me more` → auto-send the pricing + demo combo message

### Compliance note
- Meta requires DM automation tools to be connected via official Meta APIs (ManyChat/Chatfuel are Meta Business Partners — this is compliant). Never use unofficial/unauthorized automation that requires your Instagram password outside Meta's own OAuth flow — that risks the account being flagged or banned.
- Keep automated messages honest and low-pressure — no fake urgency, no impersonating a live human when it's clearly automated for the first message.

### Handoff to human
- [ ] Configure ManyChat to tag/flag any conversation where the prospect asks a specific question outside the canned keywords, so you see it and reply personally within the same day (outreach follow-up cadence in [phase-3-sales-machine.md](../phase-3-sales-machine.md) depends on fast personal follow-up, not just automation)

### Exit checklist
- [ ] `[demo link]` recorded and hosted (Loom/YouTube unlisted) — resolved before any automation goes live, not left as a placeholder
- [ ] `[wa.me link]` generated once (see [instagram-setup.md](instagram-setup.md) → WhatsApp Business Setup) and the same link pasted into every message above
- [ ] Quick Replies configured (4 shortcuts)
- [ ] Instant Reply + Away Message + FAQ automation live in Meta Business Suite
- [ ] ManyChat connected, comment-to-DM automation live for keyword `DEMO`
- [ ] Story-reply automation live
- [ ] Tested end-to-end: comment "DEMO" on your own post from a second account and confirm the DM arrives
