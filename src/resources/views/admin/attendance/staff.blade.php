@extends('layouts.admin')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/admin/attendance/staff.css') }}">
@endsection

@section('content')
<div class="attendance-list__wrapper">
        <div class="attendance-list__content">
            <h1 class="attendance-list__title">{{ $user->name }}さんの勤怠</h1>

            <div class="attendance-list__navigation">
    @php
        $prevMonth = $currentMonth->copy()->subMonth()->format('Y-m');
        $nextMonth = $currentMonth->copy()->addMonth()->format('Y-m');
    @endphp

    <a href="{{ route('admin.attendance.staff', ['id' => $user->id, 'month' => $prevMonth]) }}" class="attendance-list__nav-link">
        <i class="fa-solid fa-arrow-left attendance-list__nav-icon"></i>前月
    </a>

    <div class="attendance-list__current-month">
        <img src="{{ asset('storage/images/calendar-icon.png') }}" alt="カレンダーアイコン" class="attendance-list__month-icon">
        {{ $currentMonth->format('Y/m') }}
    </div>

    <a href="{{ route('admin.attendance.staff', ['id' => $user->id, 'month' => $nextMonth]) }}" class="attendance-list__nav-link">
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
            {{-- request_status はコントローラでセット済み --}}
            @if ($attendance->request_status === 'approved')
                <a href="{{ route('attendance.show', $attendance->id) }}" class="attendance-list__detail-link">詳細</a>
            @elseif ($attendance->request_status === 'pending')
                {{-- もし編集画面へのリンクも必要なら --}}
                @php
                    // 最新の申請を取得
                    $latestRequest = $attendance->attendanceRequests->sortByDesc('created_at')->first();
                @endphp
                <a href="{{ route('attendance-requests.edit', $latestRequest->id) }}" class="attendance-list__detail-link">詳細</a>
            @else
                <a href="{{ route('attendance.show', $attendance->id) }}" class="attendance-list__detail-link">詳細</a>
            @endif
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
