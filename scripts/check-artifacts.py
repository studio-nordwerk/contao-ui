#!/usr/bin/env python3
"""Audit actual per-package exports and Manager manifests."""
import argparse
import hashlib
import importlib.util
import json
from pathlib import Path
import subprocess
import tempfile
import zipfile

ROOT = Path(__file__).resolve().parents[1]
spec = importlib.util.spec_from_file_location('builder', ROOT / 'scripts/build-artifacts.py')
builder = importlib.util.module_from_spec(spec)
spec.loader.exec_module(builder)
parser = argparse.ArgumentParser(description=__doc__)
parser.add_argument('--ref', default='HEAD')
args = parser.parse_args()
lock = json.loads((ROOT / 'assets.lock.json').read_text())
allowed = ('src/', 'config/', 'contao/', 'translations/', 'public/', 'docs/')

with tempfile.TemporaryDirectory(prefix='contao-ui-artifacts-') as directory:
    for name in builder.PACKAGES:
        path = builder.build(name, '0.1.0-dev', Path(directory), args.ref)
        with zipfile.ZipFile(path) as archive:
            names = archive.namelist()
            assert len(names) == len(set(names)), 'Duplicate ZIP entries'
            assert all(n in ('composer.json', 'README.md', 'LICENSE', 'CHANGELOG.md') or n.startswith(allowed) for n in names), names
            for required in ('composer.json', 'README.md', 'LICENSE', 'CHANGELOG.md', 'src/ContaoManager/Plugin.php', 'config/services.yaml', 'contao/templates/.twig-root', f'public/{name}.svg', f'docs/{name}.png', f'docs/{name}-dark.png'):
                assert required in names, f'{name}: missing {required}'
            integration_source = ROOT / (f'packages/{name}/docs/integration.md' if name in ('teasers', 'testimonials') else 'docs/integration.md')
            assert archive.read('docs/integration.md') == integration_source.read_bytes(), 'Stale integration copy'
            if name in ('teasers', 'testimonials'):
                integration = archive.read('docs/integration.md').decode()
                assert 'GalleryFigures' not in integration and 'nw_sheet_assets' not in integration and 'nw_gallery_assets' not in integration, f'{name}: documents unavailable components'
                assert ('TeaserSourceInterface' if name == 'teasers' else 'submission_nonce') in integration, f'{name}: missing package integration contract'
            manifest = json.loads(archive.read('composer.json'))
            assert manifest['name'] == f'nordwerk/contao-{name}-bundle'
            assert manifest['version'] == '0.1.0-dev'
            assert manifest['type'] == 'contao-bundle' and manifest['license'] == 'MIT'
            assert manifest['require']['contao/core-bundle'] == '^5.7 || ^6.0'
            if name == 'gallery':
                assert all(f'nordwerk/contao-{dependency}-bundle' in manifest['require'] for dependency in ('carousel', 'sheet'))
            elif name in lock:
                for filename, checksum in lock[name]['files'].items():
                    assert hashlib.sha256(archive.read('public/vendor/' + filename)).hexdigest() == checksum, filename
            if name == 'teasers':
                assert 'nordwerk/contao-carousel-bundle' in manifest['require']
            if name == 'testimonials':
                assert 'nordwerk/contao-teasers-bundle' in manifest['require']
            assert f'](docs/{name}.png)' in archive.read('README.md').decode(), 'Standalone screenshot link missing'
            print(f'{name}: Manager ZIP checked ({len(names)} files, pinned runtime assets intact).')
    result = subprocess.run(['python3', str(ROOT / 'scripts/build-artifacts.py'), '0.1.0-garbage', directory], capture_output=True)
    assert result.returncode != 0, 'Invalid versions must be rejected'
    assert len(list(Path(directory).glob('*.zip'))) == len(builder.PACKAGES)
