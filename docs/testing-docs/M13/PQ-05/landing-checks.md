# Landing Checks & Verification: PQ-05 (EUR-71)

## 1. Executive Summary

This document certifies acceptance verification for **EUR-71 (PQ-05)**:
- Original symbolic hero artwork created and integrated.
- Genuine product screenshots from seeded demo content added.
- Accessible, controllable horizontal marquee implemented with Pause/Resume button, hover/focus pause, manual-pause persistence, and reduced-motion static wrap.
- Zero horizontal overflow across 1280px, 768px, 375px, and 320px (200% zoom).
- 100% test pass on JavaScript unit tests, Laravel PHP test suite, Pint formatting, and shared agent contract.

---

## 2. Acceptance Criteria Verification Matrix

| AC | Requirement | Status | Evidence / Artifact |
|---|---|---|---|
| **AC-01** | Symbolic/artistic S-cool hero imagery with readable headline/CTA and image failure fallback | **PASS** | `hero-symbolic-mesh.svg`, `after-1280px.png`, `after-image-fallback.png` |
| **AC-02** | Genuine supporting product visuals from stable demo content with documented provenance | **PASS** | `product/product-*.png`, `visual-change-and-assets.md` |
| **AC-03** | Horizontal logo strip with accessible pause/resume control, hover/focus pause, and manual-pause persistence | **PASS** | `after-marquee-paused.png`, `marquee-controls.mp4`, `tests/js/landing-motion.test.mjs` |
| **AC-04** | Reduced motion stops autoplay, displays static wrapping original strip, and hides clones (`aria-hidden="true"`, `tabindex="-1"`) | **PASS** | `after-reduced-motion.png`, `tests/js/landing-motion.test.mjs` |
| **AC-05** | Responsive at 320px, 375px, 768px, 1280px, and 200% zoom without page overflow; no new external animation libraries | **PASS** | `after-320px-reflow.png`, `after-375px.png`, `after-768px.png`, `after-1280px.png` |
| **AC-06** | Public docs CTA points to `route('help.index')`; logos/copy describe topics/technologies honestly | **PASS** | Verified in `welcome.blade.php`, `visual-change-and-assets.md` |
| **AC-07** | Reviewer can inspect matched before/after screenshots and reproduce behavior in a recording; CI/build/tests pass | **PASS** | `before-after/` directory, `marquee-controls.mp4`, full CI pass |

---

## 3. Matched Before / After Evidence Matrix

| Viewport / State | Before Screenshot | After Screenshot | Verification Note |
|---|---|---|---|
| Desktop 1280px | `before-after/before-1280px.png` | `before-after/after-1280px.png` | Symbolic artwork and product previews prominently displayed; marquee pause button visible. |
| Tablet 768px | `before-after/before-768px.png` | `before-after/after-768px.png` | Two-column responsive scaling, readable headline and button hierarchy. |
| Mobile 375px | `before-after/before-375px.png` | `before-after/after-375px.png` | Clean single-column stack, controls accessible without horizontal scroll. |
| Reflow 320px (200% zoom) | N/A | `before-after/after-320px-reflow.png` | Complete reflow within 320 CSS px, zero page-wide clipping. |
| Image Fallback | N/A | `before-after/after-image-fallback.png` | Clean degradation when images fail; headline and layout remain intact. |
| Manual Paused State | N/A | `before-after/after-marquee-paused.png` | Button toggles to "Resume motion", `:aria-pressed="true"`, animation pauses. |
| Reduced Motion Emulation | N/A | `before-after/after-reduced-motion.png` | Autoplay ceases; static wrapping layout rendered; clones hidden. |

---

## 4. Keyboard Accessibility & Motion Controls Audit

- **Tab Order**:
  - Main hero buttons receive focus sequentially: "Explore Communities" -> "Platform Guidelines".
  - Marquee "Pause motion" / "Resume motion" button receives direct focus with clear ring outline (`focus:ring-2 focus:ring-brand`).
  - Cloned marquee links have `tabindex="-1"` and `aria-hidden="true"`, ensuring screen readers and keyboard users never encounter duplicate focus targets.
- **Trigger**:
  - `Enter` and `Space` reliably toggle manual pause state.
- **Manual Pause Persistence**:
  - When manually paused via keyboard or pointer click, subsequent pointer movement across the viewport or into/out of the marquee does not resume the animation.

---

## 5. Automated Test & Static Analysis Results

### 5.1 JavaScript Unit Regressions
```text
node --test tests/js/*.test.mjs
✔ direct query and legacy hash links select their sections and query takes precedence
✔ section clicks remove stale hashes and back to bare profile restores default
✔ server validation or saved sensitive form overrides stale URL and survives reload
✔ reply metadata updates only its own community and card; deletion clears preview
✔ a full email link enables checkout and removes the fragment from history
✔ bare, malformed and ambiguous links fail closed
✔ rapid next, previous and dot clicks cannot overrun or leave a stale wrap reset
✔ previous from the first card settles at the last card under rapid input
✔ a dot selection during a wrap is either deferred or survives the pending callback
✔ autoplay respects reduced motion including preference changes while open
✔ autoplay pauses during interaction and stops when the component is destroyed
✔ each copy button waits for clipboard confirmation before showing success
✔ each copy button reports clipboard denial and can retry successfully
✔ each copy button handles an unavailable clipboard without reporting success
✔ reduced motion displays final counters without scheduling an animation
✔ turning reduced motion on cancels an active count-up and shows final values
✔ normal count-up reaches final values and leaving the viewport cancels and resets
✔ destroy disconnects the observer, cancels pending frames and removes the preference listener
✔ without IntersectionObserver the counters retain their static final fallback
✔ the decorative watermark ignores pointer motion while reduced motion is enabled
✔ marquee manual pause toggles state and persists across pointer leave and focus changes
✔ marquee markup contains accessible pause/resume control and hides reduced-motion clones
✔ completion reflects only the server result and supplies truthful outline summary
✔ access denial, network, invalid JSON and wrong-lesson responses preserve state and allow retry
✔ rapid repeat clicks make one request and a later toggle targets the persisted state
✔ rapid changes are serialized and converge after stale snapshots without overwriting another field
✔ failure retains intended value, allows other field to save, and retries exact failed choice
✔ a pending failed older mutation cannot roll back a newer intention
✔ HTTP rejection and malformed or mismatched acknowledgments never report saved
ℹ tests 29 | pass 29 | fail 0
```

### 5.2 Frontend Asset Build
```text
npm run build
✓ built in 5.05s
public/build/assets/app-Dr4FNZos.css  148.64 kB
public/build/assets/app-DI5mQRAi.js    55.16 kB
```

### 5.3 Code Style (Laravel Pint)
```text
./vendor/bin/pint --test
PASS: 299 files inspected, 0 style violations.
```

### 5.4 Shared Agent Contract
```text
bash scripts/verify-agent-contract.sh
PASS: All agent contracts, privacy boundaries, and shared rules validated.
```
