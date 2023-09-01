<template>
    <Menu as="div" class="relative block text-left">
      <div>
        <MenuButton
          class="flex w-full justify-center gap-x-1.5 rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50"
        >
          Create New
          <ChevronDownIcon
            class="ml-2 -mr-1 h-5 w-5 text-gray-800 hover:text-violet-100"
            aria-hidden="true"
          />
        </MenuButton>
      </div>

      <transition
        enter-active-class="transition duration-100 ease-out"
        enter-from-class="transform scale-95 opacity-0"
        enter-to-class="transform scale-100 opacity-100"
        leave-active-class="transition duration-75 ease-in"
        leave-from-class="transform scale-100 opacity-100"
        leave-to-class="transform scale-95 opacity-0"
      >
        <MenuItems
          class="absolute left-0 mt-2 w-56 origin-top-right divide-y divide-gray-100 rounded-md bg-white shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none"
        >
          <div class="px-1 py-1">
            <MenuItem v-slot="{ active }">
                <a href="#" 
                  @click.prevent="showCreateFolderModal" 
                  class="text-gray-700 block px-4 py-2 text-sm"
                >
                  New Folder
                </a>
            </MenuItem>
          </div>

          <div class="px-1 py-1">
            <FileUploadMenuItem />

            <FolderUploadMenuItem />
          </div>
        </MenuItems>
      </transition>
    </Menu>

    <CreateFolderModal v-model="createFolderModal" />
</template>

<script setup>
// Imports
import { ref } from 'vue'
import { Menu, MenuButton, MenuItems, MenuItem } from '@headlessui/vue'
import { ChevronDownIcon } from '@heroicons/vue/20/solid'

import CreateFolderModal from '@/Components/app/CreateFolderModal.vue'
import FileUploadMenuItem from './FileUploadMenuItem.vue'
import FolderUploadMenuItem from './FolderUploadMenuItem.vue'

// Refs
let createFolderModal = ref(false)

const showCreateFolderModal = () => {
  createFolderModal.value = true
}
</script>
