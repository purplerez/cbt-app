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
                    <div class="p-4 mb-6 rounded-lg bg-gray-50">
                        <h3 class="mb-2 text-lg font-semibold">Informasi Siswa</h3>
                        <div class="grid grid-cols-2 gap-2">

                            <div class="">
                                <p><span class="font-medium">Nama:</span> {{ $session->student->name }}</p>
                                <p><span class="font-medium">NIS:</span> {{ $session->student->nis }}</p>
                                <p><span class="font-medium">Kelas:</span> {{ $session->student->grade->name }}</p>
                            </div>
                            <div>
                                <p><span class="font-medium">Waktu :</span> {{ $session->started_at ?  $session->started_at->format('d M Y H:i:s') : '-'}} s/d {{ $session->submited_at ? $session->submited_at->format('d M Y H:i:s') : 'Belum selesai' }}</p>
                                {{-- <p><span class="font-medium">Waktu Selesai:</span> </p> --}}
                                <p><span class="font-medium">Status:</span>
                                    @if($session->status == 'submited')
                                        <span class="text-green-600">Selesai</span>
                                    @else
                                        <span class="text-yellow-600">Sedang Mengerjakan</span>
                                    @endif
                                </p>
                                <p></p><span class="font-medium">Total Nilai:</span> {{ isset($points_awarded) ? number_format($points_awarded, 2, ',', '.') : '0' }} / {{ isset($points_total) ? number_format($points_total, 2, ',', '.') : '0' }} <span class="font-bold text-gray-950">({{ isset($percentage) ? number_format($percentage, 2, ',', '.') : '0' }}%)</span></p>
                            </div>
                        </div>
                    </div>

                    <!-- Answer Details -->
                    <div class="mt-6">
                        <h3 class="mb-4 text-lg font-semibold">Detail Jawaban</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                                            No
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                                            Soal
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                                            Jawaban Siswa
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                                            Kunci Jawaban
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                                            Poin
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                                            Status
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($answers as $index => $answer)
                                        <tr>
                                            <td class="px-6 py-4 text-sm whitespace-nowrap">
                                                {{ $index + 1 }}
                                            </td>
                                            <td class="px-6 py-4 text-sm">
                                                {!! nl2br(e($answer['question_text'])) !!}
                                            </td>
                                            <td class="px-6 py-4 text-sm">
                                                @php
                                                    $qtype = $answer['question_type'] ?? null;
                                                    $ans = $answer['answer'] ?? null;
                                                @endphp

                                                @if(in_array($qtype, [0,1,2]))
                                                    {{-- Objective types: Pilihan Ganda (0), Kompleks (1), Benar/Salah (2) --}}
                                                    @if(is_array($ans))
                                                        <div>{{ implode(', ', $ans) }}</div>
                                                    @else
                                                        <div>{{ $ans ?? '-' }}</div>
                                                    @endif
                                                    @if(!empty($answer['choices']))
                                                        <div class="mt-2 text-sm text-gray-500">
                                                            {{-- show available choices (optional) --}}
                                                            @foreach($answer['choices'] as $key => $choice)
                                                                <div><strong>{{ $key }}.</strong> {!! nl2br(e($choice)) !!}</div>
                                                            @endforeach
                                                        </div>
                                                    @endif
                                                @else
                                                    {{-- Essay --}}
                                                    <div class="whitespace-pre-wrap">{{ $ans ?? '-' }}</div>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 text-sm">
                                                {{ $answer['correct_answer'] }}
                                            </td>
                                            <td class="px-6 py-4 text-sm">
                                                @php
                                                    $aw = $answer['points_awarded'] ?? 0;
                                                    $pp = $answer['points_possible'] ?? 0;
                                                @endphp
                                                <span>
                                                    {{ (floor($aw) == $aw) ? (int)$aw : number_format($aw, 2, ',', '.') }}
                                                    /
                                                    {{ (floor($pp) == $pp) ? (int)$pp : number_format($pp, 2, ',', '.') }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 text-sm whitespace-nowrap">
                                                @php
                                                    $ansVal = $answer['answer'] ?? null;
                                                    $qtype = $answer['question_type'] ?? null;
                                                    $isCorrect = $answer['is_correct'] ?? null;
                                                @endphp

                                                @if($ansVal === null)
                                                    <span class="text-yellow-600">Belum dijawab</span>
                                                @elseif(in_array($qtype, [0,1,2]) && $isCorrect === true)
                                                    <span class="text-green-600">Benar</span>
                                                @elseif(in_array($qtype, [0,1,2]))
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
                        <a href="javascript:history.back()" class="inline-flex items-center px-4 py-2 text-xs font-semibold tracking-widest text-white uppercase bg-gray-800 border border-transparent rounded-md hover:bg-gray-700">
                            Kembali
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
