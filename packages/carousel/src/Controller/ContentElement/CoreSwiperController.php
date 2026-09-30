<?php

declare(strict_types=1);

namespace Nordwerk\CarouselBundle\Controller\ContentElement;

use Contao\Config;
use Contao\ContentModel;
use Contao\CoreBundle\Controller\ContentElement\SwiperController;
use Contao\CoreBundle\DependencyInjection\Attribute\AsContentElement;
use Contao\CoreBundle\Twig\FragmentTemplate;
use Nordwerk\CarouselBundle\Options\CarouselOptions;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[AsContentElement('swiper', category: 'miscellaneous', template: 'content_element/swiper', nestedFragments: true, priority: 100)]
final class CoreSwiperController extends SwiperController
{
    protected function getResponse(FragmentTemplate $template, ContentModel $model, Request $request): Response
    {
        // Respect custom templates and keep the core implementation unless explicitly enabled.
        if (!Config::get('nwCarouselReplaceSwiper') || 'content_element/swiper' !== $template->getName()) {
            return parent::getResponse($template, $model, $request);
        }

        $template->setName('content_element/nw_carousel');
        $template->set('carousel', array_merge(
            CarouselOptions::fromData(['nwCarouselDrag' => true, 'nwCarouselAutoplay' => $model->sliderDelay]),
            ['initial' => max(0, (int) $model->sliderStartSlide), 'rewind' => (bool) $model->sliderContinuous],
        ));

        return $template->getResponse();
    }
}
