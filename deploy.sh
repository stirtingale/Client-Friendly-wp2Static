#!/bin/bash
#
# wp2static pretty deployment script
#

# USER VARIABLES 
# configure to match your production environment

wordpress_directory="~/public_html"
mu_plugins_directory="~/public_html/wp-content/mu-plugins"

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
	wp wp2static generate --path="${wordpress_directory}" --allow-root | adddate >> $log
	wp wp2static deploy --path="${wordpress_directory}" --allow-root | adddate >> $log
    
    # Write to log
	echo "Deployment Complete" | adddate >> $log


fi