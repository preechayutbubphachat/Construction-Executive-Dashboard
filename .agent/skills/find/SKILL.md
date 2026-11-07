---
name: find
description: Comprehensive engineering search and code discovery skill. Use when asked to find, locate, search, discover, trace, or audit symbols, functions, classes, API routes, configurations, state flows, error origins, usages, or dependencies across any codebase.
---

# Find Skill (Engineering Search & Codebase Discovery)

## Overview

The **Find** skill equips an engineer/agent with systematic, high-precision search methodologies to navigate, trace, and inspect any codebase. Rather than guessing file structures or making assumptions, an engineer investigates code empirically across seven distinct search dimensions.

```
       ┌────────────────────────────────────────────────────────┐
       │                7 Search Dimensions                     │
       ├────────────────────────────────────────────────────────┤
       │ 1. Symbols & Signatures (functions, types, classes)    │
       │ 2. References & Call Sites (consumers, imports)        │
       │ 3. Routes & Endpoints (URL -> Handler -> DB)           │
       │ 4. State & Data Flow (mutations, dispatches, stores)   │
       │ 5. Config & Environment (env vars, build flags)        │
       │ 6. Error & Incident Tracing (error codes, logs)        │
       │ 7. File Tree & Structural Topology (globs, components) │
       └────────────────────────────────────────────────────────┘
```

---

## The LOCATE Methodology

Every search task follows the **LOCATE** framework:

1. **L - List Hypothesis**: Identify exactly what entity, pattern, or behavior you need to find (e.g., "Where is `scoreIntentQueueReducer` defined and where is it dispatched?").
2. **O - Optimize Scope**: Always exclude build artifacts, dependencies, and caches (`node_modules`, `dist`, `.git`, `coverage`, `.turbo`, `build`, `target`).
3. **C - Choose Dimension & Tool**: Select the right search strategy (word boundary regex, glob matching, AST/type tracing).
4. **A - Analyze Context**: Do not stop at the filename. Read the definition, parameter contracts, and surrounding business logic.
5. **T - Trace Dependencies**: Check both incoming references (callers, importers) and outgoing references (callees, dependencies).
6. **E - Expose Findings**: Present structured results with clickable file paths (`[file.ts#L10-L25](file:///...)`), concise snippets, and architectural context.

---

## 7 Search Dimensions & Playbooks

### Dimension 1: Symbols & Definitions (Functions, Classes, Types, Interfaces)

**Goal**: Find where a symbol is declared and its authoritative signature.

- **Regex Pattern**: `(?:export\s+(?:default\s+)?)?(?:function|class|interface|type|const|let|enum)\s+\b<SymbolName>\b`
- **Ripgrep Command**:
  ```bash
  rg -n --glob '!node_modules/**' --glob '!dist/**' "\b(function|class|interface|type|const)\s+<SymbolName>\b"
  ```
- **Node.js Script (Windows/Cross-platform)**:
  ```javascript
  const fs = require('fs'), path = require('path');
  function findSymbol(dir, symbolName) {
    const regex = new RegExp(`\\b(function|class|interface|type|const|let|enum)\\s+${symbolName}\\b`);
    // scan files recursively skipping node_modules, dist, .git
  }
  ```

### Dimension 2: References, Call Sites, & Impact Analysis

**Goal**: Find all places that import, call, instantiate, or override a symbol before refactoring or deleting.

- **Word Boundary Search**: Always use word boundaries (`\b`) to prevent partial matching (e.g. searching for `match` must not match `matchup`, `matchId`, `unmatched`).
- **Ripgrep Command**:
  ```bash
  rg -n --glob '!node_modules/**' --glob '!dist/**' "\b<SymbolName>\b"
  ```
- **Categorize Hits into**:
  1. *Imports / Exports*: `import { X } from "..."`
  2. *Call Sites / Instantiations*: `X(...)` or `new X(...)`
  3. *Type Annotations*: `: X` or `<X>`
  4. *Test Fixtures / Mocks*: `expect(...).toContain(X)`

### Dimension 3: Routes & API Endpoints (Vertical Slice Tracing)

**Goal**: Trace an HTTP or WebSocket route from frontend invocation down to the database or event log.

- **Frontend Invocation**: Look for `fetch(`, `axios.`, `api.`, or WebSocket `socket.emit(`.
- **Backend Route Definition**:
  - Express/Fastify/Koa: `app.get("/path"`, `fastify.post("/path"`, `router.route(`
  - Next.js/Remix: File-based routing in `app/api/.../route.ts` or `pages/api/...`
- **Trace Path**:
  $$\text{Frontend Action} \longrightarrow \text{API Client} \longrightarrow \text{Route Definition} \longrightarrow \text{Controller / Handler} \longrightarrow \text{Domain Service / Repository} \longrightarrow \text{DB / Event Store}$$

