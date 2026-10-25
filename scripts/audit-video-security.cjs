/**
 * S-cool Automated Video Security & Anti-Sniffing Audit Script
 * 
 * Simulates real-world attack vectors:
 * 1. Direct unauthenticated curl/wget/IDM sniffing
 * 2. Referer / Origin header tampering
 * 3. User-Agent spoofing (IDM, CocoCut, VideoDownloadHelper, yt-dlp)
 * 4. Direct address-bar document navigation (Sec-Fetch-Dest)
 * 5. Media MIME leakage detection (Must NEVER return video/*)
 * 6. Binary container obfuscation (XOR MP4 header scrambling)
 * 7. In-memory demasking & playback integrity verification
 */

const http = require('http');
const https = require('https');
const crypto = require('crypto');
const { execSync } = require('child_process');

async function runAudit() {
    console.log('===============================================================');
    console.log('   S-COOL SECURE VIDEO STREAMING PENETRATION AUDIT REPORT      ');
    console.log('===============================================================');

    // 1. Generate valid signed URL from docker container
    let signedUrl;
    try {
        const cmd = `docker exec scool_app php artisan tinker --execute="echo \\App\\Http\\Controllers\\SecureVideoStreamController::signedUrl('videos/sample.mp4');"`;
        signedUrl = execSync(cmd, { encoding: 'utf-8' }).trim();
    } catch (e) {
        console.error('Failed to generate test signed URL:', e.message);
        process.exit(1);
    }

    const parsedUrl = new URL(signedUrl);
    const host = parsedUrl.hostname === 'localhost' ? '127.0.0.1' : parsedUrl.hostname;
    const port = parsedUrl.port || 80;
    const path = parsedUrl.pathname + parsedUrl.search;
    const appBase = `http://${host}:${port}`;

    let passCount = 0;
    let failCount = 0;

    function assertCheck(name, condition, details = '') {
        if (condition) {
            console.log(`[PASS] ${name}`);
            passCount++;
        } else {
            console.error(`[FAIL] ${name} -> ${details}`);
            failCount++;
        }
    }

    // Helper to make HTTP requests
    function makeRequest(options) {
        return new Promise((resolve) => {
            const req = http.request({
                hostname: host,
                port: port,
                path: options.path || path,
                method: options.method || 'GET',
                headers: options.headers || {}
            }, (res) => {
                const chunks = [];
                res.on('data', chunk => chunks.push(chunk));
                res.on('end', () => {
                    resolve({
                        statusCode: res.statusCode,
                        headers: res.headers,
                        body: Buffer.concat(chunks)
                    });
                });
            });
            req.on('error', (err) => resolve({ error: err.message }));
            req.end();
        });
    }

    console.log('\n--- 1. DIRECT & EXTERNAL ACCESS PREVENTION ---');

    // Test 1.1: Unsigned Request
    const res1 = await makeRequest({ path: '/secure-stream/video?path=videos/sample.mp4' });
    assertCheck('Unsigned request is rejected with 403 Forbidden', res1.statusCode === 403);

    // Test 1.2: Missing Referer
    const res2 = await makeRequest({ headers: {} });
    assertCheck('Signed request without Referer/Origin is rejected with 403', res2.statusCode === 403);

    // Test 1.3: Malicious External Referer
    const res3 = await makeRequest({ headers: { 'Referer': 'https://pirate-downloader-site.com/steal' } });
    assertCheck('Malicious external Referer is rejected with 403', res3.statusCode === 403);

    // Test 1.4: Direct Address Bar Navigation (Sec-Fetch-Dest: document)
    const res4 = await makeRequest({
        headers: {
            'Referer': `${appBase}/classroom`,
            'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/120.0.0.0 Safari/537.36',
            'Sec-Fetch-Dest': 'document'
        }
    });
    assertCheck('Direct address-bar navigation (Sec-Fetch-Dest: document) is rejected with 403', res4.statusCode === 403);

    console.log('\n--- 2. DOWNLOADER & SNIFFER USER-AGENT REJECTION ---');

    const downloaders = [
        { name: 'Internet Download Manager (IDM)', ua: 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Internet Download Manager 6.38' },
        { name: 'CocoCut Video Downloader', ua: 'CocoCut/2.4 ChromeExtension' },
        { name: 'Video DownloadHelper', ua: 'VideoDownloadHelper/7.6.0' },
        { name: 'yt-dlp', ua: 'yt-dlp/2024.08.01' },
        { name: 'cURL', ua: 'curl/7.88.1' },
        { name: 'Wget', ua: 'Wget/1.21.3' },
    ];

    for (const d of downloaders) {
        const res = await makeRequest({
            headers: {
                'Referer': `${appBase}/classroom`,
                'User-Agent': d.ua
            }
        });
        assertCheck(`Downloader agent [${d.name}] is rejected with 403`, res.statusCode === 403);
    }

    console.log('\n--- 3. ANTI-SNIFFING MIME & CONTAINER OBFUSCATION ---');

    // Test 3.1: Valid Browser Fetch
    const validRes = await makeRequest({
        headers: {
            'Referer': `${appBase}/classroom/lesson-1`,
            'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/120.0.0.0 Safari/537.36',
            'X-Requested-With': 'XMLHttpRequest'
        }
    });

    assertCheck('Legitimate browser fetch returns HTTP 200 OK', validRes.statusCode === 200);

    // Assert Content-Type is NEVER video/*
    const contentType = validRes.headers['content-type'] || '';
    assertCheck('Content-Type is application/octet-stream (Zero video/* MIME leakage)', contentType.includes('application/octet-stream') && !contentType.includes('video'));

    // Assert security headers
    assertCheck('X-Content-Type-Options: nosniff is enforced', validRes.headers['x-content-type-options'] === 'nosniff');
    assertCheck('Content-Disposition is inline; filename="stream.bin"', (validRes.headers['content-disposition'] || '').includes('stream.bin'));

    // Test 3.2: MP4 Container Scrambling
    const streamBytes = validRes.body;
    assertCheck('Received stream payload > 0 bytes', streamBytes && streamBytes.length > 512);

    // An authentic MP4 container begins with atom type 'ftyp' at bytes 4..7 (ASCII: 0x66 0x74 0x79 0x70)
    const ftypAscii = streamBytes.slice(4, 8).toString('ascii');
    assertCheck('Stream header is scrambled (Plain ftyp box is NOT present)', ftypAscii !== 'ftyp', `Found: "${ftypAscii}"`);

    // Test 3.3: Demasking Verification
    let maskKey;
    try {
        const cmd = `docker exec scool_app php artisan tinker --execute="echo \\App\\Http\\Controllers\\SecureVideoStreamController::getMaskKey('videos/sample.mp4');"`;
        maskKey = execSync(cmd, { encoding: 'utf-8' }).trim();
    } catch (e) {
        maskKey = '';
    }

    if (maskKey) {
        const demaskedHeader = Buffer.from(streamBytes.slice(0, 512));
        for (let i = 0; i < 512; i++) {
            demaskedHeader[i] ^= maskKey.charCodeAt(i % maskKey.length);
        }
        const restoredFtyp = demaskedHeader.slice(4, 8).toString('ascii');
        assertCheck('In-memory XOR demasking restores authentic MP4 container ftyp box', restoredFtyp === 'ftyp', `Restored: "${restoredFtyp}"`);
    }

    console.log('\n--- 4. HTTP 206 PARTIAL CONTENT (RANGE REQUESTS) ---');

    const rangeRes = await makeRequest({
        headers: {
            'Referer': `${appBase}/classroom/lesson-1`,
            'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/120.0.0.0 Safari/537.36',
            'Range': 'bytes=0-499'
        }
    });

    assertCheck('Range request returns HTTP 206 Partial Content', rangeRes.statusCode === 206);
    assertCheck('Range Content-Range header present', (rangeRes.headers['content-range'] || '').startsWith('bytes 0-499/'));
    assertCheck('Range Content-Length is exactly 500 bytes', rangeRes.headers['content-length'] === '500');

    console.log('\n===============================================================');
    console.log(`   AUDIT SUMMARY: ${passCount} PASSED, ${failCount} FAILED`);
    console.log('===============================================================\n');

    if (failCount > 0) {
        process.exit(1);
    }
}

runAudit();
