<?php

namespace App\Mail;

use App\Models\Client;
use App\Models\Report;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ScheduledReportMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Client $client,
        public readonly Report $report,
        public readonly string $fileContents,
        public readonly string $fileName,
        public readonly string $mimeType,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "{$this->client->company_name} - ".ucfirst($this->report->frequency).' Feedback Report',
        );
    }

    public function content(): Content
    {
        return new Content(markdown: 'emails.scheduled-report');
    }

    /**
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        return [
            Attachment::fromData(fn () => $this->fileContents, $this->fileName)->withMime($this->mimeType),
        ];
    }
}
