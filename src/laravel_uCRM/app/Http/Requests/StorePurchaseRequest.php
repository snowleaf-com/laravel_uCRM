<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePurchaseRequest extends FormRequest
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
            'customer_id' => ['required', 'exists:customers,id'], // 顧客IDが存在することを確認
            'items' => ['required', 'array', 'min:1'], // itemsは必須の配列で、少なくとも1つの要素が必要
            'items.*.id' => ['required', 'exists:items,id'], // 各itemのIDが必須で、存在するもの
            'items.*.quantity' => ['required', 'integer', 'min:1'], // quantityは1以上の整数が必要
        ];
    }

    /**
     * Get custom error messages for validation rules.
     *
     * @return array<string, mixed>
     */
    public function messages()
    {
        return [
            'items.required' => '購入アイテムを選択してください。',
            'items.array' => '購入アイテムの形式が正しくありません。',
            'items.min' => '少なくとも1つの購入アイテムを追加してください。',
            'items.*.id.required' => 'アイテムのIDは必須です。',
            'items.*.id.exists' => '選択されたアイテムが存在しません。',
            'items.*.quantity.required' => 'アイテムの数量は必須です。',
            'items.*.quantity.integer' => '数量は整数でなければなりません。',
            'items.*.quantity.min' => '数量は1以上でなければなりません。',
        ];
    }
}
