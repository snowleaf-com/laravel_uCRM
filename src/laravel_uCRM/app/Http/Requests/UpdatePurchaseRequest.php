<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePurchaseRequest extends FormRequest
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
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'status' => ['required', 'boolean'],
            'items' => ['required', 'array', 'min:1'], // itemsは必須の配列で、少なくとも1つの要素が必要
            'items.*.id' => ['required', 'exists:items,id'], // 各itemのIDが必須で、存在するもの
            'items.*.quantity' => ['required', 'integer', 'min:1'], // quantityは1以上の整数が必要
        ];
    }
}
