<?php
namespace App\Mail;

use App\Models\Employee;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PortalAccessGranted extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Employee $employee,
        public string   $username,
        public string   $password
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '🚀 KUVVET Employee Portal Access Granted',
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.portal-access-granted');
    }
}