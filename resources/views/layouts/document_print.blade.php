<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $document->name }} - Print</title>
    <link href="{{ asset('css/editor.css') }}" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    @php
        $paper = $content['paper'] ?? [];
        $pWidth = $paper['w'] ?? 794;
        $pHeight = $paper['h'] ?? 1123;
        $bgImage = $content['bgImage'] ?? '';

        // Calculate Page Size for CSS (Convert px to mm for accuracy)
        // 96 DPI constant: 1 px = 0.264583 mm
        $mmW = isset($paper['mmW']) ? $paper['mmW'] : ($pWidth * 0.264583);
        $mmH = isset($paper['mmH']) ? $paper['mmH'] : ($pHeight * 0.264583);
        
        // Format for CSS: "210mm 297mm"
        $cssSize = round($mmW) . 'mm ' . round($mmH) . 'mm';
    @endphp

<style>
        /* --- SCREEN STYLES --- */
        body {
            background-color: #f3f4f6;
            display: block; 
            text-align: center;
            padding-top: 2rem;
            padding-bottom: 2rem;
            font-family: 'Inter', sans-serif;
            overflow-y: auto;
            margin: 0;
        }

        #page-container {
            background-color: white;
            position: relative;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
            background-size: 100% 100%;
            overflow: hidden; 
            display: inline-block;
            text-align: left;
            margin: 0 auto;
            
            /* FORCE GRAPHICS ON SCREEN */
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .print-element {
            position: absolute;
            outline: none; 
        }
        
        .print-element[contenteditable="true"]:hover {
            outline: 1px dashed #ccc;
            cursor: text;
        }

        /* Floating Actions */
        .fab-container {
            position: fixed;
            bottom: 30px;
            right: 30px;
            display: flex;
            flex-direction: column;
            gap: 10px;
            z-index: 9999;
        }

        .btn-circle {
            width: 60px; height: 60px; border-radius: 50%; border: none;
            background: #2563eb; color: white; font-size: 24px;
            cursor: pointer; box-shadow: 0 4px 6px rgba(0,0,0,0.2);
            transition: transform 0.2s;
        }
        .btn-circle:hover { transform: scale(1.1); }
        .btn-circle.sec { background: #64748b; width: 50px; height: 50px; font-size: 20px; }

        /* --- PRINT SPECIFICS --- */
        @media print {
            @page {
                /* 1. FORCE PAPER SIZE (A4, A5, etc) */
                size: {{ $cssSize }}; 
                
                /* 2. REMOVE BROWSER MARGINS */
                margin: 0; 
            }

            body {
                /* 3. FORCE BACKGROUND GRAPHICS (Colors & Images) */
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
                
                margin: 0 !important; 
                padding: 0 !important; 
                background: white !important; 
                width: 100%;
                height: auto !important; 
                overflow: visible !important; 
            }

            .no-print { 
                display: none !important; 
            }

            #page-container { 
                box-shadow: none !important; 
                margin: 0 !important; 
                border: none !important;
                position: absolute !important;
                top: 0;
                left: 0;
                width: {{ $pWidth }}px !important;
                height: {{ $pHeight }}px !important;
                page-break-after: avoid !important;
                
                /* Double-check graphics for the container */
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
        }
    </style>
</head>
<body>

    <div class="fab-container no-print">
        <button onclick="window.print()" class="btn-circle" title="Print Now">
            <i class="fa-solid fa-print"></i>
        </button>
        <button onclick="window.close()" class="btn-circle sec" title="Close">
            <i class="fa-solid fa-xmark"></i>
        </button>
    </div>

    <div id="page-container" style="width: {{ $pWidth }}px; height: {{ $pHeight }}px; background-image: url('{{ $bgImage }}');">
        
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
            @endphp

            <div class="print-element" style="{{ $style }}">
                <div style="width: 100%; height: 100%;" 
                     @if($el['type'] == 'text') contenteditable="true" spellcheck="false" @endif>
                    
                    @if($el['type'] == 'text')
                        {!! nl2br(e($el['content'])) !!}
                    @elseif($el['type'] == 'image')
                        <img src="{{ $el['content'] }}" style="width: 100%; height: 100%; object-fit: fill;">
                    @elseif($el['type'] == 'shape')
                         @endif
                </div>
            </div>
        @endforeach
    </div>

</body>
</html>