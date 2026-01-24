# Builder System - Architecture Diagrams & Flows

## 1. System Architecture Overview

```
┌──────────────────────────────────────────────────────────────────────┐
│                         JA-CMS Builder System                        │
│                                                                      │
│  ┌────────────────────────────────────────────────────────────────┐ │
│  │                       UI Layer                                  │ │
│  ├────────────────────────────────────────────────────────────────┤ │
│  │                                                                │ │
│  │  Builder.vue (Main Component)                                │ │
│  │  ├── TopToolbar (save, export, etc)                         │ │
│  │  ├── LeftSidebar (blocks, pages, layers)                    │ │
│  │  ├── RightPanel (settings, design, advanced)                │ │
│  │  ├── Canvas (editing area)                                  │ │
│  │  │   ├── ModuleWrapper (individual blocks)                  │ │
│  │  │   ├── Draggable container                                │ │
│  │  │   └── Grid/Preview modes                                 │ │
│  │  └── Modals (confirm, input, presets, etc)                 │ │
│  │                                                                │ │
│  └────────────────────────────────────────────────────────────────┘ │
│  ┌────────────────────────────────────────────────────────────────┐ │
│  │              State Management (useBuilder)                     │ │
│  ├────────────────────────────────────────────────────────────────┤ │
│  │                                                                │ │
│  │  State                                                         │ │
│  │  ├── blocks, selectedModule, hoveredModule                   │ │
│  │  ├── pages, currentPage, content                             │ │
│  │  ├── history (undo/redo)                                     │ │
│  │  ├── UI state (tabs, device, zoom, etc)                      │ │
│  │  ├── modals (confirm, input, preset)                         │ │
│  │  └── preferences (localStorage)                              │ │
│  │                                                                │ │
│  │  Computed                                                      │ │
│  │  ├── selectedModule, canUndo, canRedo                        │ │
│  │  ├── isDirty, modulePath, currentPage                        │ │
│  │  └── blocks proxy (active canvas)                            │ │
│  │                                                                │ │
│  │  Methods (40+)                                                │ │
│  │  ├── Module CRUD (insert, remove, update)                    │ │
│  │  ├── Selection (select, hover, clear)                        │ │
│  │  ├── History (takeSnapshot, undo, redo)                      │ │
│  │  ├── Pages (fetch, add, delete, switch)                      │ │
│  │  ├── Canvas (add, remove, switch)                            │ │
│  │  ├── Save/Load (saveContent, loadContent)                    │ │
│  │  └── Utilities (style reset, copy, paste)                    │ │
│  │                                                                │ │
│  └────────────────────────────────────────────────────────────────┘ │
│  ┌────────────────────────────────────────────────────────────────┐ │
│  │           Core Services & Registries                           │ │
│  ├────────────────────────────────────────────────────────────────┤ │
│  │                                                                │ │
│  │  ModuleRegistry                                               │ │
│  │  ├── Store block definitions                                 │ │
│  │  ├── Create instances (new blocks)                           │ │
│  │  └── Manage components                                        │ │
│  │                                                                │ │
│  │  useTheme                                                      │ │
│  │  ├── Load theme configuration                                │ │
│  │  ├── Apply theme styles                                      │ │
│  │  └── Theme switching                                          │ │
│  │                                                                │ │
│  │  usePresets                                                    │ │
│  │  ├── Fetch user presets                                      │ │
│  │  ├── Save/delete presets                                     │ │
│  │  └── Apply presets                                            │ │
│  │                                                                │ │
│  │  useGlobalVariables                                           │ │
│  │  ├── Manage global data                                      │ │
│  │  └── Bind to settings                                         │ │
│  │                                                                │ │
│  └────────────────────────────────────────────────────────────────┘ │
│  ┌────────────────────────────────────────────────────────────────┐ │
│  │        Shared Utilities & Base Components                     │ │
│  ├────────────────────────────────────────────────────────────────┤ │
│  │                                                                │ │
│  │  BaseBlock.vue                                                │ │
│  │  ├── Universal wrapper for all blocks                        │ │
│  │  ├── Apply styles (responsive)                               │ │
│  │  ├── Handle interactions                                      │ │
│  │  └── Animations & effects                                     │ │
│  │                                                                │ │
│  │  styleUtils.js                                                │ │
│  │  ├── CSS generation functions                                │ │
│  │  ├── Responsive value resolution (getVal)                    │ │
│  │  └── Style aggregation                                        │ │
│  │                                                                │ │
│  │  InteractionManager.js                                        │ │
│  │  ├── Event handling                                          │ │
│  │  ├── Animation triggers                                      │ │
│  │  └── User interaction tracking                               │ │
│  │                                                                │ │
│  └────────────────────────────────────────────────────────────────┘ │
│  ┌────────────────────────────────────────────────────────────────┐ │
│  │           Backend API Integration                             │ │
│  ├────────────────────────────────────────────────────────────────┤ │
│  │                                                                │ │
│  │  GET    /admin/ja/contents                                   │ │
│  │  POST   /admin/ja/contents                                   │ │
│  │  PUT    /admin/ja/contents/{id}                              │ │
│  │  DELETE /admin/ja/contents/{id}                              │ │
│  │                                                                │ │
│  │  GET    /admin/ja/categories, /tags, /menus                 │ │
│  │  GET    /admin/ja/themes/{slug}                              │ │
│  │  PUT    /admin/ja/themes/{slug}/settings                     │ │
│  │  GET    /admin/ja/builder/presets                            │ │
│  │                                                                │ │
│  └────────────────────────────────────────────────────────────────┘ │
│  ┌────────────────────────────────────────────────────────────────┐ │
│  │            Content Renderer (Frontend)                        │ │
│  ├────────────────────────────────────────────────────────────────┤ │
│  │                                                                │ │
│  │  BlockRenderer.vue                                            │ │
│  │  ├── Read blocks tree                                        │ │
│  │  ├── Evaluate conditions                                      │ │
│  │  ├── Resolve dynamic content                                 │ │
│  │  └── Render recursively                                      │ │
│  │                                                                │ │
│  │  BlockRegistry                                                │ │
│  │  ├── Map names to components                                 │ │
│  │  └── Lightweight (no instance creation)                      │ │
│  │                                                                │ │
│  └────────────────────────────────────────────────────────────────┘ │
└──────────────────────────────────────────────────────────────────────┘
```

