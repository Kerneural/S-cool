# EUR-85 — Video protection and DRM research

- Issue: [EUR-85](https://linear.app/eurusdevsec/issue/EUR-85/m14-video-security-and-drm-research-note)
- Research revision: 2026-11-01
- Source baseline: `f3e87559f53c67e558b3470af18cee50820fde42`
- Scope: research only. No DRM provider, player dependency, billing account or deployment is introduced by this note. Publication and issue acceptance remain separate.

## Decision for this release

Ship M14 with honest **access-controlled playback**, not a promise of download prevention. S-cool's current 512-byte XOR masking is **not licensed DRM** and cannot stop an authorized viewer from recovering the video delivered to their browser. Do not market it as DRM or claim a percentage of prevented downloads.

If stronger protection is approved later, use one bounded **Mux DRM pilot** before production integration. Keep membership and course-entitlement decisions in Laravel; issue short-lived playback/license authorization only after those checks. The pilot must demonstrate the required browser/device matrix and actual cost before a commercial commitment.

This is a recommendation, not an approved implementation task or a provider purchase.

## Current S-cool behavior and its limits — AC-01

Source inspected:

- [SecureVideoStreamController](app/Http/Controllers/SecureVideoStreamController.php): temporary signed URL (15 minutes by default), signed viewer/community/optional lesson identifiers, verified-session binding, live community/lesson authorization, path validation and byte-range handling. It XORs at most the first 512 bytes and emits an octet-stream response.
- [Secure video player](resources/views/components/secure-video-player.blade.php): for direct MP4 playback, fetches the response into an ArrayBuffer, reverses the mask in JavaScript and creates a playable Blob URL. The demasking value reaches the client.
- [Stream authorization regression](tests/Feature/StreamAuthorizationRegressionTest.php): existing test surface for viewer binding, revoked membership and precise ranges. This note did not execute it.
- Third-party YouTube/Vimeo/Loom embeds use their provider paths; the direct-MP4 mask is not an additional DRM layer over those embeds.

The signed route is useful against unauthorized or expired access. User-Agent filtering, referrer/origin checks, hidden download controls and CSS suppression of extension elements are supplementary friction, not cryptographic rights enforcement: a legitimate client can control its requests and inspect the already delivered bytes.

The full-file ArrayBuffer/Blob approach increases startup download and memory cost with file size. The code establishes that risk; this research contains no measured crash threshold or performance benchmark. Segmented playback can improve delivery and buffering, but does not by itself prevent copying.

A signed route is also insufficient if its source file remains directly reachable through a static URL. The current stream reads the public storage disk. Before describing a deployed site as access-controlled, verify direct-file requests through the actual web server/CDN cannot bypass authorization. This note does not claim that host check passed or change storage/deployment configuration.

## Protection comparison

| Approach | Useful protection | Remaining limits |
| --- | --- | --- |
| Signed URLs plus live application authorization | Limits who can request content and for how long; can bind playback to the permitted viewer/resource | Authorized clients still receive media bytes. URL expiry cannot erase bytes already delivered; direct origin paths must also be protected. |
| Current XOR mask and browser demasking | Makes a raw response less immediately playable by simple tools | Mask and reversal occur on the client. It is obfuscation, not a protected decryption environment or licensed DRM. |
| Licensed DRM using Widevine, FairPlay and/or PlayReady | Encrypted media with license-controlled decryption; protection strength depends on the supported device and selected policy | Not universally hardware-backed. Capture/output restrictions vary by platform, security level and player. It does not prevent an external camera, account sharing or every determined extraction scenario. |

EME supplies browser APIs for protected-media playback, not a universal rights policy or an assertion that every client uses secure hardware. See the [W3C EME specification](https://www.w3.org/TR/encrypted-media/). Simple HLS encryption or the name “DRM” in a product tier is not enough to establish licensed device-backed protection.

## Provider facts checked for this review

| Provider | Verified distinction | Consequence for S-cool |
| --- | --- | --- |
| Mux | Its guide documents Widevine, FairPlay and PlayReady, signed authorization and differing device protection levels. Apple playback requires the customer's FairPlay onboarding/certificate; Mux handles the Widevine/PlayReady certificates. | Recommended future pilot: managed packaging/player/license integration, with Apple prerequisites and compatible devices verified first. |
| Bunny Stream | MediaCage Basic uses clear-key protection; MediaCage Enterprise uses Widevine/FairPlay. Its documentation explicitly excludes Widevine L3 from screen-grab protection. | The earlier “Bunny has no DRM” statement was incorrect. Compare the Enterprise tier if a later quote changes the recommendation. |
| Cloudflare Stream | Its security guide documents requiring signed tokens for otherwise publicly addressable videos. | A useful managed access-control/delivery option. That guide is not evidence for an available licensed-DRM Enterprise tier; no such tier is claimed here. |

Primary documentation: [Mux DRM](https://www.mux.com/docs/guides/protect-videos-with-drm), [Bunny MediaCage](https://bunny.net/docs/stream/drm), [Cloudflare signed playback](https://developers.cloudflare.com/stream/viewing-videos/securing-your-stream/).

Browser DRM support must be verified against the chosen provider's current matrix. Do not assume “Edge means PlayReady,” “all DRM is hardware-backed,” or “all three systems are mandatory on every site.” The required systems follow the supported devices and license policy.

## Recommended follow-up and cost questions — AC-02

Create a separately approved pilot using **one rights-cleared sample video and synthetic learner accounts**:

1. Prove allowed playback and deny a guest, an expired/revoked member and a learner lacking the paid-course entitlement. Test direct media/license requests too.
2. Check the actual supported Chrome, Edge, Safari/iOS and Android devices required for handover; record failures and security levels. Do not promise universal screen-recording prevention.
3. Exercise startup, seek, long playback/token renewal, retry and device restrictions. Keep private source files inaccessible outside the authorized delivery route.
4. Measure storage, delivered viewing minutes/bytes and license requests. Do not equate one learner or one video with one billable license. Test export/removal and the rollback path.
5. Record the result and obtain owner approval before production integration. Never publish playback tokens, license keys, certificates or real learner identifiers in evidence.

Resolve these commercial and operational inputs before committing:

- Expected stored hours, rendition quality/bitrate, delivered viewing minutes, regions, concurrent devices and license renewal behavior.
- Storage, encoding, delivery, DRM base/license fees, minimum charges, taxes and any account/onboarding prerequisites. Use a dated quote or current calculator, not a fixed monthly price invented from learner count.
- Who owns and pays the provider/Apple accounts after handover, who manages credentials, and how one expert deployment exports or deletes its assets.
- Required privacy treatment for identifiers/watermarks and the user experience on unsupported devices. Do not add phone numbers or claim a watermark prevents copying.

Official pricing inputs for a future dated estimate: [Mux video pricing](https://www.mux.com/pricing/video), [Bunny Stream pricing](https://bunny.net/docs/stream/pricing), [Cloudflare Stream pricing](https://developers.cloudflare.com/stream/pricing/). Rates are intentionally not copied here; different providers bill different units and may charge separate DRM fees.

## Evidence boundary

This audit corrects the research against the source baseline and the linked primary documentation. It makes no claim of a working DRM integration, device/capture test, successful host media-isolation check, cost benchmark or complete M14 acceptance. The original absolute guarantees, unsupported effectiveness percentages, personal machine links and unsourced cost totals have been removed.
