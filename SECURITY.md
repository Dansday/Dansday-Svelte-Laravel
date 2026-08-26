# Security Policy

## Reporting a vulnerability

Email **security@dansday.com**. Do not open a public issue or pull request for a security problem.

Please include:

- What the issue is and where in the code it lives — the public site (`main`), the admin panel (`admin`), or the MCP endpoint.
- Steps to reproduce, or a short proof of concept.
- What an attacker gains — data read, privileges gained, content modified, service taken down.
- Whether you tested dansday.com or a self-hosted install, and which version.

You will get a first reply within 72 hours. Valid reports get a fix timeline in that reply, and credit in the release notes unless you ask otherwise.

## Scope

In scope:

- This repository — the SvelteKit public site, the Laravel admin panel, the MCP tool layer and the database access in both apps.
- dansday.com and its admin panel.

Out of scope:

- Anything needing an admin session or an MCP token that you already own. An MCP token is designed to grant full content read and write, including deletes — using one as intended is not a vulnerability.
- Self-hosted installs misconfigured against the guidance below.
- Findings from an automated scanner with no working exploit, including dependency alerts already listed in this repository's Dependabot page.
- Any third-party AI or embedding provider you configure in the panel — report those to the vendor.
- Rate limits, missing headers or version disclosure with no demonstrated impact.

## Testing rules

Test only against your own self-hosted install. Do not touch other people's data, and do not run denial-of-service or automated scans against dansday.com.

## Supported versions

Fixes ship on the latest release only. There are no long-term support branches — update to the newest tag before reporting.

## Self-hosting

You own the security of your own deployment:

- Keep `.env` out of version control. It holds `APP_KEY`, database credentials and Redis credentials.
- Put both apps behind HTTPS. The admin panel authenticates with a session cookie, and the MCP endpoint authenticates with a bearer token — over plain HTTP both travel in the clear.
- Do not expose the MySQL or Redis port to the internet. Both `main` and `admin` connect directly to MySQL.
- Give each app its own database user, not root.
- **AI and embedding provider keys are stored in the database**, in the `page_setting` row, not in `.env`. Treat the database as credential storage and restrict access accordingly.
- The AI and embedding endpoints you configure in the panel are fetched by the server. Only point them at hosts you trust.
- Run `composer audit` and `npm audit` after pulling, and update dependencies.

### MCP tokens

The MCP endpoint grants full write access to site content, including deletes. Treat a token like a password:

- Serve `/mcp` over HTTPS only.
- Mint one token per client (`php artisan mcp:token "<name>"`) so you can revoke one without breaking the others.
- Revoke anything you no longer use: `php artisan mcp:token --revoke=<id>`. Audit with `--list`, which shows `last_used_at`.
- Tokens are stored as SHA-256 hashes, so a database read does not hand over usable tokens — but it does reveal the AI provider keys above.
- The endpoint bypasses the panel's `XSS` middleware by design, because that middleware's `strip_tags` pass mangles escaped angle brackets in code samples. Article and project bodies are instead stripped of `script`, `style`, `iframe`, `object`, `embed` and `form` elements, inline `on*` handlers and `javascript:` URLs before they are stored. Anything else you write through MCP is rendered as raw HTML on the public site, so do not hand a token to a client you would not trust with that.

### LinkedIn credentials

Connecting LinkedIn stores an OAuth access token in `page_setting`, alongside your member URN. That token can publish to your personal feed for the two months it stays valid.

- `LINKEDIN_CLIENT_SECRET` belongs in the environment, never in the database or the repository.
- The token is stored in plaintext in `page_setting`, so a database read hands over the ability to post as you. Same blast radius as the AI provider keys in the same row.
- Anyone holding an MCP token can call `post_to_linkedin` and publish under your name. `confirm=true` guards against accident, not against a hostile client.
- Revoke access from LinkedIn's side under **Settings → Data privacy → Permitted services**; clearing the three `linkedin_*` columns only stops this app from using the token, it does not invalidate it.
- The OAuth callback sits behind the panel's `auth` middleware and checks a session `state` value, so the code exchange cannot be driven by a third party.

### Uploads

The `admin/public/uploads` tree is user content served as-is. `POST /mcp/uploads` writes into it with only an MCP token, so that token is enough to place a file on your public web root — it is restricted to JPG and PNG under 8MB, with a random 24-character filename, but treat it as a write path when you decide who gets a token. Deletes are constrained to an allowlist of prefixes (`uploads_path_safe_to_delete`), and MCP image paths are rejected unless they resolve under `uploads/img/`. Do not widen either without understanding that both guard against path traversal.
