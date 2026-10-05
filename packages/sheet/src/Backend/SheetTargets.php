<?php

declare(strict_types=1);

namespace Nordwerk\SheetBundle\Backend;

use Contao\ContentModel;
use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\CoreBundle\Security\ContaoCorePermissions;
use Contao\CoreBundle\Security\DataContainer\ReadAction;
use Contao\ModuleModel;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

final class SheetTargets
{
    public function __construct(private readonly AuthorizationCheckerInterface $security)
    {
    }

    /**
     * @return array<int, string>
     */
    #[AsCallback(table: 'tl_content', target: 'fields.nwSheetTarget.options')]
    public function sheets(): array
    {
        $options = [];

        foreach (ContentModel::findBy('type', 'nw_sheet', ['order' => 'id']) ?? [] as $model) {
            if (!$this->security->isGranted(ContaoCorePermissions::DC_PREFIX.'tl_content', new ReadAction('tl_content', $model->row()))) {
                continue;
            }

            $options[(int) $model->id] = '#'.$model->id.' · '.($model->row()['nwSheetLabel'] ?? null);
        }

        return $options;
    }

    /**
     * @return array<int, string>
     */
    #[AsCallback(table: 'tl_module', target: 'fields.nwSheetNavigation.options')]
    public function navigations(): array
    {
        $options = [];

        foreach (ModuleModel::findBy(['type IN (?, ?)'], ['navigation', 'customnav'], ['order' => 'name']) ?? [] as $model) {
            if (!$this->security->isGranted(ContaoCorePermissions::DC_PREFIX.'tl_module', new ReadAction('tl_module', $model->row()))) {
                continue;
            }

            $options[(int) $model->id] = '#'.$model->id.' · '.$model->name;
        }

        return $options;
    }
}
