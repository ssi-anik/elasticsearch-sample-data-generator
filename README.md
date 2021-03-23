elasticsearch-sample-data-generator
---

The purpose of the project is to generate a dump for Elasticsearch Bulk API.

## Requirements
- Either you machine should PHP or docker installed to get it working. And the local PHP version should be `>=7.3` and `<8.0`

## Installation
- Clone the repository.
- If you have `php` and `composer` installed on your local machine, then run `composer install` to install the project dependencies.
- If you don't know PHP or requirement is not satisfied on your machine, then uncomment the `COPY . /app` and `RUN composer install` lines in `Dockerfile`. So, they'll look like the following.
```dockerfile
# It'll copy the project in the PHP container.
COPY . /app
# It'll install the project dependencies.
RUN composer install
```
- Run `cp docker-compose.yml.example docker-compose.yml`
- Make changes in your `docker-compose.yml` file. If you don't need the elasticsearch & kibana, remove those services.
- If you changed in the `Dockerfile`, then in you `docker-compose.yml` file, comment/remove the `.:/app` and uncomment the `./dumps:/app/dumps` volumes in your `php` service.
- Run `docker-compose up -d --build` to start your containers.
- If you're using docker, then exec in the container with `docker-compose exec php bash`.
- Run `./elasticsearch-dump generate "fields_you_need"` to generate the dump. This is just the basic command. Check the documentation to find out what you can do with it.

## Documentation
Published [an article on dev.to](https://dev.to/ssianik) as a documentation. Find it out there.

## Issues or Something is missing?
As always, let me know if something is wrongly done or missed something. Open or comment in an issue. Did I miss something that can be used by others? Submit a PR. 
