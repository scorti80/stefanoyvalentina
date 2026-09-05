# Stefano & Valentina — private wedding gallery

A Laravel application for indexing two private photo collections, creating PIN-protected playlists, serving temporary media links, and showing a private wedding film.

## Local setup

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate
npm run build
php artisan wedding:create-admin
php artisan serve
```

Set `WEDDING_SITE_PIN` in `.env` before opening the guest homepage. Once an administrator exists, the site PIN can also be changed under **Admin → Privacy**. The database value takes precedence over the environment value.

The admin is available at `/admin`. There is deliberately no public registration screen.

## Private S3 setup

Keep Amazon S3 Block Public Access enabled for the bucket. Configure these values in `.env`:

```dotenv
FILESYSTEM_DISK=s3
AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=
AWS_URL=
AWS_ENDPOINT=
AWS_USE_PATH_STYLE_ENDPOINT=false
```

For AWS itself, leave `AWS_URL` and `AWS_ENDPOINT` empty. They are available for compatible object-storage services.

The application's IAM identity needs `s3:ListBucket` for the bucket and `s3:GetObject` for the wedding-media prefix. It does not need public-object permissions. Prefer a server role over long-lived access keys when the hosting platform supports roles.

Example object layout:

```text
wedding/
  photographer-1/
    web/
      IMG_0001.jpg
    hd/
      IMG_0001.jpg
  photographer-2/
    web/
      DSC_0001.jpg
    hd/
      DSC_0001.jpg
  video/
    wedding-film.mp4
    poster.jpg
```

Create one collection for each photographer in the admin. Enter the web and HD prefixes without the bucket name. Web and HD photographs are paired by their relative filename, case-insensitively. A missing variant is allowed and is reported by the sync command.

Run a sync directly:

```bash
php artisan media:sync
php artisan media:sync photographer-one
```

The admin's **Sync now** button dispatches the same work to the queue. In production, keep a Laravel queue worker running:

```bash
php artisan queue:work --timeout=1800
```

Synchronization is idempotent. It updates indexed records without duplicating photographs or changing playlist selections. S3 files that disappear are marked inactive; the application never deletes S3 objects.

## Access model

- `/` and `/watch` require the site PIN.
- Every `/photos/{secret}` playlist requires its own PIN, even after the site PIN was entered.
- A successful playlist PIN also unlocks the homepage and film for that session, preventing a double prompt.
- Changing a PIN invalidates previous guest authorizations for that protected area.
- Playlist links contain 48-character random tokens. Tokens are encrypted at rest and also indexed using a SHA-256 digest.
- Display and download links are generated from private storage with short expirations.
- Administrators can revoke a playlist link by generating a replacement.

## Crawler protection

The application sends `X-Robots-Tag` directives on every HTTP response, includes matching HTML metadata, disallows all crawlers in `robots.txt`, publishes no sitemap, avoids social-preview images, and applies private/no-store cache headers to guest pages.

Crawler directives are advisory. The actual privacy boundary is the mandatory server-side PIN authorization plus private object storage. No media URL is generated before authorization.

## Production checklist

```bash
composer install --no-dev --optimize-autoloader
npm ci
npm run build
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Also configure HTTPS, `APP_URL`, `APP_ENV=production`, `APP_DEBUG=false`, secure database backups, a queue worker, and writable `storage` and `bootstrap/cache` directories. Use `SESSION_SECURE_COOKIE=true` under HTTPS.

## Tests

```bash
php artisan test
```

The feature suite covers the site gate, playlist gate, access revocation, crawler headers, admin authorization, playlist creation, and S3 web/HD pairing.
