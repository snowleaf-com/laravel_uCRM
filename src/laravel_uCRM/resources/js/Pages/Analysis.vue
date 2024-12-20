<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { useForm } from '@inertiajs/inertia-vue3';
import { getToday } from '@/common';
import { onMounted, computed, watch } from 'vue';
import axios from 'axios';
import { ref } from 'vue';
import dayjs from 'dayjs';
import Chart from '@/Components/Chart.vue';
import ResultTable from '@/Components/ResultTable.vue';

onMounted(() => {
  form.startDate = getToday()
  form.endDate = getToday()
})

const form = useForm({
  startDate: null,
  endDate: null,
  type: 'perDay',
  rfmPrms: [
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
  ]
})

const data = ref({
  data: null,
  labels: null,
  totals: null
})

watch(
  () => form.type, // 監視対象: form.type
  (newValue, oldValue) => {
    console.log('type が変更されました');
    console.log('新しい値:', newValue);
    console.log('古い値:', oldValue);

    form.startDate = getToday()
    form.endDate = getToday()
    // `form.type` によって 日付 を加工する
    if (form.type === 'perMonth') {
      form.startDate = dayjs(form.startDate).format('YYYY-MM');
      form.endDate = dayjs(form.endDate).format('YYYY-MM');
    } else if (form.type === 'perYear') {
      form.startDate = dayjs(form.startDate).format('YYYY');
      form.endDate = dayjs(form.endDate).format('YYYY');
    }
    console.log('更新後の startDate:', form.startDate);
    console.log('更新後の endDate:', form.endDate);
  }
);

const getData = async () => {
  try {
    // startDate と endDate を加工する
    const adjustedStartDate = computed(() => {
      if (!form.startDate) return null;

      const startDateStr = String(form.startDate); // 数値型を文字列に変換

      if (startDateStr.length === 7) {
        // YYYY-MM の場合
        return `${startDateStr}-01`;
      } else if (startDateStr.length === 4) {
        // YYYY の場合
        return `${startDateStr}-01-01`;
      }
      return startDateStr; // YYYY-MM-DD の場合はそのまま
    });

    const adjustedEndDate = computed(() => {
      if (!form.endDate) return null;

      const endDateStr = String(form.endDate); // 数値型を文字列に変換

      if (endDateStr.length === 7) {
        // YYYY-MM の場合
        return `${endDateStr}-31`;
      } else if (endDateStr.length === 4) {
        // YYYY の場合
        return `${endDateStr}-12-31`;
      }
      return endDateStr; // YYYY-MM-DD の場合はそのまま
    });
    
    await axios.get('/api/analysis', {
      params: {
        startDate: adjustedStartDate.value,
        endDate: adjustedEndDate.value,
        type: form.type
      }
    }).then( res => {
      data.value.data = res.data.data
      data.value.totals = res.data.totals
      data.value.type = res.data.type

      // `form.type` によって `labels` を加工する
      if (form.type === 'perDay' || form.type === 'decile') {
        data.value.labels = res.data.labels; // そのまま使う

      } else if (form.type === 'perMonth') {
        // 月別の場合 (YYYY-MM に整形)
        data.value.labels = res.data.labels.map((label) =>
          dayjs(label).format('YYYYMM')
        );
      } else if (form.type === 'perYear') {
        // 年別の場合 (YYYY のみ)
        data.value.labels = res.data.labels.map((label) =>
          dayjs(label).format('YYYY')
        );
      }

      // デバッグ用にデータ確認
      console.log('data:', data.value.labels);
    })
  } catch(e) {
    console.log(e.message)
  }
}
</script>

