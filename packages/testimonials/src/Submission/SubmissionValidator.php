<?php

declare(strict_types=1);

namespace Nordwerk\TestimonialsBundle\Submission;

final class SubmissionValidator
{
    /**
     * @param array<string, string> $values
     *
     * @return list<string>
     */
    public function validate(array $values): array
    {
        $errors = [];

        foreach (['name' => 255, 'text' => 10000, 'email' => 255, 'role' => 255, 'source' => 255] as $field => $maximum) {
            $value = trim($values[$field] ?? '');

            if (mb_strlen($value) > $maximum || (\in_array($field, ['name', 'text'], true) && '' === $value) || preg_match('/[\x00-\x08\x0b\x0c\x0e-\x1f]/', $value)) {
                $errors[] = 'nw.testimonials.error.'.$field;
            }
        }

        if ('' !== ($values['email'] ?? '') && false === filter_var($values['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'nw.testimonials.error.email';
        }

        if ('' !== ($values['stars'] ?? '') && !\in_array($values['stars'], ['1', '2', '3', '4', '5'], true)) {
            $errors[] = 'nw.testimonials.error.stars';
        }

        if ('1' !== ($values['consent'] ?? '')) {
            $errors[] = 'nw.testimonials.error.consent';
        }

        if ('' !== ($values['website'] ?? '')) {
            $errors[] = 'nw.testimonials.error.spam';
        }

        return array_values(array_unique($errors));
    }
}
