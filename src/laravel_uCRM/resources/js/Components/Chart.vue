<script setup>
import { Chart, registerables } from "chart.js";
import { BarChart } from "vue-chart-3";
import { computed, ref } from "vue";
import dayjs from "dayjs";

const props = defineProps({
  data: Object
})

const labels = computed(() => {
  // labels を取得（デフォルト値は空配列）
  const rawLabels = props.data?.labels || [];

  console.log(rawLabels)
  
  // 各ラベルを日付形式に変換
  const formattedLabels = rawLabels.map((label) => {
    if (label.length === 8) {
      // ラベルが YYYY-MM-DD の場合
      return dayjs(label).format('YYYY年MM月DD日');
    } else if (label.length === 6) {
      // ラベルが YYYY-MM の場合
      return dayjs(label).format('YYYY年MM月');
    } else if (label.length === 4) {
      // ラベルが YYYY の場合
      return dayjs(label).format('YYYY年');
    } else {
      // それ以外の場合はそのまま返す
      return label;
    }
  });

  console.log("フォーマット後の labels:", formattedLabels); // デバッグ用
  return formattedLabels;
});


const totals = computed(() => {
   const value = props.data?.totals || ""; // 初期値として空文字列を設定
    console.log("labels の中身:", value); // デバッグ用
    return value;
});

Chart.register(...registerables);

const barData = ref({
  labels :labels,
  datasets:[
    {
      label:'売上',
      data: totals,
      backgroundColor: "rgb(75,192,192)",
      tension: 0.1
    }
  ]
})
</script>

<template>
<div v-show="props.data.labels" class="mb-8">
  <BarChart :chartData="barData" :height="200" />
</div>
</template>