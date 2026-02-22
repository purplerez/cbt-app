{{-- Edit Question Modal Content (loaded via AJAX) --}}
<form action="{{ route('admin.exams.question.update', $question->id) }}" method="post" enctype="multipart/form-data"
    class="space-y-4">
    @csrf
    @method('PUT')

    <input type="hidden" id="questionId" value="{{ $question->id }}">
    <input type="hidden" id="questionType" value="{{ $question->question_type_id }}">

    {{-- Question Text --}}
    <div>
        <label for="question_text" class="block text-sm font-medium text-gray-700">Pertanyaan</label>
        <textarea id="question_text" name="question_text" rows="3"
            class="tinymce-editor block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">{{ $question->question_text }}</textarea>
    </div>

    {{-- Question Image Section --}}
    <div>
        <label for="question_image" class="block text-sm font-medium text-gray-700">Gambar Soal (Opsional)</label>
        <input type="file" id="question_image" name="question_image" accept="image/*"
            class="block w-full mt-1 text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">

        {{-- Current Image Preview --}}
        @if ($question->question_image)
            <div class="mt-2 p-2 bg-gray-100 rounded">
                <p class="text-xs font-medium text-gray-600 mb-1">Gambar Saat Ini:</p>
                <img src="{{ Storage::url($question->question_image) }}" alt="Current"
                    class="max-w-xs rounded max-h-32">
            </div>
        @endif

        {{-- New Image Preview --}}
        <div id="edit_question_image_preview" class="hidden mt-2 p-2 bg-gray-100 rounded">
            <p class="text-xs font-medium text-gray-600 mb-1">Preview Gambar Baru:</p>
            <img src="" alt="New Preview" class="max-w-xs rounded max-h-32">
        </div>
    </div>

    {{-- Answer Choices (for non-essay questions) --}}
    @if ($question->question_type_id != 3)
        <div>
            <label class="block text-sm font-medium text-gray-700">Pilihan Jawaban</label>
            <div id="edit-choices-container-modal" class="space-y-4">
                @if (!empty($choices))
                    @foreach ($choices as $key => $choice)
                        <div class="edit-choice-item border-l-4 border-blue-400 pl-4"
                            data-choice-id="{{ $key }}">
                            <div class="space-y-2">
                                <textarea name="choices[{{ $key }}]" rows="2"
                                    class="tinymce-editor block w-full border-gray-300 rounded-md">{{ $choice }}</textarea>

                                <div>
                                    <label class="block text-xs font-medium text-gray-600">Gambar Pilihan
                                        (Opsional)</label>
                                    <input type="file" name="choice_images[{{ $key }}]" accept="image/*"
                                        class="block w-full mt-1 text-xs text-gray-500 file:mr-2 file:py-1 file:px-3 file:rounded file:border-0 file:text-xs file:font-semibold file:bg-green-50 file:text-green-700 hover:file:bg-green-100">

                                    {{-- Current Image Preview --}}
                                    @if (isset($choicesImages[$key]))
                                        <div class="mt-2 p-2 bg-gray-100 rounded">
                                            <p class="text-xs font-medium text-gray-600 mb-1">Gambar Saat Ini:</p>
                                            <img src="{{ Storage::url($choicesImages[$key]) }}" alt="Current"
                                                class="max-w-xs rounded max-h-32">
                                        </div>
                                    @endif

                                    {{-- New Image Preview --}}
                                    <div id="edit_choice_image_preview_{{ $key }}"
                                        class="hidden mt-2 p-2 bg-gray-100 rounded">
                                        <p class="text-xs font-medium text-gray-600 mb-1">Preview Gambar Baru:</p>
                                        <img src="" alt="New Preview" class="max-w-xs rounded max-h-32">
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>
        </div>

        {{-- Answer Key --}}
        <div>
            <label class="block text-sm font-medium text-gray-700">Kunci Jawaban</label>
            {{-- Hidden input carries the stored answer key (letters) to JS --}}
            <input type="hidden" id="modal-answer-key-data" value="{{ json_encode($answerKey) }}">
            <div id="edit-answer-key-container-modal">
                <p class="text-sm text-gray-500">Loading...</p>
            </div>
        </div>
    @else
        {{-- Essay Question Answer Key --}}
        <div>
            <label class="block text-sm font-medium text-gray-700">Kunci Jawaban</label>
            <textarea name="answer_key" rows="3" class="tinymce-editor block w-full mt-1 border-gray-300 rounded-md">{{ $question->answer_key }}</textarea>
        </div>
    @endif

    {{-- Points --}}
    <div>
        <label class="block text-sm font-medium text-gray-700">Point</label>
        <input type="number" name="points" value="{{ $question->points }}"
            class="block w-full mt-1 border-gray-300 rounded-md">
    </div>

    {{-- Form Buttons --}}
    <div class="flex gap-2">
        <button type="submit" class="px-4 py-2 text-white bg-blue-600 rounded-md hover:bg-blue-700">Update
            Soal</button>
        <button type="button" onclick="closeModal('editSoalModal')"
            class="px-4 py-2 text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300">Batal</button>
    </div>
</form>
