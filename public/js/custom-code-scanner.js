/**
 * GLOBAL BARCODE LISTENER
 * Detects pattern: ~| [CODE] |~
 * Intercepts keys, prevents typing, and auto-submits search.
 */
(function () {
    let isScanning = false;
    let scanBuffer = "";
    let lastKey = "";
    let scanTimeout;

    document.addEventListener('keydown', function (e) {
        // Ignore non-printable keys (like Shift, Control)
        if (e.key.length > 1 && e.key !== "Enter") return;

        const char = e.key;

        // 1. DETECT START SEQUENCE (~|)
        if (!isScanning) {
            if (lastKey === "~" && char === "|") {
                isScanning = true;
                scanBuffer = ""; // Reset buffer
                e.preventDefault(); // Stop the '|' from being typed
                // Note: The '~' has already been typed, but we will overwrite the input anyway.

                // Safety: Stop scanning if nothing happens for 2 seconds
                clearTimeout(scanTimeout);
                scanTimeout = setTimeout(() => { isScanning = false; }, 2000);
                return;
            }
            lastKey = char; // Remember key for next check
        }

        // 2. INTERCEPT KEYS WHILE SCANNING
        else {
            e.preventDefault(); // Stop chars from appearing on screen
            scanBuffer += char;

            // Reset timeout on every keypress
            clearTimeout(scanTimeout);
            scanTimeout = setTimeout(() => { isScanning = false; }, 2000);

            // 3. DETECT END SEQUENCE (|~)
            if (scanBuffer.endsWith("|~")) {
                // Remove the suffix '|~'
                let code = scanBuffer.slice(0, -2);

                // 4. VALIDATE (Length 1-30)
                if (code.length >= 1 && code.length <= 30) {
                    performGlobalSearch(code);
                }

                // Reset
                isScanning = false;
                scanBuffer = "";
            }

            // Safety: Abort if code gets too long (e.g., user is just typing ~| and continuing)
            if (scanBuffer.length > 35) {
                isScanning = false;
            }
        }
    });

    function performGlobalSearch(text) {
        const searchInput = document.getElementById('main-search-input');

        if (searchInput) {
            // Focus and overwrite value (clearing any stray '~' characters)
            searchInput.focus();
            searchInput.value = text;

            // Trigger Search (Simulate Enter Key)
            // This triggers any listeners attached to the input
            searchInput.dispatchEvent(new Event('input', { bubbles: true }));
            const enterEvent = new KeyboardEvent('keydown', {
                bubbles: true,
                cancelable: true,
                key: 'Enter',
                code: 'Enter'
            });
            searchInput.dispatchEvent(enterEvent);

            // Optional: If you wrap your input in a <form>, uncomment this:
            // if (searchInput.form) searchInput.form.submit();
        } else {
            console.warn("Search input #main-search-input not found.");
        }
    }
})();