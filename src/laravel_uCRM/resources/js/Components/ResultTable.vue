<script setup>

import dayjs from 'dayjs';

const props = defineProps({
  data: Object
})

const formatDate = (val) => {
  if (!val) return '';

  if (val.length === 8) {
    // ラベルが YYYY-MM-DD の場合
    return dayjs(val).format('YYYY年MM月DD日');
  } else if (val.length === 6) {
    // ラベルが YYYY-MM の場合
    return dayjs(val, 'YYYY-MM').format('YYYY年MM月');
  } else if (val.length === 4) {
    // ラベルが YYYY の場合
    return dayjs(val, 'YYYY').format('YYYY年');
  } else {
    // それ以外の場合はそのまま返す
    return val;
  }
}
</script>

<template>
  <div class="lg:w-2/3 w-full mx-auto overflow-auto" v-if="data.type === 'decile'">
    <table class="table-auto w-full text-left whitespace-no-wrap">
      <thead>
        <tr>
          <th class="px-4 py-3 title-font tracking-wider font-medium text-gray-900 text-sm bg-gray-100 rounded-tl rounded-bl">グループ</th>
          <th class="px-4 py-3 title-font tracking-wider font-medium text-gray-900 text-sm bg-gray-100">平均</th>
          <th class="px-4 py-3 title-font tracking-wider font-medium text-gray-900 text-sm bg-gray-100">合計金額</th>
          <th class="px-4 py-3 title-font tracking-wider font-medium text-gray-900 text-sm bg-gray-100">構成比</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="item in data.data" :key="item.date">
          <td class="border-b-2 border-gray-200 px-4 py-3">{{ item.decile }}</td>
          <td class="border-b-2 border-gray-200 px-4 py-3">{{ Number(item.average).toLocaleString('ja-JP', { style: 'currency', currency: 'JPY' }) }}</td>
          <td class="border-b-2 border-gray-200 px-4 py-3">{{ Number(item.totalPerGroup).toLocaleString('ja-JP', { style: 'currency', currency: 'JPY' }) }}</td>
          <td class="border-b-2 border-gray-200 px-4 py-3">{{ item.totalRatio }}</td>
        </tr>
      </tbody>
    </table>
  </div>
  <div class="lg:w-2/3 w-full mx-auto overflow-auto" v-if="data.type === 'perDay' || data.type === 'perMonth' || data.type === 'perYear'">
    <table class="table-auto w-full text-left whitespace-no-wrap">
      <thead>
        <tr>
          <th class="px-4 py-3 title-font tracking-wider font-medium text-gray-900 text-sm bg-gray-100 rounded-tl rounded-bl">期間</th>
          <th class="px-4 py-3 title-font tracking-wider font-medium text-gray-900 text-sm bg-gray-100">金額</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="item in data.data" :key="item.date">
          <td class="border-b-2 border-gray-200 px-4 py-3">{{ formatDate(item.date) }}</td>
          <td class="border-b-2 border-gray-200 px-4 py-3">{{ Number(item.total).toLocaleString('ja-JP', { style: 'currency', currency: 'JPY' }) }}</td>
        </tr>
      </tbody>
    </table>
  </div>
  <div class="lg:w-2/3 w-full mx-auto overflow-auto mt-8" v-if="data.type === 'rfm'">
    <h2 class="text-2xl text-center">合計人数 {{ data.totals }}人</h2>
    <h3 class="text-xl">RFMごとの人数</h3>
    <table class="table-auto w-full text-left whitespace-no-wrap">
      <thead>
        <tr>
          <th class="px-4 py-3 title-font tracking-wider font-medium text-gray-900 text-sm bg-gray-100 rounded-tl rounded-bl">Rank</th>
          <th class="px-4 py-3 title-font tracking-wider font-medium text-gray-900 text-sm bg-gray-100">R</th>
          <th class="px-4 py-3 title-font tracking-wider font-medium text-gray-900 text-sm bg-gray-100">F</th>
          <th class="px-4 py-3 title-font tracking-wider font-medium text-gray-900 text-sm bg-gray-100">M</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="rfm in data.eachCount" :key="rfm.rank">
          <td class="border-b-2 border-gray-200 px-4 py-3">{{ rfm.rank }}</td>
          <td class="border-b-2 border-gray-200 px-4 py-3">{{ rfm.r }}</td>
          <td class="border-b-2 border-gray-200 px-4 py-3">{{ rfm.f }}</td>
          <td class="border-b-2 border-gray-200 px-4 py-3">{{ rfm.m }}</td>
        </tr>
      </tbody>
    </table>

    <h3 class="text-xl mt-8">RFM散布図</h3>
    <table class="table-auto w-full text-left whitespace-no-wrap">
      <thead>
        <tr>
          <th class="px-4 py-3 title-font tracking-wider font-medium text-gray-900 text-sm bg-gray-100 rounded-tl rounded-bl">rRank</th>
          <th class="px-4 py-3 title-font tracking-wider font-medium text-gray-900 text-sm bg-gray-100">f_5</th>
          <th class="px-4 py-3 title-font tracking-wider font-medium text-gray-900 text-sm bg-gray-100">f_4</th>
          <th class="px-4 py-3 title-font tracking-wider font-medium text-gray-900 text-sm bg-gray-100">f_3</th>
          <th class="px-4 py-3 title-font tracking-wider font-medium text-gray-900 text-sm bg-gray-100">f_2</th>
          <th class="px-4 py-3 title-font tracking-wider font-medium text-gray-900 text-sm bg-gray-100">f_1</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="rf in data.data" :key="rf.rank">
          <td class="border-b-2 border-gray-200 px-4 py-3">{{ rf.rRank }}</td>
          <td class="border-b-2 border-gray-200 px-4 py-3">{{ rf.f_5 }}</td>
          <td class="border-b-2 border-gray-200 px-4 py-3">{{ rf.f_4 }}</td>
          <td class="border-b-2 border-gray-200 px-4 py-3">{{ rf.f_3 }}</td>
          <td class="border-b-2 border-gray-200 px-4 py-3">{{ rf.f_2 }}</td>
          <td class="border-b-2 border-gray-200 px-4 py-3">{{ rf.f_1 }}</td>
        </tr>
      </tbody>
    </table>
  </div>
</template>