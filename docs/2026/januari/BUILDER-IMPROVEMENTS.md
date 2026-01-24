# Builder System - Implementation Checklist & Recommendations

## 📋 IMPLEMENTATION CHECKLIST

### Phase 1: Code Quality (Week 1-2)

#### TypeScript Migration
- [ ] Convert `useBuilder.js` to `useBuilder.ts`
  - [ ] Define BlockInstance interface
  - [ ] Define ModuleSettings interface  
  - [ ] Define HistorySnapshot interface
  - [ ] Type all state variables
  - [ ] Type all method parameters & returns
  
- [ ] Convert `ModuleRegistry.js` to TypeScript
  - [ ] Type Registry class
  - [ ] Type Module definition
  - [ ] Type instance creation
  
- [ ] Convert shared utilities
  - [ ] `styleUtils.ts`
  - [ ] `InteractionManager.ts`
  - [ ] `useResponsiveDevice.ts`

- [ ] Add tsconfig.json strict mode
  - [ ] Enable strict: true
  - [ ] Enable noImplicitAny
  - [ ] Enable strictNullChecks

#### Code Organization
- [ ] Split useBuilder() into 6 composables
  - [ ] `useBuilderState.ts` (data)
  - [ ] `useBuilderSelection.ts` (selection)
  - [ ] `useBuilderModules.ts` (CRUD)
  - [ ] `useBuilderHistory.ts` (undo/redo)
  - [ ] `useBuilderPages.ts` (page management)
  - [ ] `useBuilderSync.ts` (save/load)
  
- [ ] Create utility modules
  - [ ] `modules/` directory
  - [ ] Move path finding functions
  - [ ] Move clone utilities
  
- [ ] Consolidate API calls
  - [ ] Create `api/builder-service.ts`
  - [ ] Centralize endpoints
  - [ ] Standardize error handling

#### Documentation
- [ ] Add JSDoc to all public functions
  - [ ] Parameter descriptions
  - [ ] Return type descriptions
  - [ ] Usage examples for complex functions
  
- [ ] Create README files
  - [ ] `components/builder/README.md`
  - [ ] `components/content-renderer/README.md`
  - [ ] `shared/README.md`
  
- [ ] Document block creation process
  - [ ] Block definition schema
  - [ ] Component requirements
  - [ ] Settings structure example
  - [ ] Complete example (HelloBlock)

### Phase 2: Testing (Week 2-3)

#### Setup
- [ ] Install test dependencies
  - [ ] Vitest
  - [ ] Vue Test Utils
  - [ ] Happy-dom
  
- [ ] Configure test environment
  - [ ] `vitest.config.ts`
  - [ ] Coverage reporting (c8)
  - [ ] Test globals

#### Unit Tests (Priority)
- [ ] `core/ModuleRegistry.js`
  - [ ] [ ] register() adds definition
  - [ ] [ ] get() retrieves definition
  - [ ] [ ] createInstance() returns correct structure
  - [ ] [ ] generateId() uniqueness
  - [ ] [ ] getByCategory() filters correctly
  
- [ ] `shared/utils/styleUtils.js`
  - [ ] [ ] getVal() device resolution
  - [ ] [ ] getBackgroundStyles() generates CSS
  - [ ] [ ] getSpacingStyles() handles padding/margin
  - [ ] [ ] getBorderStyles() formats borders
  - [ ] [ ] getBoxShadowStyles() creates shadows
  - [ ] [ ] All responsive variants work
  
- [ ] Path finding utilities
  - [ ] [ ] findModuleById() locates correct module
  - [ ] [ ] getModulePath() builds breadcrumb
  - [ ] [ ] findParentById() returns parent or null

#### Integration Tests (Important)
- [ ] useBuilder composition
  - [ ] [ ] insertModule() adds & snapshots
  - [ ] [ ] updateModuleSetting() updates & marks dirty
  - [ ] [ ] removeModule() deletes from tree
  - [ ] [ ] undo/redo work correctly
  - [ ] [ ] Selection state changes
  
- [ ] History system
  - [ ] [ ] takeSnapshot() adds to history
  - [ ] [ ] undo() navigates backward
  - [ ] [ ] redo() navigates forward
  - [ ] [ ] Max history limit enforced
  - [ ] [ ] History clears on branch

#### Component Tests
- [ ] `FieldRenderer.vue`
  - [ ] [ ] Renders correct field component
  - [ ] [ ] showIf conditions work
  - [ ] [ ] Updates propagate to module
  - [ ] [ ] Responsive field works
  