## 2. State Flow Diagram

```
┌─────────────────────────────────────────────────────────────────────┐
│                    User Interaction                                  │
├─────────────────────────────────────────────────────────────────────┤
│                                                                     │
│  • Click block → selectModule(id)                                  │
│  • Edit field → updateModuleSetting(id, key, value)               │
│  • Drag block → blocks array reordered (vuedraggable)             │
│  • Save → handleSave() → builder.saveContent()                    │
│  • Undo → builder.undo()                                          │
│  • Switch page → setCurrentPage(pageId)                           │
│                                                                     │
└────────────────────┬────────────────────────────────────────────────┘
                     │
                     ↓
      ┌──────────────────────────────┐
      │   useBuilder() Methods        │
      ├──────────────────────────────┤
      │                              │
      │ • Update state variable       │
      │ • Mark dirty (isDirty=true)   │
      │ • Call takeSnapshot()         │
      │ • Emit @update event          │
      │                              │
      └──────────────┬───────────────┘
                     │
         ┌───────────┼───────────┐
         │           │           │
         ↓           ↓           ↓
   ┌────────┐  ┌──────────┐  ┌────────┐
   │ Vue    │  │ History  │  │ Modal  │
   │Reacti- │  │ System   │  │ State  │
   │vity    │  │          │  │        │
   └────┬───┘  └────┬─────┘  └───┬────┘
        │           │            │
        └─────┬─────┴────────────┘
              │
              ↓
      ┌─────────────────┐
      │ Canvas updates  │
      │ re-render       │
      └────────┬────────┘
               │
               ↓
      ┌───────────────────────┐
      │ @update event emitted │
      │ (to parent)           │
      └────────┬──────────────┘
               │
         ┌─────┴──────────┐
         │                │
         ↓                ↓
    ┌─────────┐    ┌──────────────┐
    │ Parent  │    │ Auto-save    │
    │ saves   │    │ (optional)   │
    │locally  │    │              │
    └─────────┘    └──────┬───────┘
                          │
                          ↓
                   ┌──────────────┐
                   │ API call     │
                   │ save to DB   │
                   └──────┬───────┘
                          │
                          ↓
                   ┌──────────────┐
                   │ Success      │
                   │ isDirty=false│
                   │ Toast shown  │
                   └──────────────┘
```

