<?php

declare(strict_types=1);

namespace Nordwerk\TeasersBundle\Source;

use Contao\CoreBundle\Security\ContaoCorePermissions;
use Contao\StringUtil;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use Symfony\Bundle\SecurityBundle\Security;

final class ArchiveAccess
{
    public function __construct(
        private readonly Connection $connection,
        private readonly Security $security,
    ) {
    }

    /**
     * @param list<int> $ids
     *
     * @return list<int>
     */
    public function allowed(string $table, array $ids): array
    {
        if (!$ids) {
            return [];
        }

        $allowed = [];

        foreach ($this->connection->fetchAllAssociative('SELECT id, protected, groups FROM '.$this->connection->quoteIdentifier($table).' WHERE id IN (?)', [$ids], [ArrayParameterType::INTEGER]) as $archive) {
            if (!$archive['protected'] || $this->security->isGranted(ContaoCorePermissions::MEMBER_IN_GROUPS, StringUtil::deserialize($archive['groups'], true))) {
                $allowed[] = (int) $archive['id'];
            }
        }

        return $allowed;
    }
}
