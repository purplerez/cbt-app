<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            Detail Ujian Siswa
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <!-- Student Information -->
                    <div class="mb-6 bg-gray-50 rounded-lg p-4">
                        <h3 class="text-lg font-semibold mb-2">Informasi Siswa</h3>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <p><span class="font-medium">Nama:</span> {{ $session->student->name }}</p>
                                <p><span class="font-medium">NIS:</span> {{ $session->student->nis }}</p>
                                <p><span class="font-medium">Kelas:</span> {{ $session->student->grade->name }}</p>
                            </div>
                            <div>
                                <p><span class="font-medium">Waktu Mulai:</span> {{ $session->start_time->format('d M Y H:i:s') }}</p>
                                <p><span class="font-medium">Waktu Selesai:</span> {{ $session->end_time ? $session->end_time->format('d M Y H:i:s') : 'Belum selesai' }}</p>
                                <p><span class="font-medium">Status:</span> 
                                    @if($session->is_completed)
                                        <span class="text-green-600">Selesai</span>
                                    @else
                                        <span class="text-yellow-600">Sedang Mengerjakan</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Answer Details -->
                    <div class="mt-6">
                        <h3 class="text-lg font-semibold mb-4">Detail Jawaban</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            No
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Soal
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Jawaban Siswa
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Kunci Jawaban
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Status
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($answers as $index => $answer)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                                {{ $index + 1 }}
                                            </td>
                                            <td class="px-6 py-4 text-sm">
                                                {!! nl2br(e($answer['question_text'])) !!}
                                            </td>
                                            <td class="px-6 py-4 text-sm">
                                                @if($answer['question_type'] == 1) {{-- Multiple Choice --}}
                                                    {{ $answer['answer'] ?? '-' }}
                                                @else {{-- Essay --}}
                                                    <div class="whitespace-pre-wrap">{{ $answer['answer'] ?? '-' }}</div>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 text-sm">
                                                {{ $answer['correct_answer'] }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                                @if($answer['answer'] === null)
                                                    <span class="text-yellow-600">Belum dijawab</span>
                                                @elseif($answer['question_type'] == 1 && $answer['answer'] === $answer['correct_answer'])
                                                    <span class="text-green-600">Benar</span>
                                                @elseif($answer['question_type'] == 1)
                                                    <span class="text-red-600">Salah</span>
                                                @else
                                                    <span class="text-blue-600">Essay</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Back Button -->
                    <div class="mt-6">
                        <a href="javascript:history.back()" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                            Kembali
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>