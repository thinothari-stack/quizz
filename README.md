# quizz
Interactive Quiz Application

## Local setup with WampServer

The local WampServer setup uses Apache's virtual host and serves only `public/`.
The checked local installation is `C:\wamp64`, with Apache 2.4.65, PHP 8.3.28
for Apache, and PostgreSQL 18 listening on `127.0.0.1:5432`. Apache's active
PHP configuration already has `pdo_pgsql` and `pgsql` enabled.

1. Copy `.env.example` to `.env` if you do not already have a local `.env`.
   Set `DATABASE_URL` to the credentials and database created in PostgreSQL,
   and replace `APP_SECRET` with a random value. Keep `.env` local.
2. Install dependencies from the project directory:

   ```powershell
   cd E:\quizz
   composer install
   ```

   The command-line PHP on this machine is WampServer PHP 8.4.15. It can run
   Composer and Symfony's console commands alongside Apache's PHP 8.3.28.
3. Configure the `quizz.test` virtual host in
   `C:\wamp64\bin\apache\apache2.4.65\conf\extra\httpd-vhosts.conf`.
   If WampServer already created a `quizz.test` virtual host, edit that block
   instead of adding a duplicate. Set its document root to
   `E:/quizz/public` and its `<Directory>` path to the same folder, following
   [`wamp-vhost.conf.example`](./wamp-vhost.conf.example). Never expose
   `E:/quizz` as Apache's document root.
4. Open Notepad as Administrator and add this line to
   `C:\Windows\System32\drivers\etc\hosts`:

   ```text
   127.0.0.1 quizz.test
   ```

5. From the WampServer tray menu, choose **Restart All Services**, then open
   <http://quizz.test>. If the WampServer home page still appears, exit
   WampServer, reopen it as Administrator, restart all services, and reload the
   page. Seeing `quizz.test` in WampServer's virtual-host list does not by
   itself mean the running Apache service has loaded the updated document root.
6. From the project directory, apply any pending migrations:

   ```powershell
   php bin/console doctrine:migrations:migrate
   ```

   To initialize a new, empty database, create it first with
   `php bin/console doctrine:database:create`, then load the sample data with
   `php bin/console doctrine:fixtures:load`. Loading fixtures clears existing
   tables by default; `--append` keeps them but inserts duplicate sample data
   if run more than once.

If Apache reports `could not find driver`, use the WampServer PHP Extensions
menu for the active Apache PHP version to enable `pdo_pgsql` (and `pgsql` if
needed), then restart Apache. The active Apache PHP configuration currently
limits uploads to 2 MB; increase `upload_max_filesize` and `post_max_size` in
WampServer's PHP settings if larger images are needed.

### Local email and file uploads

For WampServer, local `.env.local` can configure Gmail SMTP as described
below. The base `.env` and `.env.example` keep `MAILER_DSN=null://null`, so
SMTP credentials are never committed. With Docker Compose, the override starts
Mailcatcher; open <http://127.0.0.1:1080> to inspect captured messages.

Uploaded images are stored under `public/` in the requested subdirectory.
The uploader creates missing directories and reports filesystem errors rather
than returning a filename for a failed upload.

PDF generation through Dompdf works locally. The separate wkhtmltopdf binary
configured by `WKHTMLTOPDF_PATH` is not installed with WampServer; install a
Windows build and set that variable if a feature using KnpSnappy is enabled.

To create a development administrator, set `QUIZZ_ADMIN_PASSWORD` in
`.env.local` before loading fixtures. The fixture then creates the `admin`
account with a hashed password; optional `QUIZZ_ADMIN_USERNAME`,
`QUIZZ_ADMIN_EMAIL`, and `QUIZZ_ADMIN_NAME` values can customize it. Without
that password variable, no administrator account is seeded.

## Docker image and GitHub Actions

The `CI and deployment` workflow runs Composer validation, Symfony linting,
PHPUnit, and PostgreSQL migration/schema checks on each pull request to `main`.
Pull requests build the Docker image but do not publish it. Merges to `main`
publish `ghcr.io/thinothari-stack/quizz` to GitHub Container Registry.

To run the application locally in Docker, copy `.env.example` to `.env`, set a
unique `APP_SECRET`, then run:

```sh
docker compose up --build -d
docker compose exec app php bin/console doctrine:migrations:migrate --no-interaction
```

Open <http://127.0.0.1:8080>. `docker compose up` includes the local
Mailcatcher override; captured messages are available at
<http://127.0.0.1:1080>. Seed sample quiz data once with
`docker compose exec app php bin/console doctrine:fixtures:load`.

### Pull requests and production deployment

Configure GitHub branch protection for `main` and require the
`Tests and database checks` status check before merging. Then configure a
GitHub environment named `production` and add a required reviewer so a
deployment must be approved after a pull request is merged.

Alwaysdata's standard managed PHP hosting is not a Docker runtime. The workflow
therefore publishes the Docker image separately and deploys the PHP release to
Alwaysdata over SSH. A deployment is skipped until the repository Actions
variable `ALWAYSDATA_DEPLOY_ENABLED` is set to `true`. Once the Alwaysdata site
is configured for the address `thinothari.alwaysdata.net/quizz`, set its
document root to `/home/<account>/www/quizz/current/public` and its PHP version
to 8.3 or newer. This is the `/quizz` path on the account's Alwaysdata domain,
not a separate subdomain.

Configure `ALWAYSDATA_DEPLOY_ENABLED=true` as a repository Actions variable.
Configure these remaining variables in the GitHub `production` environment:

* `ALWAYSDATA_HOST` — `ssh-<account>.alwaysdata.net`;
* `ALWAYSDATA_USER` — the SSH user configured in Alwaysdata;
* `ALWAYSDATA_DEPLOY_PATH` — an absolute release directory such as
  `/home/<account>/www/quizz`;
* `ALWAYSDATA_URL` — `https://thinothari.alwaysdata.net/quizz/`, used for a
  post-deploy HTTP health check;
* `ALWAYSDATA_KNOWN_HOSTS` — the SSH host key verified against the fingerprint
  shown in the Alwaysdata administration interface.

Create a dedicated SSH key pair for GitHub Actions, add its public key to the
Alwaysdata SSH user, and store its private key as the
`ALWAYSDATA_SSH_KEY` secret in the `production` environment. Do not put
credentials in the workflow or repository.

Before enabling deployments, create
`/home/<account>/www/quizz/shared/.env.local` on Alwaysdata with production
values for `APP_ENV=prod`, `APP_DEBUG=0`, `APP_SECRET`, `DATABASE_URL`,
`MAILER_DSN`, `MAILER_FROM`, `MESSENGER_TRANSPORT_DSN`, and `WKHTMLTOPDF_PATH`.
Use the database hostname, database name, user and port shown in Alwaysdata's
PostgreSQL administration page; Alwaysdata supports PostgreSQL 16, 17 and 18.
The deployment keeps `.env.local`, `var/log`, and uploaded files outside
individual releases, runs Doctrine migrations, warms the production cache, and
switches the site's `current` release only after those commands succeed.

Images, PDFs, or other uploaded files in `public/profil`, `public/type`,
`public/reponse`, and `public/reponse_bonus` are ignored by Git. Add required
starter assets to version control intentionally or upload them to Alwaysdata;
the deployment preserves files already stored there.

After confirming the production site works, configure rollback/backup and
retention for old releases before treating this pipeline as a long-term
production deployment.
