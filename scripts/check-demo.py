#!/usr/bin/env python3
"""Keep the demo with published credentials reachable only on loopback."""
import json
import os
import shlex
import subprocess

command = shlex.split(os.environ.get('DC', 'docker compose'))
config = json.loads(subprocess.check_output([*command, 'config', '--format', 'json']))
for name, service in config['services'].items():
    for port in service.get('ports', []):
        assert port.get('host_ip') in ('127.0.0.1', '::1'), f'{name}: demo port is not limited to loopback'
print('Demo ports: loopback only.')
