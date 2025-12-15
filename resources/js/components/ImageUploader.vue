<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { Empty, EmptyContent, EmptyDescription, EmptyHeader, EmptyMedia, EmptyTitle } from '@/components/ui/empty';
import { Item, ItemContent, ItemTitle, ItemDescription, ItemHeader } from '@/components/ui/item';
import { X, ImageIcon, ImagePlus, Loader2, AlertCircle } from 'lucide-vue-next';
import draggable from 'vuedraggable';

// Existing image from server (has id and url)
export interface ExistingImage {
    id: number;
    url: string;
    name: string;
    size: number;
}

// Uploaded media item (after immediate upload)
export interface UploadedMedia {
    id: number;           // TemporaryUpload ID
    media_id: number;     // Media ID
    url: string;          // Preview URL
    name: string;
    size: number;
}

// Unified preview item that can be existing, uploading, or uploaded
interface PreviewItem {
    type: 'existing' | 'uploading' | 'uploaded';
    id?: number;              // For existing images (media id) or uploaded (tempUpload id)
    media_id?: number;        // For uploaded items
    file?: File;              // For uploading items
    url: string;
    name: string;
    size: number;
    progress?: number;        // Upload progress (0-100)
    error?: string;           // Upload error message
    abortController?: AbortController; // To cancel upload
}

interface Props {
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
    (e: 'update:existingImages', value: ExistingImage[]): void;
    (e: 'update:uploadedMediaIds', value: number[]): void;
}>();

const fileInputRef = ref<HTMLInputElement | null>(null);
const isDragging = ref(false);
const isDraggingExternal = ref(false);
const isReordering = ref(false);

// Unified previews list
const previews = ref<PreviewItem[]>([]);

// Track existing images separately
const localExistingImages = ref<ExistingImage[]>([]);

// Track uploaded media IDs
const uploadedMediaIds = computed(() => 
    previews.value
        .filter(p => p.type === 'uploaded' && p.id)
        .map(p => p.id!)
);

// Total files count
const totalFilesCount = computed(() => previews.value.length);
const canAddMore = computed(() => props.multiple && totalFilesCount.value < props.maxFiles);
const isMaxReached = computed(() => props.multiple && totalFilesCount.value >= props.maxFiles);

// Check if any uploads are in progress
const isUploading = computed(() => previews.value.some(p => p.type === 'uploading'));

// Initialize from props
watch(() => props.existingImages, (newExisting) => {
    localExistingImages.value = [...(newExisting || [])];
    rebuildPreviews();
}, { immediate: true, deep: true });

// Emit uploaded media IDs when they change
watch(uploadedMediaIds, (ids) => {
    emit('update:uploadedMediaIds', ids);
}, { deep: true });

// When disabled becomes true (form submitting), cancel all in-progress uploads silently
watch(() => props.disabled, (newDisabled) => {
    if (newDisabled && isUploading.value) {
        // Cancel all in-progress uploads
        previews.value.forEach(preview => {
            if (preview.type === 'uploading' && preview.abortController) {
                preview.abortController.abort();
            }
        });
        
        // Remove uploading items from previews (silently, no error shown)
        previews.value = previews.value.filter(p => p.type !== 'uploading');
    }
});

/**
 * Rebuild previews from existing images only
 * Uploaded and uploading items are managed separately
 */
function rebuildPreviews() {
    if (isReordering.value) return;
    
    const existingPreviews: PreviewItem[] = localExistingImages.value.map(img => ({
        type: 'existing' as const,
        id: img.id,
        url: img.url,
        name: img.name,
        size: img.size,
    }));
    
    // Keep uploading and uploaded items
    const nonExisting = previews.value.filter(p => p.type !== 'existing');
    
    previews.value = [...existingPreviews, ...nonExisting];
}

/**
 * Upload a file immediately
 */
