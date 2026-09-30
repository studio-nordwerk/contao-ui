<?php

declare(strict_types=1);

namespace Nordwerk\CarouselBundle\Controller\ContentElement;

use Contao\ContentModel;
use Contao\CoreBundle\Controller\ContentElement\AbstractContentElementController;
use Contao\CoreBundle\DependencyInjection\Attribute\AsContentElement;
use Contao\CoreBundle\Twig\FragmentTemplate;
use Nordwerk\CarouselBundle\Options\CarouselOptions;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[AsContentElement('nw_carousel', category: 'nordwerk_ui', template: 'content_element/nw_carousel', nestedFragments: true)]
final class CarouselController extends AbstractContentElementController
{
    protected function getResponse(FragmentTemplate $template, ContentModel $model, Request $request): Response
    {
        $template->set('carousel', CarouselOptions::fromData($model->row()));

        return $template->getResponse();
    }
}
