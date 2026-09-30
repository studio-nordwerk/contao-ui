<?php

declare(strict_types=1);

namespace Nordwerk\SheetBundle\Controller\ContentElement;

use Contao\ContentModel;
use Contao\CoreBundle\Controller\ContentElement\AbstractContentElementController;
use Contao\CoreBundle\DependencyInjection\Attribute\AsContentElement;
use Contao\CoreBundle\Twig\FragmentTemplate;
use Nordwerk\SheetBundle\Options\SheetOptions;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[AsContentElement('nw_sheet', category: 'nordwerk_ui', template: 'content_element/nw_sheet', nestedFragments: true)]
final class SheetController extends AbstractContentElementController
{
    protected function getResponse(FragmentTemplate $template, ContentModel $model, Request $request): Response
    {
        $template->set('sheet', SheetOptions::fromData($model->row()));

        return $template->getResponse();
    }
}
