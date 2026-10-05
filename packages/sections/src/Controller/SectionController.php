<?php

declare(strict_types=1);

namespace Nordwerk\SectionsBundle\Controller;

use Contao\ContentModel;
use Contao\CoreBundle\Controller\ContentElement\AbstractContentElementController;
use Contao\CoreBundle\DependencyInjection\Attribute\AsContentElement;
use Contao\CoreBundle\InsertTag\InsertTagParser;
use Contao\CoreBundle\Twig\FragmentTemplate;
use Contao\FilesModel;
use Contao\StringUtil;
use Contao\Validator;
use Nordwerk\SectionsBundle\Section\Rows;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[AsContentElement('nw_hero', category: 'nordwerk_sections')]
#[AsContentElement('nw_page_head', category: 'nordwerk_sections')]
#[AsContentElement('nw_promises', category: 'nordwerk_sections')]
#[AsContentElement('nw_split', category: 'nordwerk_sections')]
#[AsContentElement('nw_figures', category: 'nordwerk_sections')]
#[AsContentElement('nw_features', category: 'nordwerk_sections')]
#[AsContentElement('nw_steps', category: 'nordwerk_sections')]
#[AsContentElement('nw_person', category: 'nordwerk_sections')]
#[AsContentElement('nw_logos', category: 'nordwerk_sections')]
#[AsContentElement('nw_contact', category: 'nordwerk_sections')]
#[AsContentElement('nw_callout', category: 'nordwerk_sections')]
final class SectionController extends AbstractContentElementController
{
    /**
     * @param list<string> $imageExtensions
     */
    public function __construct(
        private readonly InsertTagParser $insertTags,
        #[Autowire(param: 'contao.image.valid_extensions')] private readonly array $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'avif', 'svg'],
    ) {
    }

    protected function getResponse(FragmentTemplate $template, ContentModel $model, Request $request): Response
    {
        $row = $model->row();
        $template->set('eyebrow', Rows::plain($row['nwEyebrow'] ?? ''));
        $template->set('intro', Rows::plain($row['nwIntro'] ?? ''));
        $template->set('plain', array_map(Rows::plain(...), ['name' => $row['nwName'] ?? '', 'role' => $row['nwRole'] ?? '', 'text' => $row['nwText'] ?? '', 'note' => $row['nwNote'] ?? '']));
        $template->set('primary', $this->link($row['nwUrl'] ?? '', $row['nwLinkText'] ?? ''));
        $template->set('secondary', $this->link($row['nwSecondUrl'] ?? '', $row['nwSecondLinkText'] ?? ''));
        $template->set('image', $this->image($row['nwImage'] ?? null));
        $template->set('size', $this->size($row['size'] ?? null));

        switch ($model->type) {
            case 'nw_promises':
                $template->set('lines', Rows::lines($row['nwLines'] ?? '', 4));
                break;

            case 'nw_figures':
                $template->set('figures', array_map(static fn (array $figure): array => [...Rows::figure($figure['value']), 'label' => $figure['label']], Rows::from($row['nwFigures'] ?? null, ['value', 'label'], 4)));
                break;

            case 'nw_features':
                $template->set('features', Rows::from($row['nwFeatures'] ?? null, ['icon', 'title', 'text'], 8));
                break;

            case 'nw_steps':
                $template->set('steps', Rows::from($row['nwSteps'] ?? null, ['title', 'text'], 6));
                break;

            case 'nw_logos':
                $template->set('logos', $this->images($row['nwLogos'] ?? null));
                break;

            case 'nw_contact':
                $template->set('contact', $this->contact($row));
                break;
        }

        return $template->getResponse();
    }

    /**
     * @return array{href: string, text: string}|null
     */
    private function link(mixed $url, mixed $text): array|null
    {
        // Insert tags return HTML-encoded URLs ("&amp;"); Twig encodes the href
        // again on output.
        $href = Rows::plain($this->insertTags->replaceInline((string) $url));
        $text = Rows::plain($text);

        return '' !== $href && '' !== $text ? ['href' => $href, 'text' => $text] : null;
    }

    /**
     * The picture size chosen in the element (core field "size"); null keeps the size
     * the template suggests.
     *
     * @return array<mixed>|null
     */
    private function size(mixed $value): array|null
    {
        $size = StringUtil::deserialize($value, true);

        return [] !== array_filter($size, static fn (mixed $part): bool => '' !== trim((string) $part) && '0' !== (string) $part) ? $size : null;
    }

    private function image(mixed $uuid): string|null
    {
        return \is_string($uuid) && Validator::isUuid($uuid) ? $uuid : null;
    }

    /**
     * Images of a file selection in the chosen order; folders and other files are skipped.
     *
     * @return list<string>
     */
    private function images(mixed $value): array
    {
        $uuids = array_values(array_filter(StringUtil::deserialize($value, true), static fn (mixed $uuid): bool => \is_string($uuid) && Validator::isUuid($uuid)));
        $images = [];

        foreach (FilesModel::findMultipleByUuids($uuids) ?? [] as $file) {
            if ('file' === $file->type && \in_array(strtolower((string) $file->extension), $this->imageExtensions, true)) {
                $images[] = (string) $file->uuid;
            }
        }

        return $images;
    }

    /**
     * @param array<string, mixed> $row
     *
     * @return array<string, mixed>
     */
    private function contact(array $row): array
    {
        $field = static fn (string $name): string => Rows::plain($row[$name] ?? '');
        $place = trim($field('nwPostal').' '.$field('nwCity'));
        $address = array_values(array_filter([$field('nwStreet'), $place], static fn (string $line): bool => '' !== $line));
        $email = $field('nwEmail');
        $phone = $field('nwPhone');
        $route = $field('nwRouteUrl');

        if ('' === $route && [] !== $address) {
            $route = 'https://www.openstreetmap.org/search?query='.rawurlencode(implode(', ', $address));
        }

        return [
            'name' => $field('nwName'),
            'address' => $address,
            'phone' => '' !== $phone ? ['text' => $phone, 'href' => 'tel:'.preg_replace('/[^\d+]/', '', $phone)] : null,
            // Every character is a numeric entity, so the template prints these values unescaped.
            'email' => Validator::isEmail($email) ? ['text' => Rows::obfuscate($email), 'href' => Rows::obfuscate('mailto:'.$email)] : null,
            'hours' => Rows::from($row['nwHours'] ?? null, ['days', 'times'], 10),
            'hint' => $field('nwHint'),
            'route' => '' !== $route ? Rows::plain($this->insertTags->replaceInline($route)) : '',
        ];
    }
}