async function uploadFile(file: File): Promise<void> {
    const abortController = new AbortController();
    
    // Create preview item for uploading state
    const previewItem: PreviewItem = {
        type: 'uploading',
        file,
        url: '',
        name: file.name,
        size: file.size,
        progress: 0,
        abortController,
    };
    
    // Load local preview immediately
    const reader = new FileReader();
    reader.onload = (e) => {
        const index = previews.value.findIndex(p => 
            p.type === 'uploading' && p.file === file
        );
        if (index !== -1) {
            previews.value[index] = {
                ...previews.value[index],
                url: e.target?.result as string
            };
        }
    };
    reader.readAsDataURL(file);
    
    previews.value.push(previewItem);
    
    try {
        const formData = new FormData();
        formData.append('file', file);
        formData.append('collection', 'images');
        
        // Use relative path to avoid protocol-relative URL issues
        const uploadUrl = '/api/uploads/temp';
        
        const response = await fetch(uploadUrl, {
            method: 'POST',
            body: formData,
            signal: abortController.signal,
            headers: {
                'X-XSRF-TOKEN': getCsrfToken(),
                'Accept': 'application/json',
            },
            credentials: 'same-origin',
        });
        
        if (!response.ok) {
            throw new Error('Upload failed');
        }
        
        const result = await response.json();
        
        if (result.success) {
            // Update preview to uploaded state
            const index = previews.value.findIndex(p => 
                p.type === 'uploading' && p.file === file
            );
            if (index !== -1) {
                previews.value[index] = {
                    type: 'uploaded',
                    id: result.data.id,
                    media_id: result.data.media_id,
                    url: result.data.url,
                    name: result.data.name,
                    size: result.data.size,
                };
            }
        } else {
            throw new Error(result.message || 'Upload failed');
        }
    } catch (error: any) {
        if (error.name === 'AbortError') {
            // Upload was cancelled, remove the preview
            const index = previews.value.findIndex(p => 
                p.type === 'uploading' && p.file === file
            );
            if (index !== -1) {
                previews.value.splice(index, 1);
            }
            return;
        }
        
        // Update preview with error
        const index = previews.value.findIndex(p => 
            p.type === 'uploading' && p.file === file
        );
        if (index !== -1) {
            previews.value[index] = {
                ...previews.value[index],
                error: error.message || 'Upload failed',
            };
        }
    }
}

/**
 * Get CSRF token from cookie
 */
function getCsrfToken(): string {
    const name = 'XSRF-TOKEN=';
    const decodedCookie = decodeURIComponent(document.cookie);
    const cookies = decodedCookie.split(';');
    for (let cookie of cookies) {
        cookie = cookie.trim();
        if (cookie.indexOf(name) === 0) {
            return cookie.substring(name.length);
        }
    }
    return '';
}

/**
 * Handle file selection
 */
function handleFileSelect(event: Event) {
    const target = event.target as HTMLInputElement;
    const selectedFiles = Array.from(target.files || []);
    
    if (selectedFiles.length === 0) return;
    
    if (props.multiple) {
        const availableSlots = props.maxFiles - totalFilesCount.value;
        const filesToUpload = selectedFiles.slice(0, availableSlots);
        filesToUpload.forEach(file => uploadFile(file));
    } else {
        // For single mode, clear existing and upload new
        localExistingImages.value = [];
        emit('update:existingImages', []);
        // Remove any existing uploaded/uploading items
        previews.value = [];
        uploadFile(selectedFiles[0]);
    }
    
    if (fileInputRef.value) {
        fileInputRef.value.value = '';
    }
}

/**
 * Handle file drop
 */
function handleDrop(event: DragEvent) {
    isDragging.value = false;
    isDraggingExternal.value = false;
    
    const droppedFiles = Array.from(event.dataTransfer?.files || [])
        .filter(file => file.type.startsWith('image/'));
    
    if (droppedFiles.length === 0) return;
    
    if (props.multiple) {
        const availableSlots = props.maxFiles - totalFilesCount.value;
        const filesToUpload = droppedFiles.slice(0, availableSlots);
        filesToUpload.forEach(file => uploadFile(file));
    } else {
        localExistingImages.value = [];
        emit('update:existingImages', []);
        previews.value = [];
        uploadFile(droppedFiles[0]);
    }
}

function handleDragOver(event: DragEvent) {
    event.preventDefault();
    
    const hasFiles = event.dataTransfer?.types?.includes('Files') ?? false;
    
    if (props.multiple && !isMaxReached.value) {
        isDragging.value = true;
        isDraggingExternal.value = hasFiles;
    } else if (!props.multiple && totalFilesCount.value === 0) {
        isDragging.value = true;
        isDraggingExternal.value = hasFiles;
    }
}

function handleDragLeave(event: DragEvent) {
    const target = event.currentTarget as HTMLElement;
    const related = event.relatedTarget as HTMLElement;
    
    if (!related || !target.contains(related)) {
        isDragging.value = false;
        isDraggingExternal.value = false;
    }
}

/**
 * Remove a file/image
 */
async function removeFile(index: number) {
    const preview = previews.value[index];
    
    if (preview.type === 'existing') {
        localExistingImages.value = localExistingImages.value.filter(img => img.id !== preview.id);
        previews.value.splice(index, 1);
        emit('update:existingImages', localExistingImages.value);
    } else if (preview.type === 'uploading') {
        // Cancel the upload
        preview.abortController?.abort();
        previews.value.splice(index, 1);
    } else if (preview.type === 'uploaded') {
        // Delete from server
        try {
            await fetch(`/api/uploads/temp/${preview.id}`, {
                method: 'DELETE',
                headers: {
                    'X-XSRF-TOKEN': getCsrfToken(),
                    'Accept': 'application/json',
                },
                credentials: 'same-origin',
            });
        } catch (error) {
            console.error('Failed to delete upload:', error);
        }
        previews.value.splice(index, 1);
    }
}

/**
 * Retry a failed upload
 */
