<?php

declare(strict_types=1);

namespace Nordwerk\GalleryBundle\Twig;

use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class AssetsExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [new TwigFunction('nw_gallery_assets', $this->load(...), ['needs_environment' => true])];
    }

    public function load(Environment $twig, bool $interactive = true): void
    {
        $twig->render('@Contao/component/_nw_gallery_assets.html.twig', ['interactive' => $interactive]);
    }
}
