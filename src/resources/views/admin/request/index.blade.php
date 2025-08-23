@extends('layouts.admin')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/admin/request/index.css') }}">
@endsection

@section('content')
<div class="request-list__wrapper">
    <h1 class="request-list__title">申請一覧</h1>

    <div class="request-list__tabs">
        <a href="{{ route('attendance_requests.list', ['status' => 'pending']) }}"
           class="request-list__tab {{ request('status') === 'pending' ? 'request-list__tab--active' : '' }}">
            承認待ち
        </a>
        <a href="{{ route('attendance_requests.list', ['status' => 'approved']) }}"
           class="request-list__tab {{ request('status') === 'approved' ? 'request-list__tab--active' : '' }}">
            承認済み
        </a>
    </div>
    <div class="request-list__divider"></div>

    <div class="request-list__table-container">
        <table class="request-list__table">
            <thead>
                <tr>
                    <th>状態</th>
                    <th>名前</th>
                    <th>対象日時</th>
                    <th>申請理由</th>
                    <th>申請日時</th>
                    <th>詳細</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($requests as $request)
                    <tr>
                        <td>
                            {{ $request->status === 'pending' ? '承認待ち' : '承認済み' }}
                        </td>
                        <td>{{ $request->user->name }}</td>
                        <td>{{ $request->attendance->date->format('Y/m/d') }}</td>
                        <td>{{ $request->reason }}</td>
                        <td>{{ $request->created_at->format('Y/m/d') }}</td>
                        <td>
                            <a href="{{ route('stamp_correction_request.approve_form', $request->id) }}" class="request-list__detail-link">
                                詳細
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6">申請がありません。</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
