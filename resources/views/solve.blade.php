<!DOCTYPE html>
<html lang="zh">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>AI 解题助手 / AI Quiz Solver</title>
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 min-h-screen flex flex-col items-center justify-center p-4">
  <div class="bg-white shadow-lg rounded-lg p-6 w-full max-w-lg text-center">
    <h1 class="text-xl font-bold mb-4">📸 AI 解题助手</h1>
    <p class="text-gray-600 mb-4">上传或拍照获取即时答案与解析<br><small>(Upload or take a photo to get instant answers and explanations. No data is stored)</small></p>

    <!-- Hidden input -->
    <input type="file" id="fileInput" accept="image/*" class="hidden">
    <input type="file" id="cameraInput" accept="image/*" capture="environment" class="hidden">

    <!-- Buttons -->
    <div class="flex justify-center gap-4 mb-4">
      <button id="cameraButton" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded">📷 拍照 / Take Photo</button>
      <button id="chooseButton" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">📁 选择文件 / Choose File</button>
    </div>

    <div id="preview" class="mt-4"></div>
    <button id="solveButton" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded mt-4">Solve with AI</button>
    <div id="result" class="mt-6 p-4 bg-white rounded-lg shadow hidden"></div>
  </div>

  <script>
    const fileInput = document.getElementById('fileInput');
    const cameraInput = document.getElementById('cameraInput');
    const chooseButton = document.getElementById('chooseButton');
    const cameraButton = document.getElementById('cameraButton');
    const solveButton = document.getElementById('solveButton');
    const preview = document.getElementById('preview');
    const resultDiv = document.getElementById('result');
    let selectedFile = null;

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
          preview.innerHTML = `<img src="${e.target.result}" class="rounded mt-3 shadow max-h-60 mx-auto">`;
        };
        reader.readAsDataURL(file);
      }
    }

    solveButton.addEventListener('click', async () => {
      if (!selectedFile) {
        alert("请先选择或拍照上传一张图片\n(Please choose or take a photo first)");
        return;
      }

      resultDiv.classList.remove('hidden');
      resultDiv.innerHTML = "⏳ 正在分析题目，请稍候... (Solving...)";

      const reader = new FileReader();
      reader.onload = async e => {
        const base64 = e.target.result;
        const res = await fetch('/api/solve', {
          method: 'POST',
          headers: {'Content-Type': 'application/json'},
          body: JSON.stringify({ image: base64 })
        });

        const data = await res.json();
        if (data.ok && data.data) {
          const d = data.data;
          resultDiv.innerHTML = `
            <h3 class="text-lg font-bold mb-2">✅ AI Answer</h3>
            <p><strong>Question:</strong> ${d.question}</p>
            <p><strong>Answer:</strong> ${d.answer}</p>
            <h4 class="font-semibold mt-3">🧩 Reasoning</h4>
            <ul class="list-disc pl-5 text-gray-700">
              ${d.reasoning.map(r => `<li>${r}</li>`).join('')}
            </ul>
            <h4 class="font-semibold mt-3">📘 Knowledge Points</h4>
            <ul class="list-disc pl-5 text-gray-700">
              ${d.knowledge_points.map(k => `<li>${k}</li>`).join('')}
            </ul>
          `;
        } else {
          resultDiv.innerHTML = `<p class="text-red-600">❌ ${data.error || 'Error solving question'}</p>`;
        }
      };
      reader.readAsDataURL(selectedFile);
    });
  </script>
</body>
</html>
