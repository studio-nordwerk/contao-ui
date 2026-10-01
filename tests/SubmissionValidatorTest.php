<?php

declare(strict_types=1);

namespace Nordwerk\ContaoUi\Tests;

use Nordwerk\TestimonialsBundle\Submission\SubmissionValidator;
use PHPUnit\Framework\TestCase;

final class SubmissionValidatorTest extends TestCase
{
    public function testConsentIsRequiredAndStarsAreOptional(): void
    {
        $validator = new SubmissionValidator();
        $this->assertSame(['nw.testimonials.error.consent'], $validator->validate(['name' => 'Name', 'text' => 'Experience']));
        $this->assertSame([], $validator->validate(['name' => 'Name', 'text' => 'Experience', 'consent' => '1']));
    }

    public function testRejectsInvalidRatingsLongTextAndHoneypot(): void
    {
        $errors = (new SubmissionValidator())->validate(['name' => 'Name', 'text' => str_repeat('a', 10001), 'consent' => '1', 'stars' => '5.5', 'email' => 'invalid', 'website' => 'bot']);
        $this->assertContains('nw.testimonials.error.text', $errors);
        $this->assertContains('nw.testimonials.error.stars', $errors);
        $this->assertContains('nw.testimonials.error.email', $errors);
        $this->assertContains('nw.testimonials.error.spam', $errors);
    }
}
