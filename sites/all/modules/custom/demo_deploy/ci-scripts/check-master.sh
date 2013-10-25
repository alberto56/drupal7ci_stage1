# see https://github.com/drush-ops/drush/issues/212 for the use of grep -v 

MYTESTGROUP=demo
RESULT=$(drush test-run $MYTESTGROUP 2>&1)
echo $RESULT
echo $RESULT | grep -v '\[error\]'
