<?php

declare(strict_types=1);

namespace Nordwerk\GalleryBundle\Controller\ContentElement;

use Contao\ContentModel;
use Contao\CoreBundle\Controller\ContentElement\AbstractContentElementController;
use Contao\CoreBundle\DependencyInjection\Attribute\AsContentElement;
use Contao\CoreBundle\Twig\FragmentTemplate;
use Nordwerk\GalleryBundle\Image\GalleryFigures;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[AsContentElement('nw_gallery', category: 'nordwerk_ui', template: 'content_element/nw_gallery')]
final class GalleryController extends AbstractContentElementController
{
    public function __construct(private readonly GalleryFigures $figures)
    {
    }

    protected function getResponse(FragmentTemplate $template, ContentModel $model, Request $request): Response
    {
        $images = $this->figures->build($model->multiSRC, $model->size, $model->sortBy, (bool) $model->fullsize);

        if (!$images) {
            return new Response();
        }

        if ('random' === $model->sortBy) {
            shuffle($images);
        }

        $layout = $model->row()['nwGalleryLayout'] ?? 'grid';
        $template->set('images', $images);
        $template->set('gallery_layout', \in_array($layout, ['grid', 'mosaic', 'rail'], true) ? $layout : 'grid');
        $template->set('gallery_columns', max(1, min(6, (int) $model->perRow)));

        $response = $template->getResponse();

        if ('random' === $model->sortBy) {
            $response->setPrivate();
            $response->setMaxAge(0);
        }

        return $response;
    }
}
