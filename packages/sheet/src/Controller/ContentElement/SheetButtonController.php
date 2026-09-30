<?php

declare(strict_types=1);

namespace Nordwerk\SheetBundle\Controller\ContentElement;

use Contao\ContentModel;
use Contao\CoreBundle\Controller\ContentElement\AbstractContentElementController;
use Contao\CoreBundle\DependencyInjection\Attribute\AsContentElement;
use Contao\CoreBundle\Twig\FragmentTemplate;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[AsContentElement('nw_sheet_button', category: 'nordwerk_ui', template: 'content_element/nw_sheet_button')]
final class SheetButtonController extends AbstractContentElementController
{
    protected function getResponse(FragmentTemplate $template, ContentModel $model, Request $request): Response
    {
        $template->set('sheet_id', 'nw-sheet-'.(int) ($model->row()['nwSheetTarget'] ?? null));
        $template->set('sheet_command', 'close' === ($model->row()['nwSheetAction'] ?? null) ? 'close' : 'show-modal');

        return $template->getResponse();
    }
}
