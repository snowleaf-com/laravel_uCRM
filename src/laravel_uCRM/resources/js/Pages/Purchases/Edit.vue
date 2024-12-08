<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { computed, onMounted, ref } from 'vue';
import { Head } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';
import { useForm } from '@inertiajs/inertia-vue3';
import dayjs from 'dayjs';

const props = defineProps({
  errors: Object,
  items : Array,
  order: Array
})

const form = useForm({
  id: props.order[0].id,
  date: dayjs(props.order[0].created_at).format("YYYY-MM-DD"),
  customer_id: props.order[0].customer_id,
  status: props.order[0].status,
  items: []
})

const itemList = ref([])

const quantity = [ "0", "1", "2", "3", "4", "5", "6", "7", "8", "9"]

onMounted(() => {
  console.log(props.items, props.order);

  props.items.forEach(item => {
    itemList.value.push({
      id: item.id, name:item.name, price:item.price, quantity: item.quantity
    })
  })
})

const totalPrice = computed(() => {
  let total = 0;
  itemList.value.forEach( item => {
    total += item.price * item.quantity
  })
  return total;
})

const updatePurchase = (id) => {
  itemList.value.forEach( item => {
    if( item.quantity > 0) {
      form.items.push({
        id: item.id,
        quantity: item.quantity
      })
    } 
  })
  form.put(route('purchases.update', { purchase: id }), form );
}

</script>

<template>
    <Head title="購買履歴 編集画面" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">購買履歴 編集画面</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                  <section class="text-gray-600 body-font relative">
                    <form @submit.prevent="updatePurchase(form.id)">
                      <div class="container px-5 py-8 mx-auto">
                        <div class="lg:w-1/2 md:w-2/3 mx-auto">
                          <div class="flex flex-wrap -m-2">
                            <div class="p-2 w-full">
                              <div class="relative">
                                <label for="name" class="leading-7 text-sm text-gray-600">日付</label>
                                <input type="date" id="date" name="date" :value="form.date" class="w-full bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out" disabled>
                              </div>
                            </div>
                            <div class="p-2 w-full">
                              <div class="relative">
                                <label for="customer" class="leading-7 text-sm text-gray-600">会員名</label>
                                <input type="text" id="customer" name="customer" :value="props.order[0].customer_name" class="w-full bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out" disabled>
                              </div>
                            </div>
                            <InputError v-if="errors.items" v-for="(error, index) in errors.items" 
                            :key="index" 
                            :message="error" />
                            <div class="mt-2 p-2 w-full mx-auto overflow-auto">
                              <table class="table-auto w-full text-left whitespace-no-wrap">
                                <thead>
                                  <tr>
                                    <th class="px-4 py-3 title-font tracking-wider font-medium text-gray-900 text-sm bg-gray-100 rounded-tl rounded-bl">ID</th>
                                    <th class="px-4 py-3 title-font tracking-wider font-medium text-gray-900 text-sm bg-gray-100">商品名</th>
                                    <th class="px-4 py-3 title-font tracking-wider font-medium text-gray-900 text-sm bg-gray-100">金額</th>
                                    <th class="px-4 py-3 title-font tracking-wider font-medium text-gray-900 text-sm bg-gray-100">数量</th>
                                    <th class="px-4 py-3 title-font tracking-wider font-medium text-gray-900 text-sm bg-gray-100">小計</th>
                                  </tr>
                                </thead>
                                <tbody>
                                  <tr v-for="item in itemList" :key="item.id">
                                    <td class="border-b-2 border-gray-200 px-4 py-3">
                                    {{ item.id }}
                                    </td>
                                    <td class="border-b-2 border-gray-200 px-4 py-3">{{ item.name }}</td>
                                    <td class="border-b-2 border-gray-200 px-4 py-3">{{ item.price }}</td>
                                    <td class="border-b-2 border-gray-200 px-4 py-3">
                                      <select name="quantity" v-model="item.quantity">
                                        <option v-for="q in quantity" :value="q">
                                          {{ q }}
                                        </option>
                                      </select>
                                    </td>
                                    <td class="border-b-2 border-gray-200 px-4 py-3">
                                      {{ item.price * item.quantity }}
                                    </td>
                                  </tr>
                                </tbody>
                              </table>
                            </div> 
                            <div class="p-2 w-full">
                              <div>
                                <label for="price" class="leading-7 text-sm text-gray-600">合計金額</label>
                                <div id="price" name="price" class="w-full bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out">
                                  {{ totalPrice }} 円
                                </div>
                              </div>
                            </div>
                            <div class="p-2 w-full">
                              <div class="relative">
                                <label for="status" class="leading-7 text-sm text-gray-600">ステータス</label><InputError :message="errors.status" />
                                <label for="status1" class="ml-5">
                                  <input type="radio" id="status1" name="status" value="1" v-model="form.status" class="mr-1">未キャンセル
                                </label>
                                <label for="status0" class="ml-5">
                                  <input type="radio" id="status0" name="status" value="0" v-model="form.status" class="mr-1">キャンセルする
                                </label>
                              </div>
                            </div>
                            <div class="p-2 w-full">
                              <button 
                                class="flex mx-auto text-white border-0 py-2 px-8 focus:outline-none rounded text-lg"
                                :class="{
                                  'bg-indigo-500 hover:bg-indigo-600': !form.processing,
                                  'bg-gray-400 cursor-not-allowed': form.processing
                                }"
                                :disabled="form.processing"
                              >
                                {{ form.processing ? '送信中...' : '更新する' }}
                              </button>
                            </div>
                          </div>
                        </div>
                      </div>
                    </form>
                  </section>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
