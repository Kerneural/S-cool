# EUR-68 — Independent technical catalog review

Review: 2026-11-02. Accepted application: `8a0982b1f3a374196354421c0887c7a933fb4174`.
Original PR #87: `fa694f4ee6f9f78b7825134b845edfee5e0af129`.
The previous all-PASS curriculum report is superseded by the source-backed findings below.

## Evidence classification

The submitted subnet calculation, IAM JSON, Dockerfile and Pydantic fragments are
illustrative material. No named independent run receipt, command output, fixture,
timestamp or exact actual lesson identity accompanies them. They are **not accepted
peer execution evidence**. In particular the Pydantic fragment alone cannot prove
an HTTP 422 response; it defines no HTTP endpoint.

## Reproducible peer tasks still required

| Actual course | Representative task from actual lesson | Required observable receipt |
|---|---|---|
| Networking Foundations: From Packet to Production | IPv4 Addressing and Subnetting: calculate /24, /26, /28 and non-overlapping team ranges | Submitted network/broadcast/host table plus independent subnet-tool check |
| AWS Cloud Foundations and Well-Architected Thinking | IAM and Least Privilege: compare allowed/denied actions | Task-specific policy and actual bounded simulation/sandbox result; no paid setup required |
| Docker Engineering: Containers to Production | Write an Efficient Dockerfile | Actual Dockerfile, build/digest/size and cache result in an isolated approved lab |
| Applied AI Engineering: Data to Reliable Service | Establish a Reproducible Baseline | Dataset/version, command/config, baseline metric table and rerun comparison |

Record the actual unaided attempt, wrong turn/correction and retest against the
delivered lesson revision. A static solution or classroom screenshot does not replace
this requirement. Do not create new lessons just to match the obsolete exercise titles.
