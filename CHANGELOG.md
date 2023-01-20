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