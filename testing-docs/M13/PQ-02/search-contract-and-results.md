# EUR-67 — Search contract and current evidence

2026-10-25 candidate: `eur-67-69-71-acceptance`, base `b24451869a6aefbbd14c0275cbe71cfee0a50dae` plus working diff. Earlier screenshots in this directory are historical submission artifacts, not verification of this follow-up.

## Implemented corrections

- Communities, Classroom and full content search use a progressive GET enhancement: 300 ms debounce, AbortController and a sequence guard invalidated immediately on new input. Results/tabs update without replacing the focused form.
- The form is captured at Alpine initialization; input event `$el` is not assumed to be the form.
- Query/filter/type stay in the URL. Tab/filter/page clicks and history updates use the server-rendered response. No client search index or tenant authorization is introduced.
- A network/server error is visible and retryable instead of being presented as no matches. Access denial/login redirect removes previously rendered private result snippets.
- Authorized SQL candidates are ordered before the existing limits (50 full-page, 20 typeahead), then scored for exact/prefix/token/typo relevance with stable ties. These limits bound hydrated records, not total SQL rows scanned. There is no claim of exhaustive fuzzy recall outside the candidate window.
- Typeahead eager-loads post category and course entitlement state rather than querying them for each rendered result.

## Measured probe

`SearchMeasurementTest` ran on isolated MySQL `scool_test`, PHP 8.2.35, one community with 10 and then 200 courses. The first Docker course has explicit order 0; other matching courses have order 1 so equal typo scores have a deterministic fixture order. Query was `dokcer`.

| Surface | Queries at 10 / 200 courses | Observed elapsed ms at 10 / 200 | SQL candidate cap |
|---|---|---|---|
| Communities | 1 / 1 | 639.20 / 53.24 | 50 |
| Classroom | 14 / 14 | 512.23 / 113.26 | 50 |
| Full content | 11 / 11 | 98.71 / 53.02 | 50 per content kind |
| Typeahead | 9 / 9 | 154.10 / 22.04 | 20 per content kind |

Probe passed 1 test / 30 assertions. Timings include test harness/template warm-up and are observations, not load-test latency or a production SLA. The flat query counts reject this fixture's per-result-query regression; they do not prove indexed access or optimal query plans.

## Browser evidence and acceptance limits

On canonical `http://127.0.0.1:8080`, Chrome displayed Docker Engineering for live `dokcer`, retained the q input focus and updated the URL. Rapid input changed Python results back to Docker results. Classroom search accepted Enter, typeahead displayed scoped suggestions, and full content search displayed its no-results state before recovering and switching to Courses. These are bounded smoke observations, not a complete keyboard/history/network matrix.

Nine `chrome-evidence/followup-{communities,classroom,content}-{375,768,1280}.png` captures cover the three surfaces. Observed document scroll widths were no larger than the requested CSS viewport at each size. The community workspace navigation remains locally horizontally scrollable on the narrow screenshot. These captures are tied to the follow-up working diff; earlier screenshots remain historical submission artifacts.

Browser use was interrupted and then recovered; a later Windows browser-URL safety check stopped computer use before real Chrome 200% page zoom, actual network/offline testing, playback and recording could finish. Unit race/error checks are not substitutes for those observations. EUR-67 remains subject to these AC and normal CI/review gates.

## Regression verification

An explicit file list was necessary on this Windows Docker bind mount: default PHPUnit directory discovery found only 64 tests, while the complete Git-backed inventory found 640. The complete run finished with 639 passing and one keyboard selection regression (6,135 assertions, 550.896 seconds). Candidate ordering had reversed equally ranked posts. The fix retains newest-first post ties without dropping SQL relevance priority. All affected search/normalization and safe-catalog tests then passed: 29 tests, 236 assertions, 30.544 seconds. The full 640-test suite has not been rerun after this narrow correction; remote CI is a separate required gate.

The final JavaScript suite passed 34 tests; production build passed with existing font URL warnings. A real canonical-origin font request returned HTTP 200. Scoped Pint passed 16 files, explicit PHPStan analysis of all 127 app/routes PHP files reported no errors before the final tie-direction correction, and the agent contract/whitespace checks passed. These results do not establish all issue acceptance criteria.
