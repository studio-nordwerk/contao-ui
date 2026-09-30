<?php

declare(strict_types=1);

namespace Nordwerk\SheetBundle\Twig;

use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class AssetsExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [new TwigFunction('nw_sheet_assets', $this->load(...), ['needs_environment' => true])];
    }

    public function load(Environment $twig, bool $dismissible = true): void
    {
        $twig->render('@Contao/component/_nw_sheet_assets.html.twig', ['dismissible' => $dismissible]);
    }
}
