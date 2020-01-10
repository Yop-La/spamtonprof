#write out current crontab
crontab -l > mycron
#echo new cron into cron file
echo '* * * * * curl "https://[[domain]]/wp-content/plugins/wp-bhm-generator/cron.php"  >/dev/null 2>&1 ' >> mycron
#install new cron file
crontab mycron
rm mycron
