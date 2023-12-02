#! /bin/bash
set -euo pipefail
curl -s "https://author.cota.com/wp-json/acf/v2/options/" | jq '.acf.alerts' > build/acf-options.json
