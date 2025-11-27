<x-print-layout>
    <div class="container py-6 mx-auto">
        <div class="mb-6 text-center">
            <h1 class="text-2xl font-bold">Daftar Nilai Ujian</h1>
            <h2 class="text-xl">{{ $exam->name }}</h2>
            @if($school)
                <p class="text-gray-600">{{ $school->name }}</p>
            @endif
            @if($grade)
                <p class="text-gray-600">Kelas {{ $grade->name }}</p>
            @endif
            <p class="mt-2 text-gray-600">Total Skor Maksimal: {{ $totalPossibleScore }}</p>
        </div>

        <table class="min-w-full border border-gray-300">
            <thead>
                <tr>
                    <th class="px-4 py-2 border border-gray-300">No</th>
                    <th class="px-4 py-2 border border-gray-300">NIS</th>
                     @if(!$school)
                        <th class="px-4 py-2 border border-gray-300">Madrasah</th>
                    @endif
                    <th class="px-4 py-2 border border-gray-300">Nama Lengkap</th>
                    <th class="px-4 py-2 border border-gray-300">Kelas</th>
                    <th class="px-4 py-2 border border-gray-300">Nilai </th>

                    <th class="px-4 py-2 border border-gray-300">Nilai Persentase</th>
                </tr>
            </thead>
            <tbody>
                @foreach($scores as $index => $score)
                    <tr>
                        <td class="px-4 py-2 text-center border border-gray-300">{{ $index + 1 }}</td>
                        <td class="px-4 py-2 border border-gray-300">{{ $score['nis'] }}</td>
                        @if(!$school)
                            <td class="px-4 py-2 border border-gray-300">{{ $score['school'] }}</td>
                        @endif
                        <td class="px-4 py-2 border border-gray-300">{{ $score['name'] }}</td>
                        <td class="px-4 py-2 border border-gray-300">{{ $score['grade'] }}</td>
                        <td class="px-4 py-2 text-center border border-gray-300">{{ $score['score'] }}/{{ $score['total_possible'] }}</td>
                        <td class="px-4 py-2 text-right border border-gray-300">{{ $score['percentage'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</x-print-layout>
