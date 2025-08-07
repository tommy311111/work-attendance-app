@extends('layouts.admin')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/admin/staff/index.css') }}">
@endsection

@section('content')
    <div class="staff-list__wrapper">
        <div class="staff-list__content">
            <h1 class="staff-list__title">スタッフ一覧</h1>

            <table class="staff-list__table">
                <thead>
                    <tr>
                        <th class="staff-list__th">名前</th>
                        <th class="staff-list__th">メールアドレス</th>
                        <th class="staff-list__th">月次勤怠</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $user)
                        <tr class="staff-list__tr">
                            <td class="staff-list__td">{{ $user->name }}</td>
                            <td class="staff-list__td">{{ $user->email }}</td>
                            <td class="staff-list__td">
                                <a href="{{ route('admin.attendance.staff', $user->id)}}" class="staff-list__link">詳細</a>
                            </td>
                        </tr>
                    @empty
                        <tr class="staff-list__tr">
                            <td class="staff-list__td" colspan="3">スタッフが登録されていません</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
