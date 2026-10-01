<?php

declare(strict_types=1);

namespace Nordwerk\ContaoUi\Tests;

use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\Routing\ContentUrlGenerator;
use Contao\CoreBundle\Twig\FragmentTemplate;
use Contao\ManagerBundle\HttpKernel\ContaoKernel;
use Contao\ModuleModel;
use Doctrine\DBAL\Connection;
use Nordwerk\TestimonialsBundle\Controller\FrontendModule\SubmissionController;
use Nordwerk\TestimonialsBundle\Submission\OperatorNotification;
use Nordwerk\TestimonialsBundle\Submission\SubmissionThrottle;
use Nordwerk\TestimonialsBundle\Submission\SubmissionValidator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Lock\Store\InMemoryStore;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\RateLimiter\Storage\CacheStorage;
use Symfony\Contracts\Translation\TranslatorInterface;

final class SubmissionControllerTest extends TestCase
{
    private Connection&MockObject $connection;
    private ContentUrlGenerator&MockObject $urls;
    private ModuleModel $model;
    private Session $session;
    private SubmissionController $controller;

    protected function setUp(): void
    {
        $projectDir = getcwd();
        $this->assertIsString($projectDir);
        ContaoKernel::setProjectDir($projectDir);
        $kernel = new ContaoKernel('dev', true);
        $kernel->boot();
        $framework = $kernel->getContainer()->get('contao.framework');
        $this->assertInstanceOf(ContaoFramework::class, $framework);
        $framework->initialize();
        $db = $kernel->getContainer()->get('database_connection');
        $this->assertInstanceOf(Connection::class, $db);
        $privacy = $db->fetchOne("SELECT id FROM tl_page WHERE alias='privacy'");
        $this->assertNotFalse($privacy, 'The seeded privacy page is required.');
        $this->model = new ModuleModel();
        $this->model->setRow([
            'id' => 42,
            'nwTestimonialPrivacy' => $privacy,
            'nwTestimonialConsent' => 'Displayed consent',
            'nwTestimonialRecipient' => 'operator@example.test',
            'nwTestimonialArchive' => 1,
        ]);
        $this->connection = $this->createMock(Connection::class);
        $this->connection
            ->method('fetchOne')
            ->willReturn(1)
        ;

        $this->connection
            ->method('lastInsertId')
            ->willReturn('123')
        ;
        $this->urls = $this->createMock(ContentUrlGenerator::class);
        $this->session = new Session(new MockArraySessionStorage());
        $notification = new OperatorNotification($this->connection, new NullLogger(), $this->createMock(TranslatorInterface::class));
        $limiter = new RateLimiterFactory(['id' => 'test', 'policy' => 'sliding_window', 'limit' => 5, 'interval' => '15 minutes'], new CacheStorage(new ArrayAdapter()), new LockFactory(new InMemoryStore()));
        $this->controller = new SubmissionController($this->connection, new SubmissionValidator(), $notification, $this->urls, new SubmissionThrottle($limiter, 'test-secret'));
    }

    public function testStoresConsentAndPrivacyLinkDisplayedWithTheToken(): void
    {
        $this->urls
            ->method('generate')
            ->willReturnOnConsecutiveCalls('/original-privacy.html', '/changed-privacy.html')
        ;
        [$displayed] = $this->render();
        $this->model->setRow(array_replace($this->model->row(), ['nwTestimonialConsent' => 'Changed consent']));
        $this->connection
            ->expects($this->once())
            ->method('insert')
            ->with(
                'tl_nw_testimonial',
                $this->callback(static fn (array $record): bool => "Displayed consent\n/original-privacy.html" === $record['consentText'] && '' === $record['published']),
            )
        ;

        $this->connection
            ->expects($this->once())
            ->method('fetchAssociative')
            ->willReturn(false)
        ;
        [, $response] = $this->render($this->post($displayed));
        $this->assertSame(303, $response->getStatusCode());
    }

    public function testActionAndRedirectPreserveBasePathAndQuery(): void
    {
        $this->urls
            ->method('generate')
            ->willReturn('/privacy.html')
        ;
        $uri = '/cms/submit.html?campaign=autumn&lang=de';
        $server = ['SCRIPT_NAME' => '/cms/index.php', 'SCRIPT_FILENAME' => '/var/www/cms/index.php', 'PHP_SELF' => '/cms/index.php'];
        $get = Request::create($uri, 'GET', server: $server);
        $this->assertSame('/submit.html', $get->getPathInfo());
        [$displayed] = $this->render($get);
        $this->assertSame($uri, $displayed->get('form_action'));
        $post = $this->post($displayed, $uri);
        $post->server->add($server);
        [, $response] = $this->render($post);
        $this->assertSame($uri, $response->headers->get('Location'));
    }

    public function testRepeatedModuleRendersUseUniqueFieldIds(): void
    {
        $this->urls
            ->method('generate')
            ->willReturn('/privacy.html')
        ;
        $request = Request::create('/submit.html');
        [$first] = $this->render($request);
        [$second] = $this->render($request);
        $this->assertNotSame($first->get('form_id'), $second->get('form_id'));
        $this->connection
            ->expects($this->once())
            ->method('insert')
        ;
        $post = $this->post($second);
        [, $response] = $this->render($post);
        [$other] = $this->render($post);
        $this->assertSame(303, $response->getStatusCode());
        $this->assertSame([], $other->get('errors'));
    }

    public function testFreshSessionsAndTokensCannotBypassSubmissionThrottle(): void
    {
        $this->urls
            ->method('generate')
            ->willReturn('/privacy.html')
        ;

        $this->connection
            ->expects($this->exactly(5))
            ->method('insert')
        ;

        $this->connection
            ->expects($this->exactly(5))
            ->method('fetchAssociative')
            ->willReturn(false)
        ;

        for ($attempt = 0; $attempt < 6; ++$attempt) {
            $this->session = new Session(new MockArraySessionStorage());
            $this->model->id = 42 + $attempt;
            [$displayed] = $this->render();
            [$result, $response] = $this->render($this->post($displayed));

            if ($attempt < 5) {
                $this->assertSame(303, $response->getStatusCode());
            } else {
                $this->assertContains('nw.testimonials.error.throttled', $result->get('errors'));
                $this->assertSame(200, $response->getStatusCode());
            }
        }
    }

    /**
     * @return array{FragmentTemplate, Response}
     */
    private function render(Request|null $request = null): array
    {
        $request ??= Request::create('/submit.html');
        $request->setSession($this->session);
        $template = new FragmentTemplate('test', static fn (): Response => new Response());
        $response = (new \ReflectionMethod($this->controller, 'getResponse'))->invoke($this->controller, $template, $this->model, $request);

        return [$template, $response];
    }

    private function post(FragmentTemplate $template, string $uri = '/submit.html'): Request
    {
        return Request::create(
            $uri,
            'POST',
            [
                'FORM_SUBMIT' => $template->get('form_submit'),
                'submission_nonce' => $template->get('submission_nonce'),
                'name' => 'Fictional reviewer',
                'text' => 'Fictional experience',
                'consent' => '1',
            ],
        );
    }
}
