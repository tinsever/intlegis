<?php
/** @var array $links */
?>
<div x-data="{ 
    links: <?= htmlspecialchars(json_encode($links ?? [])) ?>,
    addLink() {
        this.links.push({ url: '', label: '' });
    },
    removeLink(index) {
        this.links.splice(index, 1);
    }
}" class="space-y-4">
    <div class="flex justify-between items-center">
        <label class="block text-sm font-medium text-gray-700">Externe Links</label>
        <button type="button" @click="addLink()" class="text-xs font-black uppercase tracking-widest text-brand-primary hover:opacity-80 flex items-center gap-1">
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            Link hinzufügen
        </button>
    </div>

    <template x-for="(link, index) in links" :key="index">
        <div class="grid grid-cols-1 md:grid-cols-7 gap-4 items-end bg-gray-50 p-3 rounded-lg border border-gray-100">
            <div class="md:col-span-3">
                <label class="block text-[10px] font-black uppercase tracking-widest text-gray-400 mb-1">URL</label>
                <input type="url" :name="'links['+index+'][url]'" x-model="link.url" required class="form-input !mt-0" placeholder="https://...">
            </div>
            <div class="md:col-span-3">
                <label class="block text-[10px] font-black uppercase tracking-widest text-gray-400 mb-1">Bezeichnung (Optional)</label>
                <input type="text" :name="'links['+index+'][label]'" x-model="link.label" class="form-input !mt-0" placeholder="z.B. Offizieller Text">
            </div>
            <div class="md:col-span-1">
                <button type="button" @click="removeLink(index)" class="w-full btn btn-danger !py-2 justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                </button>
            </div>
        </div>
    </template>

    <div x-show="links.length === 0" class="text-center py-4 border-2 border-dashed border-gray-100 rounded-lg">
        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Keine Links hinzugefügt</p>
    </div>
</div>

