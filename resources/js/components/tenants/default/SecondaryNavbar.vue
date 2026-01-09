<script setup lang="ts">
import { ref, computed } from 'vue';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger
} from '@/components/ui/dropdown-menu';
import { router } from '@inertiajs/vue3';
import { ArrowLeft, Share, Copy, Check, Mail } from 'lucide-vue-next';
import { useMediaQuery, useClipboard } from '@vueuse/core';

const isDesktop = useMediaQuery('(min-width: 768px)');
const buttonSize = computed(() => isDesktop.value ? 'lg' : 'icon');

const { copy } = useClipboard({ legacy: true });
const isCopied = ref(false);

const getUrl = () => typeof window !== 'undefined' ? window.location.href : '';

const copyLink = () => {
    copy(getUrl());
    isCopied.value = true;
};

// Smart back: use history if available, otherwise go to homepage
const goBack = () => {
    resetCopied();
    // Check if there's a previous page in history from the same origin
    if (window.history.length > 1 && document.referrer && document.referrer.includes(window.location.origin)) {
        window.history.back();
    } else {
        // No history or came from external source, go to homepage
        router.visit('/');
    }
};

// Reset copied state on any click outside the button
const resetCopied = () => {
    if (isCopied.value) {
        isCopied.value = false;
    }
};

const shareOnWhatsApp = () => {
    resetCopied();
    window.open(`https://wa.me/?text=${encodeURIComponent(getUrl())}`, '_blank');
};

const shareOnEmail = () => {
    resetCopied();
    const subject = document.title || 'Check this out';
    window.open(`mailto:?subject=${encodeURIComponent(subject)}&body=${encodeURIComponent(getUrl())}`, '_blank');
};

const shareOnFacebook = () => {
    resetCopied();
    window.open(`https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(getUrl())}`, '_blank');
};

const shareOnTwitter = () => {
    resetCopied();
    const text = document.title || '';
    window.open(`https://twitter.com/intent/tweet?url=${encodeURIComponent(getUrl())}&text=${encodeURIComponent(text)}`, '_blank');
};
</script>

<template>
    <header class="bg-background w-full py-0.5 md:py-5 lg:py-7">
        <div class="mx-auto max-w-screen-xl flex items-center justify-between py-3 border-b md:px-6 md:py-0 md:border-b-0 md:gap-4">
            <div class="ps-3.5 md:ps-0 md:me-auto">
                <Button @click="goBack" variant="ghost" :size="buttonSize" aria-label="Go back" class="hover:cursor-pointer md:max-w-10">
                    <ArrowLeft class="stroke-[1.5px]" />
                </Button>
            </div>
            <div class="pe-6 md:pe-0">
                <nav>
                    <ul class="flex items-center gap-2 md:gap-4">
                        <li>
                            <Button @click="copyLink" variant="outline" :size="isCopied ? (isDesktop ? 'lg' : 'default') : buttonSize" aria-label="Copy Link" class="gap-2 hover:cursor-pointer">
                                <component :is="isCopied ? Check : Copy" class="h-4 w-4" />
                                <span :class="isCopied ? '' : 'hidden md:inline'">{{ isCopied ? 'Copied!' : 'Copy Link' }}</span>
                            </Button>
                        </li>
                        <li>
                            <DropdownMenu>
                                <DropdownMenuTrigger as-child>
                                    <Button variant="outline" :size="buttonSize" aria-label="Share" class="gap-2 hover:cursor-pointer">
                                        <Share class="h-4 w-4" />
                                        <span class="hidden md:inline">Share</span>
                                    </Button>
                                </DropdownMenuTrigger>
                                <DropdownMenuContent align="end" class="w-48">
                                    <!-- Email -->
                                    <DropdownMenuItem @click="shareOnEmail" class="gap-2 cursor-pointer">
                                        <Mail class="h-4 w-4" />
                                        Email
                                    </DropdownMenuItem>
                                    
                                    <!-- WhatsApp -->
                                    <DropdownMenuItem @click="shareOnWhatsApp" class="gap-2 cursor-pointer">
                                        <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 256 256"><rect width="256" height="256" fill="none"/><path d="M72,104a32,32,0,0,1,32-32l16,32-12.32,18.47a48.19,48.19,0,0,0,25.85,25.85L152,136l32,16a32,32,0,0,1-32,32A80,80,0,0,1,72,104Z" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="16"/><path d="M79.93,211.11a96,96,0,1,0-35-35h0L32.42,213.46a8,8,0,0,0,10.12,10.12l37.39-12.47Z" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="16"/></svg>
                                        WhatsApp
                                    </DropdownMenuItem>
                                    
                                    <!-- Facebook -->
                                    <DropdownMenuItem @click="shareOnFacebook" class="gap-2 cursor-pointer">
                                        <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 256 256"><rect width="256" height="256" fill="none"/><circle cx="128" cy="128" r="96" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="16"/><path d="M168,88H152a24,24,0,0,0-24,24V224" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="16"/><line x1="96" y1="144" x2="160" y2="144" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="16"/></svg>
                                        Facebook
                                    </DropdownMenuItem>
                                    
                                    <!-- Twitter/X -->
                                    <DropdownMenuItem @click="shareOnTwitter" class="gap-2 cursor-pointer">
                                        <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 256 256"><rect width="256" height="256" fill="none"/><polygon points="48 40 96 40 208 216 160 216 48 40" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="16"/><line x1="113.88" y1="143.53" x2="48" y2="216" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="16"/><line x1="208" y1="40" x2="142.12" y2="112.47" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="16"/></svg>
                                        Twitter
                                    </DropdownMenuItem>
                                </DropdownMenuContent>
                            </DropdownMenu>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>
    </header>
</template>