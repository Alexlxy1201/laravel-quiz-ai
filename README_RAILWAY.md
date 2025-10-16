# 🚀 Railway Auto-Init Deployment – Laravel AI Quiz Solver

This package automatically sets up Laravel on Railway **without manually adding APP_KEY**.

---

## 🧩 What it does
- Ensures `.env` exists (copies from `.env.example` if missing)
- Generates `APP_KEY` automatically using `php artisan key:generate`
- Starts Laravel server on the Railway port (`php artisan serve --host=0.0.0.0 --port=$PORT`)

---

## 📦 Files
| File | Purpose |
|------|----------|
| `init.sh` | Startup script that initializes and launches Laravel |
| `Procfile` | Tells Railway to run the shell script instead of PHP directly |
| `railway.json` | Defines PHP environment and build steps |

---

## 🧭 Usage
1. Place these files in the root of your Laravel project (same folder as `artisan`).
2. Commit and push to GitHub.
3. Deploy on Railway:
   - **New Project → Deploy from GitHub repo**
4. Add environment variables in Railway **(no need for APP_KEY)**:

| Key | Example Value |
|-----|----------------|
| `APP_ENV` | production |
| `APP_DEBUG` | false |
| `OPENAI_API_KEY` | sk-xxxxxx |
| `OPENAI_MODEL` | gpt-4o-mini |
| `OPENAI_BASE_URL` | https://api.openai.com/v1 |
| `MOCK` | false |

---

## ✅ Done
Once Railway builds successfully, visit:

```
https://<your-app>.up.railway.app
```

Laravel will auto-init APP_KEY and start your AI Quiz Solver.
