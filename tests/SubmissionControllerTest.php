<?php

declare(strict_types=1);

namespace Nordwerk\ContaoUi\Tests;

use Contao\CoreBundle\Routing\ContentUrlGenerator;
use Contao\CoreBundle\Twig\FragmentTemplate;
use Contao\ManagerBundle\HttpKernel\ContaoKernel;
use Contao\ModuleModel;
use Doctrine\DBAL\Connection;
use Nordwerk\TestimonialsBundle\Controller\FrontendModule\SubmissionController;
use Nordwerk\TestimonialsBundle\Submission\OperatorNotification;
use Nordwerk\TestimonialsBundle\Submission\SubmissionValidator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
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
        ContaoKernel::setProjectDir(getcwd());
        $kernel = new ContaoKernel('dev', true);
        $kernel->boot();
        $kernel->getContainer()->get('contao.framework')->initialize();
        $db = $kernel->getContainer()->get('database_connection');
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
        $this->controller = new SubmissionController($this->connection, new SubmissionValidator(), $notification, $this->urls);
    }

    public function testStoresConsentAndPrivacyLinkDisplayedWithTheToken(): void
    {
        $this->urls
            ->method('generate')
            ->willReturnOnConsecutiveCalls('/original-privacy.html', '/changed-privacy.html')
        ;
        [$displayed] = $this->render();
        $this->model->nwTestimonialConsent = 'Changed consent';
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
            'POST', [
                'FORM_SUBMIT' => $template->get('form_id'),
                'submission_nonce' => $template->get('submission_nonce'),
                'name' => 'Fictional reviewer',
                'text' => 'Fictional experience',
                'consent' => '1',
        ],
        );
    }
}
