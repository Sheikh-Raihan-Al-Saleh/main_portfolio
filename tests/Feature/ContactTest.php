<?php

use App\Mail\NewContactMessage;
use App\Models\ContactMessage;
use App\Models\Profile;
use Illuminate\Support\Facades\Mail;

/**
 * @return array<string, string>
 */
function validContactPayload(array $overrides = []): array
{
    return [
        'name' => 'Ada Lovelace',
        'email' => 'ada@example.com',
        'subject' => 'Contract work',
        'message' => 'I would like to talk to you about a project we are starting next month.',
        ...$overrides,
    ];
}

test('a valid submission is stored and notifies the owner', function () {
    Mail::fake();
    Profile::current()->update(['public_email' => 'owner@example.com']);

    $this->post('/contact', validContactPayload())
        ->assertRedirect();

    $message = ContactMessage::sole();

    expect($message->name)->toBe('Ada Lovelace')
        ->and($message->email)->toBe('ada@example.com')
        ->and($message->read_at)->toBeNull()
        ->and($message->ip_address)->not->toBeNull();

    Mail::assertSent(NewContactMessage::class);
});

test('the message must be substantial', function () {
    $this->post('/contact', validContactPayload(['message' => 'hi']))
        ->assertSessionHasErrors('message');

    expect(ContactMessage::count())->toBe(0);
});

test('an invalid email is rejected', function () {
    $this->post('/contact', validContactPayload(['email' => 'not-an-email']))
        ->assertSessionHasErrors('email');
});

test('a filled honeypot is rejected', function () {
    $this->post('/contact', validContactPayload(['website' => 'http://spam.example']))
        ->assertSessionHasErrors('website');

    expect(ContactMessage::count())->toBe(0);
});

test('submissions are rate limited', function () {
    Mail::fake();

    foreach (range(1, 5) as $i) {
        $this->post('/contact', validContactPayload(['email' => "sender{$i}@example.com"]))
            ->assertRedirect();
    }

    $this->post('/contact', validContactPayload(['email' => 'sender6@example.com']))
        ->assertStatus(429);

    expect(ContactMessage::count())->toBe(5);
});

test('a mail failure does not lose the message', function () {
    Profile::current()->update(['public_email' => 'owner@example.com']);

    Mail::shouldReceive('to')->andThrow(new RuntimeException('SMTP down'));

    $this->post('/contact', validContactPayload())->assertRedirect();

    expect(ContactMessage::count())->toBe(1);
});
