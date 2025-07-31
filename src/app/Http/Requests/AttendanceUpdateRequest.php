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
            'clock_in' => ['required', 'date_format:H:i'],
            'clock_out' => ['required', 'date_format:H:i', ],
            'breaks' => ['array'], // 休憩が複数ある場合
            'breaks.*.id' => ['nullable', 'integer', 'exists:breaks,id'],
            'breaks.*.break_start' => ['nullable', 'date_format:H:i'],
            'breaks.*.break_end' => ['nullable', 'date_format:H:i'],
            'reason' => ['required', 'string'],
        ];
    }

    public function withValidator($validator)
{
    $validator->after(function ($validator) {
        $data = $this->all();

        $clockIn = isset($data['clock_in']) ? strtotime($data['clock_in']) : null;
        $clockOut = isset($data['clock_out']) ? strtotime($data['clock_out']) : null;

        // 出退勤チェック
        if ($clockIn && $clockOut && $clockIn > $clockOut) {
            $validator->errors()->add('clock_in', '出勤時間もしくは退勤時間が不適切な値です');
             return; // ←ここで休憩チェックをスキップ
        }

        // 休憩時間チェック
        foreach ($data['breaks'] ?? [] as $index => $break) {
            $breakStart = isset($break['break_start']) ? strtotime($break['break_start']) : null;
            $breakEnd = isset($break['break_end']) ? strtotime($break['break_end']) : null;

            if ($clockIn && $breakStart && ($breakStart < $clockIn || $breakStart > $clockOut)) {
                $validator->errors()->add("breaks.$index.break_start", '休憩時間が不適切な値です');
            }

            if ($clockOut && $breakEnd && $breakEnd > $clockOut) {
                $validator->errors()->add("breaks.$index.break_end", '休憩時間もしくは退勤時間が不適切な値です');
            }
        }

        // 備考欄チェック
        if (!isset($data['remarks']) || trim($data['remarks']) === '') {
            $validator->errors()->add('remarks', '備考を記入してください');
        }
    });
}

}
