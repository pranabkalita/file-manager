<template>
    <div class="h-screen bg-gray-50 flex w-full gap-4">
        <Navigation />

        <main 
            class="flex flex-col flex-1 px-4 overflow-hidden"
            :class="dragOver ? 'dropzone' : ''"
            @drop.prevent="handleDrop"
            @dragover.prevent="onDragOver"
            @dragleave.prevent="onDragleave"
        >
            <template v-if="dragOver" class="text-gray-500 text-center py-8 text-sm">
                Drop files heare to upload
            </template>

            <template v-else>
                <div class="flex items-center justify-between w-full">
                    <SearchForm />
                    <UserSettingsDropdown />
                </div>

                <div class="flex-1 flex flex-col overflow-hidden">
                    <slot></slot>
                </div>
            </template>
        </main>
    </div>

    <ErrorDialog />
    <FormProgress :form="filesUploadForm" />
    <Notification />
</template>

<script setup>
// Imports
import { onMounted, ref } from 'vue';
import { emitter, FILE_UPLOAD_STARTED, showErrorDialog } from '@/event-bus';
import { useForm, usePage } from '@inertiajs/vue3';

import Navigation from '@/Components/app/Navigation.vue';
import SearchForm from '@/Components/app/SearchForm.vue';
import UserSettingsDropdown from '@/Components/app/UserSettingsDropdown.vue';
import FormProgress from '@/Components/app/FormProgress.vue';
import ErrorDialog from '@/Components/app/ErrorDialog.vue';
import Notification from '@/Components/app/Notification.vue';

// Uses
const filesUploadForm = useForm({
    files: [],
    relative_paths: [],
    parent_id: null
})

const page = usePage()

// Refs
const dragOver = ref(false)

// Props & Emits

// Computed

// Methods
const uploadFile = files => {
    if (files instanceof FileList == false) {
        return
    }

    filesUploadForm.parent_id = page.props.folder.id
    filesUploadForm.files = files
    filesUploadForm.relative_paths = [...files].map(f => f.webkitRelativePath)

    filesUploadForm.post(route('file.store'), {
        onSuccess: () => { },
        onError: errors => {
            let message = '';

            if (Object.keys(errors).length > 0) {
                message = errors[Object.keys(errors)[0]]
            } else {
                message = 'Error during file upload. Please try again later.' 
            }
    
            showErrorDialog(message)
        },
        onFinish: () => {
            filesUploadForm.clearErrors()
            filesUploadForm.reset()
        }
    })
}

const onDragOver = () => dragOver.value = true

const onDragleave = () => dragOver.value = false

const handleDrop = (e) => {
    dragOver.value = false
    const files = e.dataTransfer.files

    if (!files.length) {
        return
    }

    uploadFile(files)
}

// Hooks
onMounted(() => {
    emitter.on(FILE_UPLOAD_STARTED, uploadFile)
});
</script>

<style scoped>
.dropzone {
    width: 100%;
    height: 100%;
    color: #8d8d8d;
    border: 2px dashed gray;
    display: flex;
    justify-content: center;
    align-items: center;
}
</style>