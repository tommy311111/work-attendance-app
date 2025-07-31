@extends('layouts.user')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/user/attendance/show.css') }}">
@endsection

@section('content')
<div class="attendance-detail__wrapper">
    <h2 class="attendance-detail__title">勤怠詳細</h2>

    <form action="{{ route('attendances.request.edit', $attendance->id) }}" method="GET">
    

        <table class="attendance-detail__table">
            <tr>
                <th>名前</th>
                <td class="attendance-detail__text attendance-detail__text--slightly-left">
    {{ str_replace(' ', '　', $user->name) }}
</td>

            </tr>
            <tr>
    <th>日付</th>
    <td class="attendance-detail__text">
        {{ \Carbon\Carbon::parse($attendance->date)->format('Y年') }}
        <span style="display:inline-block; width:5.7rem;"></span>
        {{ \Carbon\Carbon::parse($attendance->date)->format('n月 j日') }}
    </td>
</tr>


            <!-- 出勤・退勤 -->
<tr>
    <th>出勤・退勤</th>
    <td class="attendance-detail__text">
        {{ $attendance->clock_in ? $attendance->clock_in->format('H:i') : '未入力' }}
        <span class="time-separator">〜</span>
        {{ $attendance->clock_out ? $attendance->clock_out->format('H:i') : '未入力' }}
    </td>
</tr>

            @foreach($breaks as $i => $break)
<tr>
    <th>{{ $i === 0 ? '休憩' : '休憩' . ($i + 1) }}</th>
    <td class="attendance-detail__text">
        {{ optional($break->break_start_at)->format('H:i') ?? '未入力' }}
        <span class="time-separator">〜</span>
        {{ optional($break->break_end_at)->format('H:i') ?? '未入力' }}
    </td>
</tr>
@endforeach

            <tr>
    <th>備考</th>
    <td class="attendance-detail__text">
        {!! nl2br(e($attendance->reason ?? '')) !!}
    </td>
</tr>

        </table>

        <div class="attendance-detail__submit">
    <p class="attendance-detail__notice">
        ※承認待ちのため修正はできません。
    </p>
</div>


    </form>
</div>
@endsection