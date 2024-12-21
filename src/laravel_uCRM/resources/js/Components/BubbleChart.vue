<script setup>
import { Chart, registerables } from "chart.js";
import { BubbleChart } from "vue-chart-3";
import { computed, ref } from "vue";
import Annotation from "chartjs-plugin-annotation";

const props = defineProps({
  data: Object
})

Chart.register(Annotation, ...registerables);

// 中心軸を描画するカスタムプラグイン
const centerAxisPlugin = {
  id: "centerAxis",
  afterDraw(chart) {
    const { ctx, chartArea, scales } = chart;

    // 中心軸のスタイル設定
    ctx.save();
    ctx.strokeStyle = "black";
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
  datasets: [
    {
      label: "顧客データ",
      data: [
        { x: 2, y: 5, r: 15 }, // x: Recency, y: Frequency, r: Monetary
        { x: 4, y: 4, r: 20 },
        { x: 1, y: 2, r: 10 },
        { x: 5, y: 3, r: 25 },
        { x: 3, y: 1, r: 18 },
      ],
      backgroundColor: "rgba(75, 192, 192, 0.5)",
      borderColor: "rgba(75, 192, 192, 1)",
      borderWidth: 1,
    },
  ],
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