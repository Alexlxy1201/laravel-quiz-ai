# 📸 AI Quiz Solver (Laravel – Student Only)

A minimal **English UI** web page for students to upload or take a photo of a question and get **instant AI answers** (no storage, no teacher panel). Uses **OpenAI Vision** (e.g., `gpt-4o-mini`).

## 📦 What is this?
This is a **drop-in feature pack** for a Laravel 10/11 project.

## 🛠 How to use
1. Create a fresh Laravel project (or use an existing one):
   ```bash
   composer create-project laravel/laravel laravel-quiz-ai
   cd laravel-quiz-ai
   ```
2. Copy the contents of this zip **into your Laravel root**, merging folders (`app/`, `routes/`, `resources/`, `public/`).
3. Install dependencies and run dev server:
   ```bash
   composer install
   php artisan key:generate
   php artisan serve
   ```
4. Configure `.env`:
   ```ini
   OPENAI_API_KEY=sk-xxxx
   OPENAI_MODEL=gpt-4o-mini
   OPENAI_BASE_URL=https://api.openai.com/v1
   MOCK=false
   ```
5. Open: `http://localhost:8000`

## 🚀 Deploy to Railway
- Push your Laravel repo to GitHub
- Create a Railway project and connect the repo
- Set environment variables in Railway:
  - `APP_KEY` (use `php artisan key:generate --show` locally)
  - `APP_ENV=production`
  - `OPENAI_API_KEY=sk-xxxx`
  - `OPENAI_MODEL=gpt-4o-mini`
  - `OPENAI_BASE_URL=https://api.openai.com/v1`
  - `MOCK=false`
- Deploy. Visit the generated URL.

## 🔒 Notes
- This app does **not** store any images or results. The image is read and directly sent to the API as base64.
- If you need to demo without an API key, set `MOCK=true` in `.env`.
- Max upload size is 10MB (configurable in `SolveController`).

## ✅ Endpoints
- `GET /` → Student page (upload & solve)
- `POST /api/solve` → Vision call, returns JSON

MIT License
