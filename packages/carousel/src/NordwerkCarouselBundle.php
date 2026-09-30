<?php

declare(strict_types=1);

namespace Nordwerk\CarouselBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

final class NordwerkCarouselBundle extends Bundle
{
    public function getPath(): string
    {
        return \dirname(__DIR__);
    }
}
