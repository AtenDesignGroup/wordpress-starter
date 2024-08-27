# wordpress-starter
This repository provides a starting point for FSE supported themes &amp; block plugins for WP sites.

##Login via CLI
- Login locally through lando install [WP-CLI Login Command](https://github.com/aaemnnosttv/wp-cli-login-command)
- Activate the plugin if not already active

Login via this command
```
lando wp login as [USERNAME]
```

Login with terminus for sites on Pantheon
```
terminus wp [SITE].[ENV] -- user create [USERNAME] [NAME]@atendesigngroup.com --role=administrator
```

For sites on WPEngine/Kinsta you will need to SSH into the site as they also come with WP CLI installed on the server. 

#FAQ

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
