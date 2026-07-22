@php
    $trackedUrl = fn (string $channel) => route('review-click.redirect', ['response' => $response->uuid, 'channel' => $channel]);
@endphp
<div class="text-center">
    <div class="mb-3" style="font-size: 3rem;">
        @if ($rule->sentiment === 'positive')
            🎉
        @elseif ($rule->sentiment === 'negative')
            💙
        @else
            🙏
        @endif
    </div>

    <p class="fs-5 mb-4">{{ $rule->thank_you_message }}</p>

    <div class="d-grid gap-2">
        @if ($rule->show_google_review && $survey->client->google_review_url)
            <a href="{{ $trackedUrl('google_review') }}" target="_blank" rel="noopener" class="btn btn-survyra-primary">
                <i class="bi bi-google"></i> Leave a Google Review
            </a>
        @endif
        @if ($rule->show_facebook && $survey->client->facebook_url)
            <a href="{{ $trackedUrl('facebook') }}" target="_blank" rel="noopener" class="btn btn-outline-secondary">
                <i class="bi bi-facebook"></i> Visit our Facebook
            </a>
        @endif
        @if ($rule->show_website && $survey->client->website)
            <a href="{{ $trackedUrl('website') }}" target="_blank" rel="noopener" class="btn btn-outline-secondary">
                <i class="bi bi-globe"></i> Visit our Website
            </a>
        @endif
        @if ($rule->show_coupon && $rule->coupon_code)
            <div class="alert alert-success">
                Your coupon code: <strong>{{ $rule->coupon_code }}</strong>
            </div>
        @endif

        @if ($rule->show_complaint_form)
            <a href="{{ $trackedUrl('complaint_form') }}" class="btn btn-outline-danger">
                <i class="bi bi-chat-left-text"></i> Tell us what went wrong
            </a>
        @endif
        @if ($rule->show_support_number && $survey->client->support_number)
            <a href="{{ $trackedUrl('support_call') }}" class="btn btn-outline-secondary">
                <i class="bi bi-telephone"></i> Call Support
            </a>
        @endif
        @if ($rule->show_whatsapp_button && $survey->client->whatsapp_number)
            <a href="{{ $trackedUrl('whatsapp') }}" target="_blank" rel="noopener" class="btn btn-success">
                <i class="bi bi-whatsapp"></i> Message us on WhatsApp
            </a>
        @endif
    </div>
</div>
