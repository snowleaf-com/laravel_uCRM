<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { onMounted, ref } from 'vue';
import { Head, useForm } from '@inertiajs/inertia-vue3';
import InputError from '@/Components/InputError.vue';
import { Core as YubinBangoCore } from "yubinbango-core2";

const nameInput = ref(null); // refを定義

const props = defineProps({
  customer: Object,
  errors: Object,
})

const form = useForm({
  id: props.customer.id,
  name: props.customer.name,
  kana: props.customer.kana,
  tel: props.customer.tel,
  email: props.customer.email,
  postcode: props.customer.postcode,
  address: props.customer.address,
  birthday: props.customer.birthday,
  gender: props.customer.gender,
  memo: props.customer.memo,
})

const fetchAddress = () => {
  new YubinBangoCore(String(form.postcode), (value) => {
    form.address = value.region + value.locality + value.street
  })
}

const updateCustomer = (id) => {
  form.put(route('customers.update', { customer: id }), form); // Inertiaを使わずにformメソッドを使う
}

onMounted(() => {
  nameInput.value.focus(); // ページロード時にフォーカス
})

</script>

<template>
    <Head title="顧客編集" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">顧客編集</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                  <section class="text-gray-600 body-font relative">
                    <form @submit.prevent="updateCustomer(form.id)">
                      <div class="container px-5 py-8 mx-auto">
                        <div class="lg:w-1/2 md:w-2/3 mx-auto">
                          <div class="flex flex-wrap -m-2">
                            <div class="p-2 w-full">
                              <div class="relative">
                                <label for="name" class="leading-7 text-sm text-gray-600">顧客名</label><InputError :message="errors.name" />
                                <input type="text" id="name" name="name" v-model="form.name" class="w-full bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out"
                                ref="nameInput">
                              </div>
                            </div>
                            <div class="p-2 w-full">
                              <div class="relative">
                                <label for="kana" class="leading-7 text-sm text-gray-600">かな</label><InputError :message="errors.kana" />
                                <input type="text" id="kana" name="kana" v-model="form.kana" class="w-full bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out">
                              </div>
                            </div>
                            <div class="p-2 w-full">
                              <div class="relative">
                                <label for="tel" class="leading-7 text-sm text-gray-600">電話番号</label><InputError :message="errors.tel" />
                                <input type="text" id="tel" name="tel" v-model="form.tel" class="w-full bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out">
                              </div>
                            </div>
                            <div class="p-2 w-full">
                              <div class="relative">
                                <label for="email" class="leading-7 text-sm text-gray-600">メールアドレス</label><InputError :message="errors.email" />
                                <input type="text" id="email" name="email" v-model="form.email" class="w-full bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out">
                              </div>
                            </div>
                            <div class="p-2 w-full">
                              <div class="relative">
                                <label for="postcode" class="leading-7 text-sm text-gray-600">郵便番号</label><InputError :message="errors.postcode" />
                                <input type="number" id="postcode" name="postcode" v-model="form.postcode" @change="fetchAddress" class="w-full bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out">
                              </div>
                            </div>
                            <div class="p-2 w-full">
                              <div class="relative">
                                <label for="address" class="leading-7 text-sm text-gray-600">住所</label><InputError :message="errors.address" />
                                <input type="text" id="address" name="address" v-model="form.address" class="w-full bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out">
                              </div>
                            </div>
                            <div class="p-2 w-full">
                              <div class="relative">
                                <label for="birthday" class="leading-7 text-sm text-gray-600">誕生日</label><InputError :message="errors.birthday" />
                                <input type="date" id="birthday" name="birthday" v-model="form.birthday" class="w-full bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out">
                              </div>
                            </div>
                            <div class="p-2 w-full">
                              <div class="relative">
                                <label class="leading-7 text-sm text-gray-600">性別</label><InputError :message="errors.gender" />
                                <input type="radio" id="gender0" name="gender" v-model="form.gender" value="0" class="ml-8">
                                <label for="gender0" class="ml-2 mr-4">男性</label>
                                <input type="radio" id="gender1" name="gender" v-model="form.gender" value="1">
                                <label for="gender1" class="ml-2 mr-4">女性</label>
                                <input type="radio" id="gender2" name="gender" v-model="form.gender" value="2">
                                <label for="gender2" class="ml-2 mr-4">その他</label>
                              </div>
                            </div>
                            <div class="p-2 w-full">
                              <div class="relative">
                                <label for="memo" class="leading-7 text-sm text-gray-600">メモ</label><InputError :message="errors.memo" />
                                <textarea id="memo" name="memo" v-model="form.memo" class="w-full bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 h-32 text-base outline-none text-gray-700 py-1 px-3 resize-none leading-6 transition-colors duration-200 ease-in-out"></textarea>
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
                                {{ form.processing ? '送信中...' : '顧客編集' }}
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
