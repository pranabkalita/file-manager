<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <Head title="My Files" />

    <AuthenticatedLayout>
        <div>My Files</div>
    </AuthenticatedLayout>
</template>