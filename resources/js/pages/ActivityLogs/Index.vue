<script setup lang="ts">
import Avatar from '@/components/ui/avatar/Avatar.vue';
import AvatarFallback from '@/components/ui/avatar/AvatarFallback.vue';
import AvatarImage from '@/components/ui/avatar/AvatarImage.vue';
import Button from '@/components/ui/button/Button.vue';
import Input from '@/components/ui/input/Input.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import dayjs from 'dayjs';
import utc from 'dayjs/plugin/utc';
import timezone from 'dayjs/plugin/timezone';
import calendar from 'dayjs/plugin/calendar';
import { ChevronLeft, ChevronRight, ChevronsLeft, ChevronsRight, X } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';
import { useInitials } from '@/composables/useInitials';
import Select from '@/components/ui/select/Select.vue';
import SelectTrigger from '@/components/ui/select/SelectTrigger.vue';
import SelectValue from '@/components/ui/select/SelectValue.vue';
import SelectContent from '@/components/ui/select/SelectContent.vue';
import SelectItem from '@/components/ui/select/SelectItem.vue';

dayjs.extend(utc);
dayjs.extend(timezone);
dayjs.extend(calendar);

const formatTime = (utcTime: string) => {
    return dayjs.utc(utcTime).tz(dayjs.tz.guess()).calendar(null, {sameElse: 'MMMM D, YYYY [at] HH:mm'})
}

const page = usePage()
const activities = ref(page.props.activities)
const perPage = ref(String(page.props.perPage || 20))
const search = ref(page.props.search || '')
const isLoading = ref(true)

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Activity Logs',
        href: '/activity-logs',
    },
];

const fetchData = () => {
    isLoading.value = true

    router.get(route('activity-logs.index'), {
        search: search.value,
        perPage: perPage.value,
        page: activities.value.current_page,
    }, {
        preserveScroll: true,
        preserveState: true,
        only: ['activities'],
        onSuccess: () => {
            activities.value = usePage().props.activities
        },
        onFinish: () => {
            isLoading.value = false // stop loading
        }
    })
}

watch([search, perPage], () => {
    activities.value.current_page = 1;
    fetchData();
})

const { getInitials } = useInitials();

const resetFilters = () => {
    search.value = '';
};

const isFirstPage = computed(() => activities.value.current_page === 1)
const isLastPage = computed(() => activities.value.current_page === activities.value.last_page)
</script>

<template>
    <Head title="Activity Logs" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4 overflow-x-auto">
            <template v-if="activities.length === 0">
                <div class="text-sm text-muted-foreground text-center py-6">
                    No activity logs available.
                </div>
            </template>

            <template v-else>
                <div class="space-y-2 md:space-y-0 md:flex md:items-center md:justify-start md:gap-2">
                    <Input
                        v-model="search"
                        id="search"
                        placeholder="Search activities..."
                        class="text-sm md:max-w-[250px]"
                    />
                    <Button
                        v-if="search !== ''"
                        variant="ghost"
                        @click="resetFilters"
                        :disabled="search === ''"
                        class="w-full border md:border-0 md:w-auto"
                    >
                        Reset
                        <X/>
                    </Button>
                </div>

                <ul class="space-y-2.5 overflow-y-auto">
                    <li v-for="(activity, index) in activities.data" :key="activity.id" class="flex items-start gap-4 p-4 border rounded-md hover:bg-muted/50" v-if="activities.data.length">
                        <div class="h-10 w-10 shrink-0">
                            <Avatar class="overflow-hidden rounded-lg h-full w-full text-sm">
                                <AvatarImage
                                    v-if="activity.causer?.avatar"
                                    :src="activity.causer.avatar"
                                    :alt="activity.causer.name"
                                />
                                <AvatarFallback class="rounded-lg text-black dark:text-white">
                                    {{ getInitials(activity.causer?.name ?? 'System') }}
                                </AvatarFallback>
                            </Avatar>
                        </div>
                        <div class="pt-0 space-y-1">
                            <!-- <p>{{ index + 1 }} - {{ activity.id }}</p> -->
                            <p class="text-sm" v-html="activity.description"></p>
                            <p class="text-xs text-muted-foreground">
                                {{ formatTime(activity.created_at) }}
                            </p>
                        </div>
                    </li>
                    <li v-else class="text-center text-muted-foreground px-4 py-6 border rounded-md hover:bg-muted/50 text-sm">
                        No activities found.
                    </li>
                </ul>

                <div v-if="activities.data.length" class="px-2 mt-auto pt-1 flex flex-wrap items-center md:justify-end md:gap-4 lg:gap-10">
                    <div class="basis-full text-sm text-muted-foreground shrink-0 mb-4 md:mb-0 md:basis-auto md:me-auto md:order-1">
                        Showing {{ activities.from }} to {{ activities.to }} of {{ activities.total }} records.
                    </div>
                    <div class="basis-1/3 text-sm font-medium shrink-0 md:basis-auto md:order-3">
                        Page {{ activities.current_page }} of {{ activities.last_page }}
                    </div>
                    <div class="basis-2/3 flex items-center justify-end gap-2 md:basis-auto md:order-4">
                        <!-- Active button -->
                        <Link v-if="!isFirstPage" :href="activities.first_page_url">
                            <Button variant="outline" size="icon">
                                <ChevronsLeft class="w-4 h-4" />
                            </Button>
                        </Link>

                        <!-- Disabled button -->
                        <Button v-else variant="outline" size="icon" disabled class="bg-muted">
                            <ChevronsLeft class="w-4 h-4 text-muted-foreground/50" />
                        </Button>

                        <!-- Active button -->
                        <Link v-if="activities.prev_page_url" :href="activities.prev_page_url">
                            <Button variant="outline" size="icon">
                                <ChevronLeft class="w-4 h-4" />
                            </Button>
                        </Link>

                        <!-- Disabled button -->
                        <Button v-else variant="outline" size="icon" disabled class="bg-muted">
                            <ChevronLeft class="w-4 h-4 text-muted-foreground/50" />
                        </Button>

                        <!-- Active button -->
                        <Link v-if="activities.next_page_url" :href="activities.next_page_url">
                            <Button variant="outline" size="icon">
                                <ChevronRight class="w-4 h-4" />
                            </Button>
                        </Link>

                        <!-- Disabled button -->
                        <Button v-else variant="outline" size="icon" disabled class="bg-muted">
                            <ChevronRight class="w-4 h-4 text-muted-foreground/50" />
                        </Button>

                        <!-- Active button -->
                        <Link v-if="!isLastPage" :href="activities.last_page_url">
                            <Button variant="outline" size="icon">
                                <ChevronsRight class="w-4 h-4" />
                            </Button>
                        </Link>

                        <!-- Disabled button -->
                        <Button v-else variant="outline" size="icon" disabled class="bg-muted">
                            <ChevronsRight class="w-4 h-4 text-muted-foreground/50" />
                        </Button>
                    </div>
                    <div class="basis-full text-sm font-medium flex items-center justify-between gap-2 shrink-0 mt-4 md:mt-0 md:basis-auto md:order-2">
                        <span>Rows per page</span>
                        <Select v-model="perPage">
                            <SelectTrigger class="w-[70px]">
                                <SelectValue placeholder="Select..."/>
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="10">10</SelectItem>
                                <SelectItem value="20">20</SelectItem>
                                <SelectItem value="30">30</SelectItem>
                                <SelectItem value="40">40</SelectItem>
                                <SelectItem value="50">50</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                </div>
            </template>
        </div>
    </AppLayout>
</template>
