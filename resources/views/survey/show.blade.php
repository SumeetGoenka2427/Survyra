@php
    $theme = $survey->theme;
    $isAllQuestionsLayout = in_array($survey->layout, ['one_page', 'card_based'], true);
    $allQuestionsView = $survey->layout === 'card_based' ? 'survey._card-based-questions' : 'survey._one-page-questions';
    $containerWidth = match ($survey->layout) {
        'card_based' => '720px',
        'one_page', 'section_wizard' => '640px',
        default => '480px',
    };
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $survey->title }}</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="{{ asset('assets/css/survey-experience.css') }}">
    @include('survey._theme-vars', ['theme' => $theme])
    @if($survey->ga_tracking_id)
    <script async src="https://www.googletagmanager.com/gtag/js?id={{ $survey->ga_tracking_id }}"></script>
    <script>window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments);}gtag('js',new Date());gtag('config','{{ $survey->ga_tracking_id }}');</script>
    @endif
    @if($survey->meta_pixel_id)
    <script>!function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,document,'script','https://connect.facebook.net/en_US/fbevents.js');fbq('init','{{ $survey->meta_pixel_id }}');fbq('track','PageView');</script>
    @endif
</head>
<body class="{{ $survey->layout === 'conversational' ? '' : 'd-flex align-items-center min-vh-100 py-4' }}">
    @if ($survey->layout === 'conversational')
        <div class="conv-shell">
            @if ($survey->client->logo_path)
                <div class="text-center mb-4">
                    <img src="{{ \Illuminate\Support\Facades\Storage::url($survey->client->logo_path) }}" alt="{{ $survey->client->company_name }}" style="max-height: 40px;">
                </div>
            @endif

            <div id="survey-app" data-response-uuid="{{ $response->uuid }}" data-slug="{{ $survey->slug }}" data-layout="conversational">
                <div id="survey-app-content">
                    @if (!empty($showWelcome))
                        @include('survey._welcome', ['survey' => $survey])
                    @elseif ($question)
                        @include('survey._question-frame-conversational', ['question' => $question, 'position' => $position, 'survey' => $survey, 'existingAnswer' => $existingAnswer ?? null])
                    @else
                        @include('survey._thankyou-frame', ['rule' => $rule, 'survey' => $survey, 'response' => $response])
                    @endif
                </div>
            </div>
        </div>
    @else
        <div class="container" style="max-width: {{ $containerWidth }};">
            @if ($survey->client->logo_path)
                <div class="text-center mb-3">
                    <img src="{{ \Illuminate\Support\Facades\Storage::url($survey->client->logo_path) }}" alt="{{ $survey->client->company_name }}" style="max-height: 48px;">
                </div>
            @endif

            <div class="card border-0 shadow-sm survey-card">
                <div class="card-body p-4" id="survey-app" data-response-uuid="{{ $response->uuid }}" data-slug="{{ $survey->slug }}" data-layout="{{ $survey->layout }}">
                    <div id="survey-app-content">
                        @if (!empty($showWelcome))
                            @include('survey._welcome', ['survey' => $survey])
                        @elseif ($isAllQuestionsLayout && $response->status !== 'completed')
                            @include($allQuestionsView, ['survey' => $survey, 'response' => $response, 'questions' => $questions])
                        @elseif ($survey->layout === 'section_wizard' && $response->status !== 'completed')
                            @include('survey._section-questions', array_merge(['survey' => $survey, 'response' => $response], $section))
                        @elseif ($question)
                            @include('survey._question-frame', ['question' => $question, 'position' => $position, 'survey' => $survey, 'existingAnswer' => $existingAnswer ?? null])
                        @else
                            @include('survey._thankyou-frame', ['rule' => $rule, 'survey' => $survey, 'response' => $response])
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif

    <script src="{{ asset('assets/js/survey.js') }}"></script>
</body>
</html>
