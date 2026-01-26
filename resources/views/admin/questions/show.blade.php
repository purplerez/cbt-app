<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Soal #{{ $question->id }}</title>
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
            max-width: 900px;
            margin: 0 auto;
        }

        .nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 32px;
        }

        .logo {
            font-size: 20px;
            font-weight: 700;
            color: #4f46e5;
            text-decoration: none;
        }

        .card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            padding: 48px;
            border: 1px solid #e2e8f0;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 32px;
            padding-bottom: 24px;
            border-bottom: 1px solid #f1f5f9;
        }

        h1 {
            color: #0f172a;
            font-size: 24px;
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

        .btn-secondary {
            background: #f8fafc;
            color: #475569;
            border: 1px solid #e2e8f0;
        }

        .btn-secondary:hover {
            background: #f1f5f9;
        }

        .question-section {
            margin-bottom: 32px;
        }

        .section-title {
            font-size: 12px;
            font-weight: 700;
            color: #4f46e5;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 12px;
        }

        .question-text {
            font-size: 17px;
            color: #0f172a;
            line-height: 1.8;
            padding: 20px 24px;
            background: #f8fafc;
            border-radius: 12px;
            border-left: 4px solid #4f46e5;
        }

        .question-image {
            max-width: 100%;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            margin-top: 20px;
        }

        .options {
            margin-top: 16px;
            display: grid;
            gap: 12px;
        }

        .option {
            padding: 16px 20px;
            background: #f8fafc;
            border-radius: 10px;
            border-left: 3px solid #e2e8f0;
            font-size: 15px;
            transition: all 0.2s ease;
            line-height: 1.6;
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .option:hover {
            background: #f1f5f9;
        }

        .option.correct {
            background: #f0fdf4;
            border-left-color: #22c55e;
            position: relative;
        }

        .option-content {
            display: flex;
            align-items: start;
            gap: 10px;
        }

        .option-image {
            max-width: 300px;
            border-radius: 8px;
            margin-top: 4px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        }

        .option-label {
            font-weight: 700;
            color: #64748b;
            margin-right: 10px;
            font-size: 16px;
        }

        .option.correct .option-label {
            color: #22c55e;
        }

        .option.correct::after {
            content: "Jawaban Benar";
            position: absolute;
            right: 20px;
            top: 50%;
            transform: translateY(-50%);
            color: #22c55e;
            font-weight: 600;
            font-size: 13px;
            background: white;
            padding: 4px 12px;
            border-radius: 6px;
        }

        .metadata {
            margin-top: 32px;
            padding: 24px;
            background: #f8fafc;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
        }

        .metadata-item {
            display: flex;
            padding: 12px 0;
            border-bottom: 1px solid #e2e8f0;
        }

        .metadata-item:last-child {
            border-bottom: none;
        }

        .metadata-label {
            font-weight: 600;
            color: #64748b;
            min-width: 160px;
            font-size: 14px;
        }

        .metadata-value {
            color: #0f172a;
            font-size: 14px;
        }

        .answer-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 64px;
            height: 64px;
            background: #22c55e;
            color: white;
            border-radius: 12px;
            font-weight: 700;
            font-size: 28px;
            box-shadow: 0 4px 12px rgba(34, 197, 94, 0.3);
        }

        .points-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 20px;
            background: #fef3c7;
            color: #f59e0b;
            border-radius: 12px;
            font-weight: 700;
            font-size: 20px;
        }

        @media (max-width: 640px) {
            .card {
                padding: 32px 24px;
            }

            .header {
                flex-direction: column;
                align-items: flex-start;
                gap: 16px;
            }

            .option.correct::after {
                position: static;
                display: block;
                margin-top: 8px;
                transform: none;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <nav class="nav">
            <a href="{{ route('admin.questions.index') }}" class="logo">ExamDoc Parser</a>
        </nav>

        <div class="card">
            <div class="header">
                <h1>Detail Soal #{{ $question->id }}</h1>
                <a href="{{ route('admin.questions.index') }}" class="btn btn-secondary">
                    Kembali
                </a>
            </div>

            <div class="question-section">
                <div class="section-title">Pertanyaan</div>
                <div class="question-text">
                    {{ $question->question_text }}
                </div>

                @if ($question->image_path)
                    <img src="{{ asset('storage/' . $question->image_path) }}" alt="Question Image"
                        class="question-image">
                @endif
            </div>

            @if ($question->option_a || $question->option_b || $question->option_c || $question->option_d)
                <div class="question-section">
                    <div class="section-title">Pilihan Jawaban</div>
                    <div class="options">
                        @if ($question->option_a)
                            <div class="option {{ $question->correct_answer === 'A' ? 'correct' : '' }}">
                                <div class="option-content">
                                    <span class="option-label">A.</span>
                                    <span>{{ $question->option_a }}</span>
                                </div>
                                @if ($question->option_a_image)
                                    <img src="{{ asset('storage/' . $question->option_a_image) }}" alt="Option A Image"
                                        class="option-image">
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
                                    <img src="{{ asset('storage/' . $question->option_b_image) }}" alt="Option B Image"
                                        class="option-image">
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
                                    <img src="{{ asset('storage/' . $question->option_c_image) }}" alt="Option C Image"
                                        class="option-image">
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
                                    <img src="{{ asset('storage/' . $question->option_d_image) }}" alt="Option D Image"
                                        class="option-image">
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            @if ($question->correct_answer)
                <div class="question-section">
                    <div class="section-title">Kunci Jawaban</div>
                    <div class="answer-badge">{{ $question->correct_answer }}</div>
                </div>
            @endif

            <div class="question-section">
                <div class="section-title">Nilai Soal</div>
                <div class="points-badge">
                    <span style="font-size: 24px;">{{ $question->points }}</span>
                    <span style="font-size: 14px; font-weight: 600;">Poin</span>
                </div>
            </div>

            <div class="metadata">
                <div class="section-title" style="margin-bottom: 16px;">Informasi Tambahan</div>
                <div class="metadata-item">
                    <div class="metadata-label">Tanggal Import</div>
                    <div class="metadata-value">{{ $question->created_at->format('d F Y, H:i') }}</div>
                </div>
                @if ($question->original_filename)
                    <div class="metadata-item">
                        <div class="metadata-label">File Sumber</div>
                        <div class="metadata-value">{{ $question->original_filename }}</div>
                    </div>
                @endif
                <div class="metadata-item">
                    <div class="metadata-label">ID Soal</div>
                    <div class="metadata-value">#{{ $question->id }}</div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>
