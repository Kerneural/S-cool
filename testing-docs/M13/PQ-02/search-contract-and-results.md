# PQ-02 (EUR-67) — Scoped Live Search with Word Matching & Typo Tolerance

## 1. Executive Summary & Verification Outcome
- **Linear Issue**: [EUR-67 (PQ-02)](https://linear.app/eurusdevsec/issue/EUR-67/pq-02-deliver-scoped-live-search-with-word-matching-and-typo-tolerance)
- **Branch**: `eur-67-scoped-live-search`
- **Verification Status**: **10/10 Tests Passed (59 assertions)**, Laravel Pint PASS (297 files), Agent contract PASS (`--require-tracked`).
- **Visual Evidence**: Captured across all 3 surfaces at 1:1 scale (no viewport zoom or distortion) for Desktop (1280x800), Tablet (768x1024), and Mobile (375x667).

---

## 2. Architecture & Normalization Pipeline

### 2.1 Search Normalization Engine (`App\Services\Search\SearchNormalizationService`)
- **Vietnamese Accent Folding**: Converts diacritics (`á, à, ả, ã, ạ, ă, ắ, ằ, ẳ, ẵ, ặ, â, ấ, ầ, ẩ, ẫ, ậ, đ, é, è...`) to base ASCII latin equivalents (`a, d, e...`) ensuring queries like `toan cao cap` match `Toán Cao Cấp`.
- **Case & Punctuation Folding**: Lowercases and strips disruptive punctuation while preserving alphanumerics.
- **Bounded Typo Distance**:
  - Distance: Levenshtein distance $\le 1$ for tokens $\ge 4$ characters.
  - Transposition: Detects adjacent letter swaps (`dokcer` $\leftrightarrow$ `docker`).
  - Short tokens ($\le 3$ chars) require exact prefix or substring to prevent noise.
- **Strict Relevance Scoring Hierarchy**:
  1. Exact normalized match: **100**
  2. Prefix match: **85**
  3. Continuous substring match: **75**
  4. Reordered token match (all query words present): **60**
  5. Typo match (transposition / Levenshtein 1): **40**
  6. Unrelated noise: **0** (Filtered out)
- **SQL Wildcard Escaping**: Sanitizes `%`, `_`, and `\` in query strings before passing to Eloquent `LIKE` queries, preventing unintentional full-table dumps or wildcard denial-of-service.

---

## 3. Surface Implementations

### Surface 1: Communities List (`GET /communities`)
- **Controller**: `CommunityController@index`
- **View**: `resources/views/communities/index.blade.php`
- **Mechanism**:
  - Pre-filters candidate records with bounded SQL query (max 50).
  - Scores candidates using `SearchNormalizationService::calculateScore()`.
  - Replaces database collection with `LengthAwarePaginator` maintaining pagination structure and query string preservation (`appends(request()->query())`).
  - Alpine.js 400ms debounce live search with clear search button and empty state handling.

### Surface 2: Classroom Courses Search (`GET /communities/{community}/classroom`)
- **Controller**: `ClassroomController@index`
- **View**: `resources/views/classroom/index.blade.php`
- **Mechanism**:
  - Added dedicated course search bar with Alpine.js debounce and clear link.
  - Enforces strict tenant boundary: `community_id = $community->id`.
  - Empty state informs users: `"No courses matched your query '{q}'. Try different keywords or clear the search."`

### Surface 3: Community Content Search & Typeahead
- **Livewire Component**: `App\Livewire\CommunitySearchTypeahead`
- **Full Page Controller**: `SearchController@index`
- **Mechanism**:
  - Livewire typeahead triggers on input, scores posts, courses, lessons, and members.
  - Ranks exact matches strictly ahead of approximate/typo matches.
  - Keyboard navigation ($\uparrow$, $\downarrow$, Enter, Esc) with deep link to full page search `/communities/{slug}/search?q={query}`.

---

## 4. Acceptance Criteria Verification Matrix

| AC | Requirement | Evidence | Status |
| :--- | :--- | :--- | :--- |
| **AC-01** | Surface 1 Communities list live search with typo & word-reorder | `ScopedLiveSearchFeatureTest::test_surface_1_communities_list_search_with_word_reordering_and_typo_tolerance` | **PASS** |
| **AC-02** | Surface 2 Classroom course search with tenant isolation | `ScopedLiveSearchFeatureTest::test_surface_2_classroom_course_search_with_ranking_and_tenant_isolation` | **PASS** |
| **AC-03** | Surface 3 Typeahead & full search with exact ranking ahead of approximate | `ScopedLiveSearchFeatureTest::test_surface_3_community_typeahead_and_full_page_search_with_ranking_and_typo` | **PASS** |
| **AC-04** | Typo tolerance bounded to Levenshtein <= 1 and adjacent transpositions for tokens >= 4 chars | `SearchNormalizationServiceTest::test_matches_token_with_bounded_typo_and_transposition` | **PASS** |
| **AC-05** | Case-folding and Vietnamese accent stripping equivalence | `SearchNormalizationServiceTest::test_handles_vietnamese_accent_and_no_accent_equivalence` | **PASS** |
| **AC-06** | Query performance bounds (max 50 candidates, SQL wildcard escaping) | `ScopedLiveSearchFeatureTest::test_input_validation_and_escapes_sql_wildcards_without_leakage` | **PASS** |
| **AC-07** | 1:1 scale browser screenshots across Desktop (1280), Tablet (768), and Mobile (375) | Saved in `testing-docs/M13/PQ-02/chrome-evidence/` and `testing-docs/EUR-67/` | **PASS** |

---

## 5. Visual Evidence Artifacts

| Screenshot Name | Surface & Viewport | Description |
| :--- | :--- | :--- |
| `surface1_desktop_1280_dokcer.png` | Surface 1 Desktop (1280x800) | Typo search `dokcer` matches "Docker Engineering" |
| `surface1_tablet_768_dokcer.png` | Surface 1 Tablet (768x1024) | Responsive layout on tablet |
| `surface1_mobile_375_dokcer.png` | Surface 1 Mobile (375x667) | Responsive layout on mobile |
| `surface1_desktop_1280_empty.png` | Surface 1 Desktop (1280x800) | Empty state with Clear search button |
| `surface2_desktop_1280_classroom_all.png` | Surface 2 Desktop (1280x800) | Classroom courses index with search bar |
| `surface2_desktop_1280_classroom_filtered.png` | Surface 2 Desktop (1280x800) | Classroom filtered by `dokcer` |
| `surface2_tablet_768_classroom_filtered.png` | Surface 2 Tablet (768x1024) | Classroom filtered on tablet |
| `surface2_mobile_375_classroom_filtered.png` | Surface 2 Mobile (375x667) | Classroom filtered on mobile |
| `surface2_desktop_1280_classroom_empty.png` | Surface 2 Desktop (1280x800) | Classroom empty state |
| `surface3_desktop_1280_typeahead_open.png` | Surface 3 Desktop (1280x800) | Typeahead dropdown open with ranked suggestions |
| `surface3_desktop_1280_fullsearch.png` | Surface 3 Desktop (1280x800) | Full page search tabs |
| `surface3_desktop_1280_fullsearch_courses.png` | Surface 3 Desktop (1280x800) | Full page search Courses tab result |
| `surface3_tablet_768_fullsearch.png` | Surface 3 Tablet (768x1024) | Full page search on tablet |
| `surface3_mobile_375_fullsearch.png` | Surface 3 Mobile (375x667) | Full page search on mobile |
