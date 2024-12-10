<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { useForm } from '@inertiajs/inertia-vue3';
import { getToday } from '@/common';
import { onMounted } from 'vue';
import axios from 'axios';
import { ref } from 'vue';


onMounted(() => {
    form.startDate = getToday()
    form.endDate = getToday()
})

const form = useForm({
    startDate: null,
    endDate: null,
    type: 'perDay'
})

const data = ref(
    {data: null}
);

const getData = async () => {
    try {
        await axios.get('/api/analysis', {
            params: {
                startDate: form.startDate,
                endDate: form.endDate,
                type: form.type
            }
        }).then( res => {
            data.value.data = res.data.data
            console.log(data);
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
                        <form @submit.prevent="getData">
                            From: <input type="date" name="startDate" v-model="form.startDate">
                            To: <input type="date" name="endDate" v-model="form.endDate"><br>
                            <button 
                                class="flex mx-auto text-white border-0 py-2 px-8 focus:outline-none rounded text-lg"
                                :class="{
                                'bg-indigo-500 hover:bg-indigo-600': !form.processing,
                                'bg-gray-400 cursor-not-allowed': form.processing
                                }"
                                :disabled="form.processing"
                            >
                                {{ form.processing ? '送信中...' : '分析する' }}</button>
                        </form>
                        <div class="lg:w-2/3 w-full mx-auto overflow-auto" v-show="data.data">
                            <table class="table-auto w-full text-left whitespace-no-wrap">
                                <thead>
                                    <tr>
                                        <th class="px-4 py-3 title-font tracking-wider font-medium text-gray-900 text-sm bg-gray-100 rounded-tl rounded-bl">年月日</th>
                                        <th class="px-4 py-3 title-font tracking-wider font-medium text-gray-900 text-sm bg-gray-100">金額</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="item in data.data" :key="item.date">
                                        <td class="border-b-2 border-gray-200 px-4 py-3">{{ item.date }}</td>
                                        <td class="border-b-2 border-gray-200 px-4 py-3">{{ item.total }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
