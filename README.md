# symfony-test

To run this project please run create first `.env` file and copy contents from `.env.test` to `.env`

Then run commands:
1. `docker-compose up --build`
2. `docker exec symfony_php composer install`
3. `docker exec symfony_php bin/console doctrine:migrations:migrate`

It will be available under `127.0.0.1:7777` or `0.0.0.0:7777`
