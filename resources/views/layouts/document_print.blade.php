<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>{{ $document->name }} - Print</title>

    {{-- 1. Include JsBarcode Library --}}
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>

    <link href="{{ asset('css/editor.css') }}" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">

    @php
        $paper = $content['paper'] ?? [];
        $pWidth = $paper['w'] ?? 794;
        $pHeight = $paper['h'] ?? 1123;
        $bgImage = $content['bgImage'] ?? '';

        // Calculate Page Size for CSS (Convert px to mm for accuracy)
        $mmW = isset($paper['mmW']) ? $paper['mmW'] : ($pWidth * 0.264583);
        $mmH = isset($paper['mmH']) ? $paper['mmH'] : ($pHeight * 0.264583);
        $cssSize = round($mmW) . 'mm ' . round($mmH) . 'mm';
    @endphp

    <link href="{{ asset('css/document_print.css') }}" rel="stylesheet">

    <style>
        /* Force SVG to fit the text container */
        .barcode-svg {
            width: 100% !important;
            height: 100% !important;
            display: block;
        }
    </style>
</head>

<body>

    <div class="no-print" style="position: fixed; top: 20px; right: 20px; z-index: 9999;">
        <button onclick="window.print()" class="btn-print">
            <i class="fa-solid fa-print"></i> Print
        </button>
        <button onclick="window.close()" class="btn-close-page">
            <i class="fa-solid fa-xmark"></i>
        </button>
    </div>

    <div id="page-container"
        style="width: {{ $pWidth }}px; height: {{ $pHeight }}px; background-image: url('{{ $bgImage }}');">

        @foreach($elements as $el)
            @php
                // Use Percentage Positioning
                $style = "left: {$el['x']}%; top: {$el['y']}%; width: {$el['w']}%; height: {$el['h']}%; z-index: 10;";

                if (isset($el['style']) && is_array($el['style'])) {
                    foreach ($el['style'] as $key => $val) {
                        $kebab = strtolower(preg_replace('/(?<!^)[A-Z]/', '-$0', $key));
                        $style .= "{$kebab}: {$val}; ";
                    }
                }

                // Detect if this is a Barcode Placeholder
                $isApptBarcode = ($el['type'] == 'text' && trim($el['content']) === '{a-barcode}');
                $isPatientBarcode = ($el['type'] == 'text' && trim($el['content']) === '{p-barcode}');
            @endphp

            <div class="print-element" style="{{ $style }}">
                <div style="width: 100%; height: 100%;">

                    @if($isApptBarcode && !empty($barcodeData['a']))
                        {{-- Render Appointment Barcode SVG --}}
                        <svg class="barcode-gen" data-value="{{ $barcodeData['a'] }}" preserveAspectRatio="none"></svg>

                    @elseif($isPatientBarcode && !empty($barcodeData['p']))
                        {{-- Render Patient Barcode SVG --}}
                        <svg class="barcode-gen" data-value="{{ $barcodeData['p'] }}" preserveAspectRatio="none"></svg>

                    @elseif($el['type'] == 'text')
                        {{-- Standard Text --}}
                        {!! nl2br(e($el['content'])) !!}

                    @elseif($el['type'] == 'image')
                        {{-- Standard Image --}}
                        <img src="{{ $el['content'] }}" style="width: 100%; height: 100%; object-fit: cover;">
                    @endif

                </div>
            </div>
        @endforeach

    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            document.querySelectorAll('.barcode-gen').forEach(function (svg) {
                var rawCode = svg.getAttribute('data-value'); // Contains ~|a123|~

                if (rawCode) {
                    // Clean the text for human display (Remove ~| and |~)
                    var cleanText = rawCode.replace('~|', '').replace('|~', '');

                    JsBarcode(svg, rawCode, {
                        format: "CODE128",
                        lineColor: "#000",
                        width: 2,
                        height: 50,         // Reduced height slightly for better fit
                        displayValue: true, // Show the text
                        text: cleanText,    // <--- OVERRIDE: Show 'a123' instead of '~|a123|~'
                        fontSize: 14,
                        margin: 0
                    });

                    svg.setAttribute('width', '100%');
                    svg.setAttribute('height', '100%');
                }
            });

            // window.print();
        });
    </script>

</body>

</html>