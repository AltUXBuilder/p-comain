{{--
    Signature Canvas Component
    Usage: <x-signature-canvas input-id="sig-override-input" />

    Props:
      input-id (string) — ID of the hidden input to write the base64 PNG into
      height   (string) — canvas height in px (default: 160)
--}}
@props(['inputId' => 'signature-data', 'height' => '160'])

<div
    x-data="signatureCanvas('{{ $inputId }}')"
    class="space-y-3"
>
    <div class="relative rounded-xl border-2 border-dashed border-plum-200 bg-white overflow-hidden"
         style="height: {{ $height }}px;">

        <canvas
            x-ref="canvas"
            @mousedown="startDraw($event)"
            @mousemove="draw($event)"
            @mouseup="stopDraw"
            @mouseleave="stopDraw"
            @touchstart.prevent="startDraw($event.touches[0])"
            @touchmove.prevent="draw($event.touches[0])"
            @touchend="stopDraw"
            class="block w-full cursor-crosshair touch-none"
            style="height: {{ $height }}px;"
        ></canvas>

        {{-- Placeholder text when empty --}}
        <p
            x-show="isEmpty"
            class="pointer-events-none absolute inset-0 flex items-center justify-center text-sm text-plum-300 select-none"
        >
            Draw your signature here
        </p>

    </div>

    <div class="flex items-center justify-between">
        <button
            type="button"
            @click="clear()"
            class="rounded-lg border border-plum-200 px-3 py-1.5 text-xs font-medium text-plum-500 hover:bg-plum-50"
        >
            Clear
        </button>
        <p x-show="!isEmpty" class="text-xs text-green-600 font-medium" x-cloak>✓ Signature captured</p>
    </div>
</div>

@once
@push('scripts')
<script>
function signatureCanvas(inputId) {
    return {
        drawing: false,
        isEmpty: true,
        lastX: 0,
        lastY: 0,
        ctx: null,

        init() {
            const canvas = this.$refs.canvas;
            // Scale for device pixel ratio
            const dpr = window.devicePixelRatio || 1;
            const rect = canvas.parentElement.getBoundingClientRect();
            canvas.width  = rect.width  * dpr;
            canvas.height = parseInt('{{ $height }}') * dpr;
            canvas.style.width  = rect.width  + 'px';
            canvas.style.height = '{{ $height }}px';

            this.ctx = canvas.getContext('2d');
            this.ctx.scale(dpr, dpr);
            this.ctx.strokeStyle = '#4A3050';
            this.ctx.lineWidth   = 2;
            this.ctx.lineCap     = 'round';
            this.ctx.lineJoin    = 'round';
        },

        getPos(e) {
            const rect = this.$refs.canvas.getBoundingClientRect();
            return {
                x: e.clientX - rect.left,
                y: e.clientY - rect.top,
            };
        },

        startDraw(e) {
            this.drawing = true;
            const pos = this.getPos(e);
            this.lastX = pos.x;
            this.lastY = pos.y;
            this.ctx.beginPath();
            this.ctx.moveTo(pos.x, pos.y);
        },

        draw(e) {
            if (!this.drawing) return;
            const pos = this.getPos(e);
            this.ctx.lineTo(pos.x, pos.y);
            this.ctx.stroke();
            this.lastX = pos.x;
            this.lastY = pos.y;
            this.isEmpty = false;
            this.capture();
        },

        stopDraw() {
            this.drawing = false;
        },

        clear() {
            const canvas = this.$refs.canvas;
            this.ctx.clearRect(0, 0, canvas.width, canvas.height);
            this.isEmpty = true;
            const input = document.getElementById(inputId);
            if (input) input.value = '';
        },

        capture() {
            const dataUrl = this.$refs.canvas.toDataURL('image/png');
            const input   = document.getElementById(inputId);
            if (input) input.value = dataUrl;
        }
    }
}
</script>
@endpush
@endonce
