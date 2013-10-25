# sample usage:
# sites/all/modules/custom/demo_deploy/ci-scripts/deploy.sh tp1@server.example.com /Applications/MAMP/htdocs/drupalcamp-prod-d7 prod

ssh $1 "cd $2 && git pull origin $3"
ssh $1 "drush -r $2 vset maintenance_mode 1"
ssh $1 "drush -r $2 rr"
ssh $1 "drush -r $2 updb -y"
ssh $1 "drush -r $2 cc all"
ssh $1 "drush -r $2 cron"
ssh $1 "drush -r $2 vset maintenance_mode 0"