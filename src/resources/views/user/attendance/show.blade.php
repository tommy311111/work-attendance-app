@extends('layouts.user')

@section('css')
<link rel="stylesheet" href="{{ asset('css/user/attendance/show.css') }}">
@endsection

@section('content')
<div class="attendance-detail__wrapper">
    <h1 class="attendance-detail__title">勤怠詳細</h1>

    <form action="{{ route('attendance-requests.store', $attendance->id) }}" method="POST">
        @csrf
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
            <tr>
                <th>出勤・退勤</th>
                <td>
                    <input type="time" name="clock_in" value="{{ old('clock_in', $attendance->clock_in ? $attendance->clock_in->format('H:i') : '') }}">
                    <span class="time-separator">〜</span>
                    <input type="time" name="clock_out" value="{{ old('clock_out', $attendance->clock_out ? $attendance->clock_out->format('H:i') : '') }}">
                    @error('clock_in')
                        <div class="error">{{ $message }}</div>
                    @enderror
                    @error('clock_out')
                        <div class="error">{{ $message }}</div>
                    @enderror
                </td>
            </tr>
            @foreach($breaks as $i => $break)
            <tr>
                <th>{{ $i === 0 ? '休憩' : '休憩' . ($i + 1) }}</th>
                <td>
                    <input type="hidden" name="breaks[{{ $i }}][id]" value="{{ $break->id }}">
                    <input type="time" name="breaks[{{ $i }}][break_start]" value="{{ old("breaks.$i.break_start", optional($break->break_start_at)->format('H:i')) }}">
                    <span class="time-separator">〜</span>
                    <input type="time" name="breaks[{{ $i }}][break_end]" value="{{ old("breaks.$i.break_end", $break->break_end_at ? $break->break_end_at->format('H:i') : '') }}">
                    @error("breaks.$i.break_start")
                        <div class="error">{{ $message }}</div>
                    @enderror
                    @error("breaks.$i.break_end")
                        <div class="error">{{ $message }}</div>
                    @enderror
                </td>
            </tr>
            @endforeach
            <tr>
                <th>休憩{{ count($breaks) + 1 }}</th>
                <td>
                    <input type="hidden" name="breaks[{{ count($breaks) }}][id]" value="">
                    <input type="time" name="breaks[{{ count($breaks) }}][break_start]" value="">
                    <span class="time-separator">〜</span>
                    <input type="time" name="breaks[{{ count($breaks) }}][break_end]" value="">
                </td>
            </tr>
            <tr>
                <th>備考</th>
                <td>
                    <textarea name="reason" rows="3">{{ old('reason', $attendance->reason) }}</textarea>
                    @error('reason')
                        <div class="error">{{ $message }}</div>
                    @enderror
                </td>
            </tr>
        </table>

        <div class="attendance-detail__submit">
            <button type="submit">修正</button>
        </div>
    </form>
</div>
@endsection
