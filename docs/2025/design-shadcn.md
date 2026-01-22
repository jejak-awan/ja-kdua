# Shadcn UI Integration Design

## Overview

Strategy for integrating Shadcn UI component library into JA-CMS for consistent, accessible, and customizable UI components.

---

## What is Shadcn UI?

**Shadcn UI** is NOT a traditional component library. It's a collection of re-usable components that you copy into your project.

**Key Features:**
- ✅ Copy-paste components (not npm package)
- ✅ Built with Radix UI (accessibility)
- ✅ Styled with Tailwind CSS
- ✅ Fully customizable
- ✅ TypeScript support
- ✅ Dark mode ready

**Why Shadcn?**
- Own your components (no dependency)
- Customize without limitations
- Better bundle size control
- Modern, beautiful defaults
- Active community

## Implementation Status ✅

**Last Updated:** 2025-12-23

### Components Created (43 total)
| Category | Components |
|----------|------------|
| **Form** | Button, Input, Textarea, Label, Select (+4 sub), Checkbox |
| **Layout** | Card (+5 sub), Tabs (+3 sub), Accordion (+3 sub) |
| **Feedback** | Badge, Alert (+2 sub), Dialog (+6 sub), Tooltip |
| **Data Display** | Table (+5 sub), Avatar (+2 sub) |

### Design Tokens Applied
- `bg-card`, `bg-muted`, `bg-secondary` (93+ files)
- `text-foreground`, `text-muted-foreground` (105+ files)
- `border-border`, `divide-border` (46+ files)
- Transparent status badges (`bg-*-500/20 text-*-500`)

---

## Architecture

### Component Structure

```
resources/js/
├── components/
│   ├── ui/                    # Shadcn components
│   │   ├── button.vue
│   │   ├── input.vue
│   │   ├── select.vue
│   │   ├── dialog.vue
│   │   ├── dropdown-menu.vue
│   │   ├── card.vue
│   │   ├── table.vue
│   │   ├── form.vue
│   │   ├── tooltip.vue
│   │   ├── alert.vue
│   │   ├── badge.vue
│   │   ├── tabs.vue
│   │   └── ...
│   │
│   ├── layouts/               # Layout components
│   ├── features/              # Feature components
│   └── ...
│
├── lib/
│   ├── utils.js              # cn() utility
│   └── validators.js
│
└── styles/
    └── globals.css           # Tailwind + custom styles
```

---

## Installation & Setup

### 1. Install Dependencies

```bash
# Radix Vue (headless UI components)
npm install radix-vue

# Class variance authority (component variants)
npm install class-variance-authority

# Tailwind merge & clsx (utility)
npm install tailwind-merge clsx

# Lucide icons (optional but recommended)
npm install lucide-vue-next
```

### 2. Configure Tailwind

**`tailwind.config.js`**
```javascript
module.exports = {
  darkMode: ['class'],
  content: [
    './resources/**/*.{js,vue,blade.php}',
  ],
  theme: {
    extend: {
      colors: {
        border: 'hsl(var(--border))',
        input: 'hsl(var(--input))',
        ring: 'hsl(var(--ring))',
        background: 'hsl(var(--background))',
        foreground: 'hsl(var(--foreground))',
        primary: {
          DEFAULT: 'hsl(var(--primary))',
          foreground: 'hsl(var(--primary-foreground))',
        },
        secondary: {
          DEFAULT: 'hsl(var(--secondary))',
          foreground: 'hsl(var(--secondary-foreground))',
        },
        destructive: {
          DEFAULT: 'hsl(var(--destructive))',
          foreground: 'hsl(var(--destructive-foreground))',
        },
        muted: {
          DEFAULT: 'hsl(var(--muted))',
          foreground: 'hsl(var(--muted-foreground))',
        },
        accent: {
          DEFAULT: 'hsl(var(--accent))',
          foreground: 'hsl(var(--accent-foreground))',
        },
        card: {
          DEFAULT: 'hsl(var(--card))',
          foreground: 'hsl(var(--card-foreground))',
        },
      },
      borderRadius: {
        lg: 'var(--radius)',
        md: 'calc(var(--radius) - 2px)',
        sm: 'calc(var(--radius) - 4px)',
      },
    },
  },
  plugins: [require('tailwindcss-animate')],
};
```

### 3. Setup CSS Variables

**`resources/css/app.css`**
```css
@tailwind base;
@tailwind components;
@tailwind utilities;

@layer base {
  :root {
    --background: 0 0% 100%;
    --foreground: 222.2 84% 4.9%;
    --card: 0 0% 100%;
    --card-foreground: 222.2 84% 4.9%;
    --primary: 222.2 47.4% 11.2%;
    --primary-foreground: 210 40% 98%;
    --secondary: 210 40% 96.1%;
    --secondary-foreground: 222.2 47.4% 11.2%;
    --muted: 210 40% 96.1%;
    --muted-foreground: 215.4 16.3% 46.9%;
    --accent: 210 40% 96.1%;
    --accent-foreground: 222.2 47.4% 11.2%;
    --destructive: 0 84.2% 60.2%;
    --destructive-foreground: 210 40% 98%;
    --border: 214.3 31.8% 91.4%;
    --input: 214.3 31.8% 91.4%;
    --ring: 222.2 84% 4.9%;
    --radius: 0.5rem;
  }

  .dark {
    --background: 222.2 84% 4.9%;
    --foreground: 210 40% 98%;
    --card: 222.2 84% 4.9%;
    --card-foreground: 210 40% 98%;
    --primary: 210 40% 98%;
    --primary-foreground: 222.2 47.4% 11.2%;
    --secondary: 217.2 32.6% 17.5%;
    --secondary-foreground: 210 40% 98%;
    --muted: 217.2 32.6% 17.5%;
    --muted-foreground: 215 20.2% 65.1%;
    --accent: 217.2 32.6% 17.5%;
    --accent-foreground: 210 40% 98%;
    --destructive: 0 62.8% 30.6%;
    --destructive-foreground: 210 40% 98%;
    --border: 217.2 32.6% 17.5%;
    --input: 217.2 32.6% 17.5%;
    --ring: 212.7 26.8% 83.9%;
  }
}
```

