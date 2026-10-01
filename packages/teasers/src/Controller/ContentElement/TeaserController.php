<?php

declare(strict_types=1);

namespace Nordwerk\TeasersBundle\Controller\ContentElement;

use Contao\ContentModel;
use Contao\CoreBundle\Controller\ContentElement\AbstractContentElementController;
use Contao\CoreBundle\DependencyInjection\Attribute\AsContentElement;
use Contao\CoreBundle\Twig\FragmentTemplate;
use Nordwerk\TeasersBundle\Rendering\TeaserRenderer;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[AsContentElement('nw_teaser', category: 'nordwerk_ui', template: 'content_element/nw_teaser')]
final class TeaserController extends AbstractContentElementController
{
    public function __construct(private readonly TeaserRenderer $renderer)
    {
    }

    protected function getResponse(FragmentTemplate $template, ContentModel $model, Request $request): Response
    {
        return $this->renderer->render($template, $model->row(), 'nw-teaser-ce-'.$model->id);
    }
}
