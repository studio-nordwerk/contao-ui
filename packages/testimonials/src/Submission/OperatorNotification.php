<?php

declare(strict_types=1);

namespace Nordwerk\TestimonialsBundle\Submission;

use Contao\Email;
use Doctrine\DBAL\Connection;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final class OperatorNotification
{
    public function __construct(
        private readonly Connection $connection,
        private readonly LoggerInterface $logger,
        private readonly TranslatorInterface $translator,
    ) {
    }

    public function send(int $id): bool
    {
        $row = $this->connection->fetchAssociative('SELECT notifyRecipient, notifiedAt FROM tl_nw_testimonial WHERE id=?', [$id]);

        if (!$row || $row['notifiedAt'] || !$row['notifyRecipient']) {
            return false;
        }

        try {
            $email = new Email();
            $email->subject = $this->translator->trans('nw.testimonials.mail.subject');
            $email->text = $this->translator->trans('nw.testimonials.mail.body', ['%id%' => (string) $id]);
            $email->sendTo($row['notifyRecipient']);
            $this->connection->update('tl_nw_testimonial', ['notifiedAt' => time()], ['id' => $id]);
        } catch (\Throwable $exception) {
            $this->logger->error('Testimonial notification failed; retry with nordwerk:testimonials:notify.', ['id' => $id, 'exception' => $exception]);

            return false;
        }

        return true;
    }
}
