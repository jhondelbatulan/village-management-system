<?php

namespace App\Http\Requests\EventRequest;

use App\Models\Household;
use App\Models\Participation;
use App\Services\EventService;
use Illuminate\Foundation\Http\FormRequest;

class StoreParticipationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'household_id' => ['required', 'exists:households,id'],
            'status' => ['required', 'in:present,absent,excused'],
            'remarks' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $event = $this->route('event');
            $householdId = $this->input('household_id');

            if (! $event || ! $householdId) {
                return;
            }

            $household = Household::find($householdId);

            if ($household && (int) $household->village_id !== (int) $event->village_id) {
                $validator->errors()->add('household_id', 'The selected household does not belong to this event\'s village.');

                return;
            }

            $exists = Participation::where('event_id', $event->id)
                ->where('household_id', $householdId)
                ->where(function ($q) {
                    $q->whereNull('remarks')
                        ->orWhere('remarks', '!=', EventService::AUTO_GENERATED_REMARK);
                })
                ->exists();

            if ($exists) {
                $validator->errors()->add('household_id', 'This household already has a participation record for this event.');
            }
        });
    }

    public function messages(): array
    {
        return [
            'household_id.required' => 'Please select a household.',
            'household_id.exists' => 'The selected household is invalid.',
            'status.required' => 'Status is required.',
        ];
    }
}
