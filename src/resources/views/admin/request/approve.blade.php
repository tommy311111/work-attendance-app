@extends('layouts.admin')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/admin/request/approve.css') }}">
@endsection

@section('content')
<div class="approve-request__wrapper">
    <h1 class="approve-request__title">勤怠詳細</h1>

    <form action="{{ route('stamp_correction_request.approve', $attendanceRequest->id) }}" method="POST">
        @csrf
        @method('PUT')

        <table class="approve-request__table">
            <tr>
                <th>名前</th>
                <td class="approve-request__text approve-request__text--slightly-left">
                    {{ str_replace(' ', '　', $user->name) }}
                </td>
            </tr>
            <tr>
                <th>日付</th>
                <td class="approve-request__text">
                    {{ \Carbon\Carbon::parse($attendance->date)->format('Y年') }}
                    <span class="attendance-detail__spacer" aria-hidden="true"></span>
                    {{ \Carbon\Carbon::parse($attendance->date)->format('n月 j日') }}
                </td>
            </tr>

            <!-- 出勤・退勤 -->
<tr>
    <th>出勤・退勤</th>
    <td class="approve-request__text">
        {{ $attendanceRequest->requested_clock_in_time
            ? \Carbon\Carbon::parse($attendanceRequest->requested_clock_in_time)->format('H:i')
            : '未入力' }}
        <span class="approve-request__time-separator">〜</span>
        {{ $attendanceRequest->requested_clock_out_time
            ? \Carbon\Carbon::parse($attendanceRequest->requested_clock_out_time)->format('H:i')
            : '未入力' }}
    </td>
</tr>

<!-- 休憩（修正申請の方） -->
@foreach($attendanceRequest->attendanceRequestBreaks as $i => $break)
    <tr>
        <th>{{ $i === 0 ? '休憩' : '休憩' . ($i + 1) }}</th>
        <td class="approve-request__text">
            {{ $break->requested_start_time
                ? \Carbon\Carbon::parse($break->requested_start_time)->format('H:i')
                : '未入力' }}
            <span class="approve-request__time-separator">〜</span>
            {{ $break->requested_end_time
                ? \Carbon\Carbon::parse($break->requested_end_time)->format('H:i')
                : '未入力' }}
        </td>
    </tr>
@endforeach

<!-- 備考 -->
<tr>
    <th>備考</th>
    <td class="approve-request__text">
        {!! nl2br(e($attendanceRequest->reason ?? '')) !!}
    </td>
</tr>

        </table>

        <div class="approve-request__action">
            @if ($attendanceRequest->status === 'approved')
                <button type="button" class="approve-request__button approve-request__button--approved" disabled>
                    承認済み
                </button>
            @else
                <button type="submit" class="approve-request__button approve-request__button--approve">
                    承認
                </button>
            @endif
        </div>
    </form>
</div>
@endsection