function retryUpload(index: number) {
    const preview = previews.value[index];
    if (preview.type === 'uploading' && preview.error && preview.file) {
        // Remove the failed preview
        previews.value.splice(index, 1);
        // Re-upload
        uploadFile(preview.file);
    }
}

function openFileDialog() {
    fileInputRef.value?.click();
}

function handleReorder() {
    if (!props.multiple) return;
    
    isReordering.value = true;
    
    const newExisting: ExistingImage[] = [];
    
    previews.value.forEach(preview => {
        if (preview.type === 'existing' && preview.id !== undefined) {
            const original = localExistingImages.value.find(img => img.id === preview.id);
            if (original) newExisting.push(original);
        }
    });
    
    localExistingImages.value = newExisting;
    emit('update:existingImages', newExisting);
    
    setTimeout(() => {
        isReordering.value = false;
    }, 0);
}

/**
 * Format file size
 */
function formatSize(bytes: number): string {
    if (bytes < 1024) return bytes + ' B';
    if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
    return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
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
            <div class="flex items-center gap-2">
                <Loader2 v-if="isUploading" class="w-4 h-4 animate-spin text-muted-foreground" />
                <span v-if="multiple && totalFilesCount > 0" class="text-xs text-muted-foreground">
                    {{ totalFilesCount }} of {{ maxFiles }} images
                </span>
            </div>
        </div>

        <!-- Hidden file input -->
        <input
            :id="name"
            ref="fileInputRef"
            type="file"
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
            <draggable
                v-model="previews"
                :item-key="(item: PreviewItem) => item.type === 'existing' ? `existing-${item.id}` : item.type === 'uploaded' ? `uploaded-${item.id}` : `uploading-${item.name}-${item.size}`"
                :disabled="!multiple || disabled || isUploading"
                ghost-class="opacity-30"
                @change="handleReorder"
                class="grid grid-cols-6 gap-4"
            >
                <template #item="{ element: preview, index }">
                    <Item 
                        variant="outline" 
                        as-child
                        role="listitem"
                        class="bg-background aspect-[12/16] relative"
                        :class="[
                            isDraggingExternal ? 'opacity-40' : '',
                            preview.type === 'uploading' && !preview.error ? '' : 'hover:cursor-move',
                            preview.error ? 'border-destructive' : ''
                        ]"
                    >
                        <div>
                            <ItemHeader class="relative">
                                <img
                                    :src="preview.url"
                                    :alt="preview.name"
                                    width="128"
                                    height="128"
                                    class="aspect-square w-full rounded-sm object-cover"
                                    :class="preview.type === 'uploading' ? 'opacity-50' : ''"
                                >
                                <!-- Uploading overlay -->
                                <div 
                                    v-if="preview.type === 'uploading' && !preview.error" 
                                    class="absolute inset-0 flex items-center justify-center bg-background/50"
                                >
                                    <Loader2 class="w-6 h-6 animate-spin text-primary" />
                                </div>
                                <!-- Error overlay -->
                                <div 
                                    v-if="preview.error"
                                    class="absolute inset-0 flex flex-col items-center justify-center bg-destructive/10 p-2"
                                >
                                    <AlertCircle class="w-6 h-6 text-destructive mb-1" />
                                    <p class="text-[10px] text-destructive text-center line-clamp-2">{{ preview.error }}</p>
                                    <Button 
                                        type="button"
                                        variant="outline" 
                                        size="sm"
                                        @click="retryUpload(index)"
                                        class="mt-1 h-6 text-xs"
                                    >
                                        Retry
                                    </Button>
                                </div>
                            </ItemHeader>
                            <ItemContent class="truncate">
                                <ItemTitle class="w-auto">
                                    <div class="truncate">{{ preview.name }}</div>
                                </ItemTitle>
                                <div class="flex justify-between items-center">
                                    <ItemDescription>
                                        {{ formatSize(preview.size) }}
                                    </ItemDescription>
                                    <Button 
                                        type="button"
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
                
                <!-- Add More Button as footer slot -->
                <template #footer>
                    <Item
                        v-if="multiple && canAddMore"
                        variant="outline"
                        as-child
                        role="button"
                        :class="[
                            'aspect-[12/16]',
                            disabled 
                                ? 'opacity-50 cursor-not-allowed' 
                                : 'cursor-pointer hover:border-primary/50 hover:bg-muted'
                        ]"
                        @click="!disabled && openFileDialog()"
                    >
                        <button
                            type="button"
                            class="border-dashed group"
                            :disabled="disabled"
                        >
                            <ItemHeader class="aspect-square flex items-center justify-center p-4">
                                <div 
                                    :class="[
                                        'flex flex-col items-center gap-2 text-center',
                                        disabled ? 'opacity-30' : 'opacity-40 group-hover:opacity-90'
                                    ]"
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
                </template>
            </draggable>
        </div>

        <!-- Error Message -->
        <Alert v-if="error" variant="destructive" class="mt-2">
            <AlertDescription>{{ error }}</AlertDescription>
        </Alert>
    </div>
</template>
