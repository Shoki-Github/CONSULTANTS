# CLM Consultants Limited API

Laravel REST API for the Vue website. Public routes use the `/api/v1` prefix. The API uses MySQL in deployments, Sanctum cookie-based sessions for the first-party SPA, Form Request validation, permission-backed policies, and server-side rate limits.

## Local setup

1. Create a MySQL database and configure `.env` with its connection details.
2. Set `APP_DEBUG=true` only in local development. Set `FRONTEND_URL` and `SANCTUM_STATEFUL_DOMAINS` to the exact frontend origin and trusted hosts.
3. Install dependencies and initialize the schema and reference data:

   ```sh
   composer install
   php artisan key:generate
   php artisan migrate --seed
   ```

4. Start the API:

   ```sh
   php artisan serve
   ```

Run the frontend separately from `frontend/` with `npm install` and `npm run dev`. The Vite development server proxies API and Sanctum requests to `http://127.0.0.1:8000`.

## Included modules

- Public services, published news, FAQs, and forum topic reads.
- Rate-limited contact and consultation submissions with server validation and opaque references.
- Sanctum session registration, login, current-user, and logout endpoints.
- Forum topics and replies require an authenticated account and are queued for moderation.
- Role/permission tables and server-side policies. Public registrations receive only the `registered_member` role.
- Permission-gated content management for services, news, and FAQs; admin workflows for consultations and forum moderation.
- Sensitive staff changes are recorded in an audit log without capturing message bodies or credentials.
- API security headers, explicit credentialed CORS origin configuration, and CSRF middleware for stateful SPA requests.

No real company copy or sample service records are seeded. Add approved content before publishing; consultation requests require at least one active service.

## First administrator

Create a normal account through the site, then grant the administrator role from an authorized terminal:

```sh
php artisan app:grant-administrator name@example.com
```

The command requires confirmation and only promotes an existing active account. Do not expose administrative access through public registration.

## Production checklist

- Use HTTPS; set `APP_DEBUG=false`, `SESSION_SECURE_COOKIE=true`, and production-only `APP_KEY`/database credentials.
- Keep session cookies `HttpOnly` and `SameSite=Lax` for the same-site SPA setup; use a shared cookie domain only when the deployment requires it.
- Restrict `FRONTEND_URL` and `SANCTUM_STATEFUL_DOMAINS` to the actual trusted site hosts. Keep credentialed CORS origins explicit.
- Keep MySQL private and use a least-privilege database account.
- Serve the built frontend through the production web server; do not use Vite's development server in production.
- Configure and verify CSP, TLS/HSTS, encrypted backups, monitoring, and recovery procedures at deployment.
- Use `php artisan migrate` for controlled schema changes; never use `migrate:fresh` against a database with data to preserve.

## Next blueprint phases

The first implementation slice establishes the public site, inquiry workflows, member session foundation, role-gated content tools, and forum moderation. The remaining proposed work includes user/role administration screens, password recovery and email verification, private file uploads, queued email notifications, and deployment monitoring/recovery.

## Tests

```sh
php artisan test
```
