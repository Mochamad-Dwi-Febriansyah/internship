<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MailSendNomorRegistrasi extends Mailable
{
    use Queueable, SerializesModels;

    public $nomor_registrasi;
    /**
     * Create a new message instance.
     */
    public function __construct($nomor_registrasi)
    {
        $this->nomor_registrasi = $nomor_registrasi;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Mail Send Nomor Registrasi',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.nomor_registrasi',
            with: [
                'nomor_registrasi' => $this->nomor_registrasi, 
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
