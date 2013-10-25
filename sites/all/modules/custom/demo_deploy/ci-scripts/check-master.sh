# see https://github.com/drush-ops/drush/issues/212 for the use of grep -v 

# this won't work unless you set your base_url in sites/default/settings.php

MYTESTGROUP=demo
RESULT=$(drush test-run $MYTESTGROUP 2>&1)
echo $RESULT
echo $RESULT | grep -v '\[error\]'
