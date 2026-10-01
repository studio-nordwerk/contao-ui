<?php

declare(strict_types=1);

namespace Nordwerk\TeasersBundle\Rendering;

use Contao\CoreBundle\Image\Studio\Studio;
use Contao\CoreBundle\Twig\FragmentTemplate;
use Nordwerk\TeasersBundle\Query\TeaserQuery;
use Nordwerk\TeasersBundle\Source\SourceRegistry;
use Symfony\Component\HttpFoundation\Response;

final class TeaserRenderer
{
    public function __construct(
        private readonly SourceRegistry $sources,
        private readonly Studio $studio,
        private readonly PublicFiles $files,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public function render(FragmentTemplate $template, array $data, string $id): Response
    {
        $cards = [];
        $source = $this->sources->get((string) ($data['nwTeaserSource'] ?? ''));
        $query = TeaserQuery::fromData($data);

        if ($source) {
            foreach ($source->fetch($query) as $card) {
                $figure = null;

                if ($card->image && ($file = $this->files->resolve($card->image))) {
                    $figure = $this->studio->createFigureBuilder()->fromFilesModel($file)->setSize($data['size'] ?? null)->buildIfResourceExists();
                }

                $cards[] = ['card' => $card, 'figure' => $figure];

                if (\count($cards) >= $query->limit) {
                    break;
                }
            }
        }

        $layout = $data['nwTeaserLayout'] ?? 'grid';
        $template->set('cards', $cards);
        $template->set('teaser_id', $id);
        $template->set('teaser_label', (string) ($data['nwTeaserLabel'] ?? 'Teaser'));
        $template->set('teaser_layout', \in_array($layout, ['grid', 'list', 'carousel'], true) ? $layout : 'grid');
        $template->set('teaser_columns', max(1, min(6, (int) ($data['nwTeaserColumns'] ?? 3))));
        $response = $template->getResponse();
        // Publication and member permissions must take effect immediately, including
        // empty lists.
        $response->headers->set('Cache-Control', 'private, no-store');

        return $response;
    }
}
