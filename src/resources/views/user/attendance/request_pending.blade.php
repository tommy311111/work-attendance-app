@extends('layouts.user')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/user/attendance/show.css') }}">
@endsection

@section('content')
    <div class="attendance-detail__wrapper">
        <h1 class="attendance-detail__title">勤怠詳細</h1>
        <form action="{{ route('attendance-requests.edit', $attendance->id) }}" method="GET">
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
                        {{ $attendance->requested_clock_in_time ? \Carbon\Carbon::parse($attendance->requested_clock_in_time)->format('H:i') : '未入力' }}
                        <span class="time-separator">〜</span>
                        {{ $attendance->requested_clock_out_time ? \Carbon\Carbon::parse($attendance->requested_clock_out_time)->format('H:i') : '未入力' }}
                    </td>
                </tr>

                <!-- 休憩 -->
                @foreach($breaks as $i => $break)
                    <tr>
                        <th>{{ $i === 0 ? '休憩' : '休憩' . ($i + 1) }}</th>
                        <td class="attendance-detail__text">
                            {{ $break->requested_start_time ? \Carbon\Carbon::parse($break->requested_start_time)->format('H:i') : '未入力' }}
                            <span class="time-separator">〜</span>
                            {{ $break->requested_end_time ? \Carbon\Carbon::parse($break->requested_end_time)->format('H:i') : '未入力' }}
                        </td>
                    </tr>
                @endforeach

                <!-- 備考 -->
                <tr>
                    <th>備考</th>
                    <td class="attendance-detail__text">
                        {!! nl2br(e($attendance->reason ?? '')) !!}
                    </td>
                </tr>
            </table>

            <div class="attendance-detail__submit">
                <p class="attendance-detail__notice">
                    *承認待ちのため修正はできません。
                </p>
            </div>
        </form>
    </div>
@endsection