### 4. Create Utils Helper

**`resources/js/lib/utils.js`**
```javascript
import { clsx } from 'clsx';
import { twMerge } from 'tailwind-merge';

export function cn(...inputs) {
  return twMerge(clsx(inputs));
}
```

---

## Component Examples

### Button Component

**`resources/js/components/ui/button.vue`**
```vue
<template>
  <button :class="cn(buttonVariants({ variant, size }), props.class)" v-bind="$attrs">
    <slot />
  </button>
</template>

<script setup>
import { computed } from 'vue';
import { cva } from 'class-variance-authority';
import { cn } from '@/lib/utils';

const buttonVariants = cva(
  'inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50',
  {
    variants: {
      variant: {
        default: 'bg-primary text-primary-foreground hover:bg-primary/90',
        destructive: 'bg-destructive text-destructive-foreground hover:bg-destructive/90',
        outline: 'border border-input bg-background hover:bg-accent hover:text-accent-foreground',
        secondary: 'bg-secondary text-secondary-foreground hover:bg-secondary/80',
        ghost: 'hover:bg-accent hover:text-accent-foreground',
        link: 'text-primary underline-offset-4 hover:underline',
      },
      size: {
        default: 'h-10 px-4 py-2',
        sm: 'h-9 rounded-md px-3',
        lg: 'h-11 rounded-md px-8',
        icon: 'h-10 w-10',
      },
    },
    defaultVariants: {
      variant: 'default',
      size: 'default',
    },
  }
);

const props = defineProps({
  variant: String,
  size: String,
  class: String,
});
</script>
```

**Usage:**
```vue
<Button>Click me</Button>
<Button variant="destructive">Delete</Button>
<Button variant="outline" size="sm">Small</Button>
```

---

## Migration Strategy

### Phase 1: Core Components (Week 1)
1. Button
2. Input
3. Select
4. Checkbox
5. Radio

**Target:** Replace all basic form elements

### Phase 2: Layout Components (Week 2)
1. Card
2. Dialog/Modal
3. Dropdown Menu
4. Tabs
5. Accordion

**Target:** Admin dashboard layouts

### Phase 3: Data Display (Week 3)
1. Table
2. Badge
3. Avatar
4. Alert
5. Tooltip

**Target:** Content lists & dashboards

### Phase 4: Advanced (Week 4)
1. Form (with validation)
2. Command palette
3. Date picker
4. Combobox
5. Calendar

**Target:** Content editor & advanced forms

---

## Component Customization

### Theme Customization

```css
:root {
  /* Change primary color */
  --primary: 221 83% 53%;  /* Blue */
  
  /* Change radius */
  --radius: 0.75rem;  /* More rounded */
}
```

### Component Variants

```javascript
// Add custom variant
const buttonVariants = cva('...', {
  variants: {
    variant: {
      // ... existing
      gradient: 'bg-gradient-to-r from-blue-500 to-purple-600 text-white',
    },
  },
});
```

---

## Integration with Existing Code

### Gradual Migration

```vue
<!-- Old component -->
<button class="btn btn-primary">Save</button>

<!-- New Shadcn component -->
<Button variant="default">Save</Button>

<!-- Coexist during migration -->
<div>
  <button class="btn btn-primary">Old Save</button>
  <Button variant="default">New Save</Button>
</div>
```

### Wrapper Components

```vue
<!-- resources/js/components/AppButton.vue -->
<template>
  <Button v-bind="$attrs">
    <slot />
  </Button>
</template>

<script setup>
import Button from './ui/button.vue';
</script>
```

---

## Best Practices

### 1. Use Composition
```vue
<Card>
  <CardHeader>
    <CardTitle>Title</CardTitle>
  </CardHeader>
  <CardContent>
    Content here
  </CardContent>
</Card>
```

### 2. Extend, Don't Modify
```vue
<!-- Create custom variant instead of modifying base -->
<Button variant="gradient">Custom</Button>
```

### 3. Maintain Accessibility
```vue
<Button aria-label="Close dialog">
  <X class="h-4 w-4" />
</Button>
```

---

## TypeScript Support (Optional)

```typescript
// types/ui.ts
export interface ButtonProps {
  variant?: 'default' | 'destructive' | 'outline';
  size?: 'default' | 'sm' | 'lg' | 'icon';
  asChild?: boolean;
}
```

---

## Testing Strategy

1. **Visual regression** - Chromatic/Percy
2. **Accessibility** - axe-core
3. **Unit tests** - Vitest
4. **E2E tests** - Playwright

---

## Documentation

### Component Docs

```markdown
# Button

## Usage
\`\`\`vue
<Button>Click me</Button>
\`\`\`

## Variants
- default
- destructive
- outline
- secondary
- ghost
- link

## Sizes
- default
- sm
- lg
- icon
```

---

## Conclusion

Shadcn UI provides:
- ✅ Ownership & customization
- ✅ Accessibility built-in
- ✅ Tailwind CSS integration
- ✅ Beautiful defaults
- ✅ No vendor lock-in
