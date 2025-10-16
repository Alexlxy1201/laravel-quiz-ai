# 📦 Student-only + Dockerfile (Railway Ready)

This ZIP overlays a Laravel project to provide a student-only photo-to-answer app using OpenAI vision, with a Dockerfile for Railway deployment.

## What's included
- `app/Http/Controllers/SolveController.php`
- `routes/web.php`
- `resources/views/solve.blade.php`
- `public/css/quiz.css`
- `.env.example` (no secrets)
- `Dockerfile`
- `.dockerignore`

## How to deploy on Railway
1. Put these files at your Laravel project root (same folder as `artisan`).
2. Commit & push to GitHub.
3. In Railway: New Project → Deploy from GitHub.
4. Set Variables: `OPENAI_API_KEY`, `OPENAI_MODEL=gpt-4o-mini`, `OPENAI_BASE_URL=https://api.openai.com/v1`, `APP_ENV=production`, `APP_DEBUG=false`, `MOCK=false`.
5. Wait for build, open the public URL. Upload a question image and get instant solution (no storage).