- [ ] `Canvas.vue`
  - [ ] [ ] Blocks render correctly
  - [ ] [ ] Empty state displays
  - [ ] [ ] Drag & drop works
  
- [ ] `BaseBlock.vue`
  - [ ] [ ] Applies responsive styles
  - [ ] [ ] Interactions trigger
  - [ ] [ ] Selection state shows

#### E2E Tests (Core Flows)
- [ ] Content creation → save → verify
- [ ] Edit content → update → verify changes
- [ ] Undo/redo → save → verify persistence
- [ ] Page switching → data integrity
- [ ] Block duplicate → independence
- [ ] Responsive preview → breakpoint switching

### Phase 3: Performance (Week 3-4)

#### Optimization
- [ ] History system
  - [ ] [ ] Implement debounced snapshots
  - [ ] [ ] Reduce snapshot size (deltas)
  - [ ] [ ] Add compression (LZ-string)
  - [ ] [ ] Benchmark memory usage
  
- [ ] Rendering optimization
  - [ ] [ ] Implement virtual scrolling
  - [ ] [ ] Add computed memoization
  - [ ] [ ] Cache style calculations
  - [ ] [ ] Profile render time
  
- [ ] API optimization
  - [ ] [ ] Batch API calls
  - [ ] [ ] Add request debouncing
  - [ ] [ ] Implement polling strategy for presets

#### Monitoring
- [ ] Performance metrics
  - [ ] [ ] Track save latency
  - [ ] [ ] Monitor memory growth
  - [ ] [ ] Profile render times
  
- [ ] Error tracking
  - [ ] [ ] Setup Sentry integration
  - [ ] [ ] Log critical errors
  - [ ] [ ] Track API failures

### Phase 4: Security (Week 4)

#### Input Validation
- [ ] Install validation library (Zod or Valibot)
- [ ] [ ] Create validation schemas
  - [ ] [ ] BlockSettings schema
  - [ ] [ ] ContentMetadata schema
  - [ ] [ ] FieldValue schemas
  
- [ ] [ ] Add client-side validation
  - [ ] [ ] Field value validation
  - [ ] [ ] Required field checks
  - [ ] [ ] Pattern matching
  - [ ] [ ] Custom validators
  
- [ ] [ ] Sanitize inputs
  - [ ] [ ] Install DOMPurify
  - [ ] [ ] Sanitize rich text
  - [ ] [ ] HTML attribute filtering

#### API Security
- [ ] [ ] Add rate limiting (client-side)
- [ ] [ ] Add request signing (if needed)
- [ ] [ ] Audit CORS settings
- [ ] [ ] Verify CSRF protection

---

## 🎯 PRIORITY RECOMMENDATIONS

### Immediate (Do First)
| # | Task | Impact | Effort | Owner |
|---|------|--------|--------|-------|
| 1 | Add JSDoc to useBuilder | High | Low | Dev |
| 2 | Create block creation guide | High | Low | Tech Lead |
| 3 | Split useBuilder() | High | Medium | Dev |
| 4 | Add unit tests for utils | High | Medium | QA |
| 5 | Setup TypeScript (partial) | Medium | Medium | Dev Lead |

### Short Term (1 Month)
| # | Task | Impact | Effort | Owner |
|---|------|--------|--------|-------|
| 6 | Full TypeScript migration | High | High | Dev Team |
| 7 | Add integration tests | High | High | QA |
| 8 | Implement validation library | Medium | Medium | Dev |
| 9 | Optimize history system | Medium | Medium | Dev |
| 10 | Add performance monitoring | Medium | Low | DevOps |

### Medium Term (1-2 Months)
| # | Task | Impact | Effort | Owner |
|---|------|--------|--------|-------|
| 11 | Add E2E tests | Medium | High | QA |
| 12 | Virtual scrolling | Medium | High | Frontend |
| 13 | Content security fixes | High | Medium | Security |
| 14 | API rate limiting | Low | Low | Backend |
| 15 | Documentation updates | Medium | Medium | Tech Lead |

---

## 📊 TECHNICAL DEBT ASSESSMENT

