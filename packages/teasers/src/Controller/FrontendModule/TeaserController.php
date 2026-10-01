<?php

declare(strict_types=1);

namespace Nordwerk\TeasersBundle\Controller\FrontendModule;

use Contao\CoreBundle\Controller\FrontendModule\AbstractFrontendModuleController;
use Contao\CoreBundle\DependencyInjection\Attribute\AsFrontendModule;
use Contao\CoreBundle\Twig\FragmentTemplate;
use Contao\ModuleModel;
use Nordwerk\TeasersBundle\Rendering\TeaserRenderer;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[AsFrontendModule('nw_teaser', category: 'nordwerk_ui', template: 'frontend_module/nw_teaser')]
final class TeaserController extends AbstractFrontendModuleController
{
    public function __construct(private readonly TeaserRenderer $renderer)
    {
    }

    protected function getResponse(FragmentTemplate $template, ModuleModel $model, Request $request): Response
    {
        return $this->renderer->render($template, $model->row() + ['size' => $model->imgSize], 'nw-teaser-mod-'.$model->id);
    }
}
