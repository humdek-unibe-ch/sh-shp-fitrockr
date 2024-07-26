# v1.0.7 - Requires SelfHelp v7.0.0+
### New Features
 - make it compatible with the `user_input` refactoring

# v1.0.6
### New Features
 - load plugin version using `BaseHook` class

# v1.0.5 requires SelfHelp <code>6.1.6+</code>
 - adjust create user with parameters: `height`, `year`, `weight` to work with the fixed `validation` style

# v1.0.4
 - requires SelfHelp <code>6.0.0+</code>
 - adjust internal/external data changes

# v1.0.3
### New Features
 - when a Fitrockr user is created automatically on successful validation the email is `user_code@unibe.ch`

# v1.0.2
### New Features
 - add a column day in `fitrockr_activities_summary`

# v1.0.1
### Bugfix
 - properly set height, weight and year

# v1.0.0
### New Features

 - The Fitrockr plugin
 - add page `sh_global_fitrockr`
  - field `fitrockr_api_key`
  - field `fitrockr_api_tenant`
 - add update `Fitrockr` user
 - automatically create `Fitrockr` user on validate and link it to Selfhelp
 - pull all `dailySummaries` for a user. The old data is deleted and it is reinserted
 - pull all `activities` for a user. The old data is deleted and it is reinserted
 - save `fitrockr_activities_summary` for a user. The old data is deleted and it is reinserted
 - add manual pull for a selected user
 - add cronjob for pulling all the data for all the users