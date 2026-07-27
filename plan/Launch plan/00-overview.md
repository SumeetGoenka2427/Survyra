# 30-Day Aggressive Launch Plan — Overview

Source: `plan.md` (original brain-dump, kept as-is for reference). This overview + the phase files in this folder are the same plan reorganized into a trackable, day-by-day execution structure.

## The Goal

You already have 2 websites + 1 survey platform (Survyra). This is a **validation + client acquisition sprint**, not a SaaS-development month.

> Finish Day 30 with: a professional Instagram brand + clear offer + sales system + 100–300 prospects contacted + demos + first paying clients + real feedback + a clear SaaS roadmap.

## Day-30 Targets

| Target | Goal |
|---|---:|
| Instagram posts | 20–25 |
| Reels | 10–15 |
| Stories | Daily |
| Prospects researched | 300+ |
| Businesses contacted | 200+ |
| Conversations | 40+ |
| Demos | 15–25 |
| Trials | 5–10 |
| Paying clients | **3–5 minimum** |
| Target MRR | **₹9K–₹15K+** |
| Case studies | 1–3 |
| Testimonials | 2–3 |

Don't worry about hitting every number exactly — the point is a repeatable sales process.

## Brand Name — Survyra (locked)

The business goes to market as **Survyra** — one unified brand across website, survey/feedback, and review-growth. Verified clean: `survyra.com` unregistered, no competing Instagram account/business found. Full decision + rejected alternatives in [setup/instagram-setup.md](setup/instagram-setup.md) → Naming Decision.

## Positioning

> **We help small businesses get more customers through modern websites, customer feedback and review growth.**

Three services: 🌐 Website · 📊 Customer Feedback · ⭐ Review Growth.

Call yourself **Customer Growth & Digital Experience**, not "website developer."

## The Offer — Growth Package (₹3,000/month)

- **Website**: modern business site, mobile responsive, custom domain, hosting, basic SEO, WhatsApp/contact integration
- **Customer Feedback**: online survey, QR code, feedback collection, rating system, dashboard
- **Review Growth**: happy customer → review request; unhappy customer → private feedback; review tracking
- **Support**: basic website updates, monthly feedback report

State explicitly: *"Website setup + basic monthly updates included. Major redesigns, additional functionality and custom development are separately quoted."* Don't promise unlimited work.

## Phase Map

| Phase | Days | Focus | File |
|---|---|---|---|
| 1 | 1–3 | Build the Foundation (offer, brand, demo assets) | [phase-1-foundation.md](phase-1-foundation.md) |
| 2 | 4–7 | Build Instagram Presence | [phase-2-instagram-presence.md](phase-2-instagram-presence.md) |
| 3 | 8–10 | Build the Sales Machine (prospect list, script, demo flow) | [phase-3-sales-machine.md](phase-3-sales-machine.md) |
| 4 | 11–17 | Aggressive Outreach | [phase-4-outreach.md](phase-4-outreach.md) |
| 5 | 18–21 | Get First Clients (trial → onboarding → launch) | [phase-5-first-clients.md](phase-5-first-clients.md) |
| 6 | 22–25 | Turn Clients Into Proof (testimonials, case studies) | [phase-6-proof.md](phase-6-proof.md) |
| 7 | 26–30 | Scale What Worked (analyze, double down on best niche) | [phase-7-scale.md](phase-7-scale.md) |

Daily tracking templates (scoreboard, prospect list, weekly funnel, content calendar, SaaS requirements backlog) live in [execution-tracker.md](execution-tracker.md).

## Supporting Assets

Detailed setup guides that plug into the phases above:

All in the [setup/](setup/) subfolder:

| Asset | File | Used in |
|---|---|---|
| Instagram name suggestions + business account setup | [setup/instagram-setup.md](setup/instagram-setup.md) | Phase 1, Day 2 |
| Brand identity (name, voice, colors, typography, logo, bio) | [setup/branding-guide.md](setup/branding-guide.md) | Phase 1, Day 2 |
| AI prompts for logo, posts, stories, reels, post layouts, bio | [setup/content-prompts.md](setup/content-prompts.md) | Phase 1 Day 2 (logo) + Phase 2, Days 5–6 (content) |
| Auto-reply / DM automation setup (native + ManyChat) | [setup/auto-reply-setup.md](setup/auto-reply-setup.md) | Phase 2, Day 7 onward |
| Pricing tiers, launch offer, early-bird/trial-to-paid offer | [setup/pricing-offers.md](setup/pricing-offers.md) | Phase 1 (offer) + Phase 5 (conversion) |

## Rules for These 30 Days

1. **No major SaaS development.** Multi-tenancy waits — see "What About Multi-Tenant SaaS?" below.
2. **No waiting for Instagram followers.** Sell directly via DM/WhatsApp outreach.
3. **Don't build custom features before payment.**
4. **Don't offer unlimited free work.**
5. **Launch client websites quickly** — target 48–72 hours from "yes."
6. **Every client request gets recorded** — becomes the SaaS roadmap (see tracker).
7. **Talk to businesses every day**, even when heads-down building.

## What About Multi-Tenant SaaS?

**Don't build it during the first 30 days.** Month 1 architecture stays manual:

```
Client A → Website A
Client B → Website B
Client C → Website C
```

Deploy/configure each client by hand. Once you reach **5–10 clients**, start designing the shared schema (`business_id`, `domain`, `theme`, `website`, `survey`, `responses`, `users`, `subscription`) and migrate gradually toward: one Laravel app → multi-tenant → multiple domains → multiple businesses.

Until then, just keep the SaaS Requirements backlog in the tracker updated every time a client asks for something new.

## Timeline Compression Rule

**Month 1 = Sell. Month 2 = Improve + sell more. Month 3 = Productize. Then = Multi-tenant SaaS + scale.**

If you prove "small businesses will pay me monthly for website + feedback + reviews" with 3–5 customers, the SaaS build becomes low-risk because you'll know exactly what to automate first.
