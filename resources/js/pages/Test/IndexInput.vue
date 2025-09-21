<script setup lang="ts">
import { ref, nextTick, onMounted, onBeforeUnmount } from "vue"

interface CalendarDay {
    date: string
    day: string
    day_number: string
    month: string
    month_full: string
    year: string
    is_weekend: boolean
    is_month_start: boolean
    is_month_end: boolean
    formatted: string
    availability: any[]
    // added fields for editing
    qty?: number
    editing?: boolean
    localValue?: number | string
}

// this would come from props or API
const calendarDays = ref<CalendarDay[]>([
    {
        date: "2025-08-24",
        day: "Sun",
        day_number: "24",
        month: "Aug",
        month_full: "August",
        year: "2025",
        is_weekend: true,
        is_month_start: false,
        is_month_end: false,
        formatted: "Aug 24, 2025",
        availability: [],
    },
    {
        date: "2025-08-25",
        day: "Mon",
        day_number: "25",
        month: "Aug",
        month_full: "August",
        year: "2025",
        is_weekend: false,
        is_month_start: false,
        is_month_end: false,
        formatted: "Aug 25, 2025",
        availability: [],
    },
    {
        date: "2025-08-26",
        day: "Tue",
        day_number: "26",
        month: "Aug",
        month_full: "August",
        year: "2025",
        is_weekend: false,
        is_month_start: false,
        is_month_end: false,
        formatted: "Aug 26, 2025",
        availability: [],
    },
].map((d, idx) => ({
    ...d,
    qty: 0, // default
    editing: false,
    localValue: 0,
})))

const inputRefs = new Map<string, HTMLInputElement>()

const startEditing = async (day: CalendarDay) => {
    day.localValue = day.qty ?? 0
    day.editing = true
    await nextTick()
    inputRefs.get(day.date)?.focus()
}

const save = (day: CalendarDay) => {
    if (!day.editing) return
    day.editing = false
    const casted = Number(day.localValue)
    day.qty = isNaN(casted) ? 0 : casted
}

const handleClickOutside = (e: MouseEvent) => {
    calendarDays.value.forEach((day) => {
        const inputEl = inputRefs.get(day.date)
        if (day.editing && inputEl && !inputEl.contains(e.target as Node)) {
            save(day)
        }
    })
}

onMounted(() => {
    document.addEventListener("click", handleClickOutside)
})

onBeforeUnmount(() => {
    document.removeEventListener("click", handleClickOutside)
})
</script>

<template>
    <div class="space-y-2">
        <div
            v-for="day in calendarDays"
            :key="day.date"
            class="flex items-center gap-4"
        >
            <span class="w-32">{{ day.formatted }}</span>

            <span
                v-if="!day.editing"
                class="cursor-pointer hover:underline"
                @click.stop="startEditing(day)"
            >
                {{ day.qty }}
            </span>

            <input
                v-else
                :ref="(el) => el && inputRefs.set(day.date, el)"
                v-model="day.localValue"
                type="number"
                min="0"
                @keyup.enter="save(day)"
                class="w-20 border rounded px-2 py-1"
            />
        </div>
    </div>
</template>