### Critical (Fix Immediately)
```
❌ ISSUE: No input validation
   Impact: Security risk, data integrity
   Fix: Add Zod or Valibot validation
   Effort: 1-2 days
   Priority: HIGH

❌ ISSUE: HTML sanitization missing
   Impact: XSS vulnerability in rich text
   Fix: Integrate DOMPurify
   Effort: 0.5 days
   Priority: HIGH

❌ ISSUE: useBuilder is 1325 lines
   Impact: Hard to maintain, test, understand
   Fix: Split into 6 composables
   Effort: 2-3 days
   Priority: HIGH
```

### Important (Fix Soon)
```
⚠️  ISSUE: No unit tests
   Impact: Regression risks, slow debugging
   Fix: Add Vitest + utilities tests
   Effort: 3-4 days
   Priority: MEDIUM

⚠️  ISSUE: No TypeScript
   Impact: Runtime errors, poor DX
   Fix: Migrate to TypeScript
   Effort: 4-5 days
   Priority: MEDIUM

⚠️  ISSUE: History uses JSON.stringify
   Impact: Memory usage grows quickly
   Fix: Implement delta-based history
   Effort: 2-3 days
   Priority: MEDIUM
```

### Nice to Have (Polish)
```
✓ ISSUE: No virtual scrolling
  Impact: Performance on huge pages
  Fix: Implement v-virtual-scroll
  Effort: 2-3 days
  Priority: LOW

✓ ISSUE: Limited error handling
  Impact: Poor UX on failures
  Fix: Add comprehensive error handling
  Effort: 1-2 days
  Priority: LOW

✓ ISSUE: No API rate limiting
  Impact: Possible DoS vulnerability
  Fix: Add client-side debouncing
  Effort: 0.5 days
  Priority: LOW
```

---

## 🔧 SPECIFIC CODE IMPROVEMENTS

### 1. Extract useBuilderState

**Current:**
```javascript
// In useBuilder()
const blocks = computed({...})
const selectedModuleId = ref(null)
const hoveredModuleId = ref(null)
const activeTab = ref('content')
const device = ref('desktop')
// ... 20+ more
```

**Recommended:**
```javascript
// useBuilderState.ts
export function useBuilderState(initialData) {
  const blocks = computed({...})
  const selectedModuleId = ref(null)
  // ... UI state only
  
  return {
    blocks,
    selectedModuleId,
    // ... clean interface
  }
}
```

### 2. Add Input Validation

**Current:**
```javascript
function updateModuleSetting(id, key, value) {
  const module = findModuleById(blocks.value, id)
  if (module) {
    module.settings[key] = value  // No validation!
    takeSnapshot()
  }
}
```

**Recommended:**
```javascript
import { z } from 'zod'

// Define schema per block type
const buttonSettingsSchema = z.object({
  text: z.string().min(1),
  color: z.string().regex(/^#[0-9A-F]{6}$/i),
  size: z.enum(['sm', 'md', 'lg']),
  // ...
})

function updateModuleSetting(id, key, value) {
  const module = findModuleById(blocks.value, id)
  if (!module) return
  
  const schema = getSchemaForBlockType(module.type)
  
  try {
    const validated = schema.parse({
      ...module.settings,
      [key]: value
    })
    module.settings = validated
    takeSnapshot()
  } catch (error) {
    showError(`Invalid ${key}: ${error.message}`)
  }
}
```

### 3. Optimize History System

**Current:**
```javascript
function takeSnapshot() {
  const snapshot = JSON.stringify(blocks.value)
  history.push(snapshot)
  historyIndex++
  
  if (history.length > maxHistory) {
    history.shift()
    historyIndex--
  }
}
```

**Recommended:**
```javascript
// Delta-based history
function takeSnapshot() {
  const currentState = blocks.value
  const lastState = history[historyIndex]
  
  // Only store what changed
  const delta = computeDelta(lastState, currentState)
  
  if (delta.hasChanges) {
    // Compress delta
    const compressed = compressSnapshot(delta)
    
    // Debounce: only add if > 100ms since last
    clearTimeout(snapshotTimer)
    snapshotTimer = setTimeout(() => {
      history.push(compressed)
      historyIndex++
      enforceHistoryLimit()
    }, 100)
  }
}

function enforceHistoryLimit() {
  if (history.length > maxHistory) {
    const toRemove = history.length - maxHistory
    history.splice(0, toRemove)
    historyIndex -= toRemove
  }
}
```

### 4. Add Error Boundary

**Current:**
```javascript
async function saveContent() {
  try {
    const response = await api.put(...)
    return response.data
  } catch (error) {
    console.error(error)
    throw error
  }
}
```

