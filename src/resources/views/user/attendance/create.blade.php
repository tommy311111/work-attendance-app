@extends('layouts.user')

@section('css')
<link rel="stylesheet" href="{{ asset('css/user/attendance/create.css') }}">
@endsection

@section('content')
<div class="attendance__content">
    <div class="attendance__status">
        {{ $attendance->status }}
    </div>
    <div class="attendance__date">
        {{ $today->format('Y年n月j日')}}（{{ $weekdayJapanese }}）
    </div>
    <div id="clock" class="attendance__clock"></div>
    <div class="attendance__form">
        @if ($attendance->status === '勤務外')
            <form method="POST" action="{{ route('attendance.action') }}">
                @csrf
                <input type="hidden" name="action" value="start_work">
                <button type="submit" class="attendance__button">出勤</button>
            </form>
        @elseif ($attendance->status === '出勤中' && !$attendance->on_break)
            <form method="POST" action="{{ route('attendance.action') }}" class="attendance__form-inline attendance__form-inline--margin-right">
                @csrf
                <input type="hidden" name="action" value="end_work">
                <button type="submit" class="attendance__button">退勤</button>
            </form>
            <form method="POST" action="{{ route('attendance.action') }}" class="attendance__form-inline">
                @csrf
                <input type="hidden" name="action" value="start_break">
                <button type="submit" class="attendance__button--white">休憩入</button>
            </form>
        @elseif ($attendance->status === '休憩中')
            <form method="POST" action="{{ route('attendance.action') }}">
                @csrf
                <input type="hidden" name="action" value="end_break">
                <button type="submit" class="attendance__button--white">休憩戻</button>
            </form>
        @elseif ($attendance->status === '退勤済')
            <div class="attendance__message">
                お疲れ様でした。
            </div>
        @endif
    </div>
</div>

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
