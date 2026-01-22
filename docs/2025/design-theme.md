# Dark/Light Mode Design

## Overview

Comprehensive theme system implementation for JA-CMS with seamless dark/light mode switching, user preference persistence, and system-level integration.

## Implementation Status ✅

**Last Updated:** 2025-12-23

| Feature | Status |
|---------|--------|
| CSS Variables | ✅ Complete - Light/dark tokens in app.css |
| Tailwind Config | ✅ Complete - Class-based dark mode |
| Theme Composable | ✅ Complete - useDarkMode.js |
| Theme Toggle | ✅ Complete - DarkModeToggle.vue in navbar |
| LocalStorage Persistence | ✅ Complete |
| System Preference Detection | ✅ Complete |

### Dark Mode Audit Complete
- **41 files** migrated from `bg-white` → `bg-card`
- **45+ files** migrated from `bg-gray-*` → `bg-muted/secondary`
- All dropdowns, modals, tables, cards support dark mode
- Transparent status badges (`bg-*-500/20`) for consistency

---

## Architecture

### Theme System Structure

```
resources/js/
├── composables/
│   └── useTheme.js          # Theme composable
├── stores/
│   └── theme.js             # Pinia theme store
└── styles/
    ├── themes/
    │   ├── light.css        # Light theme variables
    │   ├── dark.css         # Dark theme variables
    │   └── custom.css       # Custom theme (optional)
    └── app.css              # Main styles

app/
├── Http/
│   └── Middleware/
│       └── ThemePreference.php  # Server-side theme
└── Models/
    └── UserPreference.php    # Store user preferences
```

---

## Implementation Strategy

### 1. CSS Variables Approach

**Why CSS Variables?**
- ✅ Dynamic switching without reload
- ✅ No SASS recompilation needed
- ✅ Better performance
- ✅ Easy to maintain
- ✅ Works with Tailwind

**`resources/css/app.css`**
```css
@tailwind base;
@tailwind components;
@tailwind utilities;

@layer base {
  :root {
    /* Light mode (default) */
    --background: 0 0% 100%;
    --foreground: 222.2 84% 4.9%;
    
    --card: 0 0% 100%;
    --card-foreground: 222.2 84% 4.9%;
    
    --primary: 221 83% 53%;
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
    
    --sidebar: 0 0% 98%;
    --sidebar-foreground: 240 5.3% 26.1%;
    --sidebar-border: 220 13% 91%;
    
    --radius: 0.5rem;
  }

  .dark {
    /* Dark mode */
    --background: 222.2 84% 4.9%;
    --foreground: 210 40% 98%;
    
    --card: 222.2 84% 4.9%;
    --card-foreground: 210 40% 98%;
    
    --primary: 217.2 91.2% 59.8%;
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
    
    --sidebar: 240 5.9% 10%;
    --sidebar-foreground: 240 4.8% 95.9%;
    --sidebar-border: 240 3.7% 15.9%;
  }
}
```

---

### 2. Tailwind Configuration

**`tailwind.config.js`**
```javascript
module.exports = {
  darkMode: ['class'], // Use class-based dark mode
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
        // ... other colors
      },
    },
  },
};
```

---

### 3. Theme Composable

