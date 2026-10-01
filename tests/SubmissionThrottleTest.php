<?php

declare(strict_types=1);

namespace Nordwerk\ContaoUi\Tests;

use Nordwerk\TestimonialsBundle\Submission\SubmissionThrottle;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\RateLimiter\Storage\CacheStorage;

final class SubmissionThrottleTest extends TestCase
{
    public function testSharedStorageRetainsBudgetWithoutPlaintextClientAddress(): void
    {
        $cache = new ArrayAdapter();
        $factory = static fn (): RateLimiterFactory => new RateLimiterFactory(['id' => 'test', 'policy' => 'sliding_window', 'limit' => 5, 'interval' => '15 minutes'], new CacheStorage($cache));
        $request = Request::create('/submit.html', server: ['REMOTE_ADDR' => '192.0.2.42']);

        for ($attempt = 0; $attempt < 5; ++$attempt) {
            $this->assertTrue((new SubmissionThrottle($factory(), 'test-secret'))->accept($request));
        }

        $this->assertFalse((new SubmissionThrottle($factory(), 'test-secret'))->accept($request));
        $this->assertStringNotContainsString('192.0.2.42', serialize($cache->getValues()));
        $other = Request::create('/submit.html', server: ['REMOTE_ADDR' => '192.0.2.43']);
        $this->assertTrue((new SubmissionThrottle($factory(), 'test-secret'))->accept($other));
    }
}
