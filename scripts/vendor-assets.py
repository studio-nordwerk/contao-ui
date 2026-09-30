#!/usr/bin/env python3
"""Copy pinned npm distributions, verify tarball integrity and every shipped byte."""
import argparse
import base64
import hashlib
import io
import json
from pathlib import Path
import tarfile
from urllib.request import urlopen

ROOT = Path(__file__).resolve().parents[1]
LOCK = ROOT / 'assets.lock.json'
SOURCES = {
    'carousel': ('@nordwerk/scroll-carousel', '0.1.5', ['index.js', 'drag.js', 'autoplay.js', 'markup.js', 'carousel.css']),
    'sheet': ('@nordwerk/scroll-sheet', '0.5.0', ['index.js', 'history.js', 'keyboard.js', 'drag.js', 'sheet.css', 'options.css']),
}


def verify(lock):
    for bundle, package in lock.items():
        for name, checksum in package['files'].items():
            path = ROOT / 'packages' / bundle / 'public' / 'vendor' / name
            if not path.is_file() or hashlib.sha256(path.read_bytes()).hexdigest() != checksum:
                raise SystemExit(f'Asset checksum mismatch: {path.relative_to(ROOT)}')
    print('Pinned npm asset checksums verified.')


def copy():
    lock = json.loads(LOCK.read_text()) if LOCK.exists() else {}
    for bundle, (name, version, entries) in SOURCES.items():
        metadata = json.load(urlopen(f'https://registry.npmjs.org/{name}/{version}', timeout=30))
        dist = metadata['dist']
        integrity = lock.get(bundle, {}).get('integrity', dist['integrity'])
        archive = urlopen(dist['tarball'], timeout=30).read()
        actual = 'sha512-' + base64.b64encode(hashlib.sha512(archive).digest()).decode()
        if actual != integrity:
            raise SystemExit(f'npm tarball integrity mismatch: {name}@{version}')
        target = ROOT / 'packages' / bundle / 'public' / 'vendor'
        target.mkdir(parents=True, exist_ok=True)
        files = {}
        with tarfile.open(fileobj=io.BytesIO(archive), mode='r:gz') as tar:
            # Include shared chunks imported by the published ESM entry points.
            members = [m for m in tar.getmembers() if m.name.startswith('package/dist/') and m.name.endswith('.js') and m.name.split('/')[-1] not in ['react.js', 'preact.js']]
            members += [tar.getmember('package/dist/' + n) for n in entries if n.endswith('.css')]
            members += [tar.getmember('package/LICENSE')]
            for member in members:
                filename = member.name.removeprefix('package/dist/').removeprefix('package/')
                if '..' in Path(filename).parts or filename.startswith('/'):
                    raise SystemExit('Unsafe npm distribution path')
                content = tar.extractfile(member).read()
                (target / filename).parent.mkdir(parents=True, exist_ok=True)
                (target / filename).write_bytes(content)
                files[filename] = hashlib.sha256(content).hexdigest()
        lock[bundle] = {'name': name, 'version': version, 'tarball': dist['tarball'], 'integrity': integrity, 'files': dict(sorted(files.items()))}
    LOCK.write_text(json.dumps(lock, indent=2) + '\n')
    verify(lock)


if __name__ == '__main__':
    parser = argparse.ArgumentParser()
    parser.add_argument('--check', action='store_true')
    args = parser.parse_args()
    if args.check:
        verify(json.loads(LOCK.read_text()))
    else:
        copy()
