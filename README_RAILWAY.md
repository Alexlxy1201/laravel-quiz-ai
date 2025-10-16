# 🚀 Railway Deployment Guide – Laravel AI Quiz Solver

This folder contains everything needed to deploy the **AI Quiz Solver** Laravel app to **Railway.app**.

---

## 1️⃣ Prerequisites
- You already have a GitHub repository (e.g., `laravel-quiz-ai`).
- You have tested locally using:
  ```bash
  php artisan serve
  ```

---

## 2️⃣ Deploy to Railway
1. Go to [https://railway.app](https://railway.app) and sign in with GitHub.
2. Click **New Project → Deploy from GitHub repo**.
3. Choose your Laravel repo.
4. Wait until Railway builds automatically (it will detect PHP via `composer.json`).

---

## 3️⃣ Set Environment Variables
In Railway’s “Variables” tab, add:

| Key | Example Value |
|-----|----------------|
| `APP_KEY` | output of `php artisan key:generate --show` |
| `APP_ENV` | production |
| `APP_DEBUG` | false |
| `OPENAI_API_KEY` | sk-xxxxxx |
| `OPENAI_MODEL` | gpt-4o-mini |
| `OPENAI_BASE_URL` | https://api.openai.com/v1 |
| `MOCK` | false |

---

## 4️⃣ Launch
After deployment finishes, click **“View Deployment”**.

Your Laravel app will be live at:

```
https://<your-app-name>.up.railway.app
```

---

## 5️⃣ Notes
- `Procfile` tells Railway how to start Laravel using the internal PHP server.
- `railway.json` defines build and run steps (composer install, serve).
- No database is needed — this is a stateless student-side app.

If you need to re-deploy after edits:
```bash
git add .
git commit -m "update"
git push
```

Railway will rebuild automatically.

---
✨ Done! Your AI Quiz Solver is now online.
