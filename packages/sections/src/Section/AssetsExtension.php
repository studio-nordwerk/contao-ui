<?php

declare(strict_types=1);

namespace Nordwerk\SectionsBundle\Section;

use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class AssetsExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('nw_sections_assets', $this->load(...), ['needs_environment' => true]),
            new TwigFunction('nw_section_icon', Icons::svg(...), ['is_safe' => ['html']]),
        ];
    }

    public function load(Environment $twig): void
    {
        $twig->render('@Contao/component/_nw_sections_assets.html.twig', []);
    }
}
