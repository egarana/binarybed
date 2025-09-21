<script setup lang="ts">
import Button from '@/components/ui/button/Button.vue';
import Table from '@/components/ui/table/Table.vue';
import TableBody from '@/components/ui/table/TableBody.vue';
import TableCell from '@/components/ui/table/TableCell.vue';
import TableHead from '@/components/ui/table/TableHead.vue';
import TableHeader from '@/components/ui/table/TableHeader.vue';
import TableRow from '@/components/ui/table/TableRow.vue';
import { AlertCircle, Check, X } from 'lucide-vue-next';

const services = [
    {
        "feature": "No upfront cost",
        "commission_based": true,
        "one_time_payment": false
    },
    {
        "feature": "Pay only when results/bookings come",
        "commission_based": true,
        "one_time_payment": false
    },
    {
        "feature": "Aligned incentives (we win when you win)",
        "commission_based": true,
        "one_time_payment": false
    },
    {
        "feature": "Long-term support & maintenance",
        "commission_based": true,
        "one_time_payment": false
    },
    {
        "feature": "Continuous optimization & updates",
        "commission_based": true,
        "one_time_payment": false
    },
    {
        "feature": "Recurring revenue for both sides",
        "commission_based": true,
        "one_time_payment": false
    },
    {
        "feature": "Faster rollout with proven system",
        "commission_based": true,
        "one_time_payment": false
    },
    {
        "feature": "Client loyalty & partnership",
        "commission_based": true,
        "one_time_payment": false
    },
    {
        "feature": "We manage requests & protect performance",
        "commission_based": true,
        "one_time_payment": false
    },
    {
        "feature": "Integrated reservation & booking engine",
        "commission_based": true,
        "one_time_payment": false
    },
    {
        "feature": "Full website ownership",
        "commission_based": false,
        "one_time_payment": true
    },
    {
        "feature": "Freedom to modify without vendor",
        "commission_based": false,
        "one_time_payment": true
    },
    {
        "feature": "One-time payment, no ongoing cost",
        "commission_based": false,
        "one_time_payment": true
    }
];
</script>

