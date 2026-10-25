# EUR-71 — Landing acceptance evidence

2026-10-25 follow-up: base `b24451869a6aefbbd14c0275cbe71cfee0a50dae` plus `eur-67-69-71-acceptance` working diff. This replaces the prior blanket PASS certification. Existing images/recording under this directory belong to the submitted implementation; they do not establish the final follow-up's browser results.

## Current implementation

The symbolic SVG, product screenshot layout and existing motion design are retained. Hero carousel teardown and reduced-motion logic remain. Marquee supports manual pause, hover/focus pause and static original items with hidden clones for reduced motion. The help CTA targets the existing public Help route.

Follow-up copy removes unsupported customer success stories and adoption counters, labels scenarios as examples, and avoids calling creator-reviewed practical work a verified certification. The third product image is a leaderboard; it is not proof of a credential/submission workflow.

## Evidence boundaries

JavaScript regressions exercise motion state, teardown and reduced-motion preference changes. They do not establish actual browser animation, keyboard focus, image failure rendering or reflow. A 320 CSS px screenshot is not evidence that Chrome page zoom was set to 200%.

Pending on this exact candidate: matched 320/375/768/1280 screenshots; real Chrome 200% page zoom; keyboard manual-pause persistence; reduced-motion toggle while open; image failure fallback; teardown after navigation; actual motion recording. Browser use was disabled before this pass could complete.

Artwork provenance and third-party logo/media permissions need an explicit review record. Nominative fair use is not asserted as a settled license. Owner visual acceptance and the integrated M13 UAT gate remain pending; merged code and unit tests alone do not close them.
