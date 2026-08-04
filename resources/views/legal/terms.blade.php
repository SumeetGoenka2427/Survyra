<x-public-layout title="Terms of Service">
    <nav class="navbar navbar-expand-lg sv-navbar py-3">
        <div class="container">
            <a class="navbar-brand p-0" href="{{ route('home') }}"><x-brand-mark /></a>
            <a href="{{ route('home') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i> Back to Home</a>
        </div>
    </nav>

    <section class="sv-section">
        <div class="container" style="max-width: 760px;">
            <h1 class="mb-4">Terms of Service</h1>
            <div class="alert alert-warning">
                This is a placeholder policy for early-stage use. It should be reviewed and finalized by a qualified professional before Survyra is offered under a formal commercial agreement.
            </div>
            <p class="text-muted-2">These terms describe, in general terms, the basis on which Survyra provides customer survey, feedback and review-growth services to businesses.</p>

            <h2 class="h5 mt-4">Service Plans</h2>
            <p class="text-muted-2">Survyra plans are billed monthly as described on our <a href="{{ route('home') }}#pricing">pricing page</a>. Survey setup and configuration is included per plan; custom integrations and additional functionality are quoted separately.</p>

            <h2 class="h5 mt-4">Trials</h2>
            <p class="text-muted-2">Where offered, trials run for a fixed period stated at the time of signup and do not renew automatically into a paid plan without agreement.</p>

            <h2 class="h5 mt-4">Contact</h2>
            <p class="text-muted-2">Questions about these terms can be sent through the <a href="{{ route('home') }}#demo">contact form</a> on our homepage.</p>
        </div>
    </section>

    <footer class="sv-footer">
        <div class="container small text-center">&copy; {{ date('Y') }} Survyra. All rights reserved.</div>
    </footer>
</x-public-layout>
