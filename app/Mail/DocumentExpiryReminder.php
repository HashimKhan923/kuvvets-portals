<?php
namespace App\Mail;

use App\Models\EmployeeDocument;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DocumentExpiryReminder extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public EmployeeDocument $document) {}

    public function envelope(): Envelope
    {
        $subject = $this->document->isExpired()
            ? "🚨 Document Expired: {$this->document->title}"
            : "⏰ Document Expiring Soon: {$this->document->title}";

        return new Envelope(subject: $subject);
    }

    public function content(): Content
    {
        return new Content(view: 'emails.document-expiry-reminder');
    }
}