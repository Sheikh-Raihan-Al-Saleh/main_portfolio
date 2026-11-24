<?php

use App\Models\ContactMessage;
use App\Models\Founder;
use App\Models\Studio;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Mail;

test('a visitor can send an enquiry to the studio', function () {
    Mail::fake();
    Studio::current()->update(['name' => 'Acme', 'public_email' => 'studio@example.com']);

    $this->post('/contact', [
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
        'subject' => 'A project',
        'message' => 'We need a new platform built for our logistics team.',
    ])->assertRedirect();

    $message = ContactMessage::sole();

    expect($message->recipient_type)->toBe('studio')
        ->and($message->name)->toBe('Jane Doe');

    Mail::assertSent(Mailable::class, fn (Mailable $mail): bool => $mail->hasTo('studio@example.com')
        && $mail->hasReplyTo('jane@example.com')
        && $mail->hasSubject('New enquiry for Acme from Jane Doe')
        && str_contains($mail->render(), 'We need a new platform built for our logistics team.'));
});

test('an enquiry from the founder page is addressed to the founder', function () {
    Mail::fake();
    Founder::current()->update(['name' => 'Jo', 'public_email' => 'jo@example.com']);

    $this->post('/contact', [
        'site' => 'founder',
        'name' => 'Sam',
        'email' => 'sam@example.com',
        'message' => 'I would like to talk about a full-time role.',
    ])->assertRedirect();

    expect(ContactMessage::sole()->recipient_type)->toBe('founder');
    Mail::assertSent(Mailable::class, fn (Mailable $mail): bool => $mail->hasTo('jo@example.com'));
});

test('the honeypot and short messages are rejected', function () {
    $this->post('/contact', [
        'name' => 'Bot',
        'email' => 'bot@example.com',
        'message' => 'Too short',
        'website' => 'http://spam.example',
    ])->assertSessionHasErrors(['message', 'website']);

    expect(ContactMessage::count())->toBe(0);
});

test('a mail failure does not lose the message', function () {
    Mail::shouldReceive('to')->andThrow(new RuntimeException('SMTP down'));
    Studio::current()->update(['name' => 'Acme', 'public_email' => 'studio@example.com']);

    $this->post('/contact', [
        'name' => 'Jane',
        'email' => 'jane@example.com',
        'message' => 'This message should be saved even if mail fails.',
    ])->assertRedirect();

    expect(ContactMessage::count())->toBe(1);
});
