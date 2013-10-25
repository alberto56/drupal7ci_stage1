# sample usage:
# sites/all/modules/custom/demo_deploy/ci-scripts/deploy.sh tp1@server.example.com /Applications/MAMP/htdocs/drupalcamp-prod-d7 prod

ssh $1 "cd $2 && git pull origin $3 &&
drush -r $2 rr &&
drush -r $2 vset maintenance_mode 1 &&
drush -r $2 cc all &&
drush -r $2 en demo_deploy -y &&
drush -r $2 updb -y &&
drush -r $2 cc all &&
drush -r $2 cron &&
drush -r $2 vset maintenance_mode 0"

# todo: backup the db here with something like
# drush -r $2 sql-dump > ~/mysql-$(git log -n1 --pretty='%c').sql