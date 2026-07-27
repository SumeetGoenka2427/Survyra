<x-public-layout title="Privacy Policy">
    <nav class="navbar navbar-expand-lg sv-navbar py-3">
        <div class="container">
            <a class="navbar-brand sv-brand" href="{{ route('home') }}">Survyra</a>
            <a href="{{ route('home') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i> Back to Home</a>
        </div>
    </nav>

    <section class="sv-section">
        <div class="container" style="max-width: 760px;">
            <h1 class="mb-4">Privacy Policy</h1>
            <div class="alert alert-warning">
                This is a placeholder policy for early-stage use. It should be reviewed and finalized by a qualified professional before Survyra collects real customer or business data at scale.
            </div>
            <p class="text-muted-2">Survyra ("we", "us") provides website, customer feedback and review-growth services for small businesses. This page describes, in general terms, how we handle information submitted through this site and our products.</p>

            <h2 class="h5 mt-4">Information We Collect</h2>
            <p class="text-muted-2">When you request a demo, we collect the details you submit &mdash; your name, business name, business type, phone number, email address and any message you include. When a business uses Survyra's survey and feedback tools, customer responses are collected as configured by that business.</p>

            <h2 class="h5 mt-4">How We Use Information</h2>
            <p class="text-muted-2">Demo request details are used to contact you about Survyra's services. Customer feedback collected on behalf of a business is used to provide that business with ratings, feedback and review-growth features.</p>

            <h2 class="h5 mt-4">Contact</h2>
            <p class="text-muted-2">Questions about this policy can be sent through the <a href="{{ route('home') }}#demo">contact form</a> on our homepage.</p>
        </div>
    </section>

    <footer class="sv-footer">
        <div class="container small text-center">&copy; {{ date('Y') }} Survyra. All rights reserved.</div>
    </footer>
</x-public-layout>
