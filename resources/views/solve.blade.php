<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>AI Quiz Solver</title>
  <link rel="stylesheet" href="/css/quiz.css">
</head>
<body>
  <header>
    <h1>📸 AI Quiz Solver</h1>
    <p>Upload or take a photo to get instant answers and explanations. (No data is stored)</p>
  </header>
  <main>
    <div class="card">
      <form id="form">
        <input type="file" id="file" name="image" accept="image/*" capture="environment" required>
        <button type="submit">Solve with AI</button>
      </form>
      <div id="preview"></div>
      <section id="output" class="hidden">
        <h2>Result</h2>
        <div class="grid">
          <div>
            <h3>Question</h3>
            <pre id="q"></pre>
          </div>
          <div>
            <h3>Answer</h3>
            <pre id="a"></pre>
          </div>
          <div>
            <h3>Reasoning</h3>
            <ul id="r"></ul>
          </div>
          <div>
            <h3>Knowledge Points</h3>
            <ul id="k"></ul>
          </div>
        </div>
      </section>
      <pre id="raw" class="raw"></pre>
    </div>
  </main>
  <footer><small>© 2025 AI Quiz Solver – Student-only version</small></footer>

  <script>
  const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
  const form = document.getElementById('form');
  const fileInput = document.getElementById('file');
  const preview = document.getElementById('preview');
  const out = document.getElementById('output');
  const q = document.getElementById('q');
  const a = document.getElementById('a');
  const r = document.getElementById('r');
  const k = document.getElementById('k');
  const raw = document.getElementById('raw');

  form.addEventListener('submit', async e => {
    e.preventDefault();
    const file = fileInput.files[0];
    if (!file) return alert('Please select a photo');

    preview.innerHTML = '';
    const img = document.createElement('img');
    img.src = URL.createObjectURL(file);
    preview.appendChild(img);

    out.classList.add('hidden');
    raw.textContent = 'Processing...';

    const fd = new FormData();
    fd.append('image', file);

    try {
      const resp = await fetch('/api/solve', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': token },
        body: fd
      });
      const json = await resp.json();
      raw.textContent = JSON.stringify(json, null, 2);

      if (!json.ok) {
        out.classList.add('hidden');
        return;
      }

      const d = json.data || {};
      q.textContent = d.question || '';
      a.textContent = d.answer || '';

      r.innerHTML = '';
      (d.reasoning || []).forEach(step => {
        const li = document.createElement('li');
        li.textContent = step;
        r.appendChild(li);
      });

      k.innerHTML = '';
      (d.knowledge_points || []).forEach(kw => {
        const li = document.createElement('li');
        li.textContent = kw;
        k.appendChild(li);
      });

      out.classList.remove('hidden');
    } catch (err) {
      raw.textContent = 'Error: ' + err.message;
      out.classList.add('hidden');
    }
  });
  </script>
</body>
</html>
