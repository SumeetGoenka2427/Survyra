<x-mail::message>
# Your {{ ucfirst($report->frequency) }} Feedback Report

Hi,

Attached is the {{ $report->frequency }} feedback report for **{{ $client->company_name }}**
@if ($report->survey)
covering the survey **{{ $report->survey->title }}**.
@else
covering all your surveys.
@endif

<x-mail::button :url="config('app.url')">
Open Survyra
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
