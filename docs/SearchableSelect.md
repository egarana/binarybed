# UnifiedCombobox Component Documentation

## Deskripsi
UnifiedCombobox adalah komponen Vue yang reusable untuk dropdown selection dengan fitur search. Komponen ini mendukung mode single dan multiple selection, serta dapat melakukan fetch data dari server.

## Import Component
```vue
<script setup>
import UnifiedCombobox from '@/components/UnifiedCombobox.vue';
</script>
```

## Props Configuration

### Mode Configuration
| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `mode` | `'single' \| 'multiple'` | `'single'` | Mode selection: single atau multiple |

### Data Configuration
| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `modelValue` | `ComboboxOption \| ComboboxOption[]` | `undefined` | Value yang terpilih |
| `options` | `ComboboxOption[]` | `[]` | Static options untuk ditampilkan |
| `initialItems` | `ComboboxOption[]` | `[]` | Alternative untuk options |

### Fetch Configuration
| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `fetchUrl` | `string \| (() => string)` | `undefined` | URL untuk fetch data |
| `responseKey` | `string` | `undefined` | Key dari response server |
| `searchParam` | `string` | `'search'` | Parameter untuk search query |
| `debounceMs` | `number` | `300` | Delay debounce dalam ms |

### UI Configuration
| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `label` | `string` | `undefined` | Label untuk combobox |
| `placeholder` | `string` | `'Select an option'` | Placeholder text |
| `searchPlaceholder` | `string` | `'Search...'` | Placeholder untuk search input |
| `required` | `boolean` | `false` | Menampilkan "(Optional)" jika false |
| `error` | `string` | `undefined` | Error message |

### Form Integration
| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `name` | `string` | `undefined` | Name untuk hidden input |
| `hiddenInputName` | `string` | `undefined` | Alternative untuk name |
| `id` | `string` | `undefined` | ID untuk element |
| `tabindex` | `number` | `undefined` | Tab index |

### Features
| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `clearable` | `boolean` | `true` | Show clear button (single mode) |
| `draggable` | `boolean` | `true` | Enable drag reorder (multiple mode) |

### Icon Configuration (Multiple Mode)
| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `generalIcon` | `any` | `undefined` | Icon untuk semua items |
| `defaultIcon` | `any` | `undefined` | Fallback icon |
| `showDefaultIcon` | `boolean` | `false` | Show fallback icon |

## Usage Examples

### 1. Single Selection dengan Static Data
```vue
<template>
  <UnifiedCombobox
    v-model="selectedUser"
    mode="single"
    :options="users"
    label="Select User"
    placeholder="Choose a user"
    name="user_id"
  />
</template>

<script setup>
import { ref } from 'vue';

const users = [
  { value: '1', label: 'John Doe' },
  { value: '2', label: 'Jane Smith' },
  { value: '3', label: 'Bob Johnson' }
];

const selectedUser = ref(null);
</script>
```

### 2. Single Selection dengan Server Fetch
```vue
<template>
  <UnifiedCombobox
    v-model="selectedProduct"
    mode="single"
    fetchUrl="/api/products"
    responseKey="products"
    label="Product"
    placeholder="Search product..."
    searchPlaceholder="Type to search..."
    name="product_id"
    :clearable="true"
  />
</template>

<script setup>
import { ref } from 'vue';

const selectedProduct = ref(null);
</script>
```

### 3. Multiple Selection dengan Static Data
```vue
<template>
  <UnifiedCombobox
    v-model="selectedCategories"
    mode="multiple"
    :options="categories"
    label="Categories"
    placeholder="Select categories"
    name="category_ids"
    :required="true"
  />
</template>

<script setup>
import { ref } from 'vue';

const categories = [
  { value: 'tech', label: 'Technology' },
  { value: 'health', label: 'Health' },
  { value: 'finance', label: 'Finance' },
  { value: 'education', label: 'Education' }
];

const selectedCategories = ref([]);
</script>
```

### 4. Multiple Selection dengan Icons
```vue
<template>
  <UnifiedCombobox
    v-model="selectedPermissions"
    mode="multiple"
    :options="permissions"
    label="Permissions"
    placeholder="Select permissions"
    name="permissions"
    :draggable="true"
    :showDefaultIcon="true"
    :defaultIcon="Shield"
  />
</template>

<script setup>
import { ref } from 'vue';
import { Shield, Eye, Edit, Trash } from 'lucide-vue-next';

const permissions = [
  { value: 'view', label: 'View', icon: Eye },
  { value: 'edit', label: 'Edit', icon: Edit },
  { value: 'delete', label: 'Delete', icon: Trash }
];

const selectedPermissions = ref([]);
</script>
```

### 5. Multiple Selection dengan Server Fetch dan Drag
```vue
<template>
  <UnifiedCombobox
    v-model="selectedTags"
    mode="multiple"
    fetchUrl="/api/tags"
    responseKey="tags"
    label="Tags"
    placeholder="Select tags"
    searchPlaceholder="Search tags..."
    name="tag_ids"
    :draggable="true"
    :error="errors.tags"
  />
</template>

<script setup>
import { ref } from 'vue';

const selectedTags = ref([]);
const errors = ref({ tags: '' });
</script>
```

