#!/bin/bash
if [[ $(ps aux | grep '[n]ginx' | wc -l) -eq 0 ]] || [[ $(ps aux | grep '[p]hp-fpm' | wc -l) -eq 0 ]]; then
    exit 1
fi

if ! curl -f http://localhost/ > /dev/null 2>&1; then
    exit 1
fi

exit 0
