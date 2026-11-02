# EUR-68 — Independent technical catalog review

Review: 2026-11-02. Accepted application: `8a0982b1f3a374196354421c0887c7a933fb4174`.
Original PR #87: `fa694f4ee6f9f78b7825134b845edfee5e0af129`.
The previous all-PASS curriculum report is superseded by the source-backed findings below.

## Actual source catalog

| Community | Course | Sections | Lessons |
|---|---|---:|---:|
| networking-learning | Networking Foundations: From Packet to Production | 3 | 6 |
| cloud-master | AWS Cloud Foundations and Well-Architected Thinking | 4 | 8 |
| docker-engineering | Docker Engineering: Containers to Production | 4 | 8 |
| ai-engineer | Applied AI Engineering: Data to Reliable Service | 4 | 8 |

All thirty actual lesson identities and the four authored learning-content fields are
recorded in [lesson-audit.csv](lesson-audit.csv). The former report described a different
syllabus: TLS handshakes, RDS failover and FastAPI/Pydantic lessons it named do not match
these source titles. Do not add those lessons or change the accepted application merely
to make the report appear correct.

## AC disposition

- AC-01: source identity mapping corrected for all four courses / thirty lessons.
- AC-02: objectives, short explanation, practice task and evidence criteria exist.
  Dedicated worked examples and per-lesson references are absent from the current
  generic lessonContent output. Audience/prerequisites are not explicit per course.
  These are content gaps, not completed work.
- AC-03: no independently attributable execution record ties the submitted four
  practice examples to the actual lesson text/revision. No peer PASS is certified.
- AC-04: local asset presence does not establish copyright/provenance. References,
  link usability and media-rights review remain unverified.
- AC-05: existing sync preservation tests pass; a second teammate receipt and an
  existing managed-content upgrade protocol for Group A remain unverified.
- AC-06: www-data reads 8/8 Group A community/course covers. The submitted classroom
  screenshots are historical observations, not proof of every lesson, progress,
  practical review, locked access, keyboard, zoom or playback.
- AC-07: this corrected per-lesson audit supplies specific gaps; issue remains
  incomplete until corrections and affected acceptance are actually delivered.

The owner excluded five reference communities (862 lessons) from content acceptance
on 2026-11-02. This does not waive the thirty Group A lessons above.
