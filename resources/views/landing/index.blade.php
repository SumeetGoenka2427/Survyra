<x-public-layout
    title="Survyra"
    description="Collect customer feedback, grow reviews and build a professional business website with Survyra. Simple surveys, QR feedback, analytics and review tools for salons, restaurants, clinics and local businesses."
>
    <script type="application/ld+json">
        {!! json_encode([
            '@context' => 'https://schema.org',
            '@type' => 'Service',
            'serviceType' => 'Customer feedback and review-growth platform',
            'provider' => ['@type' => 'Organization', 'name' => 'Survyra'],
            'areaServed' => 'IN',
            'audience' => ['@type' => 'Audience', 'audienceType' => 'Small and local businesses'],
            'description' => 'Survyra combines a professional business website, QR-based customer feedback surveys, a review-growth funnel and an analytics dashboard for small businesses.',
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
    </script>

    <nav class="navbar navbar-expand-lg sv-navbar sticky-top py-3">
        <div class="container">
            <a class="navbar-brand sv-brand" href="{{ route('home') }}">Survyra</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#svNav" aria-controls="svNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="svNav">
                <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-2">
                    <li class="nav-item"><a class="nav-link" href="#what-we-do">What We Do</a></li>
                    <li class="nav-item"><a class="nav-link" href="#how-it-works">How It Works</a></li>
                    <li class="nav-item"><a class="nav-link" href="#try-demo">Live Demo</a></li>
                    <li class="nav-item"><a class="nav-link" href="#pricing">Pricing</a></li>
                    <li class="nav-item ms-lg-2 mt-2 mt-lg-0">
                        <a class="btn btn-accent btn-sm px-3" href="#demo">Get My Free Demo</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <header class="sv-hero">
        <div class="sv-hero-shape sv-shape-1"></div>
        <div class="sv-hero-shape sv-shape-2"></div>
        <div class="sv-hero-shape sv-shape-3"></div>
        <div class="container">
            <div class="row align-items-center g-5">
                <div class="col-lg-6">
                    <span class="sv-eyebrow sv-anim">For Salons, Restaurants, Clinics &amp; Local Businesses</span>
                    <h1 class="mt-3 mb-3 sv-anim sv-anim-delay-1">Turn Customer Feedback Into More Reviews &amp; More Business</h1>
                    <p class="lead sv-anim sv-anim-delay-2">Survyra helps salons, restaurants, clinics and local businesses collect customer feedback, identify unhappy customers, generate more review opportunities, and build a professional online presence &mdash; all from one simple platform.</p>
                    <div class="d-flex flex-wrap gap-3 mt-4 sv-anim sv-anim-delay-3">
                        <a href="#demo" class="btn btn-accent btn-lg px-4">Get My Free Demo</a>
                        <a href="#how-it-works" class="btn btn-outline-light-custom btn-lg px-4">See How It Works</a>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="sv-hero-visual sv-anim sv-anim-delay-2">
                        <div class="sv-hero-flow-labels d-none d-md-flex">
                            <span style="--i:0">Website</span>
                            <span style="--i:1">QR</span>
                            <span style="--i:2">Survey</span>
                            <span style="--i:3">Review</span>
                            <span style="--i:4">Analytics</span>
                        </div>
                        <div class="sv-demo-frame">
                            <div class="sv-demo-bar">
                                <i class="bi bi-qr-code"></i> demo.survyra.com/glow-salon
                            </div>
                            <div class="p-4">
                                <div class="small text-muted-2 text-uppercase fw-semibold" style="letter-spacing: .04em;">Glow Salon &amp; Spa</div>
                                <div class="fw-semibold mb-3" style="font-family:'Poppins',sans-serif;">Customer Experience</div>
                                <p class="text-muted-2 small mb-2">How was your visit?</p>
                                <div class="sv-star-rating mb-3">
                                    <i class="bi bi-star-fill sv-star filled"></i>
                                    <i class="bi bi-star-fill sv-star filled"></i>
                                    <i class="bi bi-star-fill sv-star filled"></i>
                                    <i class="bi bi-star-fill sv-star filled"></i>
                                    <i class="bi bi-star-fill sv-star filled"></i>
                                </div>
                                <button type="button" class="btn btn-primary w-100 mb-3" disabled>Submit Feedback</button>
                                <div class="sv-product-stats">
                                    <div><strong>4.8</strong><span><i class="bi bi-star-fill text-accent"></i> Avg</span></div>
                                    <div><strong>284</strong><span>Responses</span></div>
                                    <div><strong>+32%</strong><span>Reviews</span></div>
                                </div>
                                <p class="text-center text-muted-2 mb-0 mt-2" style="font-size: .7rem;">Illustrative preview &mdash; try the real thing below</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <a href="#what-we-do" class="sv-scroll-cue" aria-label="Scroll down"><i class="bi bi-chevron-double-down fs-4"></i></a>
    </header>

    <div class="sv-trust-strip sv-reveal">
        <div class="container">
            <p class="text-center small fw-semibold text-uppercase mb-3" style="letter-spacing: .05em; color: var(--sv-text-muted);">Built for businesses that depend on customer trust</p>
            <div class="d-flex flex-wrap justify-content-center gap-2 mb-4">
                <span class="sv-trust-pill">Salons</span>
                <span class="sv-trust-pill">Restaurants</span>
                <span class="sv-trust-pill">Clinics</span>
                <span class="sv-trust-pill">Cafes</span>
                <span class="sv-trust-pill">Gyms</span>
                <span class="sv-trust-pill">Local Services</span>
            </div>
            <div class="d-flex flex-wrap justify-content-center gap-4">
                <span class="small text-muted-2"><i class="bi bi-check-circle-fill text-accent me-1"></i>Mobile-first</span>
                <span class="small text-muted-2"><i class="bi bi-check-circle-fill text-accent me-1"></i>Custom domain</span>
                <span class="small text-muted-2"><i class="bi bi-check-circle-fill text-accent me-1"></i>Customer feedback</span>
                <span class="small text-muted-2"><i class="bi bi-check-circle-fill text-accent me-1"></i>Review growth</span>
                <span class="small text-muted-2"><i class="bi bi-check-circle-fill text-accent me-1"></i>Simple monthly pricing</span>
            </div>
        </div>
    </div>

    <section id="what-we-do" class="sv-section">
        <div class="container">
            <div class="text-center mx-auto mb-5 sv-reveal" style="max-width: 680px;">
                <span class="sv-section-eyebrow">What We Do</span>
                <h2 class="sv-section-title">Everything You Need to Build Trust &amp; Grow</h2>
                <p class="text-muted-2 mt-2">Not a website developer. Not a survey tool. A growth partner for your business.</p>
            </div>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="sv-service-card sv-reveal">
                        <div class="sv-service-icon"><i class="bi bi-window"></i></div>
                        <h3 class="h5">Professional Website</h3>
                        <p class="text-muted-2 mb-0">Give customers a modern place to discover your business, services, location and contact details.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="sv-service-card sv-reveal sv-reveal-delay-1">
                        <div class="sv-service-icon"><i class="bi bi-clipboard-data"></i></div>
                        <h3 class="h5">Customer Feedback</h3>
                        <p class="text-muted-2 mb-0">Collect feedback after every visit using QR codes and simple mobile surveys.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="sv-service-card sv-reveal sv-reveal-delay-2">
                        <div class="sv-service-icon"><i class="bi bi-star-fill"></i></div>
                        <h3 class="h5">Review Growth</h3>
                        <p class="text-muted-2 mb-0">Give every customer an easy way to share feedback, and make public review requests easy when the moment is right.</p>
                    </div>
                </div>
            </div>
            <p class="text-center fw-semibold mt-5 mb-0 sv-reveal">One simple platform &mdash; a website on its own, feedback tools, or both together. No complicated software.</p>
        </div>
    </section>

    <section id="why-survyra" class="sv-section sv-section-alt">
        <div class="container">
            <div class="text-center mx-auto mb-5 sv-reveal" style="max-width: 640px;">
                <span class="sv-section-eyebrow">Why Survyra</span>
                <h2 class="sv-section-title">Why Small Businesses Choose Survyra</h2>
            </div>
            <div class="sv-why-strip sv-reveal">
                <div class="sv-why-item">
                    <i class="bi bi-phone"></i>
                    <div>
                        <div class="fw-semibold">Easy for Customers</div>
                        <div class="text-muted-2 small">No app. No login. Just scan and respond.</div>
                    </div>
                </div>
                <div class="sv-why-item">
                    <i class="bi bi-speedometer2"></i>
                    <div>
                        <div class="fw-semibold">Easy for Owners</div>
                        <div class="text-muted-2 small">One simple dashboard for feedback and reviews.</div>
                    </div>
                </div>
                <div class="sv-why-item">
                    <i class="bi bi-geo-alt"></i>
                    <div>
                        <div class="fw-semibold">Built for Local Businesses</div>
                        <div class="text-muted-2 small">Designed around salons, restaurants, clinics and service businesses.</div>
                    </div>
                </div>
                <div class="sv-why-item">
                    <i class="bi bi-window"></i>
                    <div>
                        <div class="fw-semibold">Website Included</div>
                        <div class="text-muted-2 small">A professional online presence alongside your feedback system.</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="about" class="sv-section">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8 text-center sv-reveal">
                    <span class="sv-section-eyebrow">Why We Built Survyra</span>
                    <h2 class="sv-section-title">Turning Everyday Feedback Into Growth</h2>
                    <p class="lead mt-3 text-muted-2">Small businesses hear from their customers every day &mdash; but most don't have a simple way to capture, understand and act on that feedback.</p>
                    <p class="text-muted-2">Survyra brings a professional website, customer feedback and review growth together in one simple platform built for local businesses, so owners spend less time juggling tools and more time running their business.</p>
                    <p class="fw-semibold mt-4 mb-0">Our goal: help small businesses turn better customer experiences into sustainable growth.</p>
                </div>
            </div>
        </div>
    </section>

    <section id="how-it-works" class="sv-section">
        <div class="container">
            <div class="text-center mx-auto mb-5 sv-reveal" style="max-width: 640px;">
                <span class="sv-section-eyebrow">How It Works</span>
                <h2 class="sv-section-title">How Survyra Works</h2>
                <p class="text-muted-2 mt-2">From a customer visit to a 5-star review, in six simple steps.</p>
            </div>
            <div class="sv-steps sv-reveal">
                <div class="sv-step">
                    <div class="sv-step-num">1</div>
                    <i class="bi bi-window fs-4 text-primary"></i>
                    <div class="fw-semibold mt-2">Website</div>
                </div>
                <div class="sv-step-connector"><i class="bi bi-arrow-right"></i></div>
                <div class="sv-step">
                    <div class="sv-step-num">2</div>
                    <i class="bi bi-qr-code fs-4 text-primary"></i>
                    <div class="fw-semibold mt-2">QR / Link</div>
                </div>
                <div class="sv-step-connector"><i class="bi bi-arrow-right"></i></div>
                <div class="sv-step">
                    <div class="sv-step-num">3</div>
                    <i class="bi bi-clipboard-data fs-4 text-primary"></i>
                    <div class="fw-semibold mt-2">Survey</div>
                </div>
                <div class="sv-step-connector"><i class="bi bi-arrow-right"></i></div>
                <div class="sv-step">
                    <div class="sv-step-num">4</div>
                    <i class="bi bi-emoji-smile fs-4 text-primary"></i>
                    <div class="fw-semibold mt-2">Rating</div>
                </div>
                <div class="sv-step-connector"><i class="bi bi-arrow-right"></i></div>
                <div class="sv-step">
                    <div class="sv-step-num">5</div>
                    <i class="bi bi-chat-heart fs-4 text-primary"></i>
                    <div class="fw-semibold mt-2">Review Request</div>
                </div>
                <div class="sv-step-connector"><i class="bi bi-arrow-right"></i></div>
                <div class="sv-step">
                    <div class="sv-step-num">6</div>
                    <i class="bi bi-graph-up-arrow fs-4 text-primary"></i>
                    <div class="fw-semibold mt-2">Dashboard</div>
                </div>
            </div>
        </div>
    </section>

    <section id="try-demo" class="sv-section">
        <div class="container">
            <div class="text-center mx-auto mb-5 sv-reveal" style="max-width: 640px;">
                <span class="sv-section-eyebrow">Product Demo</span>
                <h2 class="sv-section-title">See What Your Customers See</h2>
                <p class="text-muted-2 mt-2">A live, working preview &mdash; rate it below and watch the flow branch in real time.</p>
            </div>

            <div class="row g-4 mb-5 text-center sv-reveal">
                <div class="col-md-4">
                    <div class="sv-step-num mx-auto">1</div>
                    <div class="fw-semibold mt-2">Customer Visits</div>
                    <div class="text-muted-2 small">Scans a QR code at checkout or on the table</div>
                </div>
                <div class="col-md-4">
                    <div class="sv-step-num mx-auto">2</div>
                    <div class="fw-semibold mt-2">Customer Feedback</div>
                    <div class="text-muted-2 small">Rates the visit and answers a short survey</div>
                </div>
                <div class="col-md-4">
                    <div class="sv-step-num mx-auto">3</div>
                    <div class="fw-semibold mt-2">Business Dashboard</div>
                    <div class="text-muted-2 small">Rating, feedback and reviews update instantly</div>
                </div>
            </div>

            <div class="sv-demo-frame sv-reveal" data-demo-widget>
                <div class="sv-demo-bar">
                    <i class="bi bi-qr-code"></i> demo.survyra.com/glow-salon
                </div>

                <div class="sv-demo-screen active" data-demo-screen="rate">
                    <div class="text-center mb-4">
                        <div class="fw-semibold">Glow Salon &amp; Spa</div>
                        <div class="small text-muted-2">How was your visit today?</div>
                    </div>
                    <div class="sv-star-rating justify-content-center mb-4">
                        <i class="bi bi-star-fill sv-star" data-star-value="1"></i>
                        <i class="bi bi-star-fill sv-star" data-star-value="2"></i>
                        <i class="bi bi-star-fill sv-star" data-star-value="3"></i>
                        <i class="bi bi-star-fill sv-star" data-star-value="4"></i>
                        <i class="bi bi-star-fill sv-star" data-star-value="5"></i>
                    </div>
                    <label class="form-label small">Anything you'd like to add? (optional)</label>
                    <textarea class="form-control mb-3" rows="2" placeholder="It was great, but the wait was a bit long..." data-demo-feedback></textarea>
                    <button type="button" class="btn btn-primary w-100" data-demo-submit disabled>Submit Feedback</button>
                </div>

                <div class="sv-demo-screen" data-demo-screen="review">
                    <div class="text-center">
                        <div class="sv-demo-success-icon"><i class="bi bi-check-lg"></i></div>
                        <h5>Thanks for the kind words!</h5>
                        <p class="text-muted-2 small">Would you mind sharing that on Google? It helps other customers find us.</p>
                        <button type="button" class="btn btn-accent w-100 mb-2" disabled><i class="bi bi-google me-1"></i> Leave a Google Review</button>
                        <button type="button" class="btn btn-link btn-sm text-muted-2" data-demo-continue>Maybe later &rarr;</button>
                    </div>
                </div>

                <div class="sv-demo-screen" data-demo-screen="private">
                    <div class="text-center">
                        <div class="sv-demo-success-icon" style="background: rgba(242, 169, 59, 0.15); color: #b3781a;"><i class="bi bi-envelope-heart"></i></div>
                        <h5>Thanks for letting us know</h5>
                        <p class="text-muted-2 small">This goes straight to the owner so they can follow up and make it right.</p>
                        <button type="button" class="btn btn-primary w-100" data-demo-continue>Send to Owner &amp; Continue</button>
                    </div>
                </div>

                <div class="sv-demo-screen" data-demo-screen="dashboard">
                    <div class="small text-muted-2 mb-2">Business Owner Dashboard</div>
                    <div class="row g-3 text-center mb-3">
                        <div class="col-6">
                            <div class="sv-dashboard-number" data-dashboard-avg>0.0</div>
                            <div class="small text-muted-2">Avg. Rating</div>
                        </div>
                        <div class="col-6">
                            <div class="sv-dashboard-number" data-dashboard-count>0</div>
                            <div class="small text-muted-2">Responses</div>
                        </div>
                    </div>
                    <div class="d-flex flex-column gap-2 mb-3">
                        @for ($i = 5; $i >= 1; $i--)
                            <div class="d-flex align-items-center gap-2">
                                <span class="small" style="width: 14px;">{{ $i }}</span>
                                <i class="bi bi-star-fill small text-accent"></i>
                                <div class="sv-dashboard-bar-track flex-grow-1"><div class="sv-dashboard-bar-fill" data-dashboard-bar="{{ $i }}"></div></div>
                            </div>
                        @endfor
                    </div>
                    <button type="button" class="btn btn-outline-secondary btn-sm w-100" data-demo-restart><i class="bi bi-arrow-counterclockwise me-1"></i> Try Again</button>
                </div>
            </div>

            <div class="row g-4 mt-1">
                <div class="col-md-4 sv-reveal">
                    <div class="small text-muted-2 text-uppercase fw-semibold text-center mb-2" style="letter-spacing:.04em;">Customer Side &middot; Survey</div>
                    <div class="sv-demo-frame sv-mini-frame">
                        <div class="sv-demo-bar"><i class="bi bi-qr-code"></i> demo.survyra.com/glow-salon</div>
                        <div class="p-3 text-center">
                            <div class="fw-semibold small">Glow Salon &amp; Spa</div>
                            <div class="text-muted-2 mb-2" style="font-size: .7rem;">How was your visit?</div>
                            <div class="sv-star-rating justify-content-center mb-3">
                                <i class="bi bi-star-fill sv-star filled"></i>
                                <i class="bi bi-star-fill sv-star filled"></i>
                                <i class="bi bi-star-fill sv-star filled"></i>
                                <i class="bi bi-star-fill sv-star filled"></i>
                                <i class="bi bi-star-fill sv-star filled"></i>
                            </div>
                            <button type="button" class="btn btn-primary btn-sm w-100" disabled>Submit Feedback</button>
                        </div>
                    </div>
                    <p class="text-center text-muted-2 mt-2 mb-0" style="font-size: .7rem;"><i class="bi bi-arrow-up-circle text-accent me-1"></i>Try the live version above</p>
                </div>
                <div class="col-md-4 sv-reveal sv-reveal-delay-1">
                    <div class="small text-muted-2 text-uppercase fw-semibold text-center mb-2" style="letter-spacing:.04em;">Business Side &middot; Dashboard</div>
                    <div class="sv-demo-frame sv-mini-frame">
                        <div class="sv-demo-bar"><i class="bi bi-speedometer2"></i> app.survyra.com/dashboard</div>
                        <div class="p-3">
                            <div class="sv-dash-grid mb-2">
                                <div class="sv-dash-tile"><strong>284</strong><span>Total Responses</span></div>
                                <div class="sv-dash-tile"><strong>4.8</strong><span>Avg. Rating</span></div>
                                <div class="sv-dash-tile sv-dash-tile-good"><strong>247</strong><span>Positive Feedback</span></div>
                                <div class="sv-dash-tile sv-dash-tile-warn"><strong>12</strong><span>Needs Attention</span></div>
                            </div>
                            <div class="sv-dash-tile sv-dash-tile-wide mb-2"><strong>91</strong><span>Review Opportunities</span></div>
                            <div class="small text-muted-2 text-uppercase fw-semibold mb-1" style="font-size:.65rem; letter-spacing:.04em;">Recent Feedback</div>
                            <div class="sv-dash-feedback-item"><span class="text-accent">&#9733;&#9733;&#9733;&#9733;&#9733;</span> "Loved the quick service!"</div>
                            <div class="sv-dash-feedback-item"><span class="text-accent">&#9733;&#9733;&#9733;&#9734;&#9734;</span> "Wait time could be shorter."</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 sv-reveal sv-reveal-delay-2">
                    <div class="small text-muted-2 text-uppercase fw-semibold text-center mb-2" style="letter-spacing:.04em;">Online Presence &middot; Website</div>
                    <div class="sv-demo-frame sv-mini-frame">
                        <div class="sv-demo-bar"><i class="bi bi-globe"></i> yourbusiness.com</div>
                        <div class="p-3">
                            <div class="sv-website-mock-hero mb-2"></div>
                            <div class="fw-semibold small">Glow Salon &amp; Spa</div>
                            <div class="text-muted-2" style="font-size: .7rem;">Modern, mobile-friendly business website</div>
                            <button type="button" class="btn btn-outline-secondary btn-sm w-100 mt-2" disabled>Contact Us</button>
                        </div>
                    </div>
                </div>
            </div>
            <p class="text-center text-muted-2 mt-4 mb-0 sv-reveal" style="font-size: .8rem;">Illustrative previews &mdash; real screenshots from live client sites will replace these as we onboard businesses.</p>

            <div class="text-center mt-4 sv-reveal">
                <a href="#demo" class="btn btn-accent btn-lg px-4">Like what you see? Get My Free Demo</a>
            </div>
        </div>
    </section>

    <section id="before-after" class="sv-section">
        <div class="container">
            <div class="text-center mx-auto mb-5 sv-reveal" style="max-width: 680px;">
                <span class="sv-section-eyebrow">Why It Matters</span>
                <h2 class="sv-section-title">Stop Guessing What Your Customers Think</h2>
            </div>
            <div class="row g-4 justify-content-center">
                <div class="col-md-6 col-lg-5">
                    <div class="sv-compare-card sv-reveal">
                        <div class="sv-compare-label sv-compare-bad"><i class="bi bi-x-circle-fill me-1"></i> Without a Feedback System</div>
                        <ul class="sv-compare-flow">
                            <li>Customer visits</li>
                            <li>Customer leaves</li>
                            <li>You don't know how they felt</li>
                            <li class="text-danger">Maybe they complain publicly</li>
                        </ul>
                    </div>
                </div>
                <div class="col-md-6 col-lg-5">
                    <div class="sv-compare-card sv-reveal sv-reveal-delay-1">
                        <div class="sv-compare-label sv-compare-good"><i class="bi bi-check-circle-fill me-1"></i> With Survyra</div>
                        <ul class="sv-compare-flow">
                            <li>Customer visits</li>
                            <li>Scans QR / gets a link</li>
                            <li>Shares quick feedback</li>
                            <li class="text-success">Every response reaches the business, good or bad</li>
                            <li>Business learns and improves</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="review-funnel" class="sv-section sv-section-alt">
        <div class="container">
            <div class="text-center mx-auto mb-5 sv-reveal" style="max-width: 680px;">
                <span class="sv-section-eyebrow">Turn Customer Feedback Into More Reviews</span>
                <h2 class="sv-section-title">Every Customer Has a Story. Capture It.</h2>
            </div>
            <div class="row align-items-center g-5">
                <div class="col-lg-6">
                    <div class="sv-funnel-diagram sv-reveal">
                        <div class="sv-funnel-node">Customer Visit</div>
                        <div class="sv-funnel-arrow"><i class="bi bi-arrow-down"></i></div>
                        <div class="sv-funnel-node">QR / Link</div>
                        <div class="sv-funnel-arrow"><i class="bi bi-arrow-down"></i></div>
                        <div class="sv-funnel-node">Quick Survey</div>
                        <div class="sv-funnel-arrow"><i class="bi bi-arrow-down"></i></div>
                        <div class="sv-funnel-branch">
                            <div class="sv-funnel-branch-item sv-funnel-happy">
                                <div class="fw-semibold"><i class="bi bi-emoji-smile me-1"></i> Positive Experience</div>
                                <div class="small text-muted-2 mt-1">Review Invitation Shown</div>
                            </div>
                            <div class="sv-funnel-branch-item sv-funnel-unhappy">
                                <div class="fw-semibold"><i class="bi bi-envelope-heart me-1"></i> Needs Attention</div>
                                <div class="small text-muted-2 mt-1">Business Notified Directly</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 sv-reveal sv-reveal-delay-1">
                    <p class="lead">Every customer who completes the survey has their feedback captured in full by the business &mdash; nothing is hidden or filtered out.</p>
                    <p class="text-muted-2">When a customer shares a great experience, they're invited to turn it into a public review. When a customer flags a concern, the business is notified directly so it can follow up quickly. Every customer is heard, and every response reaches the business.</p>
                </div>
            </div>
        </div>
    </section>

    <section id="business-types" class="sv-section">
        <div class="container">
            <div class="text-center mx-auto mb-5 sv-reveal" style="max-width: 680px;">
                <span class="sv-section-eyebrow">Who It's For</span>
                <h2 class="sv-section-title">Built for Businesses That Depend on Reviews</h2>
            </div>
            <div class="row g-4">
                @foreach ($businessTypes as $index => $type)
                    <div class="col-md-6 col-lg-4">
                        <div class="sv-service-card sv-reveal sv-reveal-delay-{{ ($index % 3) + 1 }} h-100">
                            <div class="sv-service-icon"><i class="bi {{ $type['icon'] }}"></i></div>
                            <h3 class="h6">{{ $type['name'] }}</h3>
                            <p class="text-muted-2 small mb-0">{{ $type['description'] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <section id="differentiation" class="sv-section">
        <div class="container">
            <div class="text-center mx-auto mb-5 sv-reveal" style="max-width: 680px;">
                <span class="sv-section-eyebrow">Why Survyra</span>
                <h2 class="sv-section-title">Why Survyra Instead of Separate Tools?</h2>
                <p class="text-muted-2 mt-2">One simple system instead of managing multiple vendors.</p>
            </div>
            <div class="table-responsive sv-reveal">
                <table class="table sv-compare-table align-middle text-center mx-auto" style="max-width: 720px;">
                    <thead>
                        <tr>
                            <th scope="col" class="text-start"><span class="visually-hidden">Feature</span></th>
                            <th scope="col">Survyra</th>
                            <th scope="col">Website Agency</th>
                            <th scope="col">Survey Tool</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <th scope="row" class="text-start fw-normal">Business Website</th>
                            <td><i class="bi bi-check-circle-fill text-accent"></i></td>
                            <td><i class="bi bi-check-circle-fill text-accent"></i></td>
                            <td>&mdash;</td>
                        </tr>
                        <tr>
                            <th scope="row" class="text-start fw-normal">Customer Surveys</th>
                            <td><i class="bi bi-check-circle-fill text-accent"></i></td>
                            <td>&mdash;</td>
                            <td><i class="bi bi-check-circle-fill text-accent"></i></td>
                        </tr>
                        <tr>
                            <th scope="row" class="text-start fw-normal">QR Feedback</th>
                            <td><i class="bi bi-check-circle-fill text-accent"></i></td>
                            <td>&mdash;</td>
                            <td><i class="bi bi-check-circle-fill text-accent"></i></td>
                        </tr>
                        <tr>
                            <th scope="row" class="text-start fw-normal">Review Growth</th>
                            <td><i class="bi bi-check-circle-fill text-accent"></i></td>
                            <td>&mdash;</td>
                            <td>&mdash;</td>
                        </tr>
                        <tr>
                            <th scope="row" class="text-start fw-normal">Dashboard</th>
                            <td><i class="bi bi-check-circle-fill text-accent"></i></td>
                            <td class="text-muted-2 small">Sometimes</td>
                            <td><i class="bi bi-check-circle-fill text-accent"></i></td>
                        </tr>
                        <tr>
                            <th scope="row" class="text-start fw-normal">Simple, Flexible Pricing</th>
                            <td><i class="bi bi-check-circle-fill text-accent"></i></td>
                            <td>&mdash;</td>
                            <td>&mdash;</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    <section id="trust-security" class="sv-section">
        <div class="container">
            <div class="row align-items-center g-5">
                <div class="col-lg-6 sv-reveal">
                    <span class="sv-section-eyebrow">Trust &amp; Privacy</span>
                    <h2 class="sv-section-title">Your Customer Data Matters</h2>
                    <p class="text-muted-2 mt-2">Because Survyra collects real customer feedback on your behalf, how that data is handled matters as much as the feedback itself.</p>
                </div>
                <div class="col-lg-6">
                    <ul class="list-unstyled sv-reveal sv-reveal-delay-1">
                        <li class="d-flex gap-2 mb-3"><i class="bi bi-shield-lock-fill text-accent"></i><span>Customer contact details are encrypted in storage, not stored as plain text.</span></li>
                        <li class="d-flex gap-2 mb-3"><i class="bi bi-person-badge fs-6 text-accent"></i><span>Business-level access controls &mdash; only authorized team members can view a business's data.</span></li>
                        <li class="d-flex gap-2 mb-3"><i class="bi bi-eye-slash-fill text-accent"></i><span>Privacy-focused feedback collection &mdash; customers aren't required to create an account or share more than necessary.</span></li>
                        <li class="d-flex gap-2"><i class="bi bi-file-earmark-text-fill text-accent"></i><span>Read our <a href="{{ route('legal.privacy') }}" class="fw-semibold">Privacy Policy</a> and <a href="{{ route('legal.terms') }}" class="fw-semibold">Terms of Service</a>.</span></li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <section id="whats-included" class="sv-section">
        <div class="container">
            <div class="row align-items-center g-5">
                <div class="col-lg-5 sv-reveal">
                    <span class="sv-section-eyebrow">What You Get</span>
                    <h2 class="sv-section-title">Your Survyra Setup Includes</h2>
                    <p class="text-muted-2 mt-2">Everything below is included in the Complete plan &mdash; one price, no surprises.</p>
                </div>
                <div class="col-lg-7">
                    <div class="row sv-reveal sv-reveal-delay-1">
                        @foreach ([
                            'Professional website', 'Custom domain', 'Hosting', 'Mobile optimization',
                            'Customer survey', 'QR code', 'Feedback dashboard', 'Review funnel',
                            'Basic SEO', 'Monthly report', 'Basic updates',
                        ] as $item)
                            <div class="col-sm-6 mb-2"><i class="bi bi-check-circle-fill text-accent me-2"></i>{{ $item }}</div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="website-showcase" class="sv-section">
        <div class="container">
            <div class="text-center mx-auto mb-5 sv-reveal" style="max-width: 680px;">
                <span class="sv-section-eyebrow">Your Online Presence</span>
                <h2 class="sv-section-title">Your Business Deserves a Better Online Presence</h2>
                <p class="text-muted-2 mt-2">We build modern, mobile-first websites designed to turn visitors into customers. Choose a style and customize it with your branding, content and business information.</p>
            </div>
            <div class="row g-4">
                @foreach ([
                    ['num' => '01', 'name' => 'Modern / Local', 'desc' => 'Clean and approachable — a great fit for most local businesses.', 'available' => true, 'color' => '#1E3A5F'],
                    ['num' => '02', 'name' => 'Premium / Editorial', 'desc' => 'Polished and editorial — suited to premium or established businesses.', 'available' => true, 'color' => '#142943'],
                    ['num' => '03', 'name' => 'Luxury Dark', 'desc' => 'Bold and moody — for restaurants, bars and creative businesses.', 'available' => false, 'color' => '#14161A'],
                    ['num' => '04', 'name' => 'Modern SaaS', 'desc' => 'Sharp and product-led — for consultants and service businesses.', 'available' => false, 'color' => '#3B82F6'],
                    ['num' => '05', 'name' => 'Minimal Mobile-First', 'desc' => 'Stripped-back and fast — built mobile-first for on-the-go customers.', 'available' => false, 'color' => '#5b6570'],
                ] as $index => $style)
                    <div class="col-md-6 col-lg-4">
                        <div class="sv-style-card sv-reveal sv-reveal-delay-{{ ($index % 3) + 1 }}">
                            <div class="sv-style-swatch" style="background: {{ $style['color'] }};">
                                <div class="sv-style-swatch-nav">
                                    <span class="sv-style-swatch-dot"></span>
                                    <span class="sv-style-swatch-dot"></span>
                                    <span class="sv-style-swatch-dot"></span>
                                    <span class="sv-style-swatch-nav-pill"></span>
                                </div>
                                <div>
                                    <div class="sv-style-swatch-line sv-style-swatch-line-lg"></div>
                                    <div class="sv-style-swatch-line sv-style-swatch-line-sm"></div>
                                    <div class="sv-style-swatch-btn"></div>
                                </div>
                            </div>
                            <div class="d-flex align-items-center justify-content-between mt-3">
                                <div class="text-muted-2 small fw-semibold">{{ $style['num'] }}</div>
                                @if ($style['available'])
                                    <span class="badge text-bg-success">Available Now</span>
                                @else
                                    <span class="badge text-bg-secondary">Coming Soon</span>
                                @endif
                            </div>
                            <h3 class="h6 mt-2 mb-1">{{ $style['name'] }}</h3>
                            <p class="text-muted-2 small mb-0">{{ $style['desc'] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <section id="pricing" class="sv-section sv-section-alt">
        <div class="container">
            <div class="text-center mx-auto mb-5 sv-reveal" style="max-width: 680px;">
                <span class="sv-section-eyebrow">Pricing</span>
                <h2 class="sv-section-title">Simple Plans. No Surprises.</h2>
                <p class="text-muted-2 mt-2">Choose how you want to start &mdash; a website on its own, feedback &amp; review tools for a site you already have, or everything in one monthly plan.</p>
            </div>

            {{-- Track 1: Website, one-time --}}
            <div class="sv-pricing-track sv-reveal">
                <div class="sv-pricing-track-heading">
                    <span class="sv-track-badge sv-track-badge-onetime">One-Time Payment</span>
                    <h3 class="h4 mt-2 mb-1">Website</h3>
                    <p class="text-muted-2 small mb-0">A professional website, paid once &mdash; no subscription required.</p>
                </div>
                <div class="row g-4 justify-content-center mt-1">
                    @foreach ($websitePlans as $index => $plan)
                        <div class="col-md-6 col-lg-4">
                            <div class="sv-plan-card sv-reveal sv-reveal-delay-{{ $index + 1 }} {{ $plan['highlighted'] ? 'sv-plan-highlighted' : '' }}">
                                @if ($plan['highlighted'])
                                    <span class="sv-plan-badge">Most Popular</span>
                                @endif
                                <div class="text-muted-2 fw-semibold text-uppercase small">{{ $plan['name'] }}</div>
                                <div class="sv-plan-price mt-1 mb-1">
                                    @if ($plan['price'])
                                        &#8377;{{ number_format($plan['price']) }}<small>one-time</small>
                                    @else
                                        {{ $plan['priceLabel'] }}<small>one-time</small>
                                    @endif
                                </div>
                                <p class="text-muted-2 small mb-3">{{ $plan['tagline'] }}</p>
                                <ul>
                                    @foreach ($plan['features'] as $feature)
                                        <li><i class="bi bi-check-circle-fill"></i><span>{{ $feature }}</span></li>
                                    @endforeach
                                </ul>
                                @if ($plan['renewal'])
                                    <p class="text-muted-2 mb-3" style="font-size: .75rem;"><i class="bi bi-info-circle me-1"></i>{{ $plan['renewal'] }}</p>
                                @endif
                                <a href="#demo" class="btn {{ $plan['highlighted'] ? 'btn-primary' : 'btn-outline-secondary' }} w-100 mt-2">Get My Free Demo</a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Track 2: Feedback & Growth, monthly --}}
            <div class="sv-pricing-track sv-reveal">
                <div class="sv-pricing-track-heading">
                    <span class="sv-track-badge sv-track-badge-monthly">Monthly</span>
                    <h3 class="h4 mt-2 mb-1">Feedback &amp; Growth</h3>
                    <p class="text-muted-2 small mb-0">Already have a website? Add customer feedback and review growth.</p>
                </div>
                <div class="row g-4 justify-content-center mt-1">
                    @foreach ($feedbackPlans as $index => $plan)
                        <div class="col-md-6 col-lg-4">
                            <div class="sv-plan-card sv-reveal sv-reveal-delay-{{ $index + 1 }} {{ $plan['highlighted'] ? 'sv-plan-highlighted' : '' }}">
                                @if ($plan['highlighted'])
                                    <span class="sv-plan-badge">Most Popular</span>
                                @endif
                                <div class="text-muted-2 fw-semibold text-uppercase small">{{ $plan['name'] }}</div>
                                <div class="sv-plan-price mt-1 mb-1">&#8377;{{ number_format($plan['price']) }}<small>/month</small></div>
                                <p class="text-muted-2 small mb-3">{{ $plan['tagline'] }}</p>
                                <ul>
                                    @foreach ($plan['features'] as $feature)
                                        <li><i class="bi bi-check-circle-fill"></i><span>{{ $feature }}</span></li>
                                    @endforeach
                                </ul>
                                <a href="#demo" class="btn {{ $plan['highlighted'] ? 'btn-primary' : 'btn-outline-secondary' }} w-100 mt-2">Get My Free Demo</a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Track 3: Complete, monthly bundle + Founding 10 offer --}}
            <div class="sv-pricing-track sv-reveal">
                <div class="sv-pricing-track-heading">
                    <span class="sv-track-badge sv-track-badge-monthly">Monthly &middot; All-in-One</span>
                    <h3 class="h4 mt-2 mb-1">Complete</h3>
                    <p class="text-muted-2 small mb-0">Don't want to pay for a website upfront? Get everything in one recurring plan.</p>
                </div>
                <div class="row justify-content-center mt-1">
                    <div class="col-md-8 col-lg-6">
                        <div class="sv-plan-card sv-plan-highlighted sv-reveal sv-reveal-delay-1">
                            <span class="sv-plan-badge">Recommended</span>
                            <div class="text-muted-2 fw-semibold text-uppercase small">{{ $completePlan['name'] }}</div>
                            <div class="sv-plan-price mt-1 mb-1">&#8377;{{ number_format($completePlan['price']) }}<small>/month</small></div>
                            <p class="text-muted-2 small mb-3">{{ $completePlan['tagline'] }}</p>
                            <ul>
                                @foreach ($completePlan['features'] as $feature)
                                    <li><i class="bi bi-check-circle-fill"></i><span>{{ $feature }}</span></li>
                                @endforeach
                            </ul>
                            <a href="#demo" class="btn btn-primary w-100 mt-2">Get My Free Demo</a>
                        </div>
                    </div>
                </div>
                <div class="sv-founding-banner sv-reveal mt-4">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                        <div>
                            <div class="fw-bold fs-5"><i class="bi bi-stars text-accent me-1"></i> Founding 10</div>
                            <div class="mt-1">Get Complete at <strong>&#8377;{{ number_format($completePlan['foundingPrice']) }}/month</strong> for your first 3 months instead of &#8377;{{ number_format($completePlan['price']) }}/month. No setup fee.</div>
                        </div>
                        <a href="#demo" class="btn btn-accent btn-lg">Claim Founding Spot</a>
                    </div>
                    <div class="small mt-2" style="color: rgba(255,255,255,0.7);">Only 10 businesses.</div>
                </div>
            </div>
        </div>
    </section>

    <section id="timeline" class="sv-section">
        <div class="container">
            <div class="text-center mx-auto mb-5 sv-reveal" style="max-width: 640px;">
                <span class="sv-section-eyebrow">What Happens Next</span>
                <h2 class="sv-section-title">From Signup to Live in About a Week</h2>
            </div>
            <div class="sv-timeline sv-reveal">
                <div class="sv-timeline-item"><div class="sv-timeline-day">Day 1</div><div>Business Details</div></div>
                <div class="sv-timeline-item"><div class="sv-timeline-day">Day 2</div><div>Website Setup</div></div>
                <div class="sv-timeline-item"><div class="sv-timeline-day">Day 3</div><div>Survey Setup</div></div>
                <div class="sv-timeline-item"><div class="sv-timeline-day">Day 4</div><div>QR + Review Funnel</div></div>
                <div class="sv-timeline-item"><div class="sv-timeline-day">Day 5</div><div>Domain + Branding</div></div>
                <div class="sv-timeline-item"><div class="sv-timeline-day">Day 6</div><div>Testing</div></div>
                <div class="sv-timeline-item sv-timeline-final"><div class="sv-timeline-day">Day 7</div><div><i class="bi bi-rocket-takeoff-fill text-accent me-1"></i>Go Live</div></div>
            </div>
        </div>
    </section>

    <section id="glance" class="sv-section">
        <div class="container">
            <div class="text-center mx-auto mb-5 sv-reveal" style="max-width: 640px;">
                <span class="sv-section-eyebrow">Quick Answer</span>
                <h2 class="sv-section-title">Survyra at a Glance</h2>
            </div>
            <div class="row g-3 justify-content-center sv-reveal" style="max-width: 900px; margin: 0 auto;">
                <div class="col-md-6 col-lg-4"><div class="sv-glance-item"><div class="sv-glance-q">What is Survyra?</div><div class="text-muted-2 small">A customer feedback, survey and review-growth platform for small businesses.</div></div></div>
                <div class="col-md-6 col-lg-4"><div class="sv-glance-item"><div class="sv-glance-q">Who is it for?</div><div class="text-muted-2 small">Salons, restaurants, clinics, cafes, gyms and local service businesses.</div></div></div>
                <div class="col-md-6 col-lg-4"><div class="sv-glance-item"><div class="sv-glance-q">What does it include?</div><div class="text-muted-2 small">Website, surveys, QR feedback, review tools and analytics.</div></div></div>
                <div class="col-md-6 col-lg-4"><div class="sv-glance-item"><div class="sv-glance-q">Do customers need an app?</div><div class="text-muted-2 small">No. Surveys work directly from a mobile browser.</div></div></div>
                <div class="col-md-6 col-lg-4"><div class="sv-glance-item"><div class="sv-glance-q">Can I use my own domain?</div><div class="text-muted-2 small">Yes, included with every website plan.</div></div></div>
                <div class="col-md-6 col-lg-4"><div class="sv-glance-item"><div class="sv-glance-q">Starting price?</div><div class="text-muted-2 small">₹999/month, or ₹4,999 one-time for a website.</div></div></div>
            </div>
        </div>
    </section>

    <section id="faq" class="sv-section sv-section-alt">
        <div class="container">
            <div class="text-center mx-auto mb-5 sv-reveal" style="max-width: 640px;">
                <span class="sv-section-eyebrow">FAQ</span>
                <h2 class="sv-section-title">Frequently Asked Questions</h2>
            </div>
            <div class="accordion sv-faq-accordion mx-auto sv-reveal" id="svFaqAccordion" style="max-width: 780px;">
                @foreach ($faqs as $index => $faq)
                    <div class="accordion-item">
                        <h3 class="accordion-header" id="svFaqHeading{{ $index }}">
                            <button class="accordion-button {{ $index === 0 ? '' : 'collapsed' }}" type="button" data-bs-toggle="collapse" data-bs-target="#svFaqCollapse{{ $index }}" aria-expanded="{{ $index === 0 ? 'true' : 'false' }}" aria-controls="svFaqCollapse{{ $index }}">
                                {{ $faq['q'] }}
                            </button>
                        </h3>
                        <div id="svFaqCollapse{{ $index }}" class="accordion-collapse collapse {{ $index === 0 ? 'show' : '' }}" aria-labelledby="svFaqHeading{{ $index }}" data-bs-parent="#svFaqAccordion">
                            <div class="accordion-body text-muted-2">{{ $faq['a'] }}</div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <script type="application/ld+json">
        {!! json_encode([
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'mainEntity' => collect($faqs)->map(fn ($faq) => [
                '@type' => 'Question',
                'name' => $faq['q'],
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => $faq['a'],
                ],
            ])->values()->all(),
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
    </script>

    <section id="demo" class="sv-section sv-lead-section">
        <div class="container">
            <div class="row g-5 align-items-center">
                <div class="col-lg-5">
                    <span class="sv-section-eyebrow">Get My Free Demo</span>
                    <h2 class="sv-section-title text-white">Ready to Turn Customer Feedback Into Growth?</h2>
                    <p class="mt-3" style="color: rgba(255,255,255,0.75);">Tell us a bit about your business and we'll send over a quick walkthrough &mdash; no commitment, no pressure.</p>
                    <ul class="list-unstyled mt-4" style="color: rgba(255,255,255,0.85);">
                        <li class="mb-2"><i class="bi bi-check-circle-fill text-accent me-2"></i>A short recorded walkthrough within 24 hours</li>
                        <li class="mb-2"><i class="bi bi-check-circle-fill text-accent me-2"></i>No credit card, no obligation</li>
                        <li class="mb-2"><i class="bi bi-check-circle-fill text-accent me-2"></i>Optional 7&ndash;14 day trial with your own customers</li>
                    </ul>
                </div>
                <div class="col-lg-7">
                    <div class="sv-lead-card sv-reveal">
                        <form method="POST" action="{{ route('leads.store') }}">
                            @csrf

                            {{-- Honeypot: left blank by humans, filled by bots --}}
                            <div style="position:absolute; left:-9999px;" aria-hidden="true">
                                <label for="company_website">Leave this field blank</label>
                                <input type="text" name="company_website" id="company_website" tabindex="-1" autocomplete="off">
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <x-form-input name="name" label="Your Name" required autocomplete="name" />
                                </div>
                                <div class="col-md-6">
                                    <x-form-input name="business_name" label="Business Name" required autocomplete="organization" />
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <x-form-select name="category" label="Business Type" placeholder="Select one" :options="[
                                        'Salon' => 'Salon',
                                        'Restaurant' => 'Restaurant / Cafe',
                                        'Clinic' => 'Clinic / Healthcare',
                                        'Gym' => 'Gym / Fitness',
                                        'Other' => 'Other',
                                    ]" />
                                </div>
                                <div class="col-md-6">
                                    <x-form-input name="phone" label="Phone / WhatsApp" type="tel" required autocomplete="tel" />
                                </div>
                            </div>
                            <x-form-input name="email" label="Email" type="email" required autocomplete="email" />

                            <div class="mb-3">
                                <label class="form-label d-block">Preferred contact method</label>
                                @foreach (['whatsapp' => 'WhatsApp', 'phone' => 'Phone', 'email' => 'Email'] as $value => $label)
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="preferred_contact" id="preferred_contact_{{ $value }}" value="{{ $value }}" @checked(old('preferred_contact') === $value)>
                                        <label class="form-check-label" for="preferred_contact_{{ $value }}">{{ $label }}</label>
                                    </div>
                                @endforeach
                            </div>

                            <div class="mb-3">
                                <label class="form-label d-block">What are you interested in?</label>
                                @foreach (['website' => 'Website', 'feedback' => 'Customer Feedback', 'reviews' => 'Review Growth', 'complete' => 'Complete Package'] as $value => $label)
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="checkbox" name="interests[]" id="interest_{{ $value }}" value="{{ $value }}" @checked(collect(old('interests', []))->contains($value))>
                                        <label class="form-check-label" for="interest_{{ $value }}">{{ $label }}</label>
                                    </div>
                                @endforeach
                            </div>

                            <div class="mb-3">
                                <label for="message" class="form-label">Anything specific you'd like us to know?</label>
                                <textarea name="message" id="message" rows="3" class="form-control {{ $errors->has('message') ? 'is-invalid' : '' }}">{{ old('message') }}</textarea>
                                @error('message')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <button type="submit" class="btn btn-accent btn-lg w-100">Get My Free Demo</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <footer class="sv-footer">
        <div class="container">
            <div class="row gy-4">
                <div class="col-lg-4 col-md-6">
                    <div class="sv-brand h5 mb-2" style="color:#fff;">Survyra</div>
                    <p class="small mb-0">Turn customer feedback into more reviews and more business &mdash; a customer feedback and review-growth platform for salons, restaurants, clinics and local businesses.</p>
                </div>
                <div class="col-lg-2 col-md-6">
                    <div class="sv-footer-heading">Product</div>
                    <ul class="list-unstyled small">
                        <li><a href="#what-we-do">Customer Feedback</a></li>
                        <li><a href="#try-demo">Surveys</a></li>
                        <li><a href="#review-funnel">Review Growth</a></li>
                        <li><a href="#how-it-works">QR Feedback</a></li>
                        <li><a href="#website-showcase">Business Websites</a></li>
                    </ul>
                </div>
                <div class="col-lg-2 col-md-6">
                    <div class="sv-footer-heading">Businesses</div>
                    <ul class="list-unstyled small">
                        <li><a href="#business-types">Salons &amp; Spas</a></li>
                        <li><a href="#business-types">Restaurants &amp; Cafes</a></li>
                        <li><a href="#business-types">Clinics</a></li>
                        <li><a href="#business-types">Gyms</a></li>
                        <li><a href="#business-types">Hotels</a></li>
                    </ul>
                </div>
                <div class="col-lg-2 col-md-6">
                    <div class="sv-footer-heading">Company</div>
                    <ul class="list-unstyled small">
                        <li><a href="#about">About</a></li>
                        <li><a href="#pricing">Pricing</a></li>
                        <li><a href="#faq">FAQ</a></li>
                        <li><a href="#demo">Contact</a></li>
                        <li><a href="{{ route('admin.login') }}">Admin Login</a></li>
                    </ul>
                </div>
                <div class="col-lg-2 col-md-6">
                    <div class="sv-footer-heading">Legal</div>
                    <ul class="list-unstyled small">
                        <li><a href="{{ route('legal.privacy') }}">Privacy Policy</a></li>
                        <li><a href="{{ route('legal.terms') }}">Terms of Service</a></li>
                    </ul>
                </div>
            </div>
            <hr class="my-4" style="border-color: rgba(255,255,255,0.1);">
            <div class="small text-center text-md-start">&copy; {{ date('Y') }} Survyra. All rights reserved.</div>
        </div>
    </footer>

    <script src="{{ asset('assets/js/landing.js') }}" defer></script>
</x-public-layout>