### Dimension 4: State Mutations & Data Flow

**Goal**: Locate where client or server state is defined, read, and mutated.

- **React / Redux / Zustand / Vuex**:
  - Search for state initializers: `useState(`, `useReducer(`, `createStore(`, `createSlice(`
  - Search for dispatch/action triggers: `dispatch({ type:`, `setState(`, `dispatchScoreQueue`
  - Search for side-effects: `useEffect(`, `useCallback(`, middleware hooks
- **WebSocket / Event Sinks**:
  - Search for event emitters: `emit(`, `broadcast(`, `on("projection.updated"`

### Dimension 5: Configurations, Environment Variables, & Feature Flags

**Goal**: Locate how runtime behavior is configured across environments.

- **Process Environment**: `process.env.<VAR_NAME>` or `import.meta.env.<VAR_NAME>`
- **Config Files**: `.env*`, `tsconfig.json`, `vite.config.*`, `docker-compose.yml`, `package.json`
- **Ripgrep Command**:
  ```bash
  rg -n "process\.env\.[A-Z0-9_]+|import\.meta\.env\.[A-Z0-9_]+"
  ```

### Dimension 6: Error Signatures & Incident Root Tracing

**Goal**: Pinpoint the exact line of code that produced an error code, exception, or validation failure.

- **Search by Reason Code**:
  - Example: Given error `SYNC_REQUIRED` or `CSRF_INVALID`, search for `reasonCode: "SYNC_REQUIRED"` or `code === "SYNC_REQUIRED"`.
- **Search by Error Message Template**:
  - Strip dynamic IDs: For `"Match match-123 is closed"`, search for `"is closed"` or `is closed`.
- **Search by Exception Class**:
  - `throw new ApiClientError` or `class DomainValidationError`.

### Dimension 7: File Tree & Structural Topology

**Goal**: Discover files by pattern, extension, or structural convention without scanning every line.

- **Locating Test Files for a Component**:
  - Pattern: `tests/**/<component-name>*.test.ts*` or `**/__tests__/<component-name>*`
- **Locating Stylesheets**:
  - Pattern: `apps/**/styles/**/*.css`
- **PowerShell / Bash Discovery**:
  ```powershell
  Get-ChildItem -Recurse -File -Include *<name>* -Exclude node_modules,dist,.git
  ```

---

## Fast Cross-Platform Discovery Snippets

### 1. High-Speed Regex Search (Node.js fallback for Windows / restricted sandbox)
When external tools like `ripgrep` are not pre-installed, execute this one-liner via `run_command`:
```javascript
node -e "
const fs = require('fs'), path = require('path');
function walk(dir) {
  let results = [];
  for (const entry of fs.readdirSync(dir, { withFileTypes: true })) {
    if (['node_modules', 'dist', '.git', 'coverage', '.turbo'].includes(entry.name)) continue;
    const full = path.join(dir, entry.name);
    if (entry.isDirectory()) results.push(...walk(full));
    else if (/\.(ts|tsx|js|jsx|json|css|md|yaml|yml)$/.test(entry.name)) {
      const content = fs.readFileSync(full, 'utf8');
      const lines = content.split('\n');
      lines.forEach((line, idx) => {
        if (/PATTERN_HERE/.test(line)) console.log(\`\${full}:\${idx + 1}: \${line.trim()}\`);
      });
    }
  }
  return results;
}
walk('.');
"
```

### 2. File Name Matcher
```javascript
node -e "
const fs = require('fs'), path = require('path');
function findFiles(dir, term) {
  for (const e of fs.readdirSync(dir, { withFileTypes: true })) {
    if (['node_modules', 'dist', '.git'].includes(e.name)) continue;
    const full = path.join(dir, e.name);
    if (e.isDirectory()) findFiles(full, term);
    else if (e.name.toLowerCase().includes(term.toLowerCase())) console.log(full);
  }
}
findFiles('.', 'TARGET_NAME');
"
```

---

## Senior Engineer Best Practices

1. **Verify Before Asserting**: Never tell the user "Function X is not implemented" without searching all subpackages and contracts first.
2. **Beware of Aliases & Re-exports**: In modern TypeScript/JS monorepos, check `index.ts` barrel files and `package.json` `exports` maps.
3. **Check Pluralization & Naming Conventions**:
   - `match` vs `matches`
   - `tournament` vs `tournaments`
   - `camelCase` vs `snake_case` vs `kebab-case`
4. **Trace the Full Lifecyle**: When auditing a feature, trace:
   - Where is data created?
   - Where is it stored/persisted?
   - Where is it rendered/displayed?
   - Where is it deleted/cleaned up?
5. **Always Output Clickable Markdown Links**: When reporting results to pair programmers, format all file references with file URL syntax: `[filename.tsx#L12-L34](file:///c:/path/to/filename.tsx#L12-L34)`.
