<?php
namespace App\Mail;

use App\Models\Attendance;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AttendanceOverridden extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Attendance $attendance) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Attendance Record Updated — ' . $this->attendance->date->format('M j, Y'),
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.attendance-overridden');
    }
}