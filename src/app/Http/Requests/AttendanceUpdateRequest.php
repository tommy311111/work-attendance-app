<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AttendanceUpdateRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'clock_in' => ['required', 'date_format:H:i'],
            'clock_out' => ['required', 'date_format:H:i'],
            'breaks' => ['array'],
            'breaks.*.id' => ['nullable', 'integer', 'exists:breaks,id'],
            'breaks.*.break_start' => ['nullable', 'date_format:H:i'],
            'breaks.*.break_end' => ['nullable', 'date_format:H:i'],
            'reason' => ['required', 'string'],
        ];
    }

    public function messages()
    {
        return [
            'reason.required' => '備考を記入してください。',
            'reason.string' => '備考は文字列で入力してください。',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $data = $this->all();

            $clockIn = isset($data['clock_in']) ? strtotime($data['clock_in']) : null;
            $clockOut = isset($data['clock_out']) ? strtotime($data['clock_out']) : null;

            if ($clockIn && $clockOut && $clockIn > $clockOut) {
                $validator->errors()->add('clock_in', '出勤時間もしくは退勤時間が不適切な値です。');
                return;
            }

            foreach ($data['breaks'] ?? [] as $index => $break) {
                $breakStart = isset($break['break_start']) && $break['break_start'] !== ''
                    ? strtotime($break['break_start'])
                    : null;
                $breakEnd = isset($break['break_end']) && $break['break_end'] !== ''
                    ? strtotime($break['break_end'])
                    : null;

                if (is_null($breakStart) && is_null($breakEnd)) {
                    continue;
                }

                if (is_null($breakStart) xor is_null($breakEnd)) {
                    $validator->errors()->add("breaks.$index", '休憩時間は開始と終了を両方入力してください。');
                    continue;
                }

                if ($clockIn && $breakStart && ($breakStart < $clockIn || $breakStart > $clockOut)) {
                    $validator->errors()->add("breaks.$index.break_start", '休憩時間が不適切な値です。');
                }

                if ($clockOut && $breakEnd && $breakEnd > $clockOut) {
                    $validator->errors()->add("breaks.$index.break_end", '休憩時間もしくは退勤時間が不適切な値です。');
                }
            }
        });
    }
}
