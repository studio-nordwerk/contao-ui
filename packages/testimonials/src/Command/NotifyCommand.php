<?php

declare(strict_types=1);

namespace Nordwerk\TestimonialsBundle\Command;

use Contao\CoreBundle\Framework\ContaoFramework;
use Doctrine\DBAL\Connection;
use Nordwerk\TestimonialsBundle\Submission\OperatorNotification;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'nordwerk:testimonials:notify', description: 'Retry pending testimonial operator notifications')]
final class NotifyCommand extends Command
{
    public function __construct(
        private readonly ContaoFramework $framework,
        private readonly Connection $connection,
        private readonly OperatorNotification $notification,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->framework->initialize();
        $failed = false;

        foreach ($this->connection->fetchFirstColumn("SELECT id FROM tl_nw_testimonial WHERE notifiedAt=0 AND notifyRecipient<>''") as $id) {
            $failed = !$this->notification->send((int) $id) || $failed;
        }

        $output->writeln($failed ? 'Some notifications failed; see the application log.' : 'Pending notifications processed.');

        return $failed ? self::FAILURE : self::SUCCESS;
    }
}
