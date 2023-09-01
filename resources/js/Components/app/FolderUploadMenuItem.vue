<template>
    <MenuItem v-slot="{ active }">
        <a href="#" class="text-gray-700 block px-4 py-2 text-sm relative">
            Upload Folder
            <input type="file" class="absolute left-0 bottom-0 right-0 cursor-pointer opacity-0" multiple directory webkitdirectory @change="onChange" />
        </a>
    </MenuItem>
</template>

<script setup>
import { emitter, FILE_UPLOAD_STARTED } from '@/event-bus'

import { MenuItem } from '@headlessui/vue'

const onChange = (e) => emitter.emit(FILE_UPLOAD_STARTED, e.target.files)
</script>