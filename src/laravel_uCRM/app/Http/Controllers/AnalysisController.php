<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Order;
use Illuminate\Support\Facades\DB;

class AnalysisController extends Controller
{
  public function index() {

    $startDate = '2021-08-01';
    $endDate = '2023-08-10';
    // $period = Order::betweenDate($startDate, $endDate)->
    // groupBy('id')->
    // selectRaw('id, sum(subtotal) as total, customer_name, status, created_at')->
    // orderBy('created_at')->
    // paginate(50);

    // $subQuery = Order::betweenDate($startDate, $endDate)->where('status', true)
    // ->groupBy('id')
    // ->selectRaw('id, sum(subtotal) as totalPerPurchase, DATE_FORMAT(created_at, "%Y%m%d") as date');

    // $data = DB::table($subQuery)
    // ->groupBy('date')
    // ->selectRaw('date, sum(totalPerPurchase) as total')
    // ->get();

    // dd($data);

    // RFM分析
    // 1.購買IDごとまとめる
    $subQuery = Order::betweenDate($startDate, $endDate)
    ->groupBy('id')
    ->selectRaw('id, customer_id, customer_name, SUM(subtotal) as totalPerPurchase, created_at');

    // 2.会員ごとにまとめて最終購入日・回数・合計金額を取得
    $subQuery = DB::table($subQuery)
    ->groupBy('customer_id')
    ->selectRaw('customer_id, customer_name, max(created_at) as recentDate,
    datediff(?, max(created_at)) as recency,
    count(customer_id) as frequency,
    sum(totalPerPurchase) as monetary', [$endDate]);

    // 3. 会員毎のRFMランクを計算
    $rfmPrms = [
      14,
      28,
      60,
      90,
      7,
      5,
      3,
      2,
      300000,
      200000,
      100000,
      30000
    ];
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
    $total = DB::table($subQuery)->count();

    $rCount = DB::table($subQuery)->groupBy('r')
    ->selectRaw('r, count(r)')->orderBy('r', 'desc')
    ->pluck('count(r)');
    $fCount = DB::table($subQuery)->groupBy('f')
    ->selectRaw('f, count(f)')->orderBy('f', 'desc')
    ->pluck('count(f)');
    $mCount = DB::table($subQuery)->groupBy('m')
    ->selectRaw('m, count(m)')->orderBy('m', 'desc')
    ->pluck('count(m)');

    $eachCount = [];
    $rank = 5;

    for($i = 0; $i < 5; $i++) {
      array_push($eachCount,
        [
          'rank' => $rank, 
          'r' => $rCount[$i], 
          'f' => $fCount[$i], 
          'm'=> $mCount[$i]
        ]
      );
      $rank--;
    }

    // dd($total, $eachCount, $rCount, $fCount, $mCount);

    // concatで文字列結合
    // 6. RとFで2次元で表示してみる
    $data = DB::table($subQuery)
    ->groupBy('r')
    ->selectRaw('
    concat("r_", r) as rRank, 
    count(case when f = 5 then 1 end ) as f_5, 
    count(case when f = 4 then 1 end ) as f_4, 
    count(case when f = 3 then 1 end ) as f_3, 
    count(case when f = 2 then 1 end ) as f_2, 
    count(case when f = 1 then 1 end ) as f_1
    ')->orderBy('rRank', 'desc')
    ->get();

    // dd($data);



    return Inertia::render('Analysis');
  }

  public function decile()
  {
    $startDate = '2022-08-01';
    $endDate = '2022-08-10';
    
    // Orderモデルから１つの購入ごとの合計金額を出す
    $subQuery = 
      Order::betweenDate($startDate, $endDate)
      ->groupBy('id')
      ->selectRaw('id, customer_id, customer_name, SUM(subtotal) as totalPerPurchase');

    // 購入ごとの合計金額（totalPerPurchase）をさらに合計し、顧客単位での「総購入額」を total として計算します。
    $subQuery = 
      DB::table($subQuery)
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
    $subQuery = 
      DB::table($subQuery)
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
    $subQuery =
      DB::table($subQuery)
      ->groupBy('decile')
      ->selectRaw('decile, round(avg(total)) as average,
      sum(total) as totalPerGroup');

    // @totalは総合計額
    // round〜の計算式は、各デシルの合計購入額が全体（@total）に占める割合を計算し、小数点第1位まで丸める。
    DB::statement("set @total = {$total} ;");
    $data = 
      DB::table($subQuery)
      ->selectRaw('decile, average, totalPerGroup, @total,  round(100 * totalPerGroup / @total, 1) as totalRatio')->get();

    // dd($data);
  }
}
