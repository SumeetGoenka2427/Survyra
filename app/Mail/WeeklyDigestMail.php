<?php

namespace App\Mail;

use App\Models\Client;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WeeklyDigestMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * Note: these are intentionally NOT named $from/$to as constructor
     * properties - Mailable already declares public $from/$to properties
     * for the envelope sender/recipient addresses, and a subclass property
     * of the same name silently overwrites them (Mail::to() would appear to
     * work, then queue() actually sends with a corrupted recipient list -
     * this exact bug previously made every weekly digest email fail).
     */
    public function __construct(
        public Client $client,
        public array $snapshot,
        public array $digest,
        public $periodFrom,
        public $periodTo,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your Weekly Survyra Digest - ' . now()->format('M j, Y'),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.weekly-digest',
            with: [
                'client' => $this->client,
                'snapshot' => $this->snapshot,
                'digest' => $this->digest,
                'from' => $this->periodFrom,
                'to' => $this->periodTo,
            ],
        );
    }
}