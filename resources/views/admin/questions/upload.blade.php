<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Soal - ExamDoc Parser</title>
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
            max-width: 720px;
            margin: 0 auto;
        }

        .header {
            margin-bottom: 40px;
        }

        .nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 48px;
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

        h1 {
            color: #0f172a;
            margin-bottom: 8px;
            font-size: 32px;
            font-weight: 700;
            letter-spacing: -0.02em;
        }

        .subtitle {
            color: #64748b;
            margin-bottom: 40px;
            font-size: 16px;
            line-height: 1.6;
        }

        .upload-area {
            border: 2px dashed #cbd5e1;
            border-radius: 12px;
            padding: 64px 32px;
            text-align: center;
            background: #f8fafc;
            transition: all 0.2s ease;
            cursor: pointer;
            margin-bottom: 24px;
        }

        .upload-area:hover {
            border-color: #4f46e5;
            background: #f1f5f9;
        }

        .upload-area.dragover {
            border-color: #4f46e5;
            background: #eef2ff;
            border-style: solid;
        }

        .upload-icon {
            width: 56px;
            height: 56px;
            margin: 0 auto 20px;
            background: #eef2ff;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
        }

        .upload-text {
            font-size: 16px;
            color: #0f172a;
            margin-bottom: 8px;
            font-weight: 500;
        }

        .upload-hint {
            font-size: 14px;
            color: #94a3b8;
        }

        input[type="file"] {
            display: none;
        }

        .file-info {
            background: #f1f5f9;
            padding: 16px 20px;
            border-radius: 10px;
            margin-bottom: 24px;
            display: none;
            border: 1px solid #e2e8f0;
        }

        .file-info.active {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .file-icon {
            width: 40px;
            height: 40px;
            background: #4f46e5;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 18px;
            flex-shrink: 0;
        }

        .file-name {
            color: #1e293b;
            font-weight: 500;
            word-break: break-all;
            font-size: 14px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 24px;
            border-radius: 10px;
            font-size: 15px;
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

        .btn-primary:hover:not(:disabled) {
            background: #4338ca;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(79, 70, 229, 0.3);
        }

        .btn-primary:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .btn-secondary {
            background: white;
            color: #475569;
            border: 1px solid #e2e8f0;
        }

        .btn-secondary:hover {
            background: #f8fafc;
            border-color: #cbd5e1;
        }

        .alert {
            padding: 16px 20px;
            border-radius: 10px;
            margin-bottom: 24px;
            font-size: 14px;
            line-height: 1.5;
        }

        .alert-success {
            background: #f0fdf4;
            color: #15803d;
            border: 1px solid #bbf7d0;
        }

        .alert-error {
            background: #fef2f2;
            color: #dc2626;
            border: 1px solid #fecaca;
        }

        .info-box {
            background: #fefce8;
            border: 1px solid #fef08a;
            border-radius: 12px;
            padding: 24px;
            margin-top: 32px;
        }

        .info-box h3 {
            color: #854d0e;
            margin-bottom: 16px;
            font-size: 15px;
            font-weight: 600;
        }

        .info-box ul {
            margin-left: 20px;
            color: #854d0e;
        }

        .info-box li {
            margin-bottom: 10px;
            font-size: 14px;
            line-height: 1.6;
        }

        .info-box code {
            display: block;
            margin-top: 12px;
            padding: 16px;
            background: white;
            border-radius: 8px;
            font-size: 13px;
            line-height: 1.8;
            color: #475569;
            border: 1px solid #fef08a;
        }

        .btn-group {
            display: flex;
            gap: 12px;
            margin-top: 24px;
        }

        @media (max-width: 640px) {
            .card {
                padding: 32px 24px;
            }

            h1 {
                font-size: 26px;
            }

            .btn-group {
                flex-direction: column;
            }

            .btn {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <nav class="nav">
            <a href="{{ isset($examId) ? route('admin.exams.manage', ['exam' => $examId]) : route('admin.questions.index') }}"
                class="logo">ExamDoc Parser</a>
        </nav>

        <div class="card">
            <div class="header">
                <h1>Upload Soal Ujian</h1>
                <p class="subtitle">
                    @if (isset($examId))
                        Import soal ujian dari file Microsoft Word (.doc, .docx) langsung ke ujian
                        <strong>{{ session('perexamname') }}</strong>
                    @else
                        Import soal ujian dari file Microsoft Word (.doc, .docx)
                    @endif
                </p>
            </div>

            @if (session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-error">
                    {{ session('error') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-error">
                    @foreach ($errors->all() as $error)
                        {{ $error }}<br>
                    @endforeach
                </div>
            @endif

            <form action="{{ route('admin.questions.import') }}" method="POST" enctype="multipart/form-data"
                id="uploadForm">
                @csrf

                @if (isset($examId))
                    <input type="hidden" name="exam_id" value="{{ $examId }}">
                @endif

                <div class="upload-area" id="uploadArea">
                    <div class="upload-icon">📄</div>
                    <div class="upload-text">Klik atau seret file ke sini</div>
                    <div class="upload-hint">Format: .doc atau .docx (Max: 10MB)</div>
                    <input type="file" name="word_file" id="wordFile" accept=".doc,.docx" required>
                </div>

                <div class="file-info" id="fileInfo">
                    <div class="file-icon">📄</div>
                    <span class="file-name" id="fileName"></span>
                </div>

                <div class="btn-group">
                    <button type="submit" class="btn btn-primary" id="submitBtn" disabled>
                        <span>Import Soal</span>
                    </button>
                    <a href="{{ route('admin.questions.index') }}" class="btn btn-secondary">
                        <span>Lihat Daftar Soal</span>
                    </a>
                </div>
            </form>

            <div class="info-box">
                <h3>Format File Word yang Benar</h3>
                <ul>
                    <li>Setiap soal dimulai dengan nomor (contoh: "1. Apa itu...")</li>
                    <li>Pilihan jawaban dimulai dengan huruf A, B, C, D (contoh: "A. Pilihan pertama")</li>
                    <li>Opsi jawaban bisa berupa teks, gambar, atau kombinasi keduanya</li>
                    <li>Gambar yang berada di bawah opsi akan otomatis dikaitkan dengan opsi tersebut</li>
                    <li>Kunci jawaban ditulis sebagai "Jawaban: A" atau "Answer: A"</li>
                    <li>Poin soal ditulis sebagai "Poin: 5" atau "Nilai: 10" (default 1 jika tidak disebutkan)</li>
                    <li>Gambar akan otomatis terdeteksi dan disimpan</li>
                    <li>Contoh format:
                        <code>1. Apa ibu kota Indonesia?<br>A. Jakarta<br>B. Bandung<br>C. Surabaya<br>D.
                            Medan<br>Jawaban: A<br>Poin: 5</code>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <script>
        const uploadArea = document.getElementById('uploadArea');
        const fileInput = document.getElementById('wordFile');
        const fileInfo = document.getElementById('fileInfo');
        const fileName = document.getElementById('fileName');
        const submitBtn = document.getElementById('submitBtn');

        // Click to upload
        uploadArea.addEventListener('click', () => {
            fileInput.click();
        });

        // File selected
        fileInput.addEventListener('change', (e) => {
            const file = e.target.files[0];
            if (file) {
                fileName.textContent = file.name;
                fileInfo.classList.add('active');
                submitBtn.disabled = false;
            }
        });

        // Drag and drop
        uploadArea.addEventListener('dragover', (e) => {
            e.preventDefault();
            uploadArea.classList.add('dragover');
        });

        uploadArea.addEventListener('dragleave', () => {
            uploadArea.classList.remove('dragover');
        });

        uploadArea.addEventListener('drop', (e) => {
            e.preventDefault();
            uploadArea.classList.remove('dragover');

            const file = e.dataTransfer.files[0];
            if (file && (file.name.endsWith('.doc') || file.name.endsWith('.docx'))) {
                fileInput.files = e.dataTransfer.files;
                fileName.textContent = file.name;
                fileInfo.classList.add('active');
                submitBtn.disabled = false;
            } else {
                alert('Harap upload file Word (.doc atau .docx)');
            }
        });
    </script>
</body>

</html>