**Recommended:**
```javascript
const handleApiError = (error, context) => {
  const message = error.response?.data?.message 
    || error.message 
    || 'Unknown error'
  
  logError(error, { context })
  showErrorToast(message)
  
  // Specific handling
  if (error.response?.status === 403) {
    // Permission denied
    redirectToLogin()
  } else if (error.response?.status === 409) {
    // Conflict
    showConflictDialog()
  }
}

async function saveContent() {
  try {
    const payload = preparePayload()
    validatePayload(payload)  // Add validation
    const response = await api.put(...)
    
    if (response.status >= 400) {
      throw new Error(`API returned ${response.status}`)
    }
    
    markAsSaved()
    showSuccessToast('Saved')
    return response.data
    
  } catch (error) {
    handleApiError(error, { operation: 'saveContent' })
    throw error
  }
}
```

### 5. Add TypeScript to Core Files

**Current:** `useBuilder.js`

**Convert To:**
```typescript
// types/builder.ts
export interface BlockInstance {
  id: string
  type: string
  settings: Record<string, any>
  children?: BlockInstance[]
}

export interface ModuleSettings {
  [key: string]: any
}

export interface Canvas {
  id: string
  title: string
  blocks: BlockInstance[]
  isMain: boolean
}

// composables/useBuilder.ts
import type { Canvas, BlockInstance } from '@/types/builder'

export function useBuilder(initialData: { blocks: BlockInstance[] } = { blocks: [] }) {
  const canvases = ref<Canvas[]>([...])
  
  function insertModule(type: string, parentId?: string): BlockInstance | null {
    const instance = ModuleRegistry.createInstance(type)
    if (!instance) return null
    // ...
    return instance
  }
  
  return {
    blocks: blocks as Ref<BlockInstance[]>,
    insertModule,
    // ... etc
  }
}
```

---

## 📈 PERFORMANCE TARGETS

| Metric | Current | Target | Method |
|--------|---------|--------|--------|
| Page Load | ~2s | <1.5s | Code split |
| Canvas Render | ~500ms | <300ms | Virtual scroll |
| Save Latency | ~1-2s | <1s | Batch requests |
| History Memory | 10MB+ | <5MB | Delta compression |
| Auto-save Calls | Every change | Every 3s | Debounce |
| Module Creation | ~50ms | <20ms | Memoization |

---

## ✅ SUCCESS CRITERIA

### Code Quality
- [ ] All critical functions have JSDoc
- [ ] useBuilder split into 6 composables
- [ ] No functions > 100 lines (except useBuilder splitting)
- [ ] Cyclomatic complexity < 5 per function

### Testing
- [ ] >80% code coverage (critical paths)
- [ ] All utils have unit tests
- [ ] Key flows have integration tests
- [ ] No failed E2E tests

### Performance
- [ ] Lighthouse score > 90
- [ ] Time to Interactive < 3s
- [ ] Cumulative Layout Shift < 0.1
- [ ] Memory usage < 50MB (normal pages)

### Security
- [ ] No XSS vulnerabilities
- [ ] Input validation on all fields
- [ ] HTML sanitization in place
- [ ] No hard-coded secrets

### Documentation
- [ ] Block creation guide complete
- [ ] API reference documented
- [ ] Architecture ADRs written
- [ ] Developer onboarding < 1 day

---

## 🚀 ROLLOUT PLAN

### Week 1: Setup
- [ ] Create feature branches
- [ ] Setup TypeScript tooling
- [ ] Add test infrastructure
- [ ] Document current state

### Week 2: Quality
- [ ] Add JSDoc
- [ ] Begin TypeScript migration
- [ ] Write unit tests (utils)
- [ ] Create documentation

### Week 3: Refactor
- [ ] Split useBuilder()
- [ ] Add integration tests
- [ ] Refactor error handling
- [ ] Performance optimization

### Week 4: Testing
- [ ] Add E2E tests
- [ ] Fix security issues
- [ ] Bug fixes
- [ ] Final review

### Week 5: Deployment
- [ ] Merge to main
- [ ] Deploy to staging
- [ ] QA verification
- [ ] Deploy to production

---

**Estimated Effort:** 4-5 weeks (1 developer)  
**Risk Level:** Medium (requires testing)  
**Business Impact:** High (stability, maintainability, security)

See [builder-system-audit.md](./builder-system-audit.md) for full architectural details.
