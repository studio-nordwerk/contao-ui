<?php

declare(strict_types=1);

namespace Nordwerk\TestimonialsBundle\Controller\FrontendModule;

use Contao\CoreBundle\Controller\FrontendModule\AbstractFrontendModuleController;
use Contao\CoreBundle\DependencyInjection\Attribute\AsFrontendModule;
use Contao\CoreBundle\Routing\ContentUrlGenerator;
use Contao\CoreBundle\Twig\FragmentTemplate;
use Contao\ModuleModel;
use Contao\PageModel;
use Doctrine\DBAL\Connection;
use Nordwerk\TestimonialsBundle\Submission\OperatorNotification;
use Nordwerk\TestimonialsBundle\Submission\SubmissionValidator;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Exception\ExceptionInterface;

#[AsFrontendModule('nw_testimonial_form', category: 'nordwerk_ui', template: 'frontend_module/nw_testimonial_form')]
final class SubmissionController extends AbstractFrontendModuleController
{
    public function __construct(
        private readonly Connection $connection,
        private readonly SubmissionValidator $validator,
        private readonly OperatorNotification $notification,
        private readonly ContentUrlGenerator $urls,
    ) {
    }

    protected function getResponse(FragmentTemplate $template, ModuleModel $model, Request $request): Response
    {
        $data = $model->row();
        $privacyPage = PageModel::findPublishedById((int) ($data['nwTestimonialPrivacy'] ?? 0));
        $privacy = null;

        try {
            $privacy = $privacyPage ? $this->urls->generate($privacyPage) : null;
        } catch (ExceptionInterface) {
            // A misconfigured form must not accept submissions.
        }

        if (!$privacy || !trim((string) ($data['nwTestimonialConsent'] ?? '')) || !filter_var($data['nwTestimonialRecipient'] ?? '', FILTER_VALIDATE_EMAIL) || !$this->connection->fetchOne('SELECT id FROM tl_nw_testimonial_archive WHERE id=?', [(int) ($data['nwTestimonialArchive'] ?? 0)])) {
            $template->set('configured', false);

            return $this->privateResponse($template->getResponse());
        }

        $formId = 'nw-testimonial-'.$model->id;
        $values = [];
        $errors = [];
        $session = $request->getSession();
        $nonceKey = $formId.'-nonces';
        $nonces = $session->get($nonceKey, []);
        // Legacy timestamp-only tokens have no proof of the displayed consent.
        $nonces = array_filter($nonces, static fn (mixed $snapshot): bool => \is_array($snapshot) && ($snapshot['created'] ?? 0) > time() - 7200 && isset($snapshot['consent'], $snapshot['privacy']));
        $receiptKey = $formId.'-receipt';
        $success = $request->isMethod('GET') && $session->remove($receiptKey);

        if ($request->isMethod('POST') && $formId === $request->request->get('FORM_SUBMIT')) {
            foreach (['name', 'text', 'email', 'role', 'source', 'stars', 'consent', 'website'] as $field) {
                $value = $request->request->all()[$field] ?? '';
                $values[$field] = \is_string($value) ? trim($value) : '';
            }

            $nonce = $request->request->get('submission_nonce', '');
            $errors = $this->validator->validate($values);

            if (!\is_string($nonce) || !isset($nonces[$nonce])) {
                $errors[] = 'nw.testimonials.error.session';
            }

            if (!$errors && \is_string($nonce)) {
                $this->connection->insert('tl_nw_testimonial', [
                    'pid' => (int) $data['nwTestimonialArchive'],
                    'tstamp' => time(),
                    'date' => time(),
                    'name' => $values['name'],
                    'text' => $values['text'],
                    'email' => $values['email'],
                    'role' => $values['role'],
                    'source' => $values['source'],
                    'stars' => (int) $values['stars'],
                    'consentedAt' => time(),
                    'consentText' => $nonces[$nonce]['consent']."\n".$nonces[$nonce]['privacy'],
                    'published' => '',
                    'notifyRecipient' => $data['nwTestimonialRecipient'],
                ]);
                $id = (int) $this->connection->lastInsertId();
                unset($nonces[$nonce]);
                $session->set($nonceKey, $nonces);
                $session->set($receiptKey, true);
                $this->notification->send($id);

                return $this->privateResponse(new RedirectResponse($request->getPathInfo(), Response::HTTP_SEE_OTHER));
            }
        }

        $nonce = bin2hex(random_bytes(24));
        $nonces[$nonce] = ['created' => time(), 'consent' => (string) $data['nwTestimonialConsent'], 'privacy' => $privacy];
        $session->set($nonceKey, \array_slice($nonces, -10, null, true));
        $template->set('configured', true);
        $template->set('form_id', $formId);
        $template->set('submission_nonce', $nonce);
        $template->set('values', $values);
        $template->set('errors', $errors);
        $template->set('success', $success);
        $template->set('consent_text', (string) $data['nwTestimonialConsent']);
        $template->set('privacy_url', $privacy);
        $template->set('form_action', $request->getPathInfo());

        return $this->privateResponse($template->getResponse());
    }

    private function privateResponse(Response $response): Response
    {
        $response->headers->set('Cache-Control', 'private, no-store');

        return $response;
    }
}
