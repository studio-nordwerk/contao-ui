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
            assert archive.read('docs/integration.md') == (ROOT / 'docs/integration.md').read_bytes(), 'Stale integration copy'
            manifest = json.loads(archive.read('composer.json'))
            assert manifest['name'] == f'nordwerk/contao-{name}-bundle'
            assert manifest['version'] == '0.1.0-dev'
            assert manifest['type'] == 'contao-bundle' and manifest['license'] == 'MIT'
            assert manifest['require']['contao/core-bundle'] == '^5.7 || ^6.0'
            if name == 'gallery':
                assert all(f'nordwerk/contao-{dependency}-bundle' in manifest['require'] for dependency in ('carousel', 'sheet'))
            else:
                for filename, checksum in lock[name]['files'].items():
                    assert hashlib.sha256(archive.read('public/vendor/' + filename)).hexdigest() == checksum, filename
            assert f'](docs/{name}.png)' in archive.read('README.md').decode(), 'Standalone screenshot link missing'
            print(f'{name}: Manager ZIP checked ({len(names)} files, pinned runtime assets intact).')
    result = subprocess.run(['python3', str(ROOT / 'scripts/build-artifacts.py'), '0.1.0-garbage', directory], capture_output=True)
    assert result.returncode != 0, 'Invalid versions must be rejected'
    assert len(list(Path(directory).glob('*.zip'))) == 3
