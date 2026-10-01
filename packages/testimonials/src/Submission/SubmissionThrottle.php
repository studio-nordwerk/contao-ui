<?php

declare(strict_types=1);

namespace Nordwerk\TestimonialsBundle\Submission;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\RateLimiter\RateLimiterFactory;

final class SubmissionThrottle
{
    public function __construct(
        #[Autowire(service: 'nordwerk.testimonials.rate_limiter')]
        private readonly RateLimiterFactory $limiter,
        #[Autowire(param: 'kernel.secret')]
        private readonly string $secret,
    ) {
    }

    public function accept(Request $request): bool
    {
        // Only a daily, secret-keyed pseudonym reaches cache and lock storage. All
        // modules and sessions share the same short submission budget.
        $salt = hash_hmac('sha256', gmdate('Y-m-d'), $this->secret);
        $key = hash_hmac('sha256', $request->getClientIp() ?? 'unknown', $salt);

        return $this->limiter->create($key)->consume()->isAccepted();
    }
}
