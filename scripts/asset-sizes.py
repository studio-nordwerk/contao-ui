#!/usr/bin/env python3
"""Measure delivered files, separately gzipped as HTTP responses (no build/minification)."""
import gzip
import json
from pathlib import Path
import re

ROOT = Path(__file__).resolve().parents[1]
PUBLIC = {name: ROOT / 'packages' / name / 'public' for name in ('carousel', 'sheet', 'gallery')}
IMPORTS = re.compile(r'''(?:from\s*|import\s*)["'](\.[^"']+)["']''')


def graph(path):
    seen = set()

    def visit(current):
        current = current.resolve()
        if current in seen:
            return
        seen.add(current)
        for relative in IMPORTS.findall(current.read_text()):
            # Gallery bundles reference the sibling bundles' public directories.
            target = (current.parent / relative).resolve()
            for bundle in ('carousel', 'sheet'):
                sibling = (PUBLIC['gallery'].parent / ('nordwerk' + bundle)).resolve()
                if target.is_relative_to(sibling):
                    target = PUBLIC[bundle] / target.relative_to(sibling)
            visit(target)

    visit(path)
    return seen


def compressed(files):
    return sum(len(gzip.compress(path.read_bytes(), mtime=0)) for path in set(files))


def main():
    carousel = PUBLIC['carousel']
    sheet = PUBLIC['sheet']
    gallery = PUBLIC['gallery']
    result = {
        'carousel': {
            'JS including wrapper': compressed(graph(carousel / 'carousel.js')),
            'CSS': compressed([carousel / 'vendor/carousel.css']),
            'Drag (additional)': compressed(graph(carousel / 'vendor/drag.js') - graph(carousel / 'carousel.js')),
            'Autoplay (additional)': compressed(graph(carousel / 'vendor/autoplay.js') - graph(carousel / 'carousel.js')),
        },
        'sheet': {
            'JS including wrapper': compressed(graph(sheet / 'sheet.js')),
            'CSS including theme': compressed([sheet / 'vendor/sheet.css', sheet / 'theme.css']),
            'Drag (additional)': compressed(graph(sheet / 'vendor/drag.js') - graph(sheet / 'sheet.js')),
            'History (additional)': compressed(graph(sheet / 'vendor/history.js') - graph(sheet / 'sheet.js')),
            'Non-dismissible CSS (additional)': compressed([sheet / 'vendor/options.css']),
        },
        'gallery': {
            'Own JS': compressed([gallery / 'gallery.js']),
            'Own CSS': compressed([gallery / 'gallery.css']),
            'Rail/lightbox JS including dependencies and drag': compressed(graph(gallery / 'gallery.js') | graph(carousel / 'vendor/drag.js')),
            'Rail/lightbox CSS including dependencies': compressed([gallery / 'gallery.css', carousel / 'vendor/carousel.css', sheet / 'vendor/sheet.css', sheet / 'theme.css']),
        },
    }
    print(json.dumps(result, indent=2))


if __name__ == '__main__':
    main()