## 3. Block Hierarchy & Rendering

```
BLOCK TREE STRUCTURE:
═════════════════════

{
  id: 'section-1',
  type: 'section',
  settings: { backgroundColor: '#fff', ... },
  children: [
    {
      id: 'row-1',
      type: 'row',
      settings: { numCols: 3, ... },
      children: [
        {
          id: 'col-1',
          type: 'column',
          settings: { width: '33%', ... },
          children: [
            {
              id: 'btn-1',
              type: 'button',
              settings: { text: 'Click', color: '#0066cc', ... },
              children: []  // No children for leaf blocks
            }
          ]
        },
        // ... more columns
      ]
    }
  ]
}


RENDERING FLOW:
═══════════════

BlockRenderer
├─ For block in blocks[]
│  ├─ ConditionEvaluator.evaluate(block) → isVisible?
│  ├─ If visible:
│  │  ├─ resolveBlockSettings(block)
│  │  │  └─ Merge static + dynamic settings
│  │  ├─ getBlockComponent(block.type) → Component
│  │  ├─ Render:
│  │  │  <component :is="Component" v-bind="settings" />
│  │  ├─ BaseBlock wrapper applied
│  │  │  ├─ Background styles
│  │  │  ├─ Spacing/padding
│  │  │  ├─ Borders
│  │  │  └─ Interactions/animations
│  │  └─ If block.children.length > 0:
│  │     └─ <BlockRenderer :blocks="block.children" />
│  │        └─ [RECURSIVE CALL]
│  └─ If not visible: skip
└─ Done


NESTED EXAMPLE RENDER:
══════════════════════

<Section>
  <Row>
    <Column>
      <Button text="Click me" />
    </Column>
  </Row>
</Section>

Each level:
- Section wraps in <div class="section">
- Row wraps in <div class="row">
- Column wraps in <div class="column">
- Button renders actual <button> tag
```

## 4. History/Undo System

```
TIMELINE VISUALIZATION:
═══════════════════════

Initial State
    │
    ├─ [0] ◄─── historyIndex = 0
    │    Blocks = [section-1]
    │
User adds row
    │
    ├─ [1] ◄─── historyIndex = 1 (current)
    │    Blocks = [section-1 { children: [row-1] }]
    │
User edits button color
    │
    ├─ [2] ◄─── historyIndex = 2 (current)
    │    Blocks = [section-1 { ... { ... { color: #ff0000 } } }]
    │
User clicks undo
    │
    ├─ [1] ◄─── historyIndex = 1 (current)
    │    Blocks = [section-1 { children: [row-1] }]
    │
User clicks redo
    │
    ├─ [2] ◄─── historyIndex = 2 (current)
    │    Blocks = [section-1 { ... { ... { color: #ff0000 } } }]
    │
User edits again (new action)
    │
    │    [3]    (future discarded)
    │    [4]    (future discarded)
    │
    ├─ [3] ◄─── historyIndex = 3 (current, new branch)
    │    Blocks = [section-1 { ... { ... { color: #0000ff } } }]


CODE IMPLEMENTATION:
════════════════════

function takeSnapshot() {
  const snapshot = JSON.stringify(blocks.value)
  
  // Discard future if not at end
  if (historyIndex < history.length - 1) {
    history = history.slice(0, historyIndex + 1)
  }
  
  history.push(snapshot)
  historyIndex++
  
  // Limit to 50
  if (history.length > maxHistory) {
    history.shift()
    historyIndex--
  }
}

function undo() {
  if (historyIndex > 0) {
    historyIndex--
    blocks = JSON.parse(history[historyIndex])
  }
}

function redo() {
  if (historyIndex < history.length - 1) {
    historyIndex++
    blocks = JSON.parse(history[historyIndex])
  }
}
```

## 5. Responsive Design System

