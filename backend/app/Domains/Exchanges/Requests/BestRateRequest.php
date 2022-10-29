<?php

declare(strict_types=1);

namespace App\Domains\Exchanges\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BestRateRequest extends FormRequest
{
    public function prepareForValidation()
    {
        $this->merge([
            'pair' => $this->route('pair'),
            'amount' => $this->route('amount'),
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed[]>
     */
    public function rules(): array
    {
        return [
            'pair' => [
                'required',
                'max:22',
                'regex:/[A-Z]_[A-Z]/u'
            ],
            'amount' => [
                'string',
                'numeric',
                'min:0',
                'not_in:0',
                'required',
            ],
        ];
    }
}