**`resources/js/composables/useTheme.js`**
```javascript
import { ref, computed, watch, onMounted } from 'vue';

const THEME_KEY = 'ja-cms-theme';
const THEMES = {
  LIGHT: 'light',
  DARK: 'dark',
  SYSTEM: 'system',
};

const currentTheme = ref(THEMES.SYSTEM);
const systemTheme = ref(THEMES.LIGHT);

export function useTheme() {
  // Get actual theme (resolve 'system' to light/dark)
  const actualTheme = computed(() => {
    if (currentTheme.value === THEMES.SYSTEM) {
      return systemTheme.value;
    }
    return currentTheme.value;
  });

  // Check if dark mode is active
  const isDark = computed(() => actualTheme.value === THEMES.DARK);

  // Apply theme to document
  const applyTheme = (theme) => {
    const root = document.documentElement;
    
    if (theme === THEMES.DARK) {
      root.classList.add('dark');
    } else {
      root.classList.remove('dark');
    }
  };

  // Set theme
  const setTheme = (theme) => {
    currentTheme.value = theme;
    localStorage.setItem(THEME_KEY, theme);
    applyTheme(actualTheme.value);
    
    // Sync with backend if user is logged in
    syncThemeWithBackend(theme);
  };

  // Toggle between light and dark
  const toggleTheme = () => {
    const newTheme = isDark.value ? THEMES.LIGHT : THEMES.DARK;
    setTheme(newTheme);
  };

  // Detect system theme preference
  const detectSystemTheme = () => {
    const isDarkMode = window.matchMedia('(prefers-color-scheme: dark)').matches;
    systemTheme.value = isDarkMode ? THEMES.DARK : THEMES.LIGHT;
  };

  // Listen to system theme changes
  const watchSystemTheme = () => {
    const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');
    
    mediaQuery.addEventListener('change', (e) => {
      systemTheme.value = e.matches ? THEMES.DARK : THEMES.LIGHT;
    });
  };

  // Sync theme with backend
  const syncThemeWithBackend = async (theme) => {
    try {
      await fetch('/api/user/preferences', {
        method: 'PUT',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
        body: JSON.stringify({ theme }),
      });
    } catch (error) {
      console.error('Failed to sync theme:', error);
    }
  };

  // Initialize theme
  const initTheme = () => {
    // 1. Try to get from localStorage
    const saved = localStorage.getItem(THEME_KEY);
    
    if (saved && Object.values(THEMES).includes(saved)) {
      currentTheme.value = saved;
    }
    
    // 2. Detect system theme
    detectSystemTheme();
    
    // 3. Apply theme
    applyTheme(actualTheme.value);
    
    // 4. Watch system theme changes
    watchSystemTheme();
  };

  // Watch theme changes
  watch(actualTheme, (newTheme) => {
    applyTheme(newTheme);
  });

  onMounted(() => {
    initTheme();
  });

  return {
    currentTheme,
    actualTheme,
    isDark,
    setTheme,
    toggleTheme,
    themes: THEMES,
  };
}
```

---

### 4. Theme Toggle Component

**`resources/js/components/ThemeToggle.vue`**
```vue
<template>
  <div class="relative">
    <button
      @click="showMenu = !showMenu"
      class="p-2 rounded-lg hover:bg-accent transition-colors"
      aria-label="Toggle theme"
    >
      <Sun v-if="!isDark" class="h-5 w-5" />
      <Moon v-else class="h-5 w-5" />
    </button>

    <!-- Dropdown Menu -->
    <div
      v-if="showMenu"
      class="absolute right-0 mt-2 w-48 bg-card border border-border rounded-lg shadow-lg z-50"
      @click.stop
    >
      <div class="p-1">
        <button
          v-for="theme in themeOptions"
          :key="theme.value"
          @click="selectTheme(theme.value)"
          class="flex items-center w-full px-3 py-2 text-sm rounded hover:bg-accent transition-colors"
          :class="{ 'bg-accent': currentTheme === theme.value }"
        >
          <component :is="theme.icon" class="h-4 w-4 mr-3" />
          <span>{{ theme.label }}</span>
          <Check v-if="currentTheme === theme.value" class="h-4 w-4 ml-auto" />
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { Sun, Moon, Monitor, Check } from 'lucide-vue-next';
import { useTheme } from '@/composables/useTheme';

const { currentTheme, isDark, setTheme, themes } = useTheme();
const showMenu = ref(false);

const themeOptions = [
  { value: themes.LIGHT, label: 'Light', icon: Sun },
  { value: themes.DARK, label: 'Dark', icon: Moon },
  { value: themes.SYSTEM, label: 'System', icon: Monitor },
];

const selectTheme = (theme) => {
  setTheme(theme);
  showMenu.value = false;
};

// Close menu when clicking outside
const handleClickOutside = (event) => {
  if (showMenu.value && !event.target.closest('.relative')) {
    showMenu.value = false;
  }
};

document.addEventListener('click', handleClickOutside);
</script>
```

