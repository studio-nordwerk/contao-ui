<?php

declare(strict_types=1);

namespace Nordwerk\SheetBundle\Backend;

use Contao\ContentModel;
use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\ModuleModel;

final class SheetTargets
{
    /**
     * @return array<int, string>
     */
    #[AsCallback(table: 'tl_content', target: 'fields.nwSheetTarget.options')]
    public function sheets(): array
    {
        $options = [];

        foreach (ContentModel::findBy('type', 'nw_sheet', ['order' => 'id']) ?? [] as $model) {
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

        foreach (ModuleModel::findBy('type', 'navigation', ['order' => 'name']) ?? [] as $model) {
            $options[(int) $model->id] = '#'.$model->id.' · '.$model->name;
        }

        return $options;
    }
}
