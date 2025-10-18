<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>AI Quiz Solver</title>
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gradient-to-br from-blue-50 to-indigo-100 min-h-screen flex flex-col items-center justify-center p-4">
  <div class="bg-white shadow-2xl rounded-2xl p-6 w-full max-w-2xl text-center transition-all duration-300">
    <h1 class="text-3xl font-extrabold mb-3 bg-gradient-to-r from-indigo-600 to-blue-600 bg-clip-text text-transparent">
      📘 AI Quiz Solver
    </h1>
    <p class="text-gray-600 mb-6">
      Upload or take a photo to get instant answers and explanations.<br>
      <small>(No data is stored)</small>
    </p>

    <!-- Hidden inputs -->
    <input type="file" id="fileInput" accept="image/*" class="hidden">
    <input type="file" id="cameraInput" accept="image/*" capture="environment" class="hidden">

    <!-- Buttons -->
    <div class="flex justify-center gap-4 mb-4">
      <button id="cameraButton" class="bg-green-600 hover:bg-green-700 text-white px-5 py-2.5 rounded-lg shadow transition">
        📷 Take Photo
      </button>
      <button id="chooseButton" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-lg shadow transition">
        📁 Choose File
      </button>
    </div>

    <div id="preview" class="mt-4"></div>
    <button id="solveButton" class="bg-gradient-to-r from-indigo-500 to-blue-500 hover:from-indigo-600 hover:to-blue-600 text-white px-6 py-2.5 rounded-lg shadow-md mt-4 transition">
      🔍 Solve with AI
    </button>

    <div id="result" class="mt-6 p-5 bg-gray-50 rounded-lg shadow-inner hidden text-left"></div>

    <!-- 🕓 History -->
    <div id="history" class="mt-8 text-left hidden">
      <h2 class="text-xl font-bold mb-2 text-indigo-700">📜 Solution History</h2>
      <div id="historyList" class="space-y-3"></div>
    </div>
  </div>

  <script>
    const fileInput = document.getElementById('fileInput');
    const cameraInput = document.getElementById('cameraInput');
    const chooseButton = document.getElementById('chooseButton');
    const cameraButton = document.getElementById('cameraButton');
    const solveButton = document.getElementById('solveButton');
    const preview = document.getElementById('preview');
    const resultDiv = document.getElementById('result');
    const historySection = document.getElementById('history');
    const historyList = document.getElementById('historyList');

    let selectedFile = null;
    let history = [];

    chooseButton.addEventListener('click', () => fileInput.click());
    cameraButton.addEventListener('click', () => cameraInput.click());

    fileInput.addEventListener('change', handleFile);
    cameraInput.addEventListener('change', handleFile);

    function handleFile(event) {
      const file = event.target.files[0];
      if (file) {
        selectedFile = file;
        const reader = new FileReader();
        reader.onload = e => {
          preview.innerHTML = `<img src="${e.target.result}" class="rounded-lg mt-3 shadow-md max-h-60 mx-auto">`;
        };
        reader.readAsDataURL(file);
      }
    }

    solveButton.addEventListener('click', async () => {
      if (!selectedFile) {
        alert("Please choose or take a photo before proceeding.");
        return;
      }

      resultDiv.classList.remove('hidden');
      resultDiv.innerHTML = `<p class="text-gray-600">⏳ Analyzing the question, please wait...</p>`;

      const reader = new FileReader();
      reader.onload = async e => {
        const base64 = e.target.result;
        try {
          const res = await fetch('/api/solve', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ image: base64 })
          });

          const data = await res.json();

          if (data.ok && data.data) {
            const d = data.data;

            const answerHtml = `
              <h3 class="text-lg font-bold mb-2 text-green-600">✅ AI Answer</h3>
              <p><strong>Question:</strong> ${d.question}</p>
              <p><strong>Answer:</strong> ${d.answer}</p>
              <h4 class="font-semibold mt-3 text-indigo-600">🧩 Reasoning</h4>
              <ul class="list-disc pl-5 text-gray-700">
                ${d.reasoning.map(r => `<li>${r}</li>`).join('')}
              </ul>
              <h4 class="font-semibold mt-3 text-indigo-600">📘 Knowledge Points</h4>
              <ul class="list-disc pl-5 text-gray-700">
                ${d.knowledge_points.map(k => `<li>${k}</li>`).join('')}
              </ul>
            `;

            resultDiv.innerHTML = answerHtml;

            // 🔹 Add to history
            history.unshift({
              time: new Date().toLocaleString(),
              question: d.question,
              answer: d.answer,
              reasoning: d.reasoning,
              knowledge_points: d.knowledge_points
            });

            updateHistoryUI();
          } else {
            resultDiv.innerHTML = `<p class="text-red-600">❌ ${data.error || 'Error solving question.'}</p>`;
          }
        } catch (err) {
          resultDiv.innerHTML = `<p class="text-red-600">❌ Network or server error: ${err.message}</p>`;
        }
      };
      reader.readAsDataURL(selectedFile);
    });

    function updateHistoryUI() {
      historySection.classList.remove('hidden');
      historyList.innerHTML = history.map(h => `
        <details class="bg-white rounded-lg p-3 shadow-md">
          <summary class="cursor-pointer font-semibold text-gray-800">
            🕓 ${h.time} — ${h.question.substring(0, 60)}...
          </summary>
          <div class="mt-2 text-sm text-gray-700">
            <p><strong>Answer:</strong> ${h.answer}</p>
            <p class="mt-1"><strong>Reasoning:</strong></p>
            <ul class="list-disc pl-5">${h.reasoning.map(r => `<li>${r}</li>`).join('')}</ul>
            <p class="mt-1"><strong>Knowledge Points:</strong></p>
            <ul class="list-disc pl-5">${h.knowledge_points.map(k => `<li>${k}</li>`).join('')}</ul>
          </div>
        </details>
      `).join('');
    }
  </script>
</body>
</html>
