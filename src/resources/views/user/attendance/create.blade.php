@extends('layouts.user')

@section('css')
<link rel="stylesheet" href="{{ asset('css/user/attendance\create.css') }}">
@endsection

@section('content')
    <div class="attendance__content">
        {{-- ステータス表示 --}}
        <div class="attendance__status">
            {{ $attendance->status }}
        </div>

        {{-- 今日の日付 --}}
        <div class="attendance__date">
            {{ $today->format('Y年n月j日') }}（{{ $weekdayJapanese }}）
        </div>


        {{-- 現在の時刻（JavaScriptでリアルタイム表示） --}}
        <div id="clock" class="attendance__clock"></div>

        {{-- ボタン --}}
        <form method="POST" action="{{ route('attendance.action') }}" class="attendance__form">
            @csrf

            @if ($attendance->status === '勤務外')
                <input type="hidden" name="action" value="start_work">
                <button type="submit" class="attendance__button">出勤</button>

            @elseif ($attendance->status === '出勤中' && !$attendance->on_break)
                <input type="hidden" name="action" value="start_break">
                <button type="submit" class="attendance__button">休憩</button>

            @elseif ($attendance->status === '休憩中')
                <input type="hidden" name="action" value="end_break">
                <button type="submit" class="attendance__button">休憩戻</button>

            @elseif ($attendance->status === '出勤中' && $attendance->work_end_at === null)
                <input type="hidden" name="action" value="end_work">
                <button type="submit" class="attendance__button">退勤</button>
            @endif
        </form>
    </div>

    {{-- 現在時刻表示用スクリプト --}}
    <script>
        function updateClock() {
            const now = new Date();
            const hour = String(now.getHours()).padStart(2, '0');
            const minute = String(now.getMinutes()).padStart(2, '0');
            document.getElementById('clock').textContent = `${hour}:${minute}`;
        }

        setInterval(updateClock, 1000);
        updateClock();
    </script>
@endsection


