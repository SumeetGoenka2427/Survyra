(function () {
    // Scroll-reveal: fade/slide sections in as they enter the viewport.
    const revealEls = document.querySelectorAll('.sv-reveal');
    if (revealEls.length) {
        if ('IntersectionObserver' in window) {
            const observer = new IntersectionObserver((entries) => {
                entries.forEach((entry) => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('is-visible');
                        observer.unobserve(entry.target);
                    }
                });
            }, { threshold: 0.15 });
            revealEls.forEach((el) => observer.observe(el));
        } else {
            revealEls.forEach((el) => el.classList.add('is-visible'));
        }
    }

    // Scroll-spy: highlight the nav link for whichever linked section is in view.
    const navLinks = document.querySelectorAll('.sv-navbar .nav-link[href^="#"]');
    if (navLinks.length && 'IntersectionObserver' in window) {
        const sectionsById = new Map();
        navLinks.forEach((link) => {
            const section = document.querySelector(link.getAttribute('href'));
            if (section) sectionsById.set(section, link);
        });

        if (sectionsById.size) {
            const navObserver = new IntersectionObserver((entries) => {
                entries.forEach((entry) => {
                    const link = sectionsById.get(entry.target);
                    if (!link) return;
                    if (entry.isIntersecting) {
                        navLinks.forEach((l) => l.classList.remove('active'));
                        link.classList.add('active');
                    }
                });
            }, { rootMargin: '-40% 0px -55% 0px' });
            sectionsById.forEach((_, section) => navObserver.observe(section));
        }
    }

    // Mobile sticky CTA: appears once the hero has been scrolled past, hides
    // again once the visitor reaches the demo form (it would be redundant there).
    const stickyCta = document.querySelector('[data-sticky-cta]');
    const hero = document.querySelector('.sv-hero');
    const demoSection = document.getElementById('demo');
    if (stickyCta && hero && 'IntersectionObserver' in window) {
        let heroVisible = true;
        let demoVisible = false;

        const updateStickyCta = () => {
            stickyCta.classList.toggle('is-visible', !heroVisible && !demoVisible);
        };

        new IntersectionObserver((entries) => {
            entries.forEach((entry) => { heroVisible = entry.isIntersecting; });
            updateStickyCta();
        }).observe(hero);

        if (demoSection) {
            new IntersectionObserver((entries) => {
                entries.forEach((entry) => { demoVisible = entry.isIntersecting; });
                updateStickyCta();
            }).observe(demoSection);
        }
    }

    // Interactive "Try It Yourself" demo widget.
    const demoFrame = document.querySelector('[data-demo-widget]');
    if (!demoFrame) return;

    const screens = demoFrame.querySelectorAll('[data-demo-screen]');
    const stars = demoFrame.querySelectorAll('[data-star-value]');
    const submitBtn = demoFrame.querySelector('[data-demo-submit]');
    const continueButtons = demoFrame.querySelectorAll('[data-demo-continue]');
    const restartBtn = demoFrame.querySelector('[data-demo-restart]');
    const feedbackInput = demoFrame.querySelector('[data-demo-feedback]');

    let selectedRating = 0;
    // Seed distribution for a fictional demo business, purely to make the
    // sample dashboard feel alive on first view — not a claim about Survyra itself.
    const ratingTotals = { 5: 7, 4: 3, 3: 1, 2: 1, 1: 0 };

    function showScreen(name) {
        screens.forEach((screen) => {
            screen.classList.toggle('active', screen.dataset.demoScreen === name);
        });
    }

    function paintStars(value) {
        stars.forEach((star) => {
            star.classList.toggle('filled', Number(star.dataset.starValue) <= value);
        });
    }

    stars.forEach((star) => {
        star.addEventListener('mouseenter', () => paintStars(Number(star.dataset.starValue)));
        star.addEventListener('mouseleave', () => paintStars(selectedRating));
        star.addEventListener('click', () => {
            selectedRating = Number(star.dataset.starValue);
            paintStars(selectedRating);
            if (submitBtn) submitBtn.disabled = false;
        });
    });

    if (submitBtn) {
        submitBtn.addEventListener('click', () => {
            if (!selectedRating) return;
            ratingTotals[selectedRating] += 1;
            showScreen(selectedRating >= 4 ? 'review' : 'private');
        });
    }

    continueButtons.forEach((btn) => {
        btn.addEventListener('click', () => {
            showScreen('dashboard');
            animateDashboard();
        });
    });

    if (restartBtn) {
        restartBtn.addEventListener('click', () => {
            selectedRating = 0;
            paintStars(0);
            if (feedbackInput) feedbackInput.value = '';
            if (submitBtn) submitBtn.disabled = true;
            showScreen('rate');
        });
    }

    function animateDashboard() {
        const total = Object.values(ratingTotals).reduce((sum, count) => sum + count, 0);
        const weighted = Object.entries(ratingTotals).reduce(
            (sum, [star, count]) => sum + Number(star) * count,
            0
        );
        const avg = total ? weighted / total : 0;

        const avgEl = demoFrame.querySelector('[data-dashboard-avg]');
        const countEl = demoFrame.querySelector('[data-dashboard-count]');
        if (avgEl) avgEl.textContent = avg.toFixed(1);
        if (countEl) countEl.textContent = total;

        [5, 4, 3, 2, 1].forEach((star) => {
            const bar = demoFrame.querySelector('[data-dashboard-bar="' + star + '"]');
            if (!bar) return;
            const pct = total ? Math.round((ratingTotals[star] / total) * 100) : 0;
            requestAnimationFrame(() => {
                bar.style.width = pct + '%';
            });
        });
    }
})();