<template>
  <Head title="データ分析" />

  <AuthenticatedLayout>
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">データ分析</h2>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6 text-gray-900">
            <div class="flex justify-center items-center mb-4">
              <span class="mr-6">分析方法</span>
              <input type="radio" v-model="form.type" id="perDay" value="perDay" checked><label class="mr-4 ml-1" for="perDay">日別</label>
              <input type="radio" v-model="form.type" id="perMonth" value="perMonth"><label class="mr-4 ml-1" for="perMonth">月別</label>
              <input type="radio" v-model="form.type" id="perYear" value="perYear"><label class="mr-4 ml-1" for="perYear">年別</label>
              <input type="radio" v-model="form.type" id="decile" value="decile"><label class="mr-4 ml-1" for="decile">デシル分析</label>
              <input type="radio" v-model="form.type" id="rfm" value="rfm"><label class="mr-4 ml-1" for="rfm">RFM分析</label>
            </div>
            <div class="flex justify-center items-center mb-4">

              <!-- 日別検索 -->
              <form @submit.prevent="getData" class="flex items-center space-x-4" v-if="form.type === 'perDay'">
                <div class="flex items-center">
                  <label for="startDate" class="mr-2">From:</label>
                  <input type="date" id="startDate" name="startDate" v-model="form.startDate" class="border p-2 rounded">
                </div>
                <div class="flex items-center">
                  <label for="endDate" class="mr-2">To:</label>
                  <input type="date" id="endDate" name="endDate" v-model="form.endDate" class="border p-2 rounded">
                </div>
                <button 
                  class="text-white border-0 py-2 px-8 focus:outline-none rounded text-lg"
                  :class="{
                    'bg-indigo-500 hover:bg-indigo-600': !form.processing,
                    'bg-gray-400 cursor-not-allowed': form.processing
                  }"
                  :disabled="form.processing"
                >
                  {{ form.processing ? '送信中...' : '分析する' }}
                </button>
              </form>

              <!-- 月別検索 -->
              <form @submit.prevent="getData" class="flex items-center space-x-4" v-if="form.type === 'perMonth'">
                <div class="flex items-center">
                  <label for="startDate" class="mr-2">From:</label>
                  <input type="month" id="startDate" name="startDate" v-model="form.startDate" class="border p-2 rounded">
                </div>
                <div class="flex items-center">
                  <label for="endDate" class="mr-2">To:</label>
                  <input type="month" id="endDate" name="endDate" v-model="form.endDate" class="border p-2 rounded">
                </div>
                <button 
                  class="text-white border-0 py-2 px-8 focus:outline-none rounded text-lg"
                  :class="{
                    'bg-indigo-500 hover:bg-indigo-600': !form.processing,
                    'bg-gray-400 cursor-not-allowed': form.processing
                  }"
                  :disabled="form.processing"
                >
                  {{ form.processing ? '送信中...' : '分析する' }}
                </button>
              </form>

              <!-- 年別検索 -->
              <form @submit.prevent="getData" class="flex items-center space-x-4"
              v-if="form.type === 'perYear'">
                <div class="flex items-center">
                  <label for="startDate" class="mr-2">From:</label>
                  <input type="number" id="startDate" name="startDate" v-model="form.startDate" class="border p-2 rounded" :min="2000" :max="2030" placeholder="年">
                </div>
                <div class="flex items-center">
                  <label for="endDate" class="mr-2">To:</label>
                  <input type="number" id="endDate" name="endDate" v-model="form.endDate" class="border p-2 rounded" :min="2000" :max="2030" placeholder="年">
                </div>
                <button 
                  class="text-white border-0 py-2 px-8 focus:outline-none rounded text-lg"
                  :class="{
                    'bg-indigo-500 hover:bg-indigo-600': !form.processing,
                    'bg-gray-400 cursor-not-allowed': form.processing
                  }"
                  :disabled="form.processing"
                >
                  {{ form.processing ? '送信中...' : '分析する' }}
                </button>
              </form>

              <!-- デシル分析検索 -->
              <form @submit.prevent="getData" class="flex items-center space-x-4"
              v-if="form.type === 'decile'">
                <div class="flex items-center">
                  <label for="startDate" class="mr-2">From:</label>
                  <input type="date" id="startDate" name="startDate" v-model="form.startDate" class="border p-2 rounded">
                </div>
                <div class="flex items-center">
                  <label for="endDate" class="mr-2">To:</label>
                  <input type="date" id="endDate" name="endDate" v-model="form.endDate" class="border p-2 rounded">
                </div>
                <button 
                  class="text-white border-0 py-2 px-8 focus:outline-none rounded text-lg"
                  :class="{
                    'bg-indigo-500 hover:bg-indigo-600': !form.processing,
                    'bg-gray-400 cursor-not-allowed': form.processing
                  }"
                  :disabled="form.processing"
                >
                  {{ form.processing ? '送信中...' : 'デシル分析' }}
                </button>
              </form>

              <!-- RFM分析検索 -->
              <div class="flex flex-col items-center space-y-4" v-if="form.type === 'rfm'">
                <!-- RFM分析検索 -->
                <form @submit.prevent="getData" class="w-full space-y-4">
                  <div class="flex flex-wrap items-center space-x-4 justify-center">
                    <div class="flex items-center space-x-2">
                      <label for="startDate" class="font-bold">From:</label>
                      <input type="date" id="startDate" name="startDate" v-model="form.startDate" class="border p-2 rounded">
                    </div>
                    <div class="flex items-center space-x-2">
                      <label for="endDate" class="font-bold">To:</label>
                      <input type="date" id="endDate" name="endDate" v-model="form.endDate" class="border p-2 rounded">
                    </div>
                  </div>

                  <table class="table-auto w-full border-collapse border border-gray-300">
                    <thead>
                      <tr class="bg-gray-100">
                        <th class="border border-gray-300 px-4 py-2">ランク</th>
                        <th class="border border-gray-300 px-4 py-2">R (最新購入日)</th>
                        <th class="border border-gray-300 px-4 py-2">F (購入回数)</th>
                        <th class="border border-gray-300 px-4 py-2">M (購入合計金額)</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr>
                        <td class="border border-gray-300 px-4 py-2 text-center">5</td>
                        <td class="border border-gray-300 px-4 py-2 text-center"><input class="w-20" type="number" v-model="form.rfmPrms[0]">日以内</td>
                        <td class="border border-gray-300 px-4 py-2 text-center"><input class="w-20" type="number" v-model="form.rfmPrms[4]">回以上</td>
                        <td class="border border-gray-300 px-4 py-2 text-center"><input class="w-32" type="number" v-model="form.rfmPrms[8]">円以上</td>
                      </tr>
                      <tr>
                        <td class="border border-gray-300 px-4 py-2 text-center">4</td>
                        <td class="border border-gray-300 px-4 py-2 text-center"><input class="w-20" type="number" v-model="form.rfmPrms[1]">日以内</td>
                        <td class="border border-gray-300 px-4 py-2 text-center"><input class="w-20" type="number" v-model="form.rfmPrms[5]">回以上</td>
                        <td class="border border-gray-300 px-4 py-2 text-center"><input class="w-32" type="number" v-model="form.rfmPrms[9]">円以上</td>
                      </tr>
                      <tr>
                        <td class="border border-gray-300 px-4 py-2 text-center">3</td>
                        <td class="border border-gray-300 px-4 py-2 text-center"><input class="w-20" type="number" v-model="form.rfmPrms[2]">日以内</td>
                        <td class="border border-gray-300 px-4 py-2 text-center"><input class="w-20" type="number" v-model="form.rfmPrms[6]">回以上</td>
                        <td class="border border-gray-300 px-4 py-2 text-center"><input class="w-32" type="number" v-model="form.rfmPrms[10]">円以上</td>
                      </tr>
                      <tr>
                        <td class="border border-gray-300 px-4 py-2 text-center">2</td>
                        <td class="border border-gray-300 px-4 py-2 text-center"><input class="w-20" type="number" v-model="form.rfmPrms[3]">日以内</td>
                        <td class="border border-gray-300 px-4 py-2 text-center"><input class="w-20" type="number" v-model="form.rfmPrms[7]">回以上</td>
                        <td class="border border-gray-300 px-4 py-2 text-center"><input class="w-32" type="number" v-model="form.rfmPrms[11]">円以上</td>
                      </tr>
                    </tbody>
                  </table>

                  <div class="flex justify-center">
                    <button 
                      class="bg-indigo-500 hover:bg-indigo-600 text-white border-0 py-2 px-8 rounded text-lg disabled:bg-gray-400 disabled:cursor-not-allowed"
                      :disabled="form.processing"
                    >
                      {{ form.processing ? '送信中...' : 'RFM分析' }}
                    </button>
                  </div>
                </form>
              </div>

            </div>

            <Chart :data="data" />
            <ResultTable :data="data" />

          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
