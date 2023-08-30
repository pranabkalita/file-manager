<template>
    <modal :show="modelValue" @show="onShow" max-width="sm">
        <div class="p-6">
            <h2 class="text-lg font-medium text-gray-900">
                Create New Folder
            </h2>

            <div class="mt-6">
                <InputLabel for="folderName" value="Folder Name" class="sr-only" />

                <TextInput 
                    type="text" 
                    id="folderName" 
                    class="mt-1 block w-full" 
                    :class="form.errors.name ? 'border-red-500 focus:border-red-500 focus:ring-red-500': ''"
                    placeholder="Folder Name"
                    ref="folderNameInput"
                    v-model="form.name" 
                    @keyup.enter="createFolder"    
                />
                <InputError :message="form.errors.name" class="mt-2" />
            </div>
            <div class="mt-6 flex justify-end">
                <SecondaryButton @click.prevent="closeModal">Cancel</SecondaryButton>

                <PrimaryButton 
                    class="ml-3"
                    :class="{ 'opacity-25': form.processing }"
                    @click.prevent="createFolder" :disabled="form.processing"
                >
                    Submit
                </PrimaryButton>
            </div>
        </div>
    </modal>
</template>

<script setup>
// Imports
import { ref, nextTick } from 'vue'
import { useForm } from '@inertiajs/vue3';

import Modal from '@/Components/Modal.vue'
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

// Uses
const form = useForm({
    name: ''
})

// Refs
let folderNameInput = ref(null)

// Props & Emits
const { modelValue } = defineProps({
    modelValue: Boolean
})

const emit = defineEmits(['update:modelValue']) 

// Computed

// Methods
const onShow = () => {
    nextTick(() => folderNameInput.value.focus())
}

const createFolder = () => {
    form.post(route('folder.create'), {
        preserveScroll: true,
        onSuccess: () => {
            closeModal()
            form.reset()

            // Show Success Notification
        },
        onError: () => folderNameInput.value.focus()
    })
}

const closeModal = () => {
    emit('update:modelValue')

    form.clearErrors()
    form.reset()
}

// Hooks


</script>