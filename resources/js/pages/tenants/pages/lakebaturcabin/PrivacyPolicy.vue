<script setup lang="ts">
import Layout from './Layout.vue';
import { Link } from '@inertiajs/vue3';

const lastUpdated = 'January 1, 2026';

const sections = [
    { id: 'collect', title: 'Information We Collect' },
    { id: 'use', title: 'How We Use Information' },
    { id: 'sharing', title: 'Information Sharing' },
    { id: 'cookies', title: 'Cookies' },
    { id: 'security', title: 'Data Security' },
    { id: 'rights', title: 'Your Rights' },
    { id: 'contact', title: 'Contact' },
];

const scrollTo = (id: string) => {
    document.getElementById(id)?.scrollIntoView({ behavior: 'smooth' });
};
</script>

<template>
    <Layout title="Privacy Policy">
        <div class="min-h-screen bg-white">

            <!-- Hero -->
            <section class="py-32 border-b border-stone-100">
                <div class="mx-auto max-w-screen-xl px-6">
                    <div class="max-w-2xl">
                        <p class="text-sm uppercase tracking-[0.3em] text-stone-400 mb-6">Legal</p>
                        <h1 class="text-4xl md:text-5xl font-light text-stone-900 mb-6">
                            Privacy <span class="font-serif italic">Policy</span>
                        </h1>
                        <p class="text-lg text-stone-500 font-light">
                            Last updated: {{ lastUpdated }}
                        </p>
                    </div>
                </div>
            </section>

            <!-- Content -->
            <section class="py-32">
                <div class="mx-auto max-w-screen-xl px-6">
                    <div class="lg:flex gap-20">
                        
                        <!-- TOC -->
                        <aside class="hidden lg:block w-64 flex-shrink-0">
                            <div class="sticky top-24">
                                <p class="text-sm uppercase tracking-[0.2em] text-stone-400 mb-6">Contents</p>
                                <nav class="space-y-3">
                                    <button 
                                        v-for="section in sections" 
                                        :key="section.id"
                                        @click="scrollTo(section.id)"
                                        class="block text-sm text-stone-500 hover:text-stone-900 transition-colors text-left"
                                    >
                                        {{ section.title }}
                                    </button>
                                </nav>
                            </div>
                        </aside>

                        <!-- Main -->
                        <div class="flex-1 max-w-2xl">
                            <div class="prose prose-stone prose-lg prose-headings:font-light prose-p:font-light prose-p:text-stone-600 prose-li:text-stone-600 prose-li:font-light space-y-16">
                                
                                <section :id="sections[0].id">
                                    <h2 class="text-xl text-stone-900">{{ sections[0].title }}</h2>
                                    <p>We collect information you provide directly:</p>
                                    <ul>
                                        <li>Name, email, and phone when booking</li>
                                        <li>Payment details (securely processed)</li>
                                        <li>Preferences and special requests</li>
                                        <li>Communications with our team</li>
                                    </ul>
                                </section>

                                <section :id="sections[1].id">
                                    <h2 class="text-xl text-stone-900">{{ sections[1].title }}</h2>
                                    <p>Your information helps us:</p>
                                    <ul>
                                        <li>Process and confirm reservations</li>
                                        <li>Communicate about your stay</li>
                                        <li>Improve our services</li>
                                        <li>Send promotional content (with consent)</li>
                                    </ul>
                                </section>

                                <section :id="sections[2].id">
                                    <h2 class="text-xl text-stone-900">{{ sections[2].title }}</h2>
                                    <p>We do not sell personal information. We may share with:</p>
                                    <ul>
                                        <li>Service providers (payment processors)</li>
                                        <li>Legal authorities when required</li>
                                        <li>Partners only with your consent</li>
                                    </ul>
                                </section>

                                <section :id="sections[3].id">
                                    <h2 class="text-xl text-stone-900">{{ sections[3].title }}</h2>
                                    <p>We use cookies for:</p>
                                    <ul>
                                        <li>Essential website functionality</li>
                                        <li>Analytics to improve our site</li>
                                        <li>Marketing (with consent)</li>
                                    </ul>
                                    <p>Control preferences through your browser settings.</p>
                                </section>

                                <section :id="sections[4].id">
                                    <h2 class="text-xl text-stone-900">{{ sections[4].title }}</h2>
                                    <p>We protect your data through:</p>
                                    <ul>
                                        <li>Encryption in transit and at rest</li>
                                        <li>Regular security assessments</li>
                                        <li>Limited data access policies</li>
                                        <li>Secure payment processing</li>
                                    </ul>
                                </section>

                                <section :id="sections[5].id">
                                    <h2 class="text-xl text-stone-900">{{ sections[5].title }}</h2>
                                    <p>You have the right to:</p>
                                    <ul>
                                        <li>Access your personal information</li>
                                        <li>Request data correction</li>
                                        <li>Request data deletion</li>
                                        <li>Opt-out of marketing</li>
                                    </ul>
                                </section>

                                <section :id="sections[6].id">
                                    <h2 class="text-xl text-stone-900">{{ sections[6].title }}</h2>
                                    <p>For privacy inquiries:</p>
                                    <ul>
                                        <li>Email: privacy@lakebaturcabin.com</li>
                                        <li>WhatsApp: +62 881-0379-90320</li>
                                    </ul>
                                </section>

                            </div>
                        </div>
                    </div>
                </div>
            </section>

        </div>
    </Layout>
</template>
