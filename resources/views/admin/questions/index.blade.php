<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Soal - ExamDoc Parser</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Inter', 'Roboto', sans-serif;
            background: #f8fafc;
            min-height: 100vh;
            padding: 32px 20px;
            color: #1e293b;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 40px;
        }

        .logo {
            font-size: 20px;
            font-weight: 700;
            color: #4f46e5;
            text-decoration: none;
        }

        .header {
            background: white;
            border-radius: 16px;
            padding: 32px;
            margin-bottom: 24px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            display: flex;
            justify-content: space-between;
            align-items: center;
            border: 1px solid #e2e8f0;
        }

        h1 {
            color: #0f172a;
            font-size: 28px;
            font-weight: 700;
            letter-spacing: -0.02em;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            border: none;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .btn-primary {
            background: #4f46e5;
            color: white;
        }

        .btn-primary:hover {
            background: #4338ca;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(79, 70, 229, 0.3);
        }

        .btn-danger {
            background: #ef4444;
            color: white;
            padding: 8px 14px;
            font-size: 13px;
        }

        .btn-danger:hover {
            background: #dc2626;
        }

        .btn-info {
            background: #f8fafc;
            color: #475569;
            padding: 8px 14px;
            font-size: 13px;
            border: 1px solid #e2e8f0;
        }

        .btn-info:hover {
            background: #f1f5f9;
        }

        .alert {
            padding: 16px 20px;
            border-radius: 12px;
            margin-bottom: 24px;
            font-size: 14px;
            background: white;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        }

        .alert-success {
            background: #f0fdf4;
            color: #15803d;
            border: 1px solid #bbf7d0;
        }

        .stats {
            background: white;
            border-radius: 16px;
            padding: 24px 32px;
            margin-bottom: 24px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            text-align: center;
            border: 1px solid #e2e8f0;
        }

        .stats-number {
            font-size: 48px;
            font-weight: 700;
            color: #4f46e5;
            margin-bottom: 4px;
        }

        .stats-label {
            font-size: 14px;
            color: #64748b;
            font-weight: 500;
        }

        .card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            overflow: hidden;
            border: 1px solid #e2e8f0;
        }

        .question-item {
            padding: 32px;
            border-bottom: 1px solid #f1f5f9;
            transition: background 0.2s ease;
        }

        .question-item:hover {
            background: #f8fafc;
        }

        .question-item:last-child {
            border-bottom: none;
        }

        .question-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 16px;
            gap: 20px;
        }

        .question-number {
            font-size: 14px;
            font-weight: 700;
            color: #4f46e5;
            background: #eef2ff;
            padding: 6px 14px;
            border-radius: 8px;
        }

        .question-points {
            font-size: 13px;
            font-weight: 600;
            color: #f59e0b;
            background: #fef3c7;
            padding: 6px 12px;
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        .question-actions {
            display: flex;
            gap: 8px;
            flex-shrink: 0;
        }

        .question-text {
            font-size: 16px;
            color: #0f172a;
            margin-bottom: 20px;
            line-height: 1.7;
        }

        .question-image {
            max-width: 400px;
            margin: 20px 0;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }

        .options {
            margin: 20px 0;
            display: grid;
            gap: 10px;
        }

        .option {
            padding: 12px 16px;
            background: #f8fafc;
            border-radius: 8px;
            border-left: 3px solid #e2e8f0;
            font-size: 14px;
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .option.correct {
            background: #f0fdf4;
            border-left-color: #22c55e;
        }

        .option-content {
            display: flex;
            align-items: start;
            gap: 8px;
        }

        .option-image {
            max-width: 200px;
            border-radius: 8px;
            margin-top: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        }

        .option-label {
            font-weight: 700;
            color: #64748b;
            margin-right: 8px;
        }

        .option.correct .option-label {
            color: #22c55e;
        }

        .metadata {
            font-size: 13px;
            color: #94a3b8;
            margin-top: 16px;
            padding-top: 16px;
            border-top: 1px solid #f1f5f9;
        }

        .empty-state {
            text-align: center;
            padding: 80px 32px;
        }

        .empty-state-icon {
            font-size: 64px;
            margin-bottom: 16px;
            opacity: 0.5;
        }

        .empty-state-text {
            font-size: 16px;
            color: #64748b;
            margin-bottom: 24px;
        }

        .pagination {
            padding: 24px 32px;
            display: flex;
            justify-content: center;
            gap: 8px;
        }

        .pagination a,
        .pagination span {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 40px;
            height: 40px;
            padding: 0 12px;
            border-radius: 8px;
            text-decoration: none;
            color: #475569;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            font-size: 14px;
            font-weight: 500;
        }

        .pagination a:hover {
            background: #4f46e5;
            color: white;
            border-color: #4f46e5;
        }

        .pagination .active {
            background: #4f46e5;
            color: white;
            border-color: #4f46e5;
        }

        @media (max-width: 768px) {
            .header {
                flex-direction: column;
                gap: 16px;
                align-items: flex-start;
            }

            h1 {
                font-size: 24px;
            }

            .question-header {
                flex-direction: column;
                gap: 12px;
            }

            .question-actions {
                width: 100%;
            }

            .btn {
                flex: 1;
                justify-content: center;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <nav class="nav">
            <a href="{{ route('admin.questions.index') }}" class="logo">ExamDoc Parser</a>
        </nav>

        <div class="header">
            <h1>Daftar Soal Ujian</h1>
            <a href="{{ route('admin.questions.create') }}" class="btn btn-primary">
                <span>Upload Soal Baru</span>
            </a>
        </div>

        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        <div class="stats">
            <div
                style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 24px; text-align: center;">
                <div>
                    <div class="stats-number">{{ $questions->total() }}</div>
                    <div class="stats-label">Total Soal</div>
                </div>
                <div>
                    <div class="stats-number" style="color: #f59e0b;">{{ $questions->sum('points') }}</div>
                    <div class="stats-label">Total Poin</div>
                </div>
            </div>
        </div>

        <div class="card">
            @if ($questions->count() > 0)
                @foreach ($questions as $index => $question)
                    <div class="question-item">
                        <div class="question-header">
                            <div style="display: flex; gap: 10px; align-items: center;">
                                <div class="question-number">Soal #{{ $question->id }}</div>
                                <div class="question-points">{{ $question->points }} Poin</div>
                            </div>
                            <div class="question-actions">
                                <a href="{{ route('admin.questions.show', $question->id) }}" class="btn btn-info">
                                    Detail
                                </a>
                                <form action="{{ route('admin.questions.destroy', $question->id) }}" method="POST"
                                    style="display: inline;"
                                    onsubmit="return confirm('Yakin ingin menghapus soal ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger">
                                        Hapus
                                    </button>
                                </form>
                            </div>
                        </div>

                        <div class="question-text">
                            {{ $question->question_text }}
                        </div>

                        @if ($question->image_path)
                            <img src="{{ asset('storage/' . $question->image_path) }}" alt="Question Image"
                                class="question-image">
                        @endif

                        @if ($question->option_a || $question->option_b || $question->option_c || $question->option_d)
                            <div class="options">
                                @if ($question->option_a)
                                    <div class="option {{ $question->correct_answer === 'A' ? 'correct' : '' }}">
                                        <div class="option-content">
                                            <span class="option-label">A.</span>
                                            <span>{{ $question->option_a }}</span>
                                        </div>
                                        @if ($question->option_a_image)
                                            <img src="{{ asset('storage/' . $question->option_a_image) }}"
                                                alt="Option A Image" class="option-image">
                                        @endif
                                    </div>
                                @endif
                                @if ($question->option_b)
                                    <div class="option {{ $question->correct_answer === 'B' ? 'correct' : '' }}">
                                        <div class="option-content">
                                            <span class="option-label">B.</span>
                                            <span>{{ $question->option_b }}</span>
                                        </div>
                                        @if ($question->option_b_image)
                                            <img src="{{ asset('storage/' . $question->option_b_image) }}"
                                                alt="Option B Image" class="option-image">
                                        @endif
                                    </div>
                                @endif
                                @if ($question->option_c)
                                    <div class="option {{ $question->correct_answer === 'C' ? 'correct' : '' }}">
                                        <div class="option-content">
                                            <span class="option-label">C.</span>
                                            <span>{{ $question->option_c }}</span>
                                        </div>
                                        @if ($question->option_c_image)
                                            <img src="{{ asset('storage/' . $question->option_c_image) }}"
                                                alt="Option C Image" class="option-image">
                                        @endif
                                    </div>
                                @endif
                                @if ($question->option_d)
                                    <div class="option {{ $question->correct_answer === 'D' ? 'correct' : '' }}">
                                        <div class="option-content">
                                            <span class="option-label">D.</span>
                                            <span>{{ $question->option_d }}</span>
                                        </div>
                                        @if ($question->option_d_image)
                                            <img src="{{ asset('storage/' . $question->option_d_image) }}"
                                                alt="Option D Image" class="option-image">
                                        @endif
                                    </div>
                                @endif
                            </div>
                        @endif

                        <div class="metadata">
                            Diimport: {{ $question->created_at->format('d M Y H:i') }}
                            @if ($question->original_filename)
                                • File: {{ $question->original_filename }}
                            @endif
                        </div>
                    </div>
                @endforeach

                <div class="pagination">
                    {{ $questions->links() }}
                </div>
            @else
                <div class="empty-state">
                    <div class="empty-state-icon">📭</div>
                    <div class="empty-state-text">Belum ada soal yang diimport</div>
                    <a href="{{ route('admin.questions.create') }}" class="btn btn-primary">
                        Upload Soal Pertama
                    </a>
                </div>
            @endif
        </div>
    </div>
</body>

</html>
