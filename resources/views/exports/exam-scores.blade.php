<x-print-layout>
    <div class="container mx-auto py-6">
        <div class="text-center mb-6">
            <h1 class="text-2xl font-bold">Daftar Nilai Ujian</h1>
            <h2 class="text-xl">{{ $exam->name }}</h2>
            @if($school)
                <p class="text-gray-600">{{ $school->name }}</p>
            @endif
            @if($grade)
                <p class="text-gray-600">Kelas {{ $grade->name }}</p>
            @endif
            <p class="text-gray-600 mt-2">Total Skor Maksimal: {{ $totalPossibleScore }}</p>
        </div>

        <table class="min-w-full border border-gray-300">
            <thead>
                <tr>
                    <th class="border border-gray-300 px-4 py-2">No</th>
                    <th class="border border-gray-300 px-4 py-2">NIS</th>
                    <th class="border border-gray-300 px-4 py-2">Nama Lengkap</th>
                    <th class="border border-gray-300 px-4 py-2">Kelas</th>
                    <th class="border border-gray-300 px-4 py-2">Nilai</th>
                    <th class="border border-gray-300 px-4 py-2">Total Skor</th>
                    <th class="border border-gray-300 px-4 py-2">Persentase</th>
                </tr>
            </thead>
            <tbody>
                @foreach($scores as $index => $score)
                    <tr>
                        <td class="border border-gray-300 px-4 py-2 text-center">{{ $index + 1 }}</td>
                        <td class="border border-gray-300 px-4 py-2">{{ $score['nis'] }}</td>
                        <td class="border border-gray-300 px-4 py-2">{{ $score['name'] }}</td>
                        <td class="border border-gray-300 px-4 py-2">{{ $score['grade'] }}</td>
                        <td class="border border-gray-300 px-4 py-2 text-right">{{ $score['score'] }}</td>
                        <td class="border border-gray-300 px-4 py-2 text-center">{{ $score['score'] }}/{{ $score['total_possible'] }}</td>
                        <td class="border border-gray-300 px-4 py-2 text-right">{{ $score['percentage'] }}%</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</x-print-layout>
