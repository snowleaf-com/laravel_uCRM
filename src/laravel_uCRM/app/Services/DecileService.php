<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class DecileService
{

  public static function decile($subQuery)
  {
    // Orderモデルから１つの購入ごとの合計金額を出す
    $subQuery = $subQuery
      ->groupBy('id')
      ->selectRaw('id, customer_id, customer_name, SUM(subtotal) as totalPerPurchase');

    // 購入ごとの合計金額（totalPerPurchase）をさらに合計し、顧客単位での「総購入額」を total として計算します。
    $subQuery = DB::table($subQuery)
      ->groupBy('customer_id')
      ->selectRaw('customer_id, customer_name, sum(totalPerPurchase) as total')
      ->orderBy('total', 'desc');

    // 総購入額で降順に並び替えた後、MySQL変数（@row_num）を利用してランキング順位（row_num）を付与。
    DB::statement('set @row_num = 0');
    $subQuery = DB::table($subQuery)
      ->selectRaw('@row_num:= @row_num +1 as row_num, 
        customer_id, customer_name, total');

    // $subQuery の行数（顧客数）を数えています。
    $count = DB::table($subQuery)->count();
    // 顧客ごとの総購入額の合計値を取得
    $total = DB::table($subQuery)->selectRaw('SUM(total) as total')->get();
    // 中身を取得
    $total = $total[0]->total; //構成比用として取得（総合計額）

    // 1つのデシル（グループ）に含める行数を計算
    $decile = ceil($count / 10);

    // デシルごとの開始インデックスと終了インデックスを格納するための配列。
    $bindValues = [];
    $tempValue = 0;
    for ($i = 1; $i <= 10; $i++) {
      array_push($bindValues, 1 + $tempValue); // 開始インデックス
      $tempValue += $decile;
      array_push($bindValues, 1 + $tempValue); // 終了インデックス
    }
    // 例
    //   0 => 1
    //   1 => 9.0
    //   2 => 9.0
    //   3 => 17.0
    //   4 => 17.0
    //   5 => 25.0
    //   6 => 25.0
    //   7 => 33.0
    //   8 => 33.0
    //   9 => 41.0
    //   10 => 41.0
    //   11 => 49.0
    //   12 => 49.0
    //   13 => 57.0
    //   14 => 57.0
    //   15 => 65.0
    //   16 => 65.0
    //   17 => 73.0
    //   18 => 73.0
    //   19 => 81.0

    // デシル分割する decileに1〜10を入れてグループ分け
    DB::statement('set @row_num = 0;');
    $subQuery = DB::table($subQuery)
      ->selectRaw('
        row_num,
        customer_id,
        customer_name,
        total,
        case
            when ? <= row_num and row_num < ? then 1
            when ? <= row_num and row_num < ? then 2
            when ? <= row_num and row_num < ? then 3
            when ? <= row_num and row_num < ? then 4
            when ? <= row_num and row_num < ? then 5
            when ? <= row_num and row_num < ? then 6
            when ? <= row_num and row_num < ? then 7
            when ? <= row_num and row_num < ? then 8
            when ? <= row_num and row_num < ? then 9
            when ? <= row_num and row_num < ? then 10
        end as decile
        ', $bindValues);

    // デシルごとの集計｜各デシルの平均購入額・合計購入額
    $subQuery = DB::table($subQuery)
      ->groupBy('decile')
      ->selectRaw('decile, round(avg(total)) as average,
        sum(total) as totalPerGroup');

    // @totalは総合計額
    // round〜の計算式は、各デシルの合計購入額が全体（@total）に占める割合を計算し、小数点第1位まで丸める。
    DB::statement("set @total = {$total} ;");
    $data = DB::table($subQuery)
      ->selectRaw('decile, average, totalPerGroup, @total,  round(100 * totalPerGroup / @total, 1) as totalRatio')->get();

    $labels = $data->pluck('decile');
    $totals = $data->pluck('totalPerGroup');

    return [$data, $labels, $totals];
  }
}
