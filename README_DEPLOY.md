# 🚀 Railway Final Add-on (Nixpacks) — Laravel AI Quiz Solver (Student Only)

Use this package to deploy your **student-only** Laravel AI quiz solver (photo → GPT answer) on Railway using **nixpacks.toml**.

## What's inside
- `nixpacks.toml` — Nixpacks config (PHP 8.3 + Composer at build & runtime)
- `init.sh` — auto-generate `APP_KEY` and start server
- `README_DEPLOY.md` — how to use

## How to use
1. Copy these files into your Laravel project root (same folder as `artisan`).
2. Commit & push:
   ```bash
   git add nixpacks.toml init.sh
   git commit -m "add nixpacks runtime php and init script"
   git push
   ```
3. Railway → Deployments → Redeploy.
4. Add Variables:
   - `APP_ENV=production`
   - `APP_DEBUG=false`
   - `OPENAI_API_KEY=sk-...`
   - `OPENAI_MODEL=gpt-4o-mini`
   - `OPENAI_BASE_URL=https://api.openai.com/v1`
   - `MOCK=false`

## Expected logs
```
installing 'php83Packages.composer'
composer install --no-interaction --prefer-dist --optimize-autoloader
🔑 Generating Laravel APP_KEY...
Application key set successfully.
🌐 Launching Laravel server...
Laravel development server started: http://0.0.0.0:PORT
```
