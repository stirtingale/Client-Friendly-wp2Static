#!/bin/bash
#
# wp2static pretty deployment script
#

# Set PATH environment variable
export PATH="/usr/local/bin:/usr/bin:/bin"

# USER VARIABLES 
# configure to match your production environment

wordpress_directory="~/public_html/wp"
mu_plugins_directory="~/public_html/app/mu-plugins"

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
	wp statichtmloutput generate --path="${wordpress_directory}" | adddate >> $log
	wp statichtmloutput deploy --path="${wordpress_directory}" | adddate >> $log

	# DEPLOY
    # Write to log
	echo "Deployment Complete" | adddate >> $log

fi