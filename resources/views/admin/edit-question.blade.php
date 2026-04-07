<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Edit Soal Ujian
            </h2>
            <a href="{{ route('exams.manage.question', session('perexamid')) }}" class="inline-flex items-center px-4 py-2 text-xs font-semibold tracking-widest text-gray-700 uppercase transition duration-150 ease-in-out bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Kembali
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-4xl sm:px-6 lg:px-8">
            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form action="{{ route('exams.question.update', session('perexamid')) }}" method="POST" enctype="multipart/form-data" id="editQuestionForm">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="question_id" value="{{ $question->id }}">

                        <!-- Question Type Indicator -->
                        <div class="mb-6 p-4 bg-blue-50 rounded-lg border border-blue-200">
                            <p class="text-sm font-medium text-blue-800">
                                Tipe Soal:
                                @switch($question->question_type_id)
                                    @case('0')
                                        Pilihan Ganda
                                        @break
                                    @case('1')
                                        Pilihan Ganda Kompleks (Jawaban Majemuk)
                                        @break
                                    @case('2')
                                        Benar/Salah
                                        @break
                                    @case('3')
                                        Esai
                                        @break
                                    @default
                                        Tidak Diketahui
                                @endswitch
                            </p>
                        </div>

                        <!-- Question Text -->
                        <div class="mb-6">
                            <label for="question_text" class="block text-sm font-medium text-gray-700 mb-2">
                                Teks Soal
                            </label>
                            <textarea name="question_text" id="question_text" rows="5" class="block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500">{{ old('question_text', $question->question_text) }}</textarea>
                            <x-input-error :messages="$errors->get('question_text')" class="mt-2" />
                        </div>

                        <!-- Question Image -->
                        <div class="mb-6">
                            <label for="question_image" class="block text-sm font-medium text-gray-700 mb-2">
                                Gambar Soal (Opsional)
                            </label>
                            @if($question->question_image)
                                <div class="mb-4">
                                    <img src="{{ Storage::url($question->question_image) }}" alt="Question Image" class="w-48 h-auto rounded-lg border border-gray-300">
                                    <div class="mt-2">
                                        <label class="flex items-center gap-2">
                                            <input type="checkbox" name="remove_question_image" value="1" class="rounded border-gray-300">
                                            <span class="text-sm text-gray-600">Hapus gambar soal</span>
                                        </label>
                                    </div>
                                </div>
                            @endif
                            <input type="file" name="question_image" id="question_image" accept="image/*" class="block w-full border border-gray-300 rounded-lg p-2 focus:ring-blue-500 focus:border-blue-500">
                            <x-input-error :messages="$errors->get('question_image')" class="mt-2" />
                        </div>

                        <!-- Choices (if not essay) -->
                        @if($question->question_type_id != '3')
                            <div class="mb-6">
                                <label class="block text-sm font-medium text-gray-700 mb-4">Pilihan Jawaban</label>
                                <div id="choicesContainer" class="space-y-4">
                                    @forelse($choices as $key => $choice)
                                        <div class="flex items-start gap-4 p-4 border border-gray-300 rounded-lg bg-gray-50">
                                            <div class="flex-1">
                                                <label class="block text-xs font-medium text-gray-600 mb-2">
                                                    Pilihan {{ chr(65 + $key) }}
                                                </label>
                                                <textarea name="choices[{{ $key }}]" rows="2" class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" placeholder="Masukkan teks pilihan">{{ $choice }}</textarea>
                                            </div>
                                            <div class="flex-shrink-0">
                                                <label class="flex items-center gap-2 pt-8">
                                                    <input type="checkbox" name="answer_key[]" value="{{ $key }}" {{ in_array($key, $answerKey) ? 'checked' : '' }} class="rounded border-gray-300 text-blue-600">
                                                    <span class="text-sm text-gray-600">Jawaban Benar</span>
                                                </label>
                                            </div>
                                        </div>
                                    @empty
                                        <p class="text-gray-500 text-sm">Tidak ada pilihan jawaban</p>
                                    @endforelse
                                </div>
                                <x-input-error :messages="$errors->get('choices')" class="mt-2" />
                                <x-input-error :messages="$errors->get('answer_key')" class="mt-2" />
                            </div>
                        @else
                            <!-- Essay Answer Key -->
                            <div class="mb-6">
                                <label for="answer_key" class="block text-sm font-medium text-gray-700 mb-2">
                                    Kunci Jawaban / Rubrik
                                </label>
                                <textarea name="answer_key" id="answer_key" rows="5" class="block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500">{{ old('answer_key', $question->answer_key) }}</textarea>
                                <x-input-error :messages="$errors->get('answer_key')" class="mt-2" />
                            </div>
                        @endif

                        <!-- Points -->
                        <div class="mb-6">
                            <label for="points" class="block text-sm font-medium text-gray-700 mb-2">
                                Nilai / Poin
                            </label>
                            <input type="number" name="points" id="points" value="{{ old('points', $question->points) }}" min="1" step="0.5" class="block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            <x-input-error :messages="$errors->get('points')" class="mt-2" />
                        </div>

                        <!-- Form Actions -->
                        <div class="flex items-center justify-end gap-4 pt-6 border-t">
                            <a href="{{ route('exams.manage.question', session('perexamid')) }}" class="inline-flex items-center px-4 py-2 text-xs font-semibold tracking-widest text-gray-700 uppercase transition duration-150 ease-in-out bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                                Batal
                            </a>
                            <button type="submit" class="inline-flex items-center px-4 py-2 text-xs font-semibold tracking-widest text-white uppercase transition duration-150 ease-in-out bg-blue-600 border border-transparent rounded-md hover:bg-blue-700">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            @if($errors->any())
                <div class="mt-6 p-4 bg-red-50 border border-red-200 rounded-lg">
                    <h4 class="text-sm font-medium text-red-800 mb-2">Terjadi Kesalahan:</h4>
                    <ul class="text-sm text-red-700 list-disc list-inside space-y-1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
