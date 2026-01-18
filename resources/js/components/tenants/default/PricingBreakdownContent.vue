<script setup lang="ts">
// Pricing breakdown content component (dummy data)
// This component is reused in both mobile drawer and desktop static expansion
</script>

<template>
    <div class="space-y-4">
        <div class="flex justify-between items-center pb-3 border-b">
            <div>
                <p class="font-medium">Base Rate</p>
                <p class="text-sm text-muted-foreground">IDR 150,000 x 3 nights</p>
            </div>
            <p class="font-medium">IDR 450,000</p>
        </div>

        <div class="flex justify-between items-center pb-3 border-b">
            <div>
                <p class="font-medium">Cleaning Fee</p>
                <p class="text-sm text-muted-foreground">One-time charge</p>
            </div>
            <p class="font-medium">IDR 50,000</p>
        </div>

        <div class="flex justify-between items-center pb-3 border-b">
            <div>
                <p class="font-medium">Service Fee</p>
                <p class="text-sm text-muted-foreground">Platform fee</p>
            </div>
            <p class="font-medium">IDR 25,000</p>
        </div>

        <div class="flex justify-between items-center pb-3 border-b">
            <div>
                <p class="font-medium">Tax (11%)</p>
                <p class="text-sm text-muted-foreground">Government tax</p>
            </div>
            <p class="font-medium">IDR 57,750</p>
        </div>

        <div class="flex justify-between items-center pt-2">
            <p class="text-lg font-semibold">Total</p>
            <p class="text-lg font-semibold">IDR 582,750</p>
        </div>

        <div class="mt-6 p-4 bg-muted rounded-lg">
            <p class="text-sm font-medium mb-2">Booking Terms</p>
            <ul class="text-sm text-muted-foreground space-y-1">
                <li>• Free cancellation before Feb 14, 2026</li>
                <li>• Full refund if cancelled 48 hours before check-in</li>
                <li>• No refund for cancellations within 48 hours</li>
            </ul>
        </div>
    </div>
</template>
