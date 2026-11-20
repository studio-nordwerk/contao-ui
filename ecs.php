<?php

declare(strict_types=1);

use Contao\EasyCodingStandard\Set\SetList;
use PhpCsFixer\Fixer\Operator\NewWithParenthesesFixer;
use Symplify\EasyCodingStandard\Config\ECSConfig;

return ECSConfig::configure()
    ->withSets([SetList::CONTAO])
    ->withPaths([__DIR__.'/packages', __DIR__.'/tests', __DIR__.'/scripts'])
    ->withRootFiles()
    // Newer releases for Contao 6 drop the parentheses of `new class()`, older ones keep them; one rule for both lines.
    ->withConfiguredRule(NewWithParenthesesFixer::class, ['named_class' => true, 'anonymous_class' => true])
;
