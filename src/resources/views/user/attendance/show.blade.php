@extends('layouts.user')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/user/attendance/show.css') }}">
@endsection

@section('content')
<div class="attendance-detail__wrapper">
    <h2 class="attendance-detail__title">勤怠詳細</h2>

    <form action="{{ route('attendances.request', $attendance->id) }}" method="POST">
        @csrf

        <table class="attendance-detail__table">
            <tr>
                <th>名前</th>
                <td class="attendance-detail__text">{{ $user->name }}</td>
            </tr>
            <tr>
                <th>日付</th>
                <td class="attendance-detail__text">
                    {{ \Carbon\Carbon::parse($attendance->date)->format('Y年n月j日') }}
                </td>
            </tr>
            <tr>
                <th>出勤・退勤</th>
                <td>
                    <input type="time" name="clock_in" value="{{ old('clock_in', \Carbon\Carbon::parse($attendance->clock_in)->format('H:i')) }}">
                    〜
                    <input type="time" name="clock_out" value="{{ old('clock_out', \Carbon\Carbon::parse($attendance->clock_out)->format('H:i')) }}">
                    @error('clock_in')<div class="error">{{ $message }}</div>@enderror
                    @error('clock_out')<div class="error">{{ $message }}</div>@enderror
                </td>
            </tr>
            @foreach($breaks as $i => $break)
                <tr>
                    <th>{{ $i === 0 ? '休憩' : '休憩' . ($i + 1) }}</th>
                    <td>
                        <input type="hidden" name="breaks[{{ $i }}][id]" value="{{ $break->id }}">
                        <input type="time" name="breaks[{{ $i }}][break_start]" value="{{ old("breaks.$i.break_start", \Carbon\Carbon::parse($break->break_start)->format('H:i')) }}">
                        〜
                        <input type="time" name="breaks[{{ $i }}][break_end]" value="{{ old("breaks.$i.break_end", \Carbon\Carbon::parse($break->break_end)->format('H:i')) }}">
                        @error("breaks.$i.break_start")<div class="error">{{ $message }}</div>@enderror
                        @error("breaks.$i.break_end")<div class="error">{{ $message }}</div>@enderror
                    </td>
                </tr>
            @endforeach
            {{-- 空の休憩入力欄1つ --}}
            <tr>
                <th>休憩{{ count($breaks) + 1 }}</th>
                <td>
                    <input type="hidden" name="breaks[{{ count($breaks) }}][id]" value="">
                    <input type="time" name="breaks[{{ count($breaks) }}][break_start]" value="">
                    〜
                    <input type="time" name="breaks[{{ count($breaks) }}][break_end]" value="">
                </td>
            </tr>
            <tr>
                <th>備考</th>
                <td>
                    <textarea name="remarks" rows="3">{{ old('remarks', $attendance->remarks) }}</textarea>
                    @error('remarks')<div class="error">{{ $message }}</div>@enderror
                </td>
            </tr>
        </table>

        <div class="attendance-detail__submit">
            <button type="submit">修正申請</button>
        </div>
    </form>
</div>
@endsection