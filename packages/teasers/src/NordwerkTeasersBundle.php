<?php

declare(strict_types=1);

namespace Nordwerk\TeasersBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

final class NordwerkTeasersBundle extends Bundle
{
    public function getPath(): string
    {
        return \dirname(__DIR__);
    }
}
