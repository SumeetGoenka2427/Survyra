@php
    $cards = [
        'positive' => ['title' => 'Positive Feedback', 'color' => 'success'],
        'neutral' => ['title' => 'Neutral Feedback', 'color' => 'secondary'],
        'negative' => ['title' => 'Negative Feedback', 'color' => 'danger'],
    ];
@endphp

<div class="row g-3">
    @foreach ($cards as $sentiment => $meta)
        @php $rule = $survey->thankyouRules->firstWhere('sentiment', $sentiment); @endphp
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-{{ $meta['color'] }}-subtle">
                    <strong>{{ $meta['title'] }}</strong>
                    @if ($rule)
                        <span class="text-muted small">(score {{ $rule->min_score ?? '—' }}–{{ $rule->max_score ?? '—' }})</span>
                    @endif
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.surveys.thankyou-rules.update', [$survey, $sentiment]) }}">
                        @csrf
                        @method('PUT')

                        <div class="mb-2">
                            <label class="form-label">Score Range</label>
                            <div class="d-flex gap-2">
                                <input type="number" name="min_score" class="form-control" placeholder="Min" value="{{ $rule?->min_score }}">
                                <input type="number" name="max_score" class="form-control" placeholder="Max" value="{{ $rule?->max_score }}">
                            </div>
                        </div>

                        <div class="mb-2">
                            <label class="form-label">Message</label>
                            <textarea name="thank_you_message" class="form-control" rows="3">{{ $rule?->thank_you_message }}</textarea>
                        </div>

                        <div class="form-check">
                            <input type="hidden" name="show_google_review" value="0">
                            <input type="checkbox" name="show_google_review" value="1" class="form-check-input"
                                   id="{{ $sentiment }}-google" @checked($rule?->show_google_review)
                                   @disabled($sentiment === 'negative')>
                            <label class="form-check-label" for="{{ $sentiment }}-google">
                                Show Google Review button
                                @if ($sentiment === 'negative')
                                    <span class="text-danger small">(never allowed for negative feedback)</span>
                                @endif
                            </label>
                        </div>
                        <div class="form-check">
                            <input type="hidden" name="show_facebook" value="0">
                            <input type="checkbox" name="show_facebook" value="1" class="form-check-input" id="{{ $sentiment }}-fb" @checked($rule?->show_facebook)>
                            <label class="form-check-label" for="{{ $sentiment }}-fb">Show Facebook link</label>
                        </div>
                        <div class="form-check">
                            <input type="hidden" name="show_instagram" value="0">
                            <input type="checkbox" name="show_instagram" value="1" class="form-check-input" id="{{ $sentiment }}-instagram" @checked($rule?->show_instagram)>
                            <label class="form-check-label" for="{{ $sentiment }}-instagram">Show Instagram link</label>
                        </div>
                        <div class="form-check">
                            <input type="hidden" name="show_website" value="0">
                            <input type="checkbox" name="show_website" value="1" class="form-check-input" id="{{ $sentiment }}-web" @checked($rule?->show_website)>
                            <label class="form-check-label" for="{{ $sentiment }}-web">Show website link</label>
                        </div>
                        <div class="form-check">
                            <input type="hidden" name="show_coupon" value="0">
                            <input type="checkbox" name="show_coupon" value="1" class="form-check-input" id="{{ $sentiment }}-coupon" @checked($rule?->show_coupon)>
                            <label class="form-check-label" for="{{ $sentiment }}-coupon">Show coupon code</label>
                        </div>
                        <input type="text" name="coupon_code" class="form-control form-control-sm mt-1 mb-2" placeholder="Coupon code" value="{{ $rule?->coupon_code }}">

                        <hr>

                        <div class="form-check">
                            <input type="hidden" name="show_complaint_form" value="0">
                            <input type="checkbox" name="show_complaint_form" value="1" class="form-check-input" id="{{ $sentiment }}-complaint" @checked($rule?->show_complaint_form)>
                            <label class="form-check-label" for="{{ $sentiment }}-complaint">Show complaint form</label>
                        </div>
                        <div class="form-check">
                            <input type="hidden" name="show_support_number" value="0">
                            <input type="checkbox" name="show_support_number" value="1" class="form-check-input" id="{{ $sentiment }}-support" @checked($rule?->show_support_number)>
                            <label class="form-check-label" for="{{ $sentiment }}-support">Show support number</label>
                        </div>
                        <div class="form-check mb-2">
                            <input type="hidden" name="show_whatsapp_button" value="0">
                            <input type="checkbox" name="show_whatsapp_button" value="1" class="form-check-input" id="{{ $sentiment }}-whatsapp" @checked($rule?->show_whatsapp_button)>
                            <label class="form-check-label" for="{{ $sentiment }}-whatsapp">Show WhatsApp button</label>
                        </div>

                        <input type="text" name="manager_contact[name]" class="form-control form-control-sm mb-1" placeholder="Manager name" value="{{ $rule?->manager_contact['name'] ?? '' }}">
                        <input type="text" name="manager_contact[phone]" class="form-control form-control-sm mb-2" placeholder="Manager phone" value="{{ $rule?->manager_contact['phone'] ?? '' }}">

                        <button type="submit" class="btn btn-sm btn-primary w-100">Save {{ ucfirst($sentiment) }} Rule</button>
                    </form>
                </div>
            </div>
        </div>
    @endforeach
</div>
