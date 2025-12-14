<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { Empty, EmptyContent, EmptyDescription, EmptyHeader, EmptyMedia, EmptyTitle } from '@/components/ui/empty';
import { Item, ItemContent, ItemTitle, ItemDescription, ItemHeader } from '@/components/ui/item';
import { X, ImageIcon, ImagePlus } from 'lucide-vue-next';
import draggable from 'vuedraggable';

// Existing image from server (has id and url)
export interface ExistingImage {
    id: number;
    url: string;
    name: string;
    size: number;
}

// Unified preview item that can be either existing or new
interface PreviewItem {
    type: 'existing' | 'new';
    id?: number;        // For existing images
    file?: File;        // For new images
    url: string;
    name: string;
    size: number;
}

interface Props {
    modelValue?: File | File[] | null;
    existingImages?: ExistingImage[];
    error?: string;
    label?: string;
    name?: string;
    required?: boolean;
    disabled?: boolean;
    tabindex?: number;
    multiple?: boolean;
    maxFiles?: number;
}

const props = withDefaults(defineProps<Props>(), {
    modelValue: null,
    existingImages: () => [],
    error: '',
    label: 'Image',
    name: 'image',
    required: false,
    disabled: false,
    tabindex: 0,
    multiple: false,
    maxFiles: 10,
});

const emit = defineEmits<{
    (e: 'update:modelValue', value: File | File[] | null): void;
    (e: 'update:existingImages', value: ExistingImage[]): void;
}>();

const fileInputRef = ref<HTMLInputElement | null>(null);
const isDragging = ref(false);

// Unified previews list containing both existing and new images
const previews = ref<PreviewItem[]>([]);

// Track existing images separately
const localExistingImages = ref<ExistingImage[]>([]);

// Computed for new files only
const newFiles = computed(() => {
    if (props.multiple) {
        return Array.isArray(props.modelValue) ? props.modelValue : [];
    }
    return props.modelValue ? [props.modelValue] : [];
});

// Total files count (existing + new)
const totalFilesCount = computed(() => localExistingImages.value.length + newFiles.value.length);
const canAddMore = computed(() => props.multiple && totalFilesCount.value < props.maxFiles);
const isMaxReached = computed(() => props.multiple && totalFilesCount.value >= props.maxFiles);

// Initialize from props
watch(() => props.existingImages, (newExisting) => {
    localExistingImages.value = [...(newExisting || [])];
    rebuildPreviews();
}, { immediate: true, deep: true });

// Watch for new files changes
watch(() => props.modelValue, () => {
    rebuildPreviews();
}, { immediate: true, deep: true });

/**
 * Rebuild unified previews from existing + new files
 */
function rebuildPreviews() {
    const existingPreviews: PreviewItem[] = localExistingImages.value.map(img => ({
        type: 'existing' as const,
        id: img.id,
        url: img.url,
        name: img.name,
        size: img.size,
    }));
    
    const currentNewFiles = Array.isArray(props.modelValue) 
        ? props.modelValue 
        : props.modelValue ? [props.modelValue] : [];
    
    const newPreviews: PreviewItem[] = currentNewFiles.map(file => ({
        type: 'new' as const,
        file,
        url: '', // Will be loaded
        name: file.name,
        size: file.size,
    }));
    
    // Check if we need to reload URLs for new files
    const existingNewUrls = new Map(
        previews.value
            .filter(p => p.type === 'new' && p.file && p.url)
            .map(p => [p.file!, p.url])
    );
    
    // Reuse existing URLs where possible
    newPreviews.forEach(preview => {
        if (preview.file) {
            const existingUrl = existingNewUrls.get(preview.file);
            if (existingUrl) {
                preview.url = existingUrl;
            }
        }
    });
    
    previews.value = [...existingPreviews, ...newPreviews];
    
    // Load URLs for new files that don't have them
    newPreviews.forEach(preview => {
        if (preview.file && !preview.url) {
            loadFilePreview(preview);
        }
    });
}

function loadFilePreview(preview: PreviewItem) {
    if (!preview.file || !preview.file.type.startsWith('image/')) return;
    
    const reader = new FileReader();
    reader.onload = (e) => {
        const index = previews.value.findIndex(p => 
            p.type === 'new' && p.file === preview.file
        );
        if (index !== -1) {
            previews.value[index] = {
                ...previews.value[index],
                url: e.target?.result as string
            };
        }
    };
    reader.readAsDataURL(preview.file);
}

