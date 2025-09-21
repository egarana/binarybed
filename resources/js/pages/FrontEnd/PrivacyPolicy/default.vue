<script setup lang="ts">
import FrontEndLayout from '@/layouts/FrontEnd/default.vue';
import { usePage } from '@inertiajs/vue3';

// Access Inertia props
const page = usePage()
</script>

<template>
    <FrontEndLayout
        :title="'Privacy Policy'"
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
                        Rimba Luna Privacy Policy
                    </h1>
                    <div class="mt-4 space-y-4 lg:mt-6">
                        <p>
                            At Rimba Luna, we are committed to protecting your privacy. This Privacy Policy outlines how we collect, use, and safeguard your personal information when you interact with our services.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <section class="py-8 md:py-12 lg:py-16">
            <div class="mx-auto max-w-screen-xl divide-y px-6 md:px-10 lg:px-16">
                <div class="pb-8 lg:pb-10">
                    <h1 class="text-lg font-medium md:text-xl">
                        Information We Collect
                    </h1>
                    <p class="mt-4 md:mt-6">
                        We may collect the following types of information:
                    </p>
                    <ul class="list-disc mx-6 space-y-4 mt-6">
                        <li>
                            <span class="block font-medium">Personal Information:</span>
                            <p class="mt-1.5">
                                Your name, contact details (such as phone number and email), booking preferences, check-in/check-out details, and any other information you provide when making a reservation or inquiry.
                            </p>
                        </li>
                        <li>
                            <span class="block font-medium">Booking & Stay Information:</span>
                            <p class="mt-1.5">
                                Information related to your reservations, guest preferences, special requests, and service usage during your stay.
                            </p>
                        </li>
                        <li>
                            <span class="block font-medium">Payment Information:</span>
                            <p class="mt-1.5">
                                Your billing details and payment method information (securely processed via our third-party payment partners).
                            </p>
                        </li>
                        <li>
                            <span class="block font-medium">Device & Usage Data:</span>
                            <p class="mt-1.5">
                                Technical details such as your device type, browser, operating system, and how you interact with our website or app.
                            </p>
                        </li>
                    </ul>
                </div>

                <div class="py-8 lg:py-10">
                    <h1 class="text-lg font-medium md:text-xl">
                        How We Use Your Information
                    </h1>
                    <p class="mt-4 md:mt-6">
                        We use your information to:
                    </p>
                    <ul class="list-disc mx-6 space-y-4 mt-6">
                        <li>
                            <span class="block font-medium">Deliver Services:</span>
                            <p class="mt-1.5">
                                Process bookings, fulfill stay requests, personalize your experience, and communicate with you.
                            </p>
                        </li>
                        <li>
                            <span class="block font-medium">Customer Support:</span>
                            <p class="mt-1.5">
                                Respond to your questions, concerns, and help requests efficiently.
                            </p>
                        </li>
                        <li>
                            <span class="block font-medium">Service Improvement:</span>
                            <p class="mt-1.5">
                                Analyze usage trends and feedback to enhance our offerings and guest experience.
                            </p>
                        </li>
                        <li>
                            <span class="block font-medium">Legal Obligations:</span>
                            <p class="mt-1.5">
                                Meet regulatory or legal requirements applicable to our operations.
                            </p>
                        </li>
                    </ul>
                </div>

                <div class="py-8 lg:py-10">
                    <h1 class="text-lg font-medium md:text-xl">
                        Sharing Your Information
                    </h1>
                    <p class="mt-4 md:mt-6">
                        We do not sell your data. However, we may share it with:
                    </p>
                    <ul class="list-disc mx-6 space-y-4 mt-6">
                        <li>
                            <span class="block font-medium">Trusted Staff & Partners:</span>
                            <p class="mt-1.5">
                                Our team and affiliated service providers (e.g., housekeeping, tour partners) who assist in delivering services.
                            </p>
                        </li>
                        <li>
                            <span class="block font-medium">Payment Gateways:</span>
                            <p class="mt-1.5">
                                Authorized third-party platforms that securely process transactions on our behalf.
                            </p>
                        </li>
                        <li>
                            <span class="block font-medium">Legal Authorities:</span>
                            <p class="mt-1.5">
                                When required by law, court order, or governmental request.
                            </p>
                        </li>
                    </ul>
                </div>

                <div class="py-8 lg:py-10">
                    <h1 class="text-lg font-medium md:text-xl">
                        Data Security
                    </h1>
                    <p class="mt-4 md:mt-6">
                        We take appropriate measures to protect your personal data. While we strive to use commercially acceptable means to safeguard your information, no method is 100% secure.
                    </p>
                </div>

                <div class="py-8 lg:py-10">
                    <h1 class="text-lg font-medium md:text-xl">
                        Changes to This Policy
                    </h1>
                    <p class="mt-4 md:mt-6">
                        Rimba Luna may revise this Privacy Policy periodically. Updates will be posted on this page with the updated effective date.
                    </p>
                </div>

                <div class="pt-8 lg:pt-10">
                    <h1 class="text-lg font-medium md:text-xl">
                        Contact Us
                    </h1>
                    <p class="mt-4 md:mt-6">
                        If you have any questions or concerns about this Privacy Policy, please <a class="font-bold underline" href="/contact">contact us</a>.
                    </p>
                </div>
            </div>
        </section>
    </FrontEndLayout>
</template>