## Configuring DDEV with Pantheon
For projects hosted on Pantheon, we can set up an integration to pull the database directly into DDEV by following these steps:

1. Create a new [Pantheon Machine Token](https://docs.pantheon.io/machine-tokens). You can create a new one for every project or reuse a DDEV specific one.
2. Check in your home folder for a default DDEV config file. (`~/.ddev/global_config.yaml`). If it doesn’t exist, you can create the file with the following information. If it does exist, add your Terminus machine token:

```
web_environment:
    - TERMINUS_MACHINE_TOKEN=your_machine_token
```

3. Update the project name within `.ddev/providers/pantheon.yaml`:

```
environment_variables:
    project: pantheon-site-name.dev
```

4. Replace the URLs in `.ddev/config.yaml` with the project's Pantheon URLs:

```
hooks:
  post-import-db:
    - exec: wp search-replace https://dev-your-site.pantheonsite.io https://your-site.ddev.site
    - exec: wp search-replace https://test-your-site.pantheonsite.io https://your-site.ddev.site
```

5. Run the following command to restart DDEV with the updated configuration:

```
ddev restart
```

6. Run the following command to pull the database and files from Pantheon:

```
ddev pull pantheon
```