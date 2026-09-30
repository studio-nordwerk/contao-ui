<?php

declare(strict_types=1);

namespace Nordwerk\ContaoUi\Tests;

use Contao\Config;
use Contao\ContentModel;
use Contao\CoreBundle\Twig\FragmentTemplate;
use Nordwerk\CarouselBundle\Controller\ContentElement\CoreSwiperController;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class CoreSwiperReplacementTest extends TestCase
{
    public function testReplacementIsOptInAndRespectsCustomTemplates(): void
    {
        $model = $this->createMock(ContentModel::class);
        $model
            ->method('__get')
            ->willReturnCallback(static fn (string $key): mixed => ['sliderDelay' => 2000, 'sliderStartSlide' => 1, 'sliderContinuous' => true][$key] ?? null)
        ;
        $method = new \ReflectionMethod(CoreSwiperController::class, 'getResponse');
        $previous = Config::get('nwCarouselReplaceSwiper');

        try {
            foreach ([[false, 'content_element/swiper', 'content_element/swiper'], [true, 'content_element/swiper', 'content_element/nw_carousel'], [true, 'content_element/swiper/custom', 'content_element/swiper/custom']] as [$enabled, $name, $expected]) {
                Config::set('nwCarouselReplaceSwiper', $enabled);
                $template = new FragmentTemplate($name, static fn (): Response => new Response());
                $method->invoke(new CoreSwiperController(), $template, $model, Request::create('/'));
                $this->assertSame($expected, $template->getName());
            }
        } finally {
            Config::set('nwCarouselReplaceSwiper', $previous);
        }
    }
}
