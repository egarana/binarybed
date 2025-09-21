<script setup lang="ts">
const services = [
    {
        name: "Clients don't need to pay upfront",
        commission: "Clients can start using the service without paying anything. They only pay when they get actual results.",
        oneTime: "Clients must pay a large amount upfront, before seeing any real results, a big barrier for many.",
    },
    {
        name: "We only win when the client wins",
        commission: "We're motivated to help them succeed because we only earn money when their property gets bookings.",
        oneTime: "Once the site is paid for, our incentive to support performance usually ends.",
    },
    {
        name: "We keep ownership of the website",
        commission: "We retain full control, so we can ensure the design, speed, and user experience stay at a high standard.",
        oneTime: "The client fully owns the website. Sometimes they request layout or structural changes that can break its core function.",
    },
    {
        name: "We can filter out unreasonable requests",
        commission: "We can decline or guide client requests if they don't make sense or affect performance.",
        oneTime: "Many clients don't understand how websites work, yet demand changes that are unreasonable, irrelevant, or even harmful.",
        error: "They often don't want to pay extra, even though web development is expensive and time-consuming."
    },
    {
        name: "We maintain and improve the website continuously",
        commission: "We keep it up-to-date, optimized, and error-free, because better performance = more bookings = shared success.",
        oneTime: "Once it's delivered, clients are on their own. Yet, they often still expect free fixes later on.",
    },
    {
        name: "We earn recurring revenue",
        commission: "Each reservation gives us income. One website can keep generating revenue for months or even years.",
        oneTime: "It's a one-time payment. To earn more, we must constantly look for new projects.",
    },
    {
        name: "Faster, scalable implementation across multiple clients",
        commission: "We use a proven system that can easily be rolled out to many properties.",
        oneTime: "Every project starts from scratch — new design, new features, more development time.",
    },
    {
        name: "Clients are more loyal",
        commission: "With no upfront cost and ongoing support, clients stay longer and rarely switch providers.",
        oneTime: "After the website is delivered, most clients stop working with us. It's a short-term relationship.",
    },
    {
        name: "We can manage client expectations more easily",
        commission: "Since we control the product, we can set clear limits and guide them toward results-focused decisions.",
        oneTime: "Clients feel \“entitled\” to request anything, even if it hurts performance or is completely outside the original scope.",
    },
    {
        name: "Matches modern digital business models",
        commission: "It aligns with successful digital trends like marketplaces, booking engines, and SaaS platforms.",
        oneTime: "It feels outdated, and doesn't support long-term client collaboration or revenue growth.",
    },
];
</script>

<template>
    <div class="bg-zinc-100">
        <div class="max-w-screen-sm mx-auto space-y-8 py-10 bg-white px-10">
            <div>
                <h1 class="text-lg font-semibold">
                    Why our flagship product is the commission-based model (pay per reservation)
                </h1>
                <p class="mt-2">
                    We offer several types of services. But we've chosen the commission model as our flagship product because it's safer, more profitable, and more sustainable both for us and for our clients.
                </p>
            </div>
            <div v-for="(service, index) in services" class="text-sm">
                <h1 class="font-medium text-base">{{index + 1}}. {{ service.name }}</h1>
                <div class="bg-green-50 border border-green-700/30 rounded-md px-3 py-2 mt-3">
                    <div class="font-medium text-green-800">Commission:</div>
                    <p class="text-green-900/70">
                        {{ service.commission }}
                    </p>
                </div>
                <div class="bg-red-50 border border-red-700/30 rounded-md px-3 py-2 mt-3">
                    <div class="font-medium text-red-800/70">One-Time Purchase:</div>
                    <p class="text-red-900/70">
                        {{ service.oneTime }}
                    </p>
                    <div v-if="service.error" class="text-white text-xs bg-red-700/70 px-3 py-2 mt-2 rounded-sm">
                        <h1><span class="font-semibold">What's worse:</span> {{ service.error }}</h1>
                    </div>
                </div>
            </div>
            <div>
                <h1 class="text-lg font-semibold">Summary:</h1>
                <h2 class="font-medium text-base mt-5">With the <span class="font-semibold text-green-700">commission-based</span> model, we:</h2>
                <ol class="list-disc list-inside text-sm pt-2 space-y-1">
                    <li>Onboard clients faster and easier</li>
                    <li>Maintain full control over quality and structure</li>
                    <li>Generate steady, recurring income</li>
                    <li>And avoid endless, unrealistic client demands</li>
                </ol>
                <h2 class="font-medium text-base mt-6">The <span class="font-semibold text-red-700">one-time purchase</span> model, on the other hand:</h2>
                <ol class="list-disc list-inside text-sm pt-2 space-y-1">
                    <li>Comes with more risk and less reward</li>
                    <li>Creates difficult client relationships</li>
                    <li>And drains our time and energy without long-term benefits</li>
                </ol>
            </div>
        </div>
    </div>
</template>