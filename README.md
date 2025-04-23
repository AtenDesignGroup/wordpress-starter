# wordpress-starter

This repository provides a starting point for FSE supported themes &amp; block plugins for WP sites.

## Login via CLI

Install the WP CLI Login Command package:

``` 
ddev exec wp package install aaemnnosttv/wp-cli-login-command
```

Once the package is installed, login via this command:

```
ddev exec wp login as [USERNAME]
```

Login with terminus for sites on Pantheon:

```
terminus wp [SITE].[ENV] -- user create [USERNAME] [NAME]@atendesigngroup.com --role=administrator
```

For sites on WPEngine/Kinsta you will need to SSH into the site as they also come with WP CLI installed on the server.

# FAQ

If you run into an issue with the homepage loading but no other pages add .htcaccess to the root of the project with these lines:

```
# BEGIN WordPress

RewriteEngine On
RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]
RewriteBase /
RewriteRule ^index\.php$ - [L]
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule . /index.php [L]

# END WordPress
```

## Code Repository

- Host: GitHub
- Repo Name: wordpress-starter
- Development URL:
- Repo URL: https://github.com/AtenDesignGroup/wordpress-starter
- Default Branch: `main`
- Owner: Aten

## Code Linting

This WordPress project integrates with GrumPHP code sniffer to lint PHP code. 

By default, commits require a project code. The default project code for this starter kit is `ADGWP`. You'll need to update the project code from Jira on a per-project basis within the `grumphp.yml` file at the project root:

```
matchers:
    Must contain job and issue number: /(ADGWP-\d+|GitHub Actions Build)/
```

By default, GrumPHP is configured to enforce WordPress best practice coding standards. Rules and standards can be changed on a per-project basis by editing the `phpcs.xml` file at the project root. 

Sniffing will automatically be performed pre-commit through git. To perform sniffing manually, run this command:

```
composer grumphp
```

If errors are found, GrumPHP will offer to automatically fix what it can. To manually attempt auto-fixing of any errors found during sniffing, run this command:

```
composer phpcbf-staged
```

# Local Development DDEV

This WordPress project was setup to support DDEV out of the box. Developers can quickly get started setting up your local environment by following the instructions below. Please make sure you've installed [DDEV](https://ddev.readthedocs.io/en/stable/users/install/ddev-installation/).

Now, you'll need to start up the DDEV instance:

```
ddev start
```

This site is hosted on Pantheon which means we can pull the database from the most recent backup directly into DDEV by running the command below, note that this also pulls files:

```
ddev pull pantheon
```

To pull only the database, run this command:

```
ddev pull pantheon --skip-files
```

To pull only the files, run this command:

```
ddev pull pantheon --skip-db
```

By default, DDEV will pull from the `dev` environment of Pantheon. Use this command to pull the most recent backup from a different Pantheon environment, replacing `{ENV_CODE}` with the target environment code from Pantheon: 

```
ddev pull pantheon --environment project=wordpress-starter.{ENV_CODE}
```

If you want to import the database manually, you'll need to run:

```
ddev import-db --file=path-to-db.sql
```

# Local Development Lando
This WordPress project was setup to support Lando out of the box. Developers can quickly get started setting up your local environment by following the instructions below. Please make sure you've installed [Lando](https://docs.lando.dev/basics/installation.html).

Now, you'll need to start up the Lando instance:

```
lando start
```

This site is hosted on Pantheon which means we can pull the database directly into lando by running the command below, note that this also pulls files:

```
lando pull --code=none
```

To pull only the database and most recent file changes run this command:

```
lando pull --code=none rsync
```

If you want to import the database manually, you'll need to run:

```
lando db-import localdatabasename.sql
```

## Development Workflows

When adding new features to the project you'll need to create a feature branch, commonly this is the Jira ticket number (e.g. project-code-XXX). You'll commit all your code changes to this feature branch and push the branch to the code repository. Next you'll need to request a code review by opening a pull request on GitHub. After that, you need to assign the pull request to the tech lead responsible for overseeing the project. In case the deployment process on Pantheon fails, typically due to too many Multi-Dev environments being used, a comment will be added to the GitHub pull request notifying you of this error.

If the tech lead finds issues while reviewing the pull request, a comment will be left within GitHub on the current PR outlining what code needs to be updated/fixed. Any code that's pushed to the current feature branch will be re-deployed to Pantheon. If no issues have been found the tech lead will merge the PR into the `main` branch via GitHub, which will then be deployed to the Pantheon development site.

Assign the Jira ticket to the QA team member, provide the link to the develop environment, and include instructions on what should be tested. Also, please make sure to set up the environment with dummy data to make sure it's working for you prior to getting the QA team involved.

1. Hotfix/Non-Release Deployments
   1. Make sure your local environment does not have uncommitted changes
   2. If your local database is very outdated (over a month), import a new one from production
   3. On the main branch `git checkout main`
   4. Create a feature branch based on the Jira ticket name `git checkout -b project-code-###`
   5. Feature branches `feature/project-code-###` with a pull request will spin up a new multidev environment in Pantheon
   6. Commit changes to your feature branch `git add .` and `git commit -m "project-code-###: note about the change" and `git push`
   7. Merge into staging branch for testing on Pantheon/WP Engine/Kinsta or other Hosting platform `git checkout staging` and `git merge project-code-###`
   8. After your changes are approved on staging, merge your feature branch into the main branch and deploy
      1. `git checkout main` and `git merge project-code-###` and `git push`
2. Using a Release/Sprint Branch - Useful if working on a bunch of features
   1. Create the release branch
      1. Do Once - `git checkout main` && `git checkout -b release-1`
      2. Push the release branch for everyone to work from
   2. Participating in the release
      1. Make sure your local environment does not have uncommitted changes
      2. If your local database is very outdated (over a month), import a new one from production
      3. On the main branch `git checkout release-1`
      4. Create a feature branch of the release branch based on the Jira ticket name `git checkout -b project-code-###`
      5. Commit changes to your feature branch `git add .` and `git commit -m "project-code-###: note about the change" and `git push`
      6. Merge into release branch for testing on Pantheon/WP Engine/Kinsta or other Hosting platform `git checkout release-1` and `git merge project-code-###`
      7. After your changes are approved on staging, merge your feature branch into the main branch and deploy
         1. `git checkout main` and `git merge project-code-###` and `git push`
   3. Deploying the release
      1. You may want to merge main into your release branch to catch any divergence since the branch was created
      2. Merge your release branch into the staging branch to test the release
      3. Once tested, merge the release branch into main
   4. Caveats: If work has been started on the release branch but not finished, it will need to be manually removed

## Theme Information

The wordpress-starter comes with two themes, once a preferred theme is chosen the unused theme can be removed.

### Aten FSE Theme

- This theme is built on top of the WordPress core theme Twenty Twenty Four (https://wordpress.com/theme/twentytwentyfour).
- The theme utilizes the ACF plugin to handle custom fields and Custom Post Types for its unique content. There are several custom plugins that provide addtional templating support within the plugins folder.

### Aten Hybrid Theme

- This theme is built on top of the WordPress core theme Twenty TwentyOne (WP Core theme) - https://wordpress.com/theme/twentytwentyone
- The theme utilizes the ACF plugin to handle custom fields and Custom Post Types for its unique content.
- Uses the classic templating structure while maintaining a modern approach to theming in wordpress
