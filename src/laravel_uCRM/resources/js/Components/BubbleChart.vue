<script setup>
import { Chart, registerables } from "chart.js";
import { BubbleChart } from "vue-chart-3";
import { computed } from "vue";
import Annotation from "chartjs-plugin-annotation";
import { watch } from "vue";

const props = defineProps({
  data: Object
})

watch( //デバッグ
  () => props.data.data,
  (newValue) => {
    console.log("Updated data:", newValue);
  },
  { immediate: true }
)

Chart.register(Annotation, ...registerables);

// rの値に対応するラベルと色を定義
const monetarySettings = {
  1: { label: "M1", color: "rgba(255, 99, 132, 0.5)" },
  2: { label: "M2", color: "rgba(54, 162, 235, 0.5)" },
  3: { label: "M3", color: "rgba(75, 192, 192, 0.5)" },
  4: { label: "M4", color: "rgba(255, 206, 86, 0.5)" },
  5: { label: "M5", color: "rgba(153, 102, 255, 0.5)" },
};

// datasetsを動的に生成
// const datasets = props.data.data.map((dataPoint) => {
//   const mValue = dataPoint.m; // mの値を取得
//   const settings = monetarySettings[mValue] || { label: "Unknown", color: "rgba(200, 200, 200, 0.5)" };

//   return {
//     label: settings.label, // ラベルを設定
//     data: [
//       {
//         x: dataPoint.r, // Recencyをxに設定
//         y: dataPoint.f, // Frequencyをyに設定
//         r: dataPoint.count, // countをバブルサイズに設定
//       },
//     ],
//     backgroundColor: settings.color, // 色を設定
//     borderColor: settings.color.replace("0.5", "1"), // 境界線の色を濃く
//     borderWidth: 1, // 枠線の太さ
//   };
// });
const datasets = computed(() => {
  const labelSet = new Set();  // 重複ラベルを追跡するSet
  const result = [];

  props.data.data.forEach((dataPoint) => {
    const mValue = dataPoint.m; // mの値を取得
    const settings = monetarySettings[mValue] || { label: "Unknown", color: "rgba(200, 200, 200, 0.5)" };
    const bubbleSize = Math.min(dataPoint.count, 100);

    // ラベルがすでに表示されているかチェック
    if (!labelSet.has(settings.label)) {
      labelSet.add(settings.label);  // ラベルをセットに追加

      // 新しいデータセットを作成
      result.push({
        label: settings.label, // ラベルを設定
        data: [
          {
            x: dataPoint.r, // Recencyをxに設定
            y: dataPoint.f, // Frequencyをyに設定
            r: bubbleSize, // countをバブルサイズに設定
          },
        ],
        backgroundColor: settings.color, // 色を設定
        borderColor: settings.color.replace("0.5", "1"), // 境界線の色を濃く
        borderWidth: 1, // 枠線の太さ
      });
    } else {
      // すでに表示されているラベルの場合は、既存のデータセットに追加
      const existingDataset = result.find(dataset => dataset.label === settings.label);
      if (existingDataset) {
        existingDataset.data.push({
          x: dataPoint.r, // Recencyをxに設定
          y: dataPoint.f, // Frequencyをyに設定
          r: bubbleSize, // countをバブルサイズに設定
        });
      }
    }
  });

  return result;
});

// 中心軸を描画するカスタムプラグイン
const centerAxisPlugin = {
  id: "centerAxis",
  afterDraw(chart) {
    const { ctx, chartArea, scales } = chart;

    // 中心軸のスタイル設定
    ctx.save();
    ctx.strokeStyle = "rgba(0, 0, 0, 0.3)";
    ctx.lineWidth = 2;

    // y軸（縦）の描画
    const xCenter = scales.x.getPixelForValue(3); // x=3の位置
    ctx.beginPath();
    ctx.moveTo(xCenter, chartArea.top);
    ctx.lineTo(xCenter, chartArea.bottom);
    ctx.stroke();

    // x軸（横）の描画
    const yCenter = scales.y.getPixelForValue(3); // y=3の位置
    ctx.beginPath();
    ctx.moveTo(chartArea.left, yCenter);
    ctx.lineTo(chartArea.right, yCenter);
    ctx.stroke();

    ctx.restore();
  },
};

Chart.register(centerAxisPlugin);

// チャートデータ
const chartData = computed(() => ({
  datasets: datasets.value,
}));

// チャートオプション
const chartOptions = computed(() => ({
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
      display: true,
    },
  },
  scales: {
    x: {
      type: "linear",
      position: "bottom",
      title: {
        display: true,
        text: "Recency (新近性)",
      },
      min: 0,
      max: 6,
      ticks: {
        stepSize: 1,
      },
    },
    y: {
      type: "linear",
      position: "left",
      title: {
        display: true,
        text: "Frequency (頻度)",
      },
      min: 0,
      max: 6,
      ticks: {
        stepSize: 1,
      },
    },
  },
  plugins: {
    annotation: {
      annotations: [
        {
          type: 'label',
          xValue: 0.5,
          yValue: 5.5,
          content: '離反客',
          font: {
            size: 20,
            weight: 'bold',
          },
        },
        {
          type: 'label',
          xValue: 5.5,
          yValue: 5.5,
          content: '常連客',
          font: {
            size: 20,
            weight: 'bold',
          },
        },
        {
          type: 'label',
          xValue: 0.5,
          yValue: 0.5,
          content: '一時客',
          font: {
            size: 20,
            weight: 'bold',
          },
        },
        {
          type: 'label',
          xValue: 5.5,
          yValue: 0.5,
          content: '新規客',
          font: {
            size: 20,
            weight: 'bold',
          },
        },
      ],
    },
  },
}));
</script>

<template>
<div class="mb-8">
  <BubbleChart :chartData="chartData" :options="chartOptions" />
</div>
</template>