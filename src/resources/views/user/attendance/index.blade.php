@extends('layouts.user')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/user/attendance/index.css') }}">
@endsection

@section('content')
    <div class="attendance-list__wrapper">
        <div class="attendance-list__content">
            <h1 class="attendance-list__title">勤怠一覧</h1>

            <div class="attendance-list__navigation">
                @php
                    $prevMonth = $currentMonth->copy()->subMonth()->format('Y-m');
                    $nextMonth = $currentMonth->copy()->addMonth()->format('Y-m');
                @endphp

                <a href="{{ route('attendance.index', ['month' => $prevMonth]) }}" class="attendance-list__nav-link">
                    <i class="fa-solid fa-arrow-left attendance-list__nav-icon"></i>前月
                </a>

                <div class="attendance-list__current-month">
                    <img src="{{ asset('storage/images/calendar-icon.png') }}" alt="カレンダーアイコン" class="attendance-list__month-icon">
                    {{ $currentMonth->format('Y/m') }}
                </div>

                <a href="{{ route('attendance.index', ['month' => $nextMonth]) }}" class="attendance-list__nav-link">
                    翌月<i class="fa-solid fa-arrow-right attendance-list__nav-icon"></i>
                </a>
            </div>

            <table class="attendance-list__table">
                <thead>
                    <tr>
                        <th>日付</th>
                        <th>出勤</th>
                        <th>退勤</th>
                        <th>休憩</th>
                        <th>合計</th>
                        <th>詳細</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($attendances as $attendance)
                        @php
                            $date = \Carbon\Carbon::parse($attendance->date);
                        @endphp
                        <tr>
                            <td>{{ $date->format('m/d') }}({{ ['日','月','火','水','木','金','土'][$date->dayOfWeek] }})</td>
                            <td>
                                {{ $attendance->status === '勤務外' ? '' : \Carbon\Carbon::parse($attendance->clock_in)->format('H:i') }}
                            </td>
                            <td>
                                {{ $attendance->status === '勤務外' ? '' : \Carbon\Carbon::parse($attendance->clock_out)->format('H:i') }}
                            </td>
                            <td>{{ $attendance->total_break_time_formatted }}</td>
                            <td>
                                {{ $attendance->work_duration_formatted !== '-' ? $attendance->work_duration_formatted : '' }}
                            </td>
                            <td>
                                <a href="{{ route('attendance.show', $attendance->id) }}" class="attendance-list__detail-link">詳細</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">勤怠データがありません</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