function handleFileSelect(event: Event) {
    const target = event.target as HTMLInputElement;
    const selectedFiles = Array.from(target.files || []);
    
    if (selectedFiles.length === 0) return;

    if (props.multiple) {
        const currentFiles = Array.isArray(props.modelValue) ? props.modelValue : [];
        const availableSlots = props.maxFiles - localExistingImages.value.length;
        const totalFiles = [...currentFiles, ...selectedFiles].slice(0, availableSlots);
        emit('update:modelValue', totalFiles);
    } else {
        // For single mode, clear existing and use new
        localExistingImages.value = [];
        emit('update:existingImages', []);
        emit('update:modelValue', selectedFiles[0]);
    }
    
    if (fileInputRef.value) {
        fileInputRef.value.value = '';
    }
}

function handleDrop(event: DragEvent) {
    isDragging.value = false;
    
    const droppedFiles = Array.from(event.dataTransfer?.files || [])
        .filter(file => file.type.startsWith('image/'));
    
    if (droppedFiles.length === 0) return;

    if (props.multiple) {
        const currentFiles = Array.isArray(props.modelValue) ? props.modelValue : [];
        const availableSlots = props.maxFiles - localExistingImages.value.length;
        const totalFiles = [...currentFiles, ...droppedFiles].slice(0, availableSlots);
        emit('update:modelValue', totalFiles);
    } else {
        localExistingImages.value = [];
        emit('update:existingImages', []);
        emit('update:modelValue', droppedFiles[0]);
    }
}

function handleDragOver(event: DragEvent) {
    event.preventDefault();
    if (props.multiple && !isMaxReached.value) {
        isDragging.value = true;
    } else if (!props.multiple && totalFilesCount.value === 0) {
        isDragging.value = true;
    }
}

function handleDragLeave(event: DragEvent) {
    // Only set isDragging to false if we're actually leaving the drop zone
    // Check if relatedTarget is null (left the window) or not a child of currentTarget
    const target = event.currentTarget as HTMLElement;
    const related = event.relatedTarget as HTMLElement;
    
    if (!related || !target.contains(related)) {
        isDragging.value = false;
    }
}

function removeFile(index: number) {
    const preview = previews.value[index];
    
    if (preview.type === 'existing') {
        // Remove existing image
        localExistingImages.value = localExistingImages.value.filter(img => img.id !== preview.id);
        emit('update:existingImages', localExistingImages.value);
    } else {
        // Remove new file
        if (props.multiple) {
            const currentFiles = Array.isArray(props.modelValue) ? [...props.modelValue] : [];
            const fileIndex = currentFiles.findIndex(f => f === preview.file);
            if (fileIndex !== -1) {
                currentFiles.splice(fileIndex, 1);
                emit('update:modelValue', currentFiles.length > 0 ? currentFiles : null);
            }
        } else {
            emit('update:modelValue', null);
        }
    }
}

function openFileDialog() {
    fileInputRef.value?.click();
}

function handleReorder(event: any) {
    if (!props.multiple) return;
    if (!event.moved) return;
    
    // Separate back into existing and new
    const newExisting: ExistingImage[] = [];
    const newNewFiles: File[] = [];
    
    previews.value.forEach(preview => {
        if (preview.type === 'existing' && preview.id !== undefined) {
            const original = localExistingImages.value.find(img => img.id === preview.id);
            if (original) newExisting.push(original);
        } else if (preview.type === 'new' && preview.file) {
            newNewFiles.push(preview.file);
        }
    });
    
    localExistingImages.value = newExisting;
    emit('update:existingImages', newExisting);
    emit('update:modelValue', newNewFiles.length > 0 ? newNewFiles : null);
}
</script>