```
DEVICE BREAKPOINTS:
═══════════════════

Mobile       Tablet       Desktop
 (375px)     (768px)    (1200px)
    │           │           │
    └───────────┼───────────┘
            ↓ ↓ ↓


SETTINGS STRUCTURE:
═══════════════════

Block Settings:
{
  // Default value (applies to all if no override)
  "padding": "20px",
  
  // Device-specific overrides
  "padding_desktop": "30px",
  "padding_tablet": "20px",
  "padding_mobile": "10px",
  
  // Color: works on all devices
  "backgroundColor": "#ffffff",
  
  // Conditional (only applies if condition met)
  "display_mobile": "none"  // Hide on mobile
}


RESOLUTION LOGIC:
═════════════════

function getVal(settings, property, device) {
  // Priority: device-specific > default > undefined
  
  if (settings[`${property}_${device}`] !== undefined) {
    return settings[`${property}_${device}`]
  }
  
  if (settings[property] !== undefined) {
    return settings[property]
  }
  
  return undefined
}


RENDERING EXAMPLE:
══════════════════

Mobile (375px):
  padding: getVal(settings, 'padding', 'mobile')
  → settings.padding_mobile
  → "10px"

Tablet (768px):
  padding: getVal(settings, 'padding', 'tablet')
  → settings.padding_tablet
  → "20px"

Desktop (1200px):
  padding: getVal(settings, 'padding', 'desktop')
  → settings.padding_desktop
  → "30px"


DEVICE MODE OPTIONS:
════════════════════

Manual Mode (user clicks):
  device.value = 'mobile' | 'tablet' | 'desktop'

Auto Mode:
  Window resize detected
  device.value = resolved based on actual width
```

## 6. Conditional Logic Evaluation

```
VISIBILITY CONDITIONS:
══════════════════════

Block has:
{
  id: 'button-1',
  type: 'button',
  conditions: {
    visibility: {
      rules: [
        { type: 'device', value: ['desktop', 'tablet'] },
        { type: 'user_auth', value: true },
        { type: 'user_role', value: ['admin', 'editor'] }
      ],
      operator: 'AND'  // all must pass
    }
  }
}


EVALUATION FLOW:
════════════════

ConditionEvaluator.evaluate(block, context)
  │
  ├─ For each rule in conditions:
  │  │
  │  ├─ Rule: device visibility
  │  │  └─ Check: currentDevice in ['desktop', 'tablet']
  │  │     → TRUE
  │  │
  │  ├─ Rule: user authentication
  │  │  └─ Check: context.user.authenticated === true
  │  │     → TRUE
  │  │
  │  ├─ Rule: user roles
  │  │  └─ Check: 'admin' in context.user.roles
  │  │     → FALSE
  │  │
  │  └─ Continue...
  │
  ├─ Aggregate with operator:
  │  └─ AND: TRUE ∧ TRUE ∧ FALSE = FALSE
  │
  └─ Result: Block NOT rendered


FIELD VISIBILITY (BUILDER):
═══════════════════════════

Field:
{
  name: 'linkUrl',
  label: 'Link URL',
  showIf: {
    operator: 'and',
    conditions: [
      { field: 'hasLink', equals: true },
      { field: 'type', in: ['button', 'link'] }
    ]
  }
}

Evaluation:
  module.settings.hasLink === true
  AND
  module.settings.type in ['button', 'link']
  → Field is visible if both true


CONTEXT EXAMPLE:
════════════════

BlockRenderer provides context:
{
  user: {
    id: 123,
    authenticated: true,
    roles: ['admin'],
    name: 'John Doe'
  },
  page: {
    id: 456,
    slug: 'home',
    status: 'published'
  },
  query: {
    sort: 'date',
    filter: 'featured'
  },
  device: 'desktop',
  timestamp: 1674432000
}

Conditions can reference any of these.
```

## 7. Module Creation Process

