{{-- Farebná paleta – vkladá sa do formulára piesne --}}
<div x-data="colorPalette()" x-init="load()" class="space-y-3">

    {{-- Color picker + aktuálna farba --}}
    <div class="flex items-center gap-3">
        <input type="color" name="color" id="color-input"
               value="{{ $currentColor ?? '#6366f1' }}"
               class="h-10 w-16 rounded border border-slate-300 cursor-pointer"
               x-model="current">
        <span class="text-sm text-slate-500">Klikni pre výber farby</span>
    </div>

    {{-- Uložené farby --}}
    <div>
        <p class="text-xs font-medium text-slate-500 uppercase tracking-wide mb-1.5">Moje farby</p>
        <div class="flex flex-wrap gap-2" id="palette-swatches">
            <template x-if="colors.length === 0">
                <span class="text-xs text-slate-400 italic">Zatiaľ žiadne uložené farby</span>
            </template>
            <template x-for="c in colors" :key="c.id">
                <div class="group relative">
                    <button type="button"
                            @click="pickColor(c.hex_code)"
                            :style="'background-color:' + c.hex_code"
                            :title="c.name"
                            class="w-8 h-8 rounded-lg border-2 border-white shadow hover:scale-110 transition-transform"
                            :class="current === c.hex_code ? 'ring-2 ring-offset-1 ring-slate-600' : ''">
                    </button>
                    <span class="absolute -bottom-5 left-1/2 -translate-x-1/2 text-[10px] text-slate-500 whitespace-nowrap hidden group-hover:block">
                        <span x-text="c.name"></span>
                    </span>
                    <button type="button"
                            @click="deleteColor(c.id)"
                            class="absolute -top-1.5 -right-1.5 w-4 h-4 bg-red-500 text-white text-[10px] rounded-full hidden group-hover:flex items-center justify-center leading-none">
                        ×
                    </button>
                </div>
            </template>
        </div>
    </div>

    {{-- Uložiť aktuálnu farbu --}}
    <div class="flex gap-2 items-center pt-1">
        <input type="text" x-model="newName" placeholder="Názov farby"
               class="border border-slate-300 rounded-lg px-2.5 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 flex-1">
        <button type="button" @click="saveColor()"
                class="px-3 py-1.5 bg-slate-700 hover:bg-slate-600 text-white text-sm rounded-lg transition-colors whitespace-nowrap">
            Uložiť farbu
        </button>
    </div>
    <p x-show="msg" x-text="msg" class="text-xs text-green-600"></p>
</div>

<script>
function colorPalette() {
    return {
        colors: [],
        current: document.getElementById('color-input')?.value ?? '#6366f1',
        newName: '',
        msg: '',

        async load() {
            const r = await fetch('/colors');
            this.colors = await r.json();
        },

        pickColor(hex) {
            this.current = hex;
            document.getElementById('color-input').value = hex;
        },

        async saveColor() {
            if (!this.newName.trim()) { this.msg = 'Zadaj nazov farby.'; return; }
            const r = await fetch('/colors', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
                body: JSON.stringify({ name: this.newName.trim(), hex_code: this.current })
            });
            const data = await r.json();
            this.colors.push(data);
            this.msg = 'Farba ulozena!';
            this.newName = '';
            setTimeout(() => this.msg = '', 2000);
        },

        async deleteColor(id) {
            await fetch('/colors/' + id, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content }
            });
            this.colors = this.colors.filter(c => c.id !== id);
        }
    }
}
</script>
