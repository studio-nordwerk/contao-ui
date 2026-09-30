<?php

declare(strict_types=1);

namespace Nordwerk\CarouselBundle\Twig;

use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class AssetsExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [new TwigFunction('nw_carousel_assets', $this->load(...), ['needs_environment' => true])];
    }

    public function load(Environment $twig): void
    {
        $twig->render('@Contao/component/_nw_carousel_assets.html.twig', []);
    }
}
