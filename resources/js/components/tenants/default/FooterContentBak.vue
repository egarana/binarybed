<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Link } from '@inertiajs/vue3';
import { Facebook, Instagram, ShieldCheck, Youtube } from 'lucide-vue-next';


</script>

<template>
    <div>
        <div class="flex items-center justify-between mx-auto max-w-screen-xl px-6 py-4">
            <ul class="flex items-center gap-3 text-sm">
                <li>
                    <Link href="/cancellation-policy" class="whitespace-nowrap">Cancellation Policy</Link>
                </li>
                <li>
                    <Link href="/privacy-policy" class="whitespace-nowrap">Privacy Policy</Link>
                </li>
                <li>
                    <Link href="/about" class="whitespace-nowrap">About</Link>
                </li>
                <li>
                    <Link href="/faq" class="whitespace-nowrap">F.A.Q</Link>
                </li>
                <li>
                    <Link href="/terms-and-conditions" class="whitespace-nowrap">Terms &amp; Conditions</Link>
                </li>
            </ul>
            <ul class="flex items-center gap-2">
                <li>
                    <Badge variant="outline" class="rounded-full">
                        <ShieldCheck class="text-green-600" />
                        Secure Payment
                    </Badge>
                </li>
                <li>
                    <img src="https://assets.binarybed.com/visa.webp" alt="Visa" class="block w-auto h-3.5">
                </li>
                <li>
                    <img src="https://assets.binarybed.com/master.webp" alt="Visa" class="block w-auto h-3.5">
                </li>
            </ul>
        </div>
    </div>
    <div class="border-t">
        <div class="mx-auto max-w-screen-xl px-6 py-5 flex justify-end items-center">
            <p class="text-sm text-balance text-muted-foreground me-auto">2026 Lake Batur Cabin, Jalan Ulun Danu Songan A Kintamani Bangli Bali 80652 Indonesia</p>
            <ul class="flex items-center gap-5">
                <li>
                    <a href="https://facebook.com" target="_blank" rel="noopener noreferrer" class="text-muted-foreground hover:text-primary">
                        <Facebook class="w-4 h-4" />
                    </a>
                </li>
                <li>
                    <a href="https://instagram.com" target="_blank" rel="noopener noreferrer" class="text-muted-foreground hover:text-primary">
                        <Instagram class="w-4 h-4" />
                    </a>
                </li>
                <li>
                    <a href="https://youtube.com" target="_blank" rel="noopener noreferrer" class="text-muted-foreground hover:text-primary">
                        <Youtube class="w-4 h-4" />
                    </a>
                </li>
            </ul>
        </div>
    </div>
</template>