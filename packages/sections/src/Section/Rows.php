<?php

declare(strict_types=1);

namespace Nordwerk\SectionsBundle\Section;

use Contao\StringUtil;

/**
 * Reads the rows of a row wizard field and drops rows without any text.
 */
final class Rows
{
    /**
     * @param list<string> $keys
     *
     * @return list<array<string, string>>
     */
    public static function from(mixed $value, array $keys, int $limit): array
    {
        $rows = [];

        foreach (StringUtil::deserialize($value, true) as $row) {
            if (!\is_array($row)) {
                continue;
            }
            $clean = [];

            foreach ($keys as $key) {
                $clean[$key] = self::plain($row[$key] ?? '');
            }
            if ('' !== implode('', $clean) && (!isset($row['enable']) || $row['enable'])) {
                $rows[] = $clean;
            }
        }

        return \array_slice($rows, 0, $limit);
    }

    /**
     * Non-empty lines of a textarea.
     *
     * @return list<string>
     */
    public static function lines(mixed $value, int $limit): array
    {
        $lines = preg_split('/\R/u', self::plain($value)) ?: [];

        return \array_slice(array_values(array_filter(array_map('trim', $lines), static fn (string $line): bool => '' !== $line)), 0, $limit);
    }

    /**
     * Plain text from a backend field: Contao stores input with HTML entities, Twig
     * escapes on output.
     */
    public static function plain(mixed $value): string
    {
        $decoded = StringUtil::decodeEntities((string) $value);

        return trim(\is_string($decoded) ? $decoded : '');
    }

    /**
     * Fully entity-encoded text, safe to print unescaped; hides e-mail addresses from
     * simple robots.
     */
    public static function obfuscate(string $value): string
    {
        return implode('', array_map(static fn (string $char): string => '&#'.mb_ord($char).';', mb_str_split($value)));
    }

    /**
     * Splits a figure like "6 Wo.", "2019" or "4,9" into the number and its unit.
     *
     * @return array{number: string, unit: string}
     */
    public static function figure(string $value): array
    {
        if (preg_match('/^([+\-–−]?\d[\d.,]*\s?[+%]?)\s*(.*)$/u', trim($value), $match)) {
            return ['number' => trim($match[1]), 'unit' => trim($match[2])];
        }

        return ['number' => trim($value), 'unit' => ''];
    }
}
