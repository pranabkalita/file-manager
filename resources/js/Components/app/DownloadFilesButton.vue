<template>
    <PrimaryButton @click="onDownloadClick">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 mr-2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 7.5h-.75A2.25 2.25 0 004.5 9.75v7.5a2.25 2.25 0 002.25 2.25h7.5a2.25 2.25 0 002.25-2.25v-7.5a2.25 2.25 0 00-2.25-2.25h-.75m-6 3.75l3 3m0 0l3-3m-3 3V1.5m6 9h.75a2.25 2.25 0 012.25 2.25v7.5a2.25 2.25 0 01-2.25 2.25h-7.5a2.25 2.25 0 01-2.25-2.25v-.75" />
        </svg>

        Download
    </PrimaryButton>
</template>

<script setup>
import { defineProps, defineEmits, ref } from 'vue';
import { httpGet } from '@/Helpers/http-helpers';
import { usePage } from '@inertiajs/vue3';

import PrimaryButton from '../PrimaryButton.vue';
import { showSuccessNotification } from '@/event-bus';

// Uses
const page = usePage()

// Refs
const showDeleteDialog = ref(false)

// Props & Emits
const props = defineProps({
    all: {
        type: Boolean,
        default: false
    },
    ids: {
        type: Array,
        required: false
    }
})

const emit = defineEmits(['delete'])

// Methods
const onDownloadClick = async () => {
    if (!props.all && props.ids.length === 0) {
        return;
    }

    const urlSearchParams = new URLSearchParams()
    urlSearchParams.append('parent_id', page.props.folder.id)
    if (props.all) {
        urlSearchParams.append('all', props.all ? 1: 0);
    } else {
        for (let id of props.ids) {
            urlSearchParams.append('ids[]', id)
        }
    }

    const { filename, url } = await httpGet(route('files.download') + "?" + urlSearchParams.toString())

    if (!url) {
        return
    }

    const _anchor = document.createElement('a')
    _anchor.download = filename
    _anchor.href = url
    _anchor.click()

    showSuccessNotification('File has been downloaded !')
}

</script>