<template>
    <Modal :show="show" max-width="md">
        <div class="p-6">
            <h2 class="text-2xl mb-2 text-red-600 font-semibold">Error</h2>
            <p>{{ message }}</p>
            <div class="mt-6 flex justify-end">
                <PrimaryButton @click="close">OK</PrimaryButton>
            </div>
        </div>
    </Modal>
</template>

<script setup>
// Imports
import { ref, onMounted } from 'vue';

import Modal from '@/Components/Modal.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { emitter, SHOW_ERROR_DIALOG } from '@/event-bus';

// Refs
const show = ref(false)
const message = ref('')

// Props & Emits
const emit = defineEmits(['close'])

//Computed

// Methods
const close = () => {
    show.value = false
    message.value = ''
}

// Hooks
onMounted(() => {
    emitter.on(SHOW_ERROR_DIALOG, ({ message: msg }) => {
        show.value = true
        message.value = msg
    })
})
</script>