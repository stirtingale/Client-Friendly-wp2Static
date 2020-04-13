#!/bin/bash
#
# wp2static pretty deployment script
#

# Set PATH environment variable
export PATH="/usr/local/bin:/usr/bin:/bin"

# USER VARIABLES 
# configure to match your production environment

wordpress_directory="/home/www/html/menasp/wp"
mu_plugins_directory="/home/www/html/menasp/app/mu-plugins"

# SCRIPT VARIABLES

runscript="${mu_plugins_directory}/static-deployment/.run"
log="${mu_plugins_directory}/static-deployment/log.txt"

# CHECK LOCKFILE EXISTS

if [[ -f $runscript ]]; then

    # REMOVE LOCKFILE
    # avoids cron running twice if generation
    # takes longer than the cron interval.
    rm $runscript

    # LOG BASICS
	adddate() {
	    while IFS= read -r line; do
	        echo "$(date +"%Y-%m-%d") $(date +"%T") :: $line"
	    done
	}

    # Write to log
	echo >> $log
	echo "Starting Deployment" | adddate >> $log

	# DEPLOY
	/usr/local/bin/wp wp2static generate --path="${wordpress_directory}" --allow-root | adddate >> $log
	/usr/local/bin/wp wp2static deploy --path="${wordpress_directory}" --allow-root | adddate >> $log
    
    # Write to log
	echo "Deployment Complete" | adddate >> $log


fi