### 6. Dynamic URL dengan Function
```vue
<template>
  <UnifiedCombobox
    v-model="selectedItem"
    mode="single"
    :fetchUrl="getSearchUrl"
    responseKey="items"
    label="Item"
    placeholder="Select item"
    name="item_id"
  />
</template>

<script setup>
import { ref } from 'vue';

const categoryId = ref(1);
const selectedItem = ref(null);

const getSearchUrl = () => {
  return `/api/categories/${categoryId.value}/items`;
};
</script>
```

### 7. Form Integration dengan Inertia.js
```vue
<template>
  <form @submit.prevent="form.post('/submit')">
    <!-- Single Selection -->
    <UnifiedCombobox
      v-model="form.user_id"
      mode="single"
      fetchUrl="/api/users"
      responseKey="users"
      label="Assign to User"
      placeholder="Select user"
      name="user_id"
      :error="form.errors.user_id"
    />
    
    <!-- Multiple Selection -->
    <UnifiedCombobox
      v-model="form.tags"
      mode="multiple"
      :options="availableTags"
      label="Tags"
      placeholder="Select tags"
      name="tags"
      :error="form.errors.tags"
      :required="true"
    />
    
    <button type="submit">Submit</button>
  </form>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3';

const availableTags = [
  { value: 'urgent', label: 'Urgent' },
  { value: 'important', label: 'Important' },
  { value: 'low', label: 'Low Priority' }
];

const form = useForm({
  user_id: null,
  tags: []
});
</script>
```

### 8. Dengan Initial Value
```vue
<template>
  <!-- Single dengan initial value -->
  <UnifiedCombobox
    v-model="selectedCountry"
    mode="single"
    :options="countries"
    label="Country"
    placeholder="Select country"
    name="country"
  />
  
  <!-- Multiple dengan initial values -->
  <UnifiedCombobox
    v-model="selectedLanguages"
    mode="multiple"
    :options="languages"
    label="Languages"
    placeholder="Select languages"
    name="languages"
  />
</template>

<script setup>
import { ref } from 'vue';

const countries = [
  { value: 'us', label: 'United States' },
  { value: 'uk', label: 'United Kingdom' },
  { value: 'id', label: 'Indonesia' }
];

const languages = [
  { value: 'en', label: 'English' },
  { value: 'id', label: 'Indonesian' },
  { value: 'ja', label: 'Japanese' }
];

// Set initial values
const selectedCountry = ref({ value: 'id', label: 'Indonesia' });
const selectedLanguages = ref([
  { value: 'en', label: 'English' },
  { value: 'id', label: 'Indonesian' }
]);
</script>
```

## Server Response Format

Untuk fetch dari server, response harus dalam format array of objects:

```json
{
  "products": [
    { "value": "1", "label": "Product A" },
    { "value": "2", "label": "Product B" },
    { "value": "3", "label": "Product C" }
  ]
}
```

Dengan icon (opsional):
```json
{
  "items": [
    { 
      "value": "1", 
      "label": "Item A",
      "icon": "<svg>...</svg>"  // SVG string
    },
    { 
      "value": "2", 
      "label": "Item B"
      // No icon, will use generalIcon or defaultIcon
    }
  ]
}
```

## TypeScript Interface

```typescript
interface ComboboxOption {
  value: string;
  label: string;
  icon?: any; // Optional icon (component or SVG string)
}
```

## Events

### update:modelValue
Emitted ketika selection berubah.

```vue
<template>
  <UnifiedCombobox
    :modelValue="selected"
    @update:modelValue="handleChange"
    mode="single"
    :options="options"
  />
</template>

<script setup>
const handleChange = (value) => {
  console.log('Selected:', value);
  // Single mode: value is ComboboxOption | undefined
  // Multiple mode: value is ComboboxOption[]
};
</script>
```

## Notes

1. **Mode Consistency**: Pastikan `modelValue` sesuai dengan mode:
   - Single mode: `ComboboxOption | undefined`
   - Multiple mode: `ComboboxOption[]`

2. **Fetch URL**: Bisa berupa string atau function yang return string untuk dynamic URLs

3. **Icons**: Support untuk Lucide icons atau SVG string dari database

4. **Draggable**: Hanya bekerja di multiple mode untuk reorder items

5. **Hidden Inputs**: Otomatis generate hidden inputs untuk form submission

6. **Error Handling**: Pass error prop untuk display validation errors

## Migration Guide

### Dari Component Lama (Multiple)
```vue
<!-- Before -->
<MultiSelectCombobox
  v-model="selected"
  :options="items"
  label="Select Items"
  name="items"
  fetchUrl="/api/items"
/>

<!-- After -->
<UnifiedCombobox
  v-model="selected"
  mode="multiple"
  :options="items"
  label="Select Items"
  name="items"
  fetchUrl="/api/items"
  responseKey="items"
/>
```

### Dari Component Lama (Single)
```vue
<!-- Before -->
<SingleCombobox
  v-model="selected"
  :initialItems="items"
  fetchUrl="/api/items"
  responseKey="items"
  hiddenInputName="item_id"
/>

<!-- After -->
<UnifiedCombobox
  v-model="selected"
  mode="single"
  :options="items"
  fetchUrl="/api/items"
  responseKey="items"
  name="item_id"
/>
```
