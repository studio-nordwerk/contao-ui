<?php

declare(strict_types=1);

namespace Nordwerk\SheetBundle\Controller\FrontendModule;

use Contao\CoreBundle\Controller\FrontendModule\AbstractFrontendModuleController;
use Contao\CoreBundle\DependencyInjection\Attribute\AsFrontendModule;
use Contao\CoreBundle\Twig\FragmentTemplate;
use Contao\ModuleModel;
use Nordwerk\SheetBundle\Options\SheetOptions;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[AsFrontendModule('nw_offcanvas_navigation', category: 'navigationMenu', template: 'frontend_module/nw_offcanvas_navigation')]
final class OffcanvasNavigationController extends AbstractFrontendModuleController
{
    protected function getResponse(FragmentTemplate $template, ModuleModel $model, Request $request): Response
    {
        $navigation = ModuleModel::findById((int) ($model->row()['nwSheetNavigation'] ?? null));
        if (!$navigation || 'navigation' !== $navigation->type) {
            return new Response();
        }

        $template->set('navigation_id', (int) $navigation->id);
        $template->set('sheet', SheetOptions::fromData(['nwSheetPresentation' => ($model->row()['nwSheetPresentation'] ?? null), 'nwSheetHistory' => true]));

        return $template->getResponse();
    }
}
