<div id="importModal" class="fixed inset-0 bg-black bg-opacity-50 hidden">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full">
            <div class="p-6">
                <div class="flex justify-between items-start">
                    <div>
                        <h3 class="text-lg font-medium">Import Questions</h3>
                        <p class="text-sm text-gray-600">Upload a file containing quiz, set and question data. This is UI-only placeholder.</p>
                    </div>
                    <button onclick="closeImportModal()" class="text-gray-500">Close</button>
                </div>

                <div class="mt-4">
                    <label class="block font-medium">File</label>
                    <input type="file" class="mt-2" />
                </div>

                <div class="mt-4 bg-gray-50 p-4 rounded">
                    <h4 class="font-semibold">Import format (recommended)</h4>
                    <p class="text-sm text-gray-700 mt-2">Provide a JSON file with this structure (example):</p>
                    <pre class="text-xs bg-white p-2 rounded border mt-2"><code>{
  "quizzes": [
    {
      "title": "Quiz A",
      "description": "...",
      "sets": [
        {
          "title": "Set 1",
          "part": 1,
          "questions": [
            {
              "type": "mcq",
              "stem": "Question text",
              "options": ["A","B","C","D"],
              "correct": 0
            }
          ]
        }
      ]
    }
  ]
}</code></pre>

                    <p class="text-sm text-gray-600 mt-2">Fields: quiz -> sets -> questions. Questions must include type, stem, options, correct index (0-based). Other meta fields allowed.</p>
                </div>

                <div class="mt-4 flex justify-end space-x-2">
                    <button onclick="closeImportModal()" class="px-4 py-2 border rounded">Cancel</button>
                    <button class="px-4 py-2 bg-indigo-600 text-white rounded">Upload (placeholder)</button>
                </div>
            </div>
        </div>
    </div>
</div>