<template>
    <div class="grid gap-4">
        <div class="flex items-center justify-between">
            <div>
                <Label :for="name" class="flex items-center gap-1">
                    {{ label }}
                    <span v-if="required" class="text-destructive">*</span>
                    <span v-if="!required" class="text-muted-foreground">(Optional)</span>
                </Label>
                <p class="text-xs text-muted-foreground mt-1">
                    Upload images to showcase your listing visually.
                </p>
            </div>
            <span v-if="multiple && totalFilesCount > 0" class="text-xs text-muted-foreground">
                {{ totalFilesCount }} of {{ maxFiles }} images
            </span>
        </div>

        <!-- Hidden file input -->
        <input
            :id="name"
            ref="fileInputRef"
            type="file"
            :name="multiple ? `${name}[]` : name"
            accept="image/jpeg,image/jpg,image/png,image/webp"
            :multiple="multiple"
            class="sr-only"
            :tabindex="tabindex"
            @change="handleFileSelect"
        />

        <!-- Empty State - shown only when no images -->
        <Empty
            v-if="totalFilesCount === 0"
            class="border border-dashed transition-colors"
            :class="[
                isDragging ? 'border-primary bg-muted' : 'border-border hover:border-primary/50',
                disabled ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer'
            ]"
            @click="!disabled && openFileDialog()"
            @drop.prevent="handleDrop"
            @dragover.prevent="handleDragOver"
            @dragleave="handleDragLeave"
        >
            <EmptyHeader>
                <EmptyMedia variant="icon">
                    <ImageIcon />
                </EmptyMedia>
                <EmptyTitle>No images uploaded</EmptyTitle>
                <EmptyDescription>
                    Click to upload or drag and drop{{ multiple ? ` up to ${maxFiles} images` : '' }}
                </EmptyDescription>
            </EmptyHeader>
            <EmptyContent>
                <p class="text-xs text-muted-foreground">
                    JPG, PNG or WEBP (max 10MB)
                </p>
            </EmptyContent>
        </Empty>

        <!-- Image Previews Grid with Drop Zone -->
        <div
            v-if="totalFilesCount > 0"
            class="relative"
            @drop.prevent="handleDrop"
            @dragover.prevent="handleDragOver"
            @dragleave="handleDragLeave"
        >
            <div class="grid grid-cols-6 gap-4">
                <draggable
                    v-model="previews"
                    :item-key="(item: PreviewItem) => item.type === 'existing' ? `existing-${item.id}` : `new-${item.name}-${item.size}`"
                    :disabled="!multiple || disabled"
                    @change="handleReorder"
                    class="contents"
                >
                    <template #item="{ element: preview, index }">
                        <Item 
                            variant="outline" 
                            as-child
                            role="listitem"
                            class="bg-background hover:cursor-move aspect-[12/16]"
                            :class="isDragging ? 'opacity-40' : ''"
                        >
                            <div>
                                <ItemHeader>
                                    <img
                                        :src="preview.url"
                                        :alt="preview.name"
                                        width="128"
                                        height="128"
                                        class="aspect-square w-full rounded-sm object-cover"
                                    >
                                </ItemHeader>
                                <ItemContent class="truncate">
                                    <ItemTitle class="w-auto">
                                        <div class="truncate">{{ preview.name }}</div>
                                    </ItemTitle>
                                    <div class="flex justify-between items-center">
                                        <ItemDescription>
                                            {{ (preview.size / 1024).toFixed(2) }} KB
                                        </ItemDescription>
                                        <Button 
                                            variant="outline" 
                                            size="icon" 
                                            @click="removeFile(index)"
                                            class="w-auto h-auto border-0 hover:bg-transparent opacity-50 hover:opacity-100"
                                        >
                                            <X class="w-4 h-4 text-muted-foreground" />
                                        </Button>
                                    </div>
                                </ItemContent>
                            </div>
                        </Item>
                    </template>
                </draggable>

                <!-- Add More Button -->
                <Item
                    v-if="multiple && canAddMore && !disabled"
                    variant="outline"
                    as-child
                    role="button"
                    class="cursor-pointer aspect-[12/16] hover:border-primary/50 hover:bg-muted"
                    @click="openFileDialog"
                >
                    <button
                        type="button"
                        class="border-dashed group"
                    >
                        <ItemHeader class="aspect-square flex items-center justify-center p-4">
                            <div 
                                class="flex flex-col items-center gap-2 text-center opacity-40 group-hover:opacity-90"
                            >
                                <div class="mb-2">
                                    <ImagePlus class="w-7 h-7 stroke-[1px] text-muted-foreground" />
                                </div>
                                <p class="text-muted-foreground text-sm/snug italic font-thin">
                                    Add images
                                </p>
                            </div>
                        </ItemHeader>
                    </button>
                </Item>
            </div>
        </div>

        <!-- Error Message -->
        <Alert v-if="error" variant="destructive" class="mt-2">
            <AlertDescription>{{ error }}</AlertDescription>
        </Alert>
    </div>
</template>
