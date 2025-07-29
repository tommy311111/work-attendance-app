<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AttendanceUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'clock_in' => ['required', 'date'],
            'clock_out' => ['required', 'date', 'after_or_equal:clock_in'],
            'breaks' => ['array'], // 休憩が複数ある場合
            'breaks.*.id' => ['nullable', 'integer', 'exists:breaks,id'],
            'breaks.*.break_start' => ['nullable', 'date'],
            'breaks.*.break_end' => ['nullable', 'date', 'after_or_equal:breaks.*.break_start'],
            'reason' => ['required', 'string'],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $data = $this->all();

            $clockIn = strtotime($data['clock_in'] ?? '');
            $clockOut = strtotime($data['clock_out'] ?? '');

            if ($clockIn && $clockOut && $clockIn > $clockOut) {
                $validator->errors()->add('clock_in', '出勤時間もしくは退勤時間が不適切な値です');
            }

            if (isset($data['breaks']) && is_array($data['breaks'])) {
                foreach ($data['breaks'] as $index => $break) {
                    $breakStart = strtotime($break['break_start'] ?? '');
                    $breakEnd = strtotime($break['break_end'] ?? '');

                    if ($breakStart && ($breakStart < $clockIn || $breakStart > $clockOut)) {
                        $validator->errors()->add("breaks.$index.break_start", '休憩時間が不適切な値です');
                    }

                    if ($breakEnd && $breakEnd > $clockOut) {
                        $validator->errors()->add("breaks.$index.break_end", '休憩時間もしくは退勤時間が不適切な値です');
                    }
                }
            }

            if (empty(trim($data['remarks'] ?? ''))) {
                $validator->errors()->add('remarks', '備考を記入してください');
            }
        });
    }
}
