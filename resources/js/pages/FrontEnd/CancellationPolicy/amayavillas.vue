<script setup lang="ts">
import FrontEndLayout from '@/layouts/FrontEnd/amayavillas.vue';
import { usePage } from '@inertiajs/vue3';

// Access Inertia props
const page = usePage()
</script>

<template>
    <FrontEndLayout
        :title="'Cancellation Policy'"
        :slug="page.props.slug"
        :name="page.props.name"
        :theme="page.props.theme"
    >
        <section class="bg-zinc-50 py-10 md:py-16 lg:py-24">
            <div class="mx-auto max-w-screen-xl px-6 md:px-10 lg:px-16">
                <div class="max-w-xl">
                    <h2 class="uppercase font-medium text-xs tracking-widest lg:text-sm">
                        Effective Date: November 22, 2024
                    </h2>
                    <h1 class="font-medium text-xl mt-4 md:text-2xl lg:text-3xl">
                        Amaya Villas Cancellation Policy
                    </h1>
                    <div class="mt-4 space-y-4 lg:mt-6">
                        <p>
                            At Amaya Villas, we understand that plans may shift. To ensure a smooth experience for our guests and team, we’ve outlined our cancellation and refund policy below.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <section class="py-8 md:py-12 lg:py-16">
            <div class="mx-auto max-w-screen-xl divide-y px-6 md:px-10 lg:px-16">
                <div class="pb-8 lg:pb-10">
                    <h1 class="text-lg font-medium md:text-xl">
                        Cancelling Your Stay at Amaya Villas
                    </h1>
                    <p class="mt-4 md:mt-6">
                        If you need to cancel your reservation, please follow these simple steps:
                    </p>
                    <ul class="list-decimal ms-4 space-y-4 mt-6">
                        <li>
                            <span class="block font-medium">Get in Touch</span>
                            <p class="mt-1.5">
                                Contact our guest services team via WhatsApp or email. <a class="font-medium underline" href="/contact">Click here</a> for our contact details. We're happy to assist you promptly.
                            </p>
                        </li>
                        <li>
                            <span class="block font-medium">Share Booking Details</span>
                            <p class="mt-1.5">
                                Please provide your booking confirmation number or guest name so we can locate your reservation quickly.
                            </p>
                        </li>
                        <li>
                            <span class="block font-medium">Receive Confirmation</span>
                            <p class="mt-1.5">
                                We'll guide you through the final steps and confirm the cancellation of your booking.
                            </p>
                        </li>
                    </ul>
                    <p class="text-sm text-muted-foreground font-light mt-8">
                        <span class="font-medium text-foreground">Note:</span> We understand that unexpected events happen. Please contact us as early as possible to avoid any cancellation fees. Cancellations made too close to the check-in date may be subject to charges.
                    </p>
                </div>

                <div class="py-8 lg:py-10">
                    <h1 class="text-lg font-medium md:text-xl">
                        Refund Policy
                    </h1>
                    <p class="mt-4 md:mt-6">
                        Our goal is to be transparent and fair in all refund-related matters.
                    </p>
                    <ul class="list-disc mx-6 space-y-4 mt-6">
                        <li>
                            <span class="block font-medium">Cancellations Within the Allowed Window</span>
                            <p class="mt-1.5">
                                If your cancellation is made within the permitted timeframe (e.g., 7 days before arrival), you may be eligible for a full refund. Please reach out for specifics based on your booking.
                            </p>
                        </li>
                        <li>
                            <span class="block font-medium">Other Cases</span>
                            <p class="mt-1.5">
                                Cancellations made outside the standard timeframe will be reviewed on a case-by-case basis. We aim to find a solution that’s fair to all parties.
                            </p>
                        </li>
                    </ul>
                    <p class="text-sm text-muted-foreground font-light mt-8">
                        <span class="font-medium text-foreground">Note:</span> If a refund is approved, it will be processed promptly and returned to your original payment method.
                    </p>
                </div>

                <div class="py-8 lg:py-10">
                    <h1 class="text-lg font-medium md:text-xl">
                        Exceptions
                    </h1>
                    <p class="mt-4 md:mt-6">
                        While we aim to be flexible, the following conditions may apply:
                    </p>
                    <ul class="list-disc mx-6 space-y-4 mt-6">
                        <li>
                            <span class="block font-medium">Last-Minute Cancellations</span>
                            <p class="mt-1.5">
                                Cancellations made very close to your scheduled arrival may incur a cancellation fee.
                            </p>
                        </li>
                        <li>
                            <span class="block font-medium">No-Shows</span>
                            <p class="mt-1.5">
                                Guests who do not show up for their stay without prior notice may be charged the full amount.
                            </p>
                        </li>
                    </ul>
                </div>

                <div class="py-8 lg:py-10">
                    <h1 class="text-lg font-medium md:text-xl">
                        Need Assistance?
                    </h1>
                    <p class="mt-4 md:mt-6">
                        We're here to help. If you have questions or need support with your booking, <a class="font-medium underline" href="/contact">contact us</a> and our team will gladly assist.
                    </p>
                </div>

                <div class="pt-8 lg:pt-10">
                    <h1 class="text-lg font-medium md:text-xl">
                        Policy Updates
                    </h1>
                    <p class="mt-4 md:mt-6">
                        Amaya Villas reserves the right to revise this policy at any time. Guests will be notified of any significant updates via email or through our website.
                    </p>
                </div>
            </div>
        </section>
    </FrontEndLayout>
</template>