<template>
    <section class="bg-zinc-100">
        <div class="mx-auto w-[794px] h-[1123px] bg-white py-10 px-12 text-zinc-700 space-y-6 text-sm flex flex-col">
            <div class="flex items-center justify-between">
                <div class="w-44">
                    <svg class="w-full h-auto" xml:space="preserve" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 389.702 76.923"><g paint-order="fill markers stroke"><g transform="matrix(2.3382 0 0 2.3382 -195.492 -165.644)"><rect width="31.221" height="31.221" x="83.608" y="70.843" rx="4.762" ry="4.762" fill="#ec131c"/><g transform="translate(-24.741 -2.813)" fill="#fff"><rect width="2.91" height="11.906" x="118.933" y="88.262" rx="1.455" ry="1.488"/><g transform="translate(-.688)"><rect width="2.91" height="11.906" x="92.759" y="-135.549" rx="1.455" ry="1.488" transform="rotate(90)"/><rect width="2.91" height="11.906" x="97.257" y="-135.549" rx="1.455" ry="1.488" transform="rotate(90)"/><rect width="2.91" height="7.355" x="123.643" y="92.812" rx="1.455" ry="1.455"/><rect width="2.91" height="7.355" x="132.638" y="92.812" rx="1.455" ry="1.455"/></g></g></g><g fill="#484848" font-weight="600" font-size="56" font-family="Poppins" letter-spacing="-2.8" text-anchor="middle"><path d="M62.675 138.154q.4-.593 1.096-.963.712-.37 1.615-.37 1.052 0 1.897.518.86.519 1.348 1.482.504.948.504 2.207 0 1.26-.504 2.238-.489.963-1.348 1.496-.845.534-1.897.534-.918 0-1.615-.356-.681-.37-1.096-.948v1.17h-2.074v-10.964h2.074zm4.341 2.874q0-.74-.31-1.274-.297-.548-.8-.83-.49-.281-1.068-.281-.563 0-1.067.296-.488.282-.8.83-.296.548-.296 1.29 0 .74.296 1.288.312.548.8.845.504.281 1.067.281.578 0 1.067-.296.504-.296.8-.845.311-.548.311-1.304zM70.958 135.976q-.549 0-.92-.34-.355-.356-.355-.875t.356-.86q.37-.355.919-.355.548 0 .903.356.37.34.37.859 0 .519-.37.874-.355.341-.903.341zm1.022.978v8.208h-2.074v-8.208zM77.832 136.835q1.467 0 2.371.934.904.918.904 2.578v4.815h-2.074v-4.534q0-.977-.49-1.496-.488-.533-1.333-.533-.86 0-1.363.533-.489.519-.489 1.496v4.534h-2.074v-8.208h2.074v1.022q.415-.533 1.052-.83.652-.31 1.422-.31zM81.803 141.028q0-1.244.49-2.207.503-.963 1.348-1.482.859-.518 1.91-.518.92 0 1.601.37.697.37 1.111.933v-1.17h2.09v8.208h-2.09v-1.2q-.4.578-1.11.963-.697.37-1.616.37-1.037 0-1.896-.533-.845-.533-1.349-1.496-.489-.978-.489-2.238zm6.46.03q0-.756-.296-1.289-.296-.548-.8-.83-.504-.296-1.082-.296-.578 0-1.066.281-.49.282-.8.83-.297.534-.297 1.274 0 .741.296 1.304.312.549.8.845.504.296 1.067.296.578 0 1.082-.281.504-.297.8-.83.296-.548.296-1.304zM93.716 138.228q.4-.652 1.037-1.022.652-.37 1.482-.37v2.177h-.548q-.978 0-1.482.46-.49.459-.49 1.6v4.09h-2.073v-8.21h2.074zM96.931 136.954v4.075c0 .84.163 1.585.489 2.237.336.642.79 1.14 1.363 1.496a3.525 3.525 0 0 0 1.897.534c.602 0 1.136-.129 1.6-.385.474-.257.844-.578 1.111-.964v1.275c0 .73-.188 1.274-.563 1.63-.375.365-.864.548-1.467.548-.513 0-.953-.114-1.318-.341a1.51 1.51 0 0 1-.697-.89h-2.06c.1.94.52 1.68 1.26 2.223.75.544 1.71.815 2.875.815.869 0 1.61-.177 2.222-.533a3.355 3.355 0 0 0 1.378-1.423c.306-.592.46-1.269.46-2.03v-8.267h-2.09v4.104c0 .504-.098.938-.296 1.304a2.157 2.157 0 0 1-.8.83 2.182 2.182 0 0 1-1.082.281c-.375 0-.73-.099-1.067-.296a2.275 2.275 0 0 1-.8-.845c-.194-.367-.292-.792-.296-1.274v-4.104zM108.844 138.154q.4-.593 1.096-.963.711-.37 1.615-.37 1.052 0 1.897.518.86.519 1.348 1.482.504.948.504 2.207 0 1.26-.504 2.238-.489.963-1.348 1.496-.845.534-1.897.534-.918 0-1.615-.356-.681-.37-1.096-.948v1.17h-2.075v-10.964h2.075zm4.341 2.874q0-.74-.311-1.274-.296-.548-.8-.83-.49-.281-1.067-.281-.563 0-1.067.296-.489.282-.8.83-.296.548-.296 1.29 0 .74.296 1.288.311.548.8.845.504.281 1.067.281.578 0 1.067-.296.504-.296.8-.845.311-.548.311-1.304zM124.164 140.88q0 .445-.059.8h-6q.073.89.622 1.393.548.504 1.348.504 1.156 0 1.644-.993h2.238q-.356 1.186-1.363 1.956-1.008.756-2.475.756-1.185 0-2.133-.519-.934-.533-1.467-1.496-.519-.963-.519-2.223 0-1.274.519-2.237.518-.963 1.452-1.482.933-.518 2.148-.518 1.17 0 2.09.503.933.504 1.437 1.438.518.918.518 2.118zm-2.148-.592q-.015-.8-.578-1.275-.563-.489-1.378-.489-.77 0-1.304.475-.518.459-.637 1.289zM124.86 141.028q0-1.244.49-2.207.503-.963 1.363-1.482.86-.518 1.911-.518.8 0 1.526.355.726.34 1.156.919v-3.897h2.104v10.964h-2.104v-1.215q-.385.608-1.082.978-.696.37-1.615.37-1.037 0-1.896-.533-.86-.533-1.363-1.496-.49-.978-.49-2.238zm6.46.03q0-.756-.296-1.289-.296-.548-.8-.83-.503-.296-1.081-.296t-1.067.281q-.49.282-.8.83-.297.534-.297 1.274 0 .741.297 1.304.31.549.8.845.504.296 1.067.296.578 0 1.081-.281.504-.297.8-.83.297-.548.297-1.304z" style="-inkscape-font-specification:&quot;Poppins Semi-Bold&quot;;text-align:center;white-space:pre" transform="matrix(4.1025 0 0 4.1025 -157.616 -535.205)"/></g></g></svg>
                </div>
                <div class="text-right italic font-light text-zinc-400">
                    Our Service Models
                </div>
            </div>
            <div class="mt-10">
                <p>
                    At <span class="font-semibold">BinaryBed</span>, we understand that every accommodation business has different needs and preferences. That’s why we offer two flexible service models to help you generate more direct bookings from your official website.
                </p>
                <p class="mt-4">
                    We’ve designed our models to give you the freedom to choose what works best for your property.
                </p>
            </div>
            <div class="pt-4">
                <h1 class="font-medium text-base text-zinc-800">Option 1: Commission-Based Model</h1>
                <p class="italic text-sm mt-1">
                    Perfect for hotels and villas that want to grow bookings without upfront investment.
                </p>
                <p class="mt-5">
                    With this model, we take on the responsibility of building, maintaining, and optimizing your website. You don’t need to pay anything upfront, we only earn a commission (starting at 5% of each booking price, negotiable) when you receive actual confirmed bookings.
                </p>
                <p class="mt-4">
                    This keeps our goals perfectly aligned: we only succeed when you succeed. Plus, you’ll enjoy ongoing support, continuous updates, and performance improvements to ensure your website stays fast, user-friendly, and effective.
                </p>

                <h1 class="mt-4 font-medium">Highlights:</h1>
                <ul class="mt-4 space-y-1.5">
                    <li class="flex items-center gap-2">
                        <Check class="w-4 h-4 text-green-600"/>
                        No upfront cost
                    </li>
                    <li class="flex items-center gap-2">
                        <Check class="w-4 h-4 text-green-600"/>
                        Pay only when results/bookings come in
                    </li>
                    <li class="flex items-center gap-2">
                        <Check class="w-4 h-4 text-green-600"/>
                        Long-term support & maintenance included
                    </li>
                    <li class="flex items-center gap-2">
                        <Check class="w-4 h-4 text-green-600"/>
                        Continuous optimization for higher conversions
                    </li>
                    <li class="flex items-center gap-2">
                        <Check class="w-4 h-4 text-green-600"/>
                        Aligned success — we win only when you win
                    </li>
                </ul>
            </div>
            <div class="pt-3">
                <h1 class="font-medium text-base text-zinc-800">Option 2: One-Time Payment Model</h1>
                <p class="italic text-sm mt-1">
                    Ideal for accommodations that prefer full ownership and a one-off investment.
                </p>
                <p class="mt-5">
                    With this model, you make a one-time payment for the design and development of your website. You will fully own the site and have the freedom to modify it without relying on us.
                </p>
                <p class="mt-4">
                    However, support, updates, and fixes are not included long-term. If you want changes or improvements after delivery, they would require additional costs.
                </p>
                <h1 class="mt-4 font-medium">Highlights:</h1>
                <ul class="mt-4 space-y-1.5">
                    <li class="flex items-center gap-2">
                        <Check class="w-4 h-4 text-green-600"/>
                        Full ownership of the website
                    </li>
                    <li class="flex items-center gap-2">
                        <Check class="w-4 h-4 text-green-600"/>
                        Freedom to modify without vendor
                    </li>
                    <li class="flex items-center gap-2">
                        <Check class="w-4 h-4 text-green-600"/>
                        One-time payment with no ongoing costs
                    </li>
                    <li class="flex items-center gap-2">
                        <AlertCircle class="w-4 h-4 text-yellow-500"/>
                        Limited support & maintenance after delivery
                    </li>
                    <li class="flex items-center gap-2">
                        <AlertCircle class="w-4 h-4 text-yellow-500"/>
                        Updates and fixes require additional payment
                    </li>
                </ul>
            </div>
            <div class="mt-auto">
                <p class="text-right text-xs text-zinc-400">Page 1 of 2</p>
            </div>
        </div>

        <div class="mx-auto w-[794px] h-[1123px] bg-white py-10 px-12 text-zinc-700 space-y-6 text-sm flex flex-col mt-14">
            <div class="flex items-center justify-between">
                <div class="w-44">
                    <svg class="w-full h-auto" xml:space="preserve" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 389.702 76.923"><g paint-order="fill markers stroke"><g transform="matrix(2.3382 0 0 2.3382 -195.492 -165.644)"><rect width="31.221" height="31.221" x="83.608" y="70.843" rx="4.762" ry="4.762" fill="#ec131c"/><g transform="translate(-24.741 -2.813)" fill="#fff"><rect width="2.91" height="11.906" x="118.933" y="88.262" rx="1.455" ry="1.488"/><g transform="translate(-.688)"><rect width="2.91" height="11.906" x="92.759" y="-135.549" rx="1.455" ry="1.488" transform="rotate(90)"/><rect width="2.91" height="11.906" x="97.257" y="-135.549" rx="1.455" ry="1.488" transform="rotate(90)"/><rect width="2.91" height="7.355" x="123.643" y="92.812" rx="1.455" ry="1.455"/><rect width="2.91" height="7.355" x="132.638" y="92.812" rx="1.455" ry="1.455"/></g></g></g><g fill="#484848" font-weight="600" font-size="56" font-family="Poppins" letter-spacing="-2.8" text-anchor="middle"><path d="M62.675 138.154q.4-.593 1.096-.963.712-.37 1.615-.37 1.052 0 1.897.518.86.519 1.348 1.482.504.948.504 2.207 0 1.26-.504 2.238-.489.963-1.348 1.496-.845.534-1.897.534-.918 0-1.615-.356-.681-.37-1.096-.948v1.17h-2.074v-10.964h2.074zm4.341 2.874q0-.74-.31-1.274-.297-.548-.8-.83-.49-.281-1.068-.281-.563 0-1.067.296-.488.282-.8.83-.296.548-.296 1.29 0 .74.296 1.288.312.548.8.845.504.281 1.067.281.578 0 1.067-.296.504-.296.8-.845.311-.548.311-1.304zM70.958 135.976q-.549 0-.92-.34-.355-.356-.355-.875t.356-.86q.37-.355.919-.355.548 0 .903.356.37.34.37.859 0 .519-.37.874-.355.341-.903.341zm1.022.978v8.208h-2.074v-8.208zM77.832 136.835q1.467 0 2.371.934.904.918.904 2.578v4.815h-2.074v-4.534q0-.977-.49-1.496-.488-.533-1.333-.533-.86 0-1.363.533-.489.519-.489 1.496v4.534h-2.074v-8.208h2.074v1.022q.415-.533 1.052-.83.652-.31 1.422-.31zM81.803 141.028q0-1.244.49-2.207.503-.963 1.348-1.482.859-.518 1.91-.518.92 0 1.601.37.697.37 1.111.933v-1.17h2.09v8.208h-2.09v-1.2q-.4.578-1.11.963-.697.37-1.616.37-1.037 0-1.896-.533-.845-.533-1.349-1.496-.489-.978-.489-2.238zm6.46.03q0-.756-.296-1.289-.296-.548-.8-.83-.504-.296-1.082-.296-.578 0-1.066.281-.49.282-.8.83-.297.534-.297 1.274 0 .741.296 1.304.312.549.8.845.504.296 1.067.296.578 0 1.082-.281.504-.297.8-.83.296-.548.296-1.304zM93.716 138.228q.4-.652 1.037-1.022.652-.37 1.482-.37v2.177h-.548q-.978 0-1.482.46-.49.459-.49 1.6v4.09h-2.073v-8.21h2.074zM96.931 136.954v4.075c0 .84.163 1.585.489 2.237.336.642.79 1.14 1.363 1.496a3.525 3.525 0 0 0 1.897.534c.602 0 1.136-.129 1.6-.385.474-.257.844-.578 1.111-.964v1.275c0 .73-.188 1.274-.563 1.63-.375.365-.864.548-1.467.548-.513 0-.953-.114-1.318-.341a1.51 1.51 0 0 1-.697-.89h-2.06c.1.94.52 1.68 1.26 2.223.75.544 1.71.815 2.875.815.869 0 1.61-.177 2.222-.533a3.355 3.355 0 0 0 1.378-1.423c.306-.592.46-1.269.46-2.03v-8.267h-2.09v4.104c0 .504-.098.938-.296 1.304a2.157 2.157 0 0 1-.8.83 2.182 2.182 0 0 1-1.082.281c-.375 0-.73-.099-1.067-.296a2.275 2.275 0 0 1-.8-.845c-.194-.367-.292-.792-.296-1.274v-4.104zM108.844 138.154q.4-.593 1.096-.963.711-.37 1.615-.37 1.052 0 1.897.518.86.519 1.348 1.482.504.948.504 2.207 0 1.26-.504 2.238-.489.963-1.348 1.496-.845.534-1.897.534-.918 0-1.615-.356-.681-.37-1.096-.948v1.17h-2.075v-10.964h2.075zm4.341 2.874q0-.74-.311-1.274-.296-.548-.8-.83-.49-.281-1.067-.281-.563 0-1.067.296-.489.282-.8.83-.296.548-.296 1.29 0 .74.296 1.288.311.548.8.845.504.281 1.067.281.578 0 1.067-.296.504-.296.8-.845.311-.548.311-1.304zM124.164 140.88q0 .445-.059.8h-6q.073.89.622 1.393.548.504 1.348.504 1.156 0 1.644-.993h2.238q-.356 1.186-1.363 1.956-1.008.756-2.475.756-1.185 0-2.133-.519-.934-.533-1.467-1.496-.519-.963-.519-2.223 0-1.274.519-2.237.518-.963 1.452-1.482.933-.518 2.148-.518 1.17 0 2.09.503.933.504 1.437 1.438.518.918.518 2.118zm-2.148-.592q-.015-.8-.578-1.275-.563-.489-1.378-.489-.77 0-1.304.475-.518.459-.637 1.289zM124.86 141.028q0-1.244.49-2.207.503-.963 1.363-1.482.86-.518 1.911-.518.8 0 1.526.355.726.34 1.156.919v-3.897h2.104v10.964h-2.104v-1.215q-.385.608-1.082.978-.696.37-1.615.37-1.037 0-1.896-.533-.86-.533-1.363-1.496-.49-.978-.49-2.238zm6.46.03q0-.756-.296-1.289-.296-.548-.8-.83-.503-.296-1.081-.296t-1.067.281q-.49.282-.8.83-.297.534-.297 1.274 0 .741.297 1.304.31.549.8.845.504.296 1.067.296.578 0 1.081-.281.504-.297.8-.83.297-.548.297-1.304z" style="-inkscape-font-specification:&quot;Poppins Semi-Bold&quot;;text-align:center;white-space:pre" transform="matrix(4.1025 0 0 4.1025 -157.616 -535.205)"/></g></g></svg>
                </div>
                <div class="text-right italic font-light text-zinc-400">
                    Side-by-Side Comparison
                </div>
            </div>
            <div class="mt-10">
                <p>
                    To make it easier, we’ve put together a simple comparison between our <span class="font-semibold">Commission-Based Model</span> (our recommended option) and the traditional <span class="font-semibold">One-Time Payment Model</span>.
                </p>
                <p class="mt-4">
                    The table below highlights the key differences, so you can quickly see which approach better fits your needs.
                </p>
            </div>
            <div class="pt-4">
                <div class="overflow-hidden rounded-lg border">
                    <div class="relative w-full overflow-x-auto">
                        <Table>
                            <TableHeader>
                                <TableRow class="bg-muted">
                                    <TableHead>
                                        <Button variant="ghost" size="sm" class="font-normal">
                                            Feature
                                        </Button>
                                    </TableHead>
                                    <TableHead>
                                        <Button variant="ghost" size="sm" class="font-normal">
                                            Commission-Based
                                        </Button>
                                    </TableHead>
                                    <TableHead>
                                        <Button variant="ghost" size="sm" class="font-normal">
                                            One-Time Payment
                                        </Button>
                                    </TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow v-for="service in services">
                                    <TableCell class="ps-5 h-auto py-3.5">{{ service.feature }}</TableCell>
                                    <TableCell class="pe-6 h-auto">
                                        <Check v-if="service.commission_based === true" class="w-4 h-4 text-green-600 mx-auto"/>
                                        <X v-else class="w-4 h-4 text-red-700 mx-auto"/>
                                    </TableCell>
                                    <TableCell class="ps-5 h-auto">
                                        <Check v-if="service.one_time_payment === true" class="w-4 h-4 text-green-600 mx-auto"/>
                                        <X v-else class="w-4 h-4 text-red-700 mx-auto"/>
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </div>
                </div>
            </div>
            <div class="italic p-4">
                <p class="text-xs font-medium text-zinc-500">
                    Note on “Full Website Ownership” & “Freedom to Modify”:
                </p>
                <p class="text-xs text-zinc-500 mt-2 leading-relaxed">
                    With our Commission-Based model, you can still suggest ideas, just discuss with us first. If they’re feasible and improve performance, we’ll add them right away at no extra cost to your website.
                </p>
            </div>
            <div class="mt-auto">
                <p class="text-right text-xs text-zinc-400">Page 2 of 2</p>
            </div>
        </div>
    </section>
</template>