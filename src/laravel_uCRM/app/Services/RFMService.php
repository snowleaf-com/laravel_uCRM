<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RFMService
{

  public static function rfm($subQuery, $rfmPrms, $endDate)
  {
    // RFM分析
    // 1.購買IDごとまとめる
    $subQuery = $subQuery->groupBy('id')
      ->selectRaw('id, customer_id, customer_name, SUM(subtotal) as totalPerPurchase, created_at');

    // 2.会員ごとにまとめて最終購入日・回数・合計金額を取得
    $subQuery = DB::table($subQuery)
      ->groupBy('customer_id')
      ->selectRaw('customer_id, customer_name, max(created_at) as recentDate,
    datediff(?, max(created_at)) as recency,
    count(customer_id) as frequency,
    sum(totalPerPurchase) as monetary', [$endDate]);

    // 3. 会員毎のRFMランクを計算
    $subQuery = DB::table($subQuery)
      ->selectRaw('
    customer_id,
    customer_name, 
    recentDate, 
    recency, 
    frequency, 
    monetary, 
    case
    when recency < ? then 5 
    when recency < ? then 4 
    when recency < ? then 3 
    when recency < ? then 2 
    else 1 end as r,
    
    case
    when ? <= frequency then 5 
    when ? <= frequency then 4 
    when ? <= frequency then 3 
    when ? <= frequency then 2 
    else 1 end as f,
    
    case
    when ? <= monetary then 5 
    when ? <= monetary then 4 
    when ? <= monetary then 3 
    when ? <= monetary then 2 
    else 1 end as m
    ', $rfmPrms);

    // 4.ランク毎の数を計算する
    $totals = DB::table($subQuery)->count();

    $rCount = DB::table($subQuery)
    ->rightJoin('ranks', 'ranks.rank', '=', 'r')
    ->selectRaw('ranks.rank as r, count(r)')
    ->groupBy('rank')
    ->orderBy('r', 'desc')
    ->pluck('count(r)');

    $fCount = DB::table($subQuery)
    ->selectRaw('ranks.rank as f, count(f)')->orderBy('f', 'desc')
    ->rightJoin('ranks', 'ranks.rank', '=', 'f')->groupBy('rank')
    ->pluck('count(f)');

    $mCount = DB::table($subQuery)
    ->rightJoin('ranks', 'ranks.rank', '=', 'm')
    ->selectRaw('ranks.rank as m, count(m)')->orderBy('m', 'desc')
    ->groupBy('rank')
    ->pluck('count(m)');

    Log::debug($rCount);

    $eachCount = [];
    $rank = 5;

    for ($i = 0; $i < 5; $i++) {
      array_push(
        $eachCount,
        [
          'rank' => $rank,
          'r' => $rCount[$i],
          'f' => $fCount[$i],
          'm' => $mCount[$i]
        ]
      );
      $rank--;
    }

    // dd($total, $eachCount, $rCount, $fCount, $mCount);

    // concatで文字列結合
    // 5. RとFで2次元で表示してみる
    // $data = DB::table($subQuery)
    //   ->rightJoin('ranks', 'ranks.rank', '=', 'r')
    //   ->selectRaw('
    // concat("r_", ranks.rank) as rRank, 
    // count(case when f = 5 then 1 end ) as f_5, 
    // count(case when f = 4 then 1 end ) as f_4, 
    // count(case when f = 3 then 1 end ) as f_3, 
    // count(case when f = 2 then 1 end ) as f_2, 
    // count(case when f = 1 then 1 end ) as f_1
    // ')->orderBy('rRank', 'desc')
    //   ->groupBy('rank')
    //   ->get();

    $data = DB::table($subQuery)
      ->leftJoin('ranks as r_rank', 'r_rank.rank', '=', 'r')
      ->leftJoin(
        'ranks as f_rank',
        'f_rank.rank',
        '=',
        'f'
      )
      ->leftJoin(
        'ranks as m_rank',
        'm_rank.rank',
        '=',
        'm'
      )
      ->selectRaw(
        '
        r_rank.rank as r, 
        f_rank.rank as f,
        m_rank.rank as m,
        COALESCE(count(*), 0) as count'  // NULLの場合は0にする
      )
      ->groupBy('r', 'f', 'm')->get();

    return [$data, $totals, $eachCount];
  }

}