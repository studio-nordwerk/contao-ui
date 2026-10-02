#!/usr/bin/env python3
"""Build one Contao Manager ZIP per bundle from a Git tree; never package untracked files."""
import argparse
import io
import json
from pathlib import Path
import re
import subprocess
import zipfile


def unix_mode(info):
    """Readable for every PHP user: files 0644, directories 0755 (zipfile writes 0600 by default)."""
    return (0o40755 << 16 | 0x10) if info.is_dir() else 0o100644 << 16

ROOT = Path(__file__).resolve().parents[1]
PACKAGES = ('carousel', 'sheet', 'gallery', 'sections')
VERSION = re.compile(r'\d+\.\d+\.\d+(?:-dev|-(?:alpha|beta|RC|rc)(?:[.-]?\d+)?)?')


def build(name, version, directory, ref='HEAD'):
    source = subprocess.check_output(
        ['git', 'archive', '--format=zip', f'{ref}:packages/{name}'], cwd=ROOT,
    )
    output = directory / f'contao-{name}-bundle-{version}.zip'
    directory.mkdir(parents=True, exist_ok=True)
    with zipfile.ZipFile(io.BytesIO(source)) as archive, zipfile.ZipFile(output, 'x', compression=zipfile.ZIP_DEFLATED) as target:
        for entry in archive.infolist():
            if entry.is_dir():
                continue
            data = archive.read(entry.filename)
            if entry.filename == 'composer.json':
                manifest = json.loads(data)
                manifest['version'] = version
                data = (json.dumps(manifest, ensure_ascii=False, indent=2) + '\n').encode()
            entry.create_system, entry.external_attr = 3, unix_mode(entry)
            target.writestr(entry, data)
    return output


def main():
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument('version')
    parser.add_argument('output', type=Path)
    parser.add_argument('--ref', default='HEAD', help='Git commit/tree (default: HEAD)')
    args = parser.parse_args()
    if not VERSION.fullmatch(args.version):
        parser.error('Use a Composer version such as 0.1.0, 0.1.0-dev or 0.1.0-RC1')
    for name in PACKAGES:
        print(build(name, args.version, args.output, args.ref))


if __name__ == '__main__':
    main()
