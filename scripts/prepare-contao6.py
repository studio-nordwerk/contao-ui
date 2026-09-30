#!/usr/bin/env python3
import json
from pathlib import Path
import shutil
root = Path(__file__).resolve().parents[1]
app = root / 'app6'
app.mkdir(exist_ok=True)
(app / 'public').mkdir(exist_ok=True)
shutil.copytree(root / 'app/config', app / 'config', dirs_exist_ok=True)
config = json.loads((root / 'app/composer.json').read_text())
config['require']['php'] = '^8.4'
config['require']['contao/managed-edition'] = '6.0.*'
(app / 'composer.json').write_text(json.dumps(config, indent=2) + '\n')