```
SEQUENTIAL FLOW:
════════════════

1. User clicks "Add Block"
   │
   ├─ Modal/Menu opens
   └─ Shows 47+ block types

2. User selects "Button"
   │
   └─ Call: builder.insertModule('button', parentId, index)

3. insertModule() method
   │
   ├─ Get definition: ModuleRegistry.get('button')
   ├─ Create instance: ModuleRegistry.createInstance('button')
   │  │
   │  ├─ Generate ID: 'module-1674432000-abc123'
   │  ├─ Set type: 'button'
   │  ├─ Copy defaults from definition
   │  │  └─ settings = { text: 'Click me', color: '#0066cc', ... }
   │  └─ Initialize children array (empty for buttons)
   │
   ├─ Insert into tree
   │  └─ If parentId: parent.children.splice(index, instance)
   │  └─ Else: blocks.splice(index, instance)
   │
   ├─ takeSnapshot() → Add to history
   │
   └─ selectModule(instance.id) → Mark as selected

4. Vue reactivity triggers
   │
   ├─ Template updates
   ├─ New ModuleWrapper renders
   ├─ Canvas preview updates
   └─ Left panel shows new block

5. User sees new button selected
   │
   └─ Can immediately edit settings in right panel


CODE STRUCTURE:
═══════════════

const instance = ModuleRegistry.createInstance('button')
→ Returns:
{
  id: 'module-1674432000-abc123',
  type: 'button',
  settings: {
    text: 'Click me',
    color: '#0066cc',
    size: 'md',
    // ... 20+ other defaults
  },
  children: []  // Empty for leaf blocks
}

// Add to tree
blocks.push(instance)  // or parent.children.push(instance)

// Track in history
takeSnapshot()

// Mark as selected
selectModule(instance.id)
```

## 8. Save & Sync Flow

```
SAVE SEQUENCE:
══════════════

1. User clicks Save or Auto-save triggers
   │
   └─ emit('save') or auto-save interval

2. handleSave() in Builder.vue
   │
   ├─ builder.saveContent()
   └─ showToast('Saving...')

3. builder.saveContent() in useBuilder
   │
   ├─ Validate (content has ID)
   │
   ├─ Prepare payload:
   │  {
   │    id: 123,
   │    title: 'Home Page',
   │    slug: 'home',
   │    excerpt: '...',
   │    body: '',
   │    status: 'published',
   │    type: 'page',
   │    category_id: 5,
   │    blocks: [...full tree...],
   │    global_variables: {...},
   │    tags: [{id: 1}, {id: 2}],
   │    featured_image: 'url...',
   │    meta_title: '...',
   │    meta_description: '...',
   │    comment_status: true,
   │    // ... and more
   │  }
   │
   ├─ Call API:
   │  PUT /admin/ja/contents/123
   │
   └─ With prepared payload

4. Backend processes
   │
   ├─ Validate all fields
   ├─ Update database
   ├─ Return response
   └─ Response: { success: true, data: {...} }

5. On success
   │
   ├─ markAsSaved() → isDirty = false
   ├─ showToast('Saved successfully')
   └─ Clear auto-save timer

6. On error
   │
   ├─ Catch error
   ├─ showToast('Save failed: ' + error.message)
   └─ isDirty remains true (unsaved)


API PAYLOAD EXAMPLE:
════════════════════

{
  "id": 123,
  "title": "Home",
  "slug": "home",
  "excerpt": "Welcome",
  "body": "",
  "status": "published",
  "type": "page",
  "editor_type": "builder",
  "category_id": null,
  "featured_image": "https://...",
  "published_at": "2024-01-23T10:00:00Z",
  "meta_title": "Home - JA CMS",
  "meta_description": "Welcome to JA CMS",
  "meta_keywords": "cms,builder",
  "og_image": "https://...",
  "comment_status": true,
  "is_featured": true,
  "blocks": [
    {
      "id": "section-1",
      "type": "section",
      "settings": { ... },
      "children": [
        {
          "id": "row-1",
          "type": "row",
          "settings": { ... },
          "children": [ ... ]
        }
      ]
    }
  ],
  "global_variables": {
    "primary_color": "#0066cc",
    "company_name": "Example"
  },
  "tags": [1, 2, 3],
  "menu_item": {
    "add_to_menu": true,
    "menu_id": 1,
    "parent_id": null,
    "title": "Home"
  }
}
```

---

**Reference Diagrams for:**
- Architecture visualization
- Data flow & state management
- Block hierarchy & rendering
- History/undo mechanics
- Responsive design system
- Conditional logic evaluation
- Module creation process
- Save & synchronization

See [builder-system-audit.md](./builder-system-audit.md) for detailed explanations.
