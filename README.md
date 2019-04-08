## Project title
Client Friendly wp2Static

## Motivation
wp2Static is fantastic for a number of use cases. However, it is not client friendly.
Using it in a production environment means clients must be able to deploy safely without breaking the deployment by changing keys or advanced settings. 

## Build status
This is a rough first pass on the plug in. Follow for future updates. 
 
## Screenshots

[PluginDeployment](https://i.imgur.com/RA2g7h3.jpg)


## Tech/framework used

<b>Built with</b>
- Wordpress MU Plugin
-- Basic admin page 
-- Simple ajax script to check status
- Bash Script

## Installation

The plug in works in two steps

1) Wordpress install

- Download this repo and copy the folder to your wp-content directory. ( e.g. domain.tld/wp-content/mu-plugins/static-deployment )

- Add deploy.php the load.php in your mu-plugins directory ( e.g. require WPMU_PLUGIN_DIR.'/static-deployment/deploy.php'; ).

- Modify the *deploy.php* file to include your username ($showforusername)

- Modify the *deploy_button_admin_page* function to include text for your client.

2) Bash / Cron Install

- Move the 'deploy.sh' out of your mu-plugins directory to your hosting root (or somewhere private)

- Update the deploy.sh to have the correct path for Wordpress and the mu-plugin directory. 

- Create a cron to run the deploy script every minute (or 5, 10, 15 minutes depending on how patient your client is). e.g. ( */2 * * * * bash deploy.sh >/dev/null 2>&1 )


## How it works

The plugin works by creating a lockfile when the client is ready to deploy.

The bash file is tiny, and won't do anything unless the lock file exists (so it can happily run every few minutes). 

When the bash script finds the lock file is then executes the wp2Static generate and deploy commands using WP-CLI and saves what it is doing to a txt file that the admin page reads every second. 

## More on wp2Static

[wp2Static](https://github.com/WP2Static/wp2static) is used to generate a static copy of your site and deploy to GitHub Pages, S3, Netlify, etc. 
Increase security, pageload speed and hosting options. Connect WordPress into your CI/CD workflow.