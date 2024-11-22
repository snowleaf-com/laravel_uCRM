<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const isShow = ref(false);
const search = ref('');
const customers = ref({});
const modal = ref(null); // モーダル要素を参照するため
const lastFocusedElement = ref(null); // フォーカス管理用

const searchCustomers = async () => {
  try {
    const res = await axios.get(`/api/searchCustomers/?search=${search.value}`);
    customers.value = res.data; // refなのでvalueをつける！
    toggleStatus();
  } catch (e) {
    console.error(e.message);
  }
};

const toggleStatus = () => {
  isShow.value = !isShow.value;
  if (isShow.value) {
    lastFocusedElement.value = document.activeElement; // 現在のフォーカスを記録
    modal.value?.setAttribute('aria-hidden', 'false'); // モーダルを支援技術に表示
    modal.value?.querySelector('[tabindex]').focus(); // 最初のフォーカス可能要素にフォーカス
  } else {
    modal.value?.setAttribute('aria-hidden', 'true'); // モーダルを支援技術から隠す
    lastFocusedElement.value?.focus(); // 元の要素にフォーカスを戻す
  }
}

onMounted(() => {
  modal.value?.setAttribute('aria-hidden', 'true'); // 初期状態でモーダルを非表示
})

const emit = defineEmits(['update:customerId'])

const setCustomer = (e) => {
  search.value = e.kana
  emit('update:customerId', e.id)
  toggleStatus();
}
</script>

<template>
  <div class="modal" id="modal-1" ref="modal" :aria-hidden="!isShow" v-show="isShow">
    <div class="modal__overlay" tabindex="-1" data-micromodal-close>
      <div class="modal__container w-2/3" role="dialog" aria-modal="true" aria-labelledby="modal-1-title">
        <header class="modal__header">
          <h2 class="modal__title" id="modal-1-title">
            顧客検索
          </h2>
          <button
            type="button"
            class="modal__close"
            aria-label="Close modal"
            data-micromodal-close
            @click="toggleStatus"
          >
          </button>
        </header>
        <main class="modal__content" id="modal-1-content">
          <div class="lg:w-2/3 w-full mx-auto overflow-auto">
            <table class="table-auto w-full text-left whitespace-no-wrap">
              <thead>
                <tr>
                  <th class="px-4 py-3 title-font tracking-wider font-medium text-gray-900 text-sm bg-gray-100 rounded-tl rounded-bl">ID</th>
                  <th class="px-4 py-3 title-font tracking-wider font-medium text-gray-900 text-sm bg-gray-100">氏名</th>
                  <th class="px-4 py-3 title-font tracking-wider font-medium text-gray-900 text-sm bg-gray-100">かな</th>
                  <th class="px-4 py-3 title-font tracking-wider font-medium text-gray-900 text-sm bg-gray-100">電話番号</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="customer in customers.data" :key="customer.id">
                  <td class="border-b-2 border-gray-200 px-4 py-3">
                    <button type="button" class="text-blue-400" @click="setCustomer({id: customer.id, kana: customer.kana})">
                      {{ customer.id }}
                    </button>
                  </td>
                  <td class="border-b-2 border-gray-200 px-4 py-3">{{ customer.name }}</td>
                  <td class="border-b-2 border-gray-200 px-4 py-3">{{ customer.kana }}</td>
                  <td class="border-b-2 border-gray-200 px-4 py-3">{{ customer.tel }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </main>
        <footer class="modal__footer">
          <button
            type="button"
            class="modal__btn"
            data-micromodal-close
            aria-label="Close this dialog window"
            @click="toggleStatus"
          >
            閉じる
          </button>
        </footer>
      </div>
    </div>
  </div>
  <div class="flex items-center space-x-2">
    <input 
      type="text" 
      v-model="search" 
      placeholder="検索" 
      class="w-full bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-base outline-none text-gray-700 py-2 px-4 leading-8 transition-colors duration-200 ease-in-out"
    >
    <button 
      type="button" 
      @click="searchCustomers" 
      class="text-white border-0 py-2 px-6 focus:outline-none rounded text-lg bg-teal-500 hover:bg-teal-600 transition duration-200 ease-in-out whitespace-nowrap"
    >
      検索する
    </button>
</div>
</template>
