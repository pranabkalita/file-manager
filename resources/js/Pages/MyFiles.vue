<template>
    <Head title="My Files" />

    <AuthenticatedLayout>
        <nav class="flex items-center justify-between p-1 mb-3">
            <ol class="inline-flex items-center space-x-1 md:space-x-3">
                <li v-for="ans in ancestors.data" :key="ans.id" class="inline-flex items-center">
                    <Link v-if="!ans.parent_id" :href="route('myFiles')" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600 dark:text-gray-400 dark:hover:text-white">
                        <HomeIcon class="w-4 h-4"/>
                        My Files
                    </Link>

                    <div v-else class="flex items-center">
                        <svg aria-hidden="true" class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20"
                             xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd"
                                d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                                clip-rule="evenodd">
                            </path>
                        </svg>

                        <Link 
                            :href="route('myFiles', {folder: ans.path})"
                            class="ml-1 text-sm font-medium text-gray-700 hover:text-blue-600 md:ml-2 dark:text-gray-400 dark:hover:text-white"
                        >
                            {{ ans.name }}
                        </Link>
                    </div>
                </li>
            </ol>
        </nav>


        <div class="flex-1 overflow-auto">
            <table class="min-w-full">
                <thead class="bg-gray-100 border-b">
                    <tr>
                        <th class="text-sm font-medium text-gray-900 px-6 py-4 text-left">
                            Name
                        </th>
                        <th class="text-sm font-medium text-gray-900 px-6 py-4 text-left">
                            Owner
                        </th>
                        <th class="text-sm font-medium text-gray-900 px-6 py-4 text-left">
                            Last Modified
                        </th>
                        <th class="text-sm font-medium text-gray-900 px-6 py-4 text-left">
                            Size
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="bg-white border-b transition duration-300 ease-in-out hover:bg-gray-100 cursor-pointer" 
                        v-if="allFiles.data.length > 0"
                        v-for="file in allFiles.data" :key="file.id"
                        @dblclick="openFolder(file)"
                    >
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 flex items-center">
                            <FileIcon :file="file" />
                            {{ file.name }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                            {{ file.owner }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                            {{ file.updated_at }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                            {{ file.size }}
                        </td>
                    </tr>
                </tbody>
            </table>

            <div v-if="!allFiles.data.length" class="py-8 text-sm text-gray-400 flex items-center justify-center">
                There is no data in this folder.
            </div>

            <div ref="loadMoreIntersect"></div>
        </div>

    </AuthenticatedLayout>
</template>

<script setup>
// Imports
import { onMounted, ref } from 'vue'
import { httpGet } from '@/Helpers/http-helpers'

import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, router, Link } from '@inertiajs/vue3'
import {HomeIcon} from '@heroicons/vue/20/solid'
import FileIcon from '@/Components/FileIcon.vue'

// Uses

// Refs
const loadMoreIntersect = ref(null)
const allFiles = ref({
    data: files.data,
    next: files.links.next
})

// Props & Emits
const { files, foldeer, ancestors } = defineProps({
    files: Object,
    folder: Object,
    ancestors: Object
})

// Computed

// Methods
const openFolder = (file) => {
    if (!file.is_folder) {
        return
    }

    router.visit(route('myFiles', { folder: file.path }))
}

const loadMore = async () => {
    if (allFiles.value.next === null) {
        return
    }

    const fileSet = await httpGet(allFiles.value.next)
    allFiles.value.data = [...allFiles.value.data, ...fileSet.data]
    allFiles.value.next = fileSet.links.next
};

// Hooks
onMounted(() => {
    const observer = new IntersectionObserver((entries) =>
        entries.forEach(entry => entry.isIntersecting && loadMore()),
        {
            rootMargin: '-250px 0px 0px 0px'
        }
    )

    observer.observe(loadMoreIntersect.value)
})
</script>