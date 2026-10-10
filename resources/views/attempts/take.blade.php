<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $attempt->exam->title }} | CBT Syahidah</title>
    <link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Fraunces:opsz,wght@9..144,500;9..144,600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/cbt.css') }}"><link rel="stylesheet" href="{{ asset('css/operations.css') }}">
</head>
<body class="take-page">
    <header class="take-header"><a class="brand brand-dark" href="{{ route('exams.index') }}"><span class="brand-mark">S</span><span><strong>SYAHIDAH</strong><small>RUANG UJIAN</small></span></a><div class="take-header-title"><strong>{{ $attempt->exam->title }}</strong><small>{{ $attempt->exam->subject->name }}</small></div><div class="timer" id="exam-timer" data-deadline="{{ $attempt->deadline_at->toIso8601String() }}"><span aria-hidden="true">◷</span> <strong>--:--</strong></div></header>
    <main class="take-layout">
        <section class="question-area"><div class="take-progress"><span id="question-position">Soal 1 dari {{ $questions->count() }}</span><span id="save-status">Semua perubahan tersimpan</span></div>
            <form method="POST" action="{{ route('attempts.submit', $attempt) }}" id="submit-exam">@csrf
                @foreach ($questions as $index => $question)
                    @php($saved = $savedAnswers->get($question->id)?->selected_option)
                    <article class="question-paper" data-question="{{ $index }}" @if ($index > 0)hidden @endif><div class="question-paper-top"><span class="question-index">{{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}</span><span class="question-points">{{ $question->points }} poin</span></div><div class="question-prompt">{!! nl2br(e($question->prompt)) !!}</div><div class="answer-options">
                        @foreach ($question->options as $letter => $option)
                            <label class="answer-option {{ $saved === $letter ? 'is-selected' : '' }}"><input type="radio" name="answer-{{ $question->id }}" value="{{ $letter }}" data-answer-url="{{ route('attempts.answers.update', [$attempt, $question]) }}" @checked($saved === $letter)><span class="option-letter">{{ $letter }}</span><span>{{ $option }}</span></label>
                        @endforeach
                    </div></article>
                @endforeach
                <div class="question-controls"><button type="button" class="outline-button" id="previous-question">← Sebelumnya</button><button type="button" class="action-button" id="next-question">Selanjutnya <span aria-hidden="true">→</span></button><button type="submit" class="finish-button" id="finish-exam">Kumpulkan jawaban</button></div>
            </form>
        </section>
        <aside class="question-rail"><div class="rail-heading"><span class="eyebrow eyebrow-green">NAVIGASI SOAL</span><strong>{{ $questions->count() }} soal</strong></div><div class="question-jump-grid">@foreach ($questions as $index => $question)<button type="button" class="question-jump @if ($savedAnswers->has($question->id)) is-answered @endif @if ($index === 0) is-current @endif" data-jump="{{ $index }}" aria-label="Buka soal {{ $index + 1 }}">{{ $index + 1 }}</button>@endforeach</div><div class="rail-legend"><span><i class="legend-dot legend-answered"></i> Terjawab</span><span><i class="legend-dot"></i> Belum dijawab</span></div><div class="rail-reminder"><strong>Periksa jawabanmu</strong><p>Jawaban tersimpan otomatis. Ujian dikumpulkan ketika waktu habis.</p></div></aside>
    </main>
    <script>
        const questionCards = [...document.querySelectorAll('.question-paper')];
        const jumps = [...document.querySelectorAll('.question-jump')];
        const positionLabel = document.querySelector('#question-position');
        let currentQuestion = 0;
        let pendingSave = Promise.resolve();
        const showQuestion = (index) => {
            currentQuestion = Math.max(0, Math.min(index, questionCards.length - 1));
            questionCards.forEach((card, cardIndex) => card.hidden = cardIndex !== currentQuestion);
            jumps.forEach((button, buttonIndex) => button.classList.toggle('is-current', buttonIndex === currentQuestion));
            positionLabel.textContent = `Soal ${currentQuestion + 1} dari ${questionCards.length}`;
            document.querySelector('#previous-question').disabled = currentQuestion === 0;
            document.querySelector('#next-question').hidden = currentQuestion === questionCards.length - 1;
            document.querySelector('#finish-exam').hidden = currentQuestion !== questionCards.length - 1;
        };
        const saveAnswer = (input) => {
            const status = document.querySelector('#save-status');
            status.textContent = 'Menyimpan...';
            pendingSave = fetch(input.dataset.answerUrl, {
                method: 'PUT',
                headers: {'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content},
                body: JSON.stringify({selected_option: input.value})
            }).then((response) => {
                if (!response.ok) throw new Error('save failed');
                input.closest('.answer-option').parentElement.querySelectorAll('.answer-option').forEach((option) => option.classList.toggle('is-selected', option.contains(input)));
                jumps[currentQuestion].classList.add('is-answered');
                status.textContent = 'Semua perubahan tersimpan';
            }).catch(() => { status.textContent = 'Gagal menyimpan. Periksa koneksi.'; });
        };
        document.querySelectorAll('.answer-option input').forEach((input) => input.addEventListener('change', () => saveAnswer(input)));
        jumps.forEach((button) => button.addEventListener('click', () => showQuestion(Number(button.dataset.jump))));
        document.querySelector('#previous-question').addEventListener('click', () => showQuestion(currentQuestion - 1));
        document.querySelector('#next-question').addEventListener('click', () => showQuestion(currentQuestion + 1));
        const submitForm = document.querySelector('#submit-exam');
        submitForm.addEventListener('submit', async (event) => {
            if (!submitForm.dataset.confirmed && !confirm('Kumpulkan jawaban dan akhiri ujian?')) { event.preventDefault(); return; }
            event.preventDefault();
            submitForm.dataset.confirmed = 'true';
            await pendingSave;
            submitForm.submit();
        });
        showQuestion(0);
        const timer = document.querySelector('#exam-timer');
        const timerText = timer.querySelector('strong');
        const deadline = new Date(timer.dataset.deadline).getTime();
        const tick = () => {
            const remaining = Math.max(0, Math.floor((deadline - Date.now()) / 1000));
            timerText.textContent = `${String(Math.floor(remaining / 60)).padStart(2, '0')}:${String(remaining % 60).padStart(2, '0')}`;
            if (remaining === 0) submitForm.requestSubmit();
            else window.setTimeout(tick, 1000);
        };
        tick();
    </script>
</body>
</html>