---

### 5. Backend Integration

**Store User Preference:**

```php
// app/Models/UserPreference.php
class UserPreference extends Model
{
    protected $fillable = ['user_id', 'theme', 'locale', 'preferences'];
    
    protected $casts = [
        'preferences' => 'array',
    ];
}

// Migration
Schema::create('user_preferences', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->string('theme')->default('system');
    $table->string('locale')->default('en');
    $table->json('preferences')->nullable();
    $table->timestamps();
    
    $table->unique('user_id');
});
```

**API Endpoints:**

```php
// routes/api.php
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user/preferences', [UserPreferenceController::class, 'show']);
    Route::put('/user/preferences', [UserPreferenceController::class, 'update']);
});

// app/Http/Controllers/UserPreferenceController.php
public function update(Request $request)
{
    $validated = $request->validate([
        'theme' => 'in:light,dark,system',
        'locale' => 'string',
    ]);
    
    $preference = $request->user()->preference()->updateOrCreate(
        ['user_id' => $request->user()->id],
        $validated
    );
    
    return response()->json($preference);
}
```

---

## Component Usage

### Usage in Templates

```vue
<template>
  <!-- Background -->
  <div class="bg-background text-foreground">
    
    <!-- Card -->
    <div class="bg-card text-card-foreground border border-border">
      
      <!-- Primary button -->
      <button class="bg-primary text-primary-foreground">
        Save
      </button>
      
      <!-- Muted text -->
      <p class="text-muted-foreground">
        Secondary information
      </p>
    </div>
  </div>
</template>
```

### Custom Component Styling

```vue
<style scoped>
.custom-component {
  background-color: hsl(var(--card));
  color: hsl(var(--card-foreground));
  border: 1px solid hsl(var(--border));
}

.custom-component:hover {
  background-color: hsl(var(--accent));
}
</style>
```

---

## Navigation Integration

**Add to AdminNavbar.vue:**

```vue
<!-- Right side items -->
<div class="flex items-center space-x-4 ml-auto">
  <Search />
  <Notifications />
  <ThemeToggle />  <!-- Add theme toggle -->
  <UserMenu />
</div>
```

---

## Best Practices

### 1. Always Use CSS Variables

```css
/* ✅ Good */
.element {
  background: hsl(var(--background));
  color: hsl(var(--foreground));
}

/* ❌ Bad */
.element {
  background: #ffffff;
  color: #000000;
}
```

### 2. Use Tailwind Classes

```vue
<!-- ✅ Good -->
<div class="bg-background text-foreground">

<!-- ❌ Bad -->
<div style="background: white; color: black;">
```

### 3. Test Both Themes

Always test components in both light and dark mode before deploying.

### 4. Respect User Preference

Load user's saved preference on login, fallback to system preference.

---

## Transition Effects

```css
/* Smooth theme transition */
* {
  transition: background-color 0.3s ease, color 0.3s ease, border-color 0.3s ease;
}

/* Disable transition on theme change to avoid flash */
.theme-transitioning * {
  transition: none !important;
}
```

---

## Testing Strategy

1. **Visual testing**: Check all pages in both themes
2. **Contrast ratio**: Ensure WCAG AA compliance
3. **System preference**: Test auto-switching
4. **Persistence**: Verify saved preferences
5. **Performance**: Measure transition smoothness

---

## Accessibility

```vue
<button 
  @click="toggleTheme"
  aria-label="Toggle dark mode"
  :aria-pressed="isDark"
>
  <Moon v-if="isDark" />
  <Sun v-else />
</button>
```

---

## Conclusion

This theme system provides:
- ✅ Instant theme switching
- ✅ User preference persistence
- ✅ System preference integration
- ✅ Smooth transitions
- ✅ Backend synchronization
- ✅ Accessible implementation
