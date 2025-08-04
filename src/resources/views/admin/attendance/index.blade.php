@extends('layouts.admin')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/admin/attendance/index.css') }}">
@endsection

@section('content')
    <div class="admin-attendance-list__wrapper">
        <div class="admin-attendance-list__content">
            <h1 class="admin-attendance-list__title">
                {{ $currentDate->format('Y年n月j日') }}の勤怠
            </h1>

            <div class="admin-attendance-list__navigation">
                @php
                    $prevDay = $currentDate->copy()->subDay()->format('Y-m-d');
                    $nextDay = $currentDate->copy()->addDay()->format('Y-m-d');
                @endphp

                <a href="{{ route('admin.attendance.index', ['date' => $prevDay]) }}" class="admin-attendance-list__nav-link">
                    <i class="fa-solid fa-arrow-left admin-attendance-list__nav-icon"></i>前日
                </a>

                <div class="admin-attendance-list__current-date">
                    <img src="{{ asset('storage/images/calendar-icon.png') }}" alt="カレンダーアイコン" class="admin-attendance-list__date-icon">
                    {{ $currentDate->format('Y/m/d') }}
                </div>

                <a href="{{ route('admin.attendance.index', ['date' => $nextDay]) }}" class="admin-attendance-list__nav-link">
                    翌日<i class="fa-solid fa-arrow-right admin-attendance-list__nav-icon"></i>
                </a>
            </div>

            <table class="admin-attendance-list__table">
                <thead>
                    <tr>
                        <th>名前</th>
                        <th>出勤</th>
                        <th>退勤</th>
                        <th>休憩</th>
                        <th>合計</th>
                        <th>詳細</th>
                    </tr>
                </thead>
                @php
    $workingAttendances = $attendances->filter(function ($attendance) {
        return $attendance->status !== '勤務外';
    });
@endphp

<tbody>
                    @forelse ($attendances as $attendance)
                        @php
                            $user = $attendance->user;
                        @endphp
                        <tr>
                            <td>{{ $user->name }}</td>
                            <td>{{ $attendance->status === '勤務外' ? '' : \Carbon\Carbon::parse($attendance->clock_in)->format('H:i') }}</td>
                            <td>{{ $attendance->status === '勤務外' ? '' : \Carbon\Carbon::parse($attendance->clock_out)->format('H:i') }}</td>
                            <td>{{ $attendance->total_break_time_formatted }}</td>
                            <td>{{ $attendance->work_duration_formatted !== '-' ? $attendance->work_duration_formatted : '' }}</td>
                            <td>
    @if ($attendance->request_status === 'pending')
        @php
            $latestRequest = $attendance->attendanceRequests->sortByDesc('created_at')->first();
        @endphp
        <a href="{{ route('attendance-requests.edit', $latestRequest->id) }}" class="admin-attendance-list__detail-link">詳細</a>
    @else
        <a href="{{ route('attendance.show', $attendance->id) }}" class="admin-attendance-list__detail-link">詳細</a>
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
