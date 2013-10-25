# see https://github.com/drush-ops/drush/issues/212 for the use of grep -v 

MYTESTGROUP=demo
echo $(drush test-run $MYTESTGROUP 2>&1) | grep -v '\[error\]' 
