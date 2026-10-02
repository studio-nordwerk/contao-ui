<?php

declare(strict_types=1);

namespace Nordwerk\SectionsBundle\Controller;

use Contao\ContentModel;
use Contao\CoreBundle\Controller\ContentElement\AbstractContentElementController;
use Contao\CoreBundle\DependencyInjection\Attribute\AsContentElement;
use Contao\CoreBundle\Twig\FragmentTemplate;
use Nordwerk\SectionsBundle\Section\Rows;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Team: a container whose child elements are the people. Grid or carousel
 * (contao-carousel-bundle).
 */
#[AsContentElement('nw_team', category: 'nordwerk_sections', nestedFragments: ['allowedTypes' => ['nw_person']])]
final class TeamController extends AbstractContentElementController
{
    protected function getResponse(FragmentTemplate $template, ContentModel $model, Request $request): Response
    {
        $template->set('intro', Rows::plain($model->row()['nwIntro'] ?? ''));
        $template->set('plain', ['note' => Rows::plain($model->row()['nwNote'] ?? '')]);
        $template->set('layout', 'carousel' === ($model->row()['nwTeamLayout'] ?? '') ? 'carousel' : 'grid');

        return $template->getResponse();
    }
}
