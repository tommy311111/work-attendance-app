@extends('layouts.user')

@section('content')
<div class="container">
    <h1>勤怠管理</h1>
    <p>現在のステータス：<strong>{{ $attendance->status }}</strong></p>

    <form method="POST" action="{{ route('attendance.action') }}">
        @csrf

        @if($attendance->status === '勤務外')
            <button type="submit" name="action" value="start_work">出勤</button>
        @elseif($attendance->status === '出勤中')
            <button type="submit" name="action" value="start_break">休憩</button>
            <button type="submit" name="action" value="end_work">退勤</button>
        @elseif($attendance->status === '休憩中')
            <button type="submit" name="action" value="end_break">休憩終了</button>
        @elseif($attendance->status === '退勤済')
            <p>本日の勤務は終了しています。</p>
        @endif
    </form>
</div>
@endsection
