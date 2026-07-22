<?php

namespace App\Http\Requests\EventRequest;

use Illuminate\Foundation\Http\FormRequest;

class StoreParticipationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $eventId = $this->route('event')?->id;

        return [
            'resident_id' => ['required', 'exists:residents,id'],
            'status' => ['required', 'in:present,absent,excused'],
            'remarks' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $eventId = $this->route('event')?->id;
            $residentId = $this->input('resident_id');

            if ($eventId && $residentId) {
                $exists = \App\Models\Participation::where('event_id', $eventId)
                    ->where('resident_id', $residentId)
                    ->exists();

                if ($exists) {
                    $validator->errors()->add('resident_id', 'This resident already has a participation record for this event.');
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'resident_id.required' => 'Please select a resident.',
            'resident_id.exists' => 'The selected resident is invalid.',
            'status.required' => 'Status is required.',
        ];
    }